<?php

namespace Tests\Feature;

use App\Models\CustomerMapping;
use App\Models\LiveStream;
use App\Models\MessengerSetting;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacebookWebhookFallbackMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_fallback_maps_when_exactly_one_pending_candidate_exists(): void
    {
        [$shop, $mapping] = $this->createPendingMapping(now()->subMinutes(2));

        MessengerSetting::create([
            'shop_id' => $shop->id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token',
            'is_active' => true,
        ]);

        $response = $this->postJson('/webhooks/facebook/messenger', $this->webhookPayload('3729781057088459', 'hello fallback'));

        $response->assertOk();

        $mapping->refresh();

        $this->assertSame('3729781057088459', $mapping->facebook_psid);
        $this->assertSame('103832441332425', $mapping->facebook_page_id);
        $this->assertSame('fallback_recent_portal_connect', $mapping->connected_source);
        $this->assertSame('connected', $mapping->status);
        $this->assertNull($mapping->messenger_link_pending_at);

        $this->assertDatabaseHas('messenger_messages', [
            'shop_id' => $shop->id,
            'page_id' => '103832441332425',
            'psid' => '3729781057088459',
            'message_text' => 'hello fallback',
        ]);
    }

    public function test_it_does_not_fallback_map_when_multiple_pending_candidates_exist(): void
    {
        $shop = $this->createShop();

        MessengerSetting::create([
            'shop_id' => $shop->id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token',
            'is_active' => true,
        ]);

        $this->createPendingMapping(now()->subMinutes(2), $shop, 'userone');
        $this->createPendingMapping(now()->subMinutes(1), $shop, 'usertwo');

        $response = $this->postJson('/webhooks/facebook/messenger', $this->webhookPayload('3729781057088459', 'ambiguous fallback'));

        $response->assertOk();

        $this->assertSame(0, CustomerMapping::where('shop_id', $shop->id)->where('facebook_psid', '3729781057088459')->count());
        $this->assertSame(2, CustomerMapping::where('shop_id', $shop->id)->where('status', CustomerMapping::STATUS_AMBIGUOUS)->count());
    }

    public function test_it_does_not_fallback_map_when_pending_candidate_is_expired(): void
    {
        [$shop, $mapping] = $this->createPendingMapping(now()->subMinutes(11));

        MessengerSetting::create([
            'shop_id' => $shop->id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token',
            'is_active' => true,
        ]);

        $response = $this->postJson('/webhooks/facebook/messenger', $this->webhookPayload('3729781057088459', 'expired fallback'));

        $response->assertOk();

        $mapping->refresh();

        $this->assertNull($mapping->facebook_psid);
        $this->assertSame(CustomerMapping::STATUS_PENDING_MESSENGER, $mapping->status);
        $this->assertSame(CustomerMapping::STATUS_EXPIRED, $mapping->effectiveStatus());
    }

    public function test_it_keeps_referral_flow_as_primary_when_ref_is_present(): void
    {
        [$shop, $mapping] = $this->createPendingMapping(now()->subMinutes(2));

        MessengerSetting::create([
            'shop_id' => $shop->id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token',
            'is_active' => true,
        ]);

        $payload = $this->webhookPayload('3729781057088459', 'referral first');
        $payload['entry'][0]['messaging'][0]['referral'] = ['ref' => $mapping->portalSession->sid];

        $response = $this->postJson('/webhooks/facebook/messenger', $payload);

        $response->assertOk();

        $mapping->refresh();

        $this->assertSame('3729781057088459', $mapping->facebook_psid);
        $this->assertSame('messenger_ref', $mapping->connected_source);
        $this->assertSame(CustomerMapping::STATUS_CONNECTED, $mapping->status);
    }

    protected function createPendingMapping($pendingAt, ?Shop $shop = null, string $username = 'bestlivetester'): array
    {
        $shop = $shop ?: $this->createShop();
        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => 'tiktok',
            'live_url' => 'https://tiktok.test/live',
            'status' => 'active',
        ]);

        $session = PortalSession::create([
            'shop_id' => $shop->id,
            'live_stream_id' => $stream->id,
            'sid' => 'sid-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        $mapping = CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => $username,
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'connected_at' => now(),
            'last_seen_at' => now(),
            'messenger_link_pending_at' => $pendingAt,
        ]);

        return [$shop, $mapping];
    }

    protected function createShop(): Shop
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        return Shop::create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop-'.uniqid(),
            'is_active' => true,
        ]);
    }

    protected function webhookPayload(string $psid, string $text): array
    {
        return [
            'object' => 'page',
            'entry' => [[
                'time' => now()->getTimestampMs(),
                'id' => '103832441332425',
                'messaging' => [[
                    'sender' => [
                        'id' => $psid,
                        'name' => 'Narubet Wayuseng',
                    ],
                    'recipient' => [
                        'id' => '103832441332425',
                    ],
                    'timestamp' => now()->getTimestampMs(),
                    'message' => [
                        'mid' => 'm_'.uniqid(),
                        'text' => $text,
                    ],
                ]],
            ]],
        ];
    }
}
