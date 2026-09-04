<?php

namespace Tests\Feature;

use App\Models\CustomerMapping;
use App\Models\LiveStream;
use App\Models\MessengerMessage;
use App\Models\MessengerOrderLink;
use App\Models\MessengerReplyDraft;
use App\Models\Order;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessengerOrderOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_attach_order_to_mapping(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('attachuser', 'psid-attach-1');
        $order = $this->createOrder($mapping->shop_id, 'attachuser', 'ORD-ATTACH-001');

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.orders.attach', [$mapping->id, $order->id]), [
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
        ]);

        $response->assertRedirect();

        $link = MessengerOrderLink::first();
        $this->assertNotNull($link);
        $this->assertSame(MessengerOrderLink::STATUS_ATTACHED, $link->status);
        $this->assertSame($mapping->id, $link->customer_mapping_id);
        $this->assertSame($order->id, $link->order_id);
    }

    public function test_duplicate_attach_is_blocked(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('dupuser', 'psid-dup-1');
        $order = $this->createOrder($mapping->shop_id, 'dupuser', 'ORD-DUP-001');

        MessengerOrderLink::create([
            'customer_mapping_id' => $mapping->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        $response = $this->from(route('customers.messenger-mappings.show', $mapping->id))
            ->actingAs($user)
            ->post(route('customers.messenger-mappings.orders.attach', [$mapping->id, $order->id]));

        $response->assertRedirect(route('customers.messenger-mappings.show', $mapping->id));
        $response->assertSessionHasErrors('order_id');
        $this->assertSame(1, MessengerOrderLink::count());
    }

    public function test_admin_can_detach_attached_order(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('detachuser', 'psid-detach-1');
        $order = $this->createOrder($mapping->shop_id, 'detachuser', 'ORD-DETACH-001');

        $link = MessengerOrderLink::create([
            'customer_mapping_id' => $mapping->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-order-links.detach', $link->id));
        $response->assertRedirect();

        $link->refresh();
        $this->assertSame(MessengerOrderLink::STATUS_DETACHED, $link->status);
        $this->assertNotNull($link->detached_at);
    }

    public function test_admin_can_mark_order_needs_review(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('reviewuser', 'psid-review-1');
        $order = $this->createOrder($mapping->shop_id, 'reviewuser', 'ORD-REVIEW-001');

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.orders.review', [$mapping->id, $order->id]), [
            'matched_by' => 'tiktok_username',
            'confidence' => 'ambiguous',
        ]);

        $response->assertRedirect();

        $link = MessengerOrderLink::first();
        $this->assertNotNull($link);
        $this->assertSame(MessengerOrderLink::STATUS_NEEDS_REVIEW, $link->status);
    }

    public function test_message_detail_shows_mapping_and_candidates(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('messageuser', 'psid-message-1');
        $order = $this->createOrder($mapping->shop_id, 'messageuser', 'ORD-MSG-001');
        $message = MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => 'psid-message-1',
            'sender_name' => 'Message User',
            'message_text' => 'message with candidate',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'message with candidate']],
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.messages.show', $message->id));
        $response->assertOk();
        $response->assertSee('Matched Customer Mapping');
        $response->assertSee('Order Candidates');
        $response->assertSee($order->code);
        $response->assertSee('Attach order');
    }

    public function test_reply_draft_is_saved_as_draft_with_send_disabled_by_default(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('replyuser', 'psid-reply-1');

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.store', $mapping->id), [
            'draft_text' => 'hello dry run',
        ]);

        $response->assertRedirect();

        $draft = MessengerReplyDraft::first();
        $this->assertNotNull($draft);
        $this->assertSame(MessengerReplyDraft::STATUS_DRAFT, $draft->status);
        $this->assertFalse($draft->send_enabled);
        $this->assertSame('hello dry run', $draft->draft_text);
        $this->assertFalse((bool) config('facebook.send_enabled'));
    }

    protected function createConnectedMapping(string $username, string $psid): array
    {
        $user = User::create([
            'name' => 'Order Tester',
            'email' => 'order'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Order Shop',
            'slug' => 'order-shop-'.uniqid(),
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
            'sid' => 'ops-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        $mapping = CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => $username,
            'status' => CustomerMapping::STATUS_CONNECTED,
            'connected_source' => 'messenger_ref',
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => $psid,
            'connected_at' => now(),
        ]);

        return [$user, $mapping];
    }

    protected function createOrder(int $shopId, string $username, string $code): Order
    {
        return Order::create([
            'shop_id' => $shopId,
            'customer_name' => ucfirst($username),
            'customer_username' => $username,
            'code' => $code,
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'pending',
        ]);
    }
}
