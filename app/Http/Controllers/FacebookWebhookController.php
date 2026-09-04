<?php

namespace App\Http\Controllers;

use App\Models\CustomerMapping;
use App\Models\MessengerMessage;
use App\Models\MessengerSetting;
use App\Models\PortalSession;
use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    protected const FALLBACK_WINDOW_MINUTES = 10;

    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        if ($mode === 'subscribe' && $token === config('facebook.verify_token')) {
            return response($challenge, 200);
        }

        return response('Invalid verify token', 403);
    }

    public function handle(Request $request)
    {
        $payload = $request->all();
        Log::info('facebook_webhook_received', ['payload' => $payload]);

        foreach ($payload['entry'] ?? [] as $entry) {
            $pageId = $entry['id'] ?? null;
            $setting = MessengerSetting::where('fb_page_id', $pageId)->where('is_active', true)->first();

            if (! $setting) {
                continue;
            }

            foreach ($entry['messaging'] ?? [] as $messageEvent) {
                $psid = data_get($messageEvent, 'sender.id');
                if (! $psid) {
                    continue;
                }

                $ref = data_get($messageEvent, 'referral.ref')
                    ?? data_get($messageEvent, 'postback.referral.ref')
                    ?? data_get($messageEvent, 'postback.payload');

                if ($ref) {
                    $this->bindReferral($setting->shop_id, $pageId, $psid, $ref, $messageEvent);
                } else {
                    Log::info('referral_ref_missing', [
                        'shop_id' => $setting->shop_id,
                        'page_id' => $pageId,
                        'psid' => $psid,
                        'message_mid' => data_get($messageEvent, 'message.mid'),
                    ]);

                    $this->attemptFallbackMapping($setting->shop_id, $pageId, $psid, $messageEvent);
                }

                $text = data_get($messageEvent, 'message.text');
                if ($text) {
                    MessengerMessage::create([
                        'shop_id' => $setting->shop_id,
                        'page_id' => $pageId,
                        'psid' => $psid,
                        'sender_name' => data_get($messageEvent, 'sender.name'),
                        'message_text' => $text,
                        'direction' => 'inbound',
                        'payload' => $messageEvent,
                        'sent_at' => now(),
                    ]);

                    $this->touchCustomerMapping($setting->shop_id, $psid, data_get($messageEvent, 'sender.name'));
                }
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function bindReferral(int $shopId, ?string $pageId, string $psid, string $ref, array $messageEvent): void
    {
        $sid = trim((string) $ref);
        if ($sid === '') {
            return;
        }

        $session = PortalSession::where('sid', $sid)
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->first();

        if (! $session) {
            return;
        }

        $mapping = CustomerMapping::where('portal_session_id', $session->id)
            ->where(function ($query) use ($psid) {
                $query->where('facebook_psid', $psid)
                    ->orWhereNull('facebook_psid');
            })
            ->orderByRaw('CASE WHEN facebook_psid = ? THEN 0 WHEN facebook_psid IS NULL THEN 1 ELSE 2 END', [$psid])
            ->orderByDesc('id')
            ->first() ?? new CustomerMapping([
                'portal_session_id' => $session->id,
                'shop_id' => $session->shop_id,
                'live_stream_id' => $session->live_stream_id,
            ]);

        $wasNew = ! $mapping->exists;
        $mapping->fill([
            'shop_id' => $session->shop_id,
            'live_stream_id' => $session->live_stream_id,
            'facebook_page_id' => $pageId,
            'facebook_psid' => $psid,
            'facebook_name' => data_get($messageEvent, 'sender.name'),
            'messenger_ref' => $sid,
            'connected_source' => 'messenger_ref',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'messenger_link_pending_at' => null,
            'connected_at' => $mapping->connected_at ?: now(),
            'last_seen_at' => now(),
        ]);
        $mapping->save();

        if ($wasNew) {
            $session->increment('connected_count');
        }

        $pageToken = $session->shop?->messengerSetting?->fb_page_token;
        if ($pageToken) {
            try {
                app(FacebookService::class)->sendPageMessage(
                    $pageToken,
                    $psid,
                    "เชื่อมต่อสำเร็จ\nTiktok ของคุณคือ " . ($mapping->tiktok_username ?: 'บัญชีนี้') . "\nลูกค้าสามารถ CF สินค้าได้เลยนะคะ"
                );
            } catch (\Throwable $e) {
                Log::warning('Messenger referral reply failed: ' . $e->getMessage());
            }
        }
    }

    protected function attemptFallbackMapping(int $shopId, ?string $pageId, string $psid, array $messageEvent): void
    {
        $cutoff = now()->subMinutes(self::FALLBACK_WINDOW_MINUTES);
        $activePortalSessionIds = PortalSession::where('shop_id', $shopId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->pluck('id');

        $candidates = CustomerMapping::with('portalSession.shop.messengerSetting')
            ->where('shop_id', $shopId)
            ->whereIn('portal_session_id', $activePortalSessionIds)
            ->where('status', CustomerMapping::STATUS_PENDING_MESSENGER)
            ->whereNull('facebook_psid')
            ->whereNotNull('messenger_link_pending_at')
            ->where('messenger_link_pending_at', '>=', $cutoff)
            ->orderByDesc('messenger_link_pending_at')
            ->get();

        if ($candidates->isEmpty()) {
            Log::info('fallback_mapping_no_candidate', [
                'shop_id' => $shopId,
                'page_id' => $pageId,
                'psid' => $psid,
                'window_minutes' => self::FALLBACK_WINDOW_MINUTES,
            ]);

            return;
        }

        if ($candidates->count() > 1) {
            CustomerMapping::whereIn('id', $candidates->pluck('id'))
                ->update([
                    'status' => CustomerMapping::STATUS_AMBIGUOUS,
                    'updated_at' => now(),
                ]);

            Log::warning('fallback_mapping_ambiguous', [
                'shop_id' => $shopId,
                'page_id' => $pageId,
                'psid' => $psid,
                'window_minutes' => self::FALLBACK_WINDOW_MINUTES,
                'candidate_ids' => $candidates->pluck('id')->all(),
                'portal_session_ids' => $candidates->pluck('portal_session_id')->all(),
            ]);

            return;
        }

        $mapping = $candidates->first();

        Log::info('fallback_mapping_candidate_found', [
            'shop_id' => $shopId,
            'page_id' => $pageId,
            'psid' => $psid,
            'customer_mapping_id' => $mapping->id,
            'portal_session_id' => $mapping->portal_session_id,
        ]);

        $mapping->fill([
            'facebook_page_id' => $pageId,
            'facebook_psid' => $psid,
            'facebook_name' => data_get($messageEvent, 'sender.name'),
            'connected_source' => 'fallback_recent_portal_connect',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'messenger_link_pending_at' => null,
            'connected_at' => $mapping->connected_at ?: now(),
            'last_seen_at' => now(),
        ]);
        $mapping->save();

        Log::info('fallback_mapping_success', [
            'shop_id' => $shopId,
            'page_id' => $pageId,
            'psid' => $psid,
            'customer_mapping_id' => $mapping->id,
            'portal_session_id' => $mapping->portal_session_id,
        ]);

        $pageToken = $mapping->portalSession?->shop?->messengerSetting?->fb_page_token;
        if ($pageToken) {
            try {
                app(FacebookService::class)->sendPageMessage(
                    $pageToken,
                    $psid,
                    "เชื่อมต่อสำเร็จ\nTiktok ของคุณคือ " . ($mapping->tiktok_username ?: 'บัญชีนี้') . "\nลูกค้าสามารถ CF สินค้าได้เลยนะคะ"
                );
            } catch (\Throwable $e) {
                Log::warning('Messenger fallback reply failed: ' . $e->getMessage());
            }
        }
    }

    protected function touchCustomerMapping(int $shopId, string $psid, ?string $senderName = null): void
    {
        $mapping = CustomerMapping::where('shop_id', $shopId)
            ->where('facebook_psid', $psid)
            ->latest('id')
            ->first();

        if (! $mapping) {
            return;
        }

        $mapping->update([
            'facebook_name' => $senderName ?: $mapping->facebook_name,
            'last_seen_at' => now(),
        ]);
    }
}
