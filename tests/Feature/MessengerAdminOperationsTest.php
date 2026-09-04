<?php

namespace Tests\Feature;

use App\Models\CustomerMapping;
use App\Models\LiveStream;
use App\Models\MessengerMessage;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MessengerAdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reopen_ambiguous_mapping_as_pending(): void
    {
        [$user, $mapping] = $this->createMapping(['status' => CustomerMapping::STATUS_AMBIGUOUS]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.action', $mapping->id), [
            'action' => 'reopen_pending',
        ]);

        $response->assertRedirect();
        $mapping->refresh();

        $this->assertSame(CustomerMapping::STATUS_PENDING_MESSENGER, $mapping->status);
        $this->assertSame('admin_reopen_pending', $mapping->connected_source);
        $this->assertNotNull($mapping->messenger_link_pending_at);
    }

    public function test_admin_can_mark_mapping_as_expired(): void
    {
        [$user, $mapping] = $this->createMapping(['status' => CustomerMapping::STATUS_PENDING_MESSENGER]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.action', $mapping->id), [
            'action' => 'mark_expired',
        ]);

        $response->assertRedirect();
        $mapping->refresh();

        $this->assertSame(CustomerMapping::STATUS_EXPIRED, $mapping->status);
    }

    public function test_admin_can_clear_psid_and_reset_mapping(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088459',
            'connected_source' => 'fallback_recent_portal_connect',
            'connected_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.action', $mapping->id), [
            'action' => 'clear_psid_reset',
        ]);

        $response->assertRedirect();
        $mapping->refresh();

        $this->assertNull($mapping->facebook_psid);
        $this->assertNull($mapping->facebook_page_id);
        $this->assertNull($mapping->connected_at);
        $this->assertSame(CustomerMapping::STATUS_PENDING_MESSENGER, $mapping->status);
        $this->assertSame('admin_clear_psid_reset', $mapping->connected_source);
    }

    public function test_message_list_and_detail_show_matched_mapping(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088459',
        ]);

        $message = MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => '3729781057088459',
            'sender_name' => 'Narubet',
            'message_text' => 'hello from messenger',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'hello from messenger']],
            'sent_at' => now(),
        ]);

        $list = $this->actingAs($user)->get(route('customers.messenger.messages'));
        $list->assertOk();
        $list->assertSee('hello from messenger');
        $list->assertSee('mapped');
        $list->assertSee('@'.$mapping->tiktok_username);

        $detail = $this->actingAs($user)->get(route('customers.messenger.messages.show', $message->id));
        $detail->assertOk();
        $detail->assertSee('hello from messenger');
        $detail->assertSee('Raw Payload');
        $detail->assertSee('Matched Customer Mapping');
        $detail->assertSee((string) $mapping->portal_session_id);
        $detail->assertSee('เปิด Mapping Detail');
    }

    public function test_unmapped_message_is_not_matched_in_list(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => '111',
        ]);

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => '999',
            'sender_name' => 'Unknown',
            'message_text' => 'unmapped message',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'unmapped message']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.messages', ['match' => 'unmapped']));
        $response->assertOk();
        $response->assertSee('unmapped message');
        $response->assertSee('unmapped');
    }

    public function test_mapping_detail_shows_latest_message_and_history(): void
    {
        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088459',
        ]);

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => '3729781057088459',
            'sender_name' => 'Narubet',
            'message_text' => 'latest message body',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'latest message body']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertOk();
        $response->assertSee('Mapping Info');
        $response->assertSee('latest message body');
        $response->assertSee((string) $mapping->id);
        $response->assertSee((string) $mapping->portal_session_id);
        $response->assertSee('Messenger real send is disabled.');
    }

    public function test_admin_can_manual_resolve_mapping_with_psid_and_log_action(): void
    {
        Log::spy();

        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'facebook_page_id' => null,
            'facebook_psid' => null,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.action', $mapping->id), [
            'action' => 'resolve_manually',
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => 'manual-psid-001',
            'facebook_name' => 'Manual User',
        ]);

        $response->assertRedirect(route('customers.messenger-mappings.show', $mapping->id));
        $mapping->refresh();

        $this->assertSame(CustomerMapping::STATUS_CONNECTED, $mapping->status);
        $this->assertSame('manual-psid-001', $mapping->facebook_psid);
        $this->assertSame('103832441332425', $mapping->facebook_page_id);
        $this->assertSame('admin_manual_resolve', $mapping->connected_source);
        $this->assertNotNull($mapping->connected_at);
        $this->assertNull($mapping->messenger_link_pending_at);

        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($mapping) {
            return $message === 'messenger_mapping_admin_action'
                && $context['mapping_id'] === $mapping->id
                && $context['action'] === 'resolve_manually';
        })->once();
    }

    public function test_admin_cannot_manual_resolve_with_duplicate_connected_psid(): void
    {
        [$user, $existing] = $this->createMapping([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_psid' => 'dup-psid-001',
            'facebook_page_id' => '103832441332425',
        ]);

        [, $target] = $this->createMapping([
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'facebook_psid' => null,
            'shop_id' => $existing->shop_id,
            'portal_session_id' => $existing->portal_session_id,
            'live_stream_id' => $existing->live_stream_id,
        ]);

        $response = $this->from(route('customers.messenger-mappings.show', $target->id))
            ->actingAs($user)
            ->post(route('customers.messenger-mappings.action', $target->id), [
                'action' => 'resolve_manually',
                'facebook_page_id' => '103832441332425',
                'facebook_psid' => 'dup-psid-001',
            ]);

        $response->assertRedirect(route('customers.messenger-mappings.show', $target->id));
        $response->assertSessionHasErrors('facebook_psid');

        $target->refresh();
        $this->assertNull($target->facebook_psid);
        $this->assertSame(CustomerMapping::STATUS_PENDING_MESSENGER, $target->status);
    }

    public function test_unmapped_message_detail_can_resolve_to_mapping(): void
    {
        Log::spy();

        [$user, $mapping] = $this->createMapping([
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'facebook_psid' => null,
            'facebook_page_id' => null,
        ]);

        $message = MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'resolve-from-message-psid',
            'sender_name' => 'Resolve Helper',
            'message_text' => 'please map me',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'please map me']],
            'sent_at' => now(),
        ]);

        $detail = $this->actingAs($user)->get(route('customers.messenger.messages.show', $message->id));
        $detail->assertOk();
        $detail->assertSee('Resolve to mapping');
        $detail->assertSee((string) $mapping->id);

        $response = $this->actingAs($user)->post(route('customers.messenger.messages.resolve', $message->id), [
            'mapping_id' => $mapping->id,
            'facebook_page_id' => '103832441332425',
            'facebook_name' => 'Resolved Name',
        ]);

        $response->assertRedirect(route('customers.messenger-mappings.show', $mapping->id));
        $mapping->refresh();

        $this->assertSame(CustomerMapping::STATUS_CONNECTED, $mapping->status);
        $this->assertSame('resolve-from-message-psid', $mapping->facebook_psid);
        $this->assertSame('admin_manual_resolve', $mapping->connected_source);

        Log::shouldHaveReceived('info')->withArgs(function ($messageName, $context) use ($mapping, $message) {
            return $messageName === 'messenger_mapping_admin_action'
                && $context['mapping_id'] === $mapping->id
                && $context['action'] === 'manual_resolve_from_message'
                && $context['message_id'] === $message->id;
        })->once();
    }

    protected function createMapping(array $overrides = []): array
    {
        $user = User::create([
            'name' => 'Admin Tester',
            'email' => 'admin'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Admin Shop',
            'slug' => 'admin-shop-'.uniqid(),
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
            'sid' => 'msg-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        $mapping = CustomerMapping::create(array_merge([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'admintester',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
        ], $overrides));

        return [$user, $mapping];
    }
}
