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

class PortalMessengerFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_connect_creates_pending_messenger_mapping(): void
    {
        [$shop, $session] = $this->createPortalContext();

        MessengerSetting::create([
            'shop_id' => $shop->id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token',
            'is_active' => true,
        ]);

        $response = $this->post('/pt/connect', [
            'sid' => $session->sid,
            'tiktok_username' => '@FallBack Tester',
        ]);

        $response->assertRedirect('/pt?sid=' . urlencode($session->sid));

        $mapping = CustomerMapping::first();
        $this->assertNotNull($mapping);
        $this->assertSame('fallbacktester', $mapping->tiktok_username);
        $this->assertSame(CustomerMapping::STATUS_PENDING_MESSENGER, $mapping->status);
        $this->assertSame('portal_connect_pending', $mapping->connected_source);
        $this->assertNotNull($mapping->messenger_link_pending_at);
    }

    public function test_portal_connect_updates_existing_mapping_instead_of_creating_duplicate(): void
    {
        [$shop, $session] = $this->createPortalContext();

        $mapping = CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => '',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now()->subMinute(),
            'connected_at' => now()->subMinute(),
            'last_seen_at' => now()->subMinute(),
        ]);

        $response = $this->post('/pt/connect', [
            'sid' => $session->sid,
            'tiktok_username' => '@newname',
        ]);

        $response->assertRedirect('/pt?sid=' . urlencode($session->sid));

        $this->assertSame(1, CustomerMapping::count());
        $mapping->refresh();
        $this->assertSame('newname', $mapping->tiktok_username);
    }

    public function test_admin_customer_page_shows_mapping_statuses(): void
    {
        [$shop, $session] = $this->createPortalContext();
        $user = $shop->user;

        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => 'pendinguser',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
            'connected_at' => now(),
            'last_seen_at' => now(),
        ]);
        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => 'connecteduser',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'connected_source' => 'messenger_ref',
            'facebook_psid' => '123',
        ]);
        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => 'ambiguoususer',
            'status' => CustomerMapping::STATUS_AMBIGUOUS,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
        ]);
        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => 'expireduser',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now()->subMinutes(11),
        ]);

        $response = $this->actingAs($user)->get('/customers');

        $response->assertOk();
        $response->assertSee('Messenger Mapping Monitor');
        $response->assertSee('pending_messenger');
        $response->assertSee('connected');
        $response->assertSee('ambiguous');
        $response->assertSee('expired');
    }

    public function test_expire_pending_command_marks_stale_rows_as_expired(): void
    {
        [$shop, $session] = $this->createPortalContext();

        $mapping = CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $session->live_stream_id,
            'tiktok_username' => 'expireme',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now()->subMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('messenger:expire-pending')
            ->expectsOutput('Expired 1 pending messenger mapping(s).')
            ->assertExitCode(0);

        $mapping->refresh();
        $this->assertSame(CustomerMapping::STATUS_EXPIRED, $mapping->status);
    }

    protected function createPortalContext(): array
    {
        $user = User::create([
            'name' => 'Portal Tester',
            'email' => 'portal'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Portal Shop',
            'slug' => 'portal-shop-'.uniqid(),
            'is_active' => true,
        ]);

        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => 'tiktok',
            'live_url' => 'https://tiktok.test/live',
            'status' => 'active',
        ]);

        $session = PortalSession::create([
            'shop_id' => $shop->id,
            'live_stream_id' => $stream->id,
            'sid' => 'pt-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        return [$shop, $session];
    }
}
