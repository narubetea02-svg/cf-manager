<?php

namespace App\Http\Controllers;

use App\Models\CustomerMapping;
use App\Models\PortalSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function index(Request $request)
    {
        $sid = $request->query('sid');

        if (!$sid) {
            return view('portal.error', ['message' => 'ไม่พบ Session ID']);
        }

        $session = PortalSession::with(['shop.messengerSetting', 'liveStream'])->where('sid', $sid)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$session) {
            return view('portal.error', ['message' => 'Session ID ไม่ถูกต้องหรือหมดอายุแล้ว']);
        }

        $messengerPageId = $session->shop?->messengerSetting?->fb_page_id;
        $messengerUrl = $messengerPageId ? "https://m.me/{$messengerPageId}?ref={$session->sid}" : null;
        $mapping = CustomerMapping::where('portal_session_id', $session->id)
            ->latest('id')
            ->first();
        $mappingStatus = $mapping?->effectiveStatus() ?? null;

        return view('portal.index', [
            'session' => $session,
            'mapping' => $mapping,
            'mappingStatus' => $mappingStatus,
            'messengerUrl' => $messengerUrl,
            'pageConfigured' => (bool) $messengerPageId,
        ]);
    }

    public function connect(Request $request)
    {
        $request->validate([
            'sid' => 'required|string',
            'tiktok_username' => 'required|string|max:100',
        ]);

        $session = PortalSession::with(['shop.messengerSetting', 'liveStream'])->where('sid', $request->sid)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$session) {
            return back()->withErrors(['message' => 'Session ID ไม่ถูกต้องหรือหมดอายุแล้ว']);
        }

        $normalizedUsername = $this->normalizeUsername($request->tiktok_username);

        $mapping = CustomerMapping::where('portal_session_id', $session->id)
            ->where(function ($query) use ($normalizedUsername) {
                $query->where('tiktok_username', $normalizedUsername)
                    ->orWhereNull('tiktok_username')
                    ->orWhere('tiktok_username', '');
            })
            ->orderByRaw("CASE WHEN tiktok_username = ? THEN 0 WHEN tiktok_username IS NULL OR tiktok_username = '' THEN 1 ELSE 2 END", [$normalizedUsername])
            ->orderByDesc('id')
            ->first();

        if (! $mapping) {
            $mapping = new CustomerMapping([
                'portal_session_id' => $session->id,
                'shop_id' => $session->shop_id,
                'live_stream_id' => $session->live_stream_id,
            ]);
        }

        $wasNew = ! $mapping->exists;
        $hasMessengerLinked = (bool) $mapping->facebook_psid;
        $mapping->fill([
            'shop_id' => $session->shop_id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => $normalizedUsername,
            'status' => $hasMessengerLinked ? CustomerMapping::STATUS_CONNECTED : CustomerMapping::STATUS_PENDING_MESSENGER,
            'messenger_link_pending_at' => $hasMessengerLinked ? null : now(),
            'connected_at' => $mapping->connected_at ?: now(),
            'last_seen_at' => now(),
            'connected_source' => $hasMessengerLinked
                ? ($mapping->connected_source ?: 'manual')
                : 'portal_connect_pending',
        ]);
        $mapping->save();

        if ($wasNew) {
            $session->increment('connected_count');
        }

        if ($mapping->connected_source !== 'messenger_ref') {
            $this->notifyMessenger($mapping, $session);
        }

        return redirect('/pt?sid=' . urlencode($session->sid))
            ->with('portal_status', 'saved');
    }

    protected function normalizeUsername(string $value): string
    {
        return strtolower(trim(str_replace('@', '', preg_replace('/\s+/', '', $value))));
    }

    protected function notifyMessenger(CustomerMapping $mapping, PortalSession $session): void
    {
        $pageToken = $session->shop?->messengerSetting?->fb_page_token;
        $pageId = $session->shop?->messengerSetting?->fb_page_id;

        if (! $pageToken || ! $pageId) {
            return;
        }

        if (! $mapping->facebook_psid) {
            return;
        }

        try {
            app(\App\Services\FacebookService::class)->sendPageMessage(
                $pageToken,
                $mapping->facebook_psid,
                "เชื่อมต่อสำเร็จ\nTiktok ของคุณคือ " . ($mapping->tiktok_username ?: 'บัญชีนี้') . "\nลูกค้าสามารถ CF สินค้าได้เลยนะคะ"
            );
        } catch (\Throwable $e) {
            Log::warning('Messenger notify failed: '.$e->getMessage());
        }
    }
}
