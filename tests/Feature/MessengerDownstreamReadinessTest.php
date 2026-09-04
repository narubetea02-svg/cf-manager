<?php

namespace Tests\Feature;

use App\Models\CustomerMapping;
use App\Models\LiveStream;
use App\Models\MessengerMessage;
use App\Models\Order;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessengerDownstreamReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_connected_mapping_with_exact_order_match_is_ready(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => 'psid-ready-1',
            'facebook_page_id' => '103832441332425',
            'tiktok_username' => 'portalverifyuser',
        ]);

        Order::create([
            'shop_id' => $mapping->shop_id,
            'customer_name' => 'Portal Verify',
            'customer_username' => 'portalverifyuser',
            'code' => 'ORD-READY-001',
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'pending',
        ]);

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'psid-ready-1',
            'sender_name' => 'Portal Verify',
            'message_text' => 'ready message',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'ready message']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertOk();
        $response->assertSee('Ready');
        $response->assertSee('ORD-READY-001');
        $response->assertSee('tiktok_username');
    }

    public function test_connected_mapping_without_order_candidate_is_not_ready(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => 'psid-no-order',
            'facebook_page_id' => '103832441332425',
            'tiktok_username' => 'noorderuser',
        ]);

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'psid-no-order',
            'sender_name' => 'No Order User',
            'message_text' => 'message but no order',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'message but no order']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertOk();
        $response->assertSee('Not ready');
        $response->assertSee('no exact order candidate');
    }

    public function test_connected_mapping_with_multiple_order_candidates_needs_review(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => 'psid-multi-order',
            'facebook_page_id' => '103832441332425',
            'tiktok_username' => 'multiuser',
        ]);

        foreach ([1, 2] as $idx) {
            Order::create([
                'shop_id' => $mapping->shop_id,
                'customer_name' => 'Multi User',
                'customer_username' => 'multiuser',
                'code' => 'ORD-MULTI-00' . $idx,
                'quantity' => 1,
                'total_price' => 100,
                'status' => 'pending',
            ]);
        }

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'psid-multi-order',
            'sender_name' => 'Multi User',
            'message_text' => 'multiple order candidates',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'multiple order candidates']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertOk();
        $response->assertSee('Needs review');
        $response->assertSee('multiple order candidates');
    }

    public function test_pending_mapping_is_not_ready_even_if_order_exists(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'facebook_psid' => null,
            'tiktok_username' => 'pendinguser',
        ]);

        Order::create([
            'shop_id' => $mapping->shop_id,
            'customer_name' => 'Pending User',
            'customer_username' => 'pendinguser',
            'code' => 'ORD-PENDING-001',
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertOk();
        $response->assertSee('Not ready');
        $response->assertSee('mapping not connected');
    }

    public function test_readiness_list_displays_statuses_and_does_not_update_orders(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => 'psid-readiness-list',
            'facebook_page_id' => '103832441332425',
            'tiktok_username' => 'listuser',
        ]);

        $order = Order::create([
            'shop_id' => $mapping->shop_id,
            'customer_name' => 'List User',
            'customer_username' => 'listuser',
            'code' => 'ORD-LIST-001',
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'pending',
        ]);

        $originalUpdatedAt = $order->updated_at;

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'psid-readiness-list',
            'sender_name' => 'List User',
            'message_text' => 'list message',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'list message']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.readiness'));
        $response->assertOk();
        $response->assertSee('CF Manager Readiness Dashboard');
        $response->assertSee('Ready');
        $response->assertSee('list message');

        $order->refresh();
        $this->assertSame($originalUpdatedAt?->toDateTimeString(), $order->updated_at?->toDateTimeString());
    }

    protected function createMapping(array $overrides = []): array
    {
        $user = User::create([
            'name' => 'Readiness Tester',
            'email' => 'readiness'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Readiness Shop',
            'slug' => 'readiness-shop-'.uniqid(),
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
            'sid' => 'readiness-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        $mapping = CustomerMapping::create(array_merge([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'readinessuser',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
        ], $overrides));

        return [$user, $mapping];
    }
}
