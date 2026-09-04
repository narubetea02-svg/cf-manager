<?php

namespace Tests\Feature;

use App\Models\AdminActionLog;
use App\Models\CustomerMapping;
use App\Models\LiveStream;
use App\Models\MessengerMessage;
use App\Models\MessengerOrderLink;
use App\Models\MessengerReplyDraft;
use App\Models\MessengerSetting;
use App\Models\Order;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use App\Services\FacebookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessengerConflictAndSendTest extends TestCase
{
    use RefreshDatabase;

    public function test_conflict_center_lists_order_conflict(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        $mappingA = $this->createMapping($shop, ['tiktok_username' => 'conflict-a', 'facebook_psid' => 'psid-a', 'status' => CustomerMapping::STATUS_CONNECTED]);
        $mappingB = $this->createMapping($shop, ['tiktok_username' => 'conflict-b', 'facebook_psid' => 'psid-b', 'status' => CustomerMapping::STATUS_CONNECTED]);
        $order = $this->createOrder($shop->id, 'conflict-a', 'ORD-CONFLICT-001');

        MessengerOrderLink::create([
            'customer_mapping_id' => $mappingA->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        MessengerOrderLink::create([
            'customer_mapping_id' => $mappingB->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.conflicts'));

        $response->assertOk();
        $response->assertSee('order_conflict');
        $response->assertSee('ORD-CONFLICT-001');
        $response->assertSee('Keep this link and detach others');
        $response->assertSee('@conflict-b');
    }

    public function test_keep_this_link_detach_others_detaches_other_attached_links(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        $mappingA = $this->createMapping($shop, ['tiktok_username' => 'keep-a', 'facebook_psid' => 'psid-keep-a', 'status' => CustomerMapping::STATUS_CONNECTED]);
        $mappingB = $this->createMapping($shop, ['tiktok_username' => 'keep-b', 'facebook_psid' => 'psid-keep-b', 'status' => CustomerMapping::STATUS_CONNECTED]);
        $order = $this->createOrder($shop->id, 'keep-a', 'ORD-KEEP-001');

        $primary = MessengerOrderLink::create([
            'customer_mapping_id' => $mappingA->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        $other = MessengerOrderLink::create([
            'customer_mapping_id' => $mappingB->id,
            'order_id' => $order->id,
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => 'tiktok_username',
            'confidence' => 'exact',
            'attached_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-order-links.action', $primary->id), [
            'action' => 'keep_primary_detach_others',
        ]);

        $response->assertRedirect();

        $primary->refresh();
        $other->refresh();

        $this->assertSame(MessengerOrderLink::STATUS_ATTACHED, $primary->status);
        $this->assertSame(MessengerOrderLink::STATUS_DETACHED, $other->status);
        $this->assertDatabaseHas('admin_action_logs', [
            'action' => 'keep_primary_detach_others',
            'target_id' => $primary->id,
        ]);
    }

    public function test_dry_run_action_updates_existing_draft(): void
    {
        [$user, $mapping] = $this->createConnectedMapping('dryrunuser', 'psid-dryrun-1');

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'hello draft',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => false,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'dry_run',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_DRY_RUN, $draft->status);
    }

    public function test_send_test_is_blocked_by_flag_when_send_is_disabled(): void
    {
        config()->set('facebook.send_enabled', false);
        config()->set('facebook.send_test_psid_allowlist', ['psid-flag-1']);

        [$user, $mapping] = $this->createConnectedMapping('blockedflaguser', 'psid-flag-1');

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'blocked by flag',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => false,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_BLOCKED_BY_FLAG, $draft->status);
        $this->assertSame('MESSENGER_SEND_ENABLED=false', $draft->failure_reason);
    }

    public function test_send_test_is_blocked_by_allowlist_when_psid_not_allowed(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['other-psid']);

        [$user, $mapping] = $this->createConnectedMapping('blockedallowuser', 'psid-allow-1');

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'blocked by allowlist',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_BLOCKED_BY_ALLOWLIST, $draft->status);
        $this->assertSame('psid_not_in_allowlist', $draft->failure_reason);
    }

    public function test_send_test_is_blocked_when_allowlist_is_empty(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', []);

        [$user, $mapping] = $this->createConnectedMapping('emptyallowuser', 'psid-empty-1');

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'blocked by empty allowlist',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_BLOCKED_BY_ALLOWLIST, $draft->status);
        $this->assertSame('psid_not_in_allowlist', $draft->failure_reason);
    }

    public function test_send_test_fails_safely_when_page_token_is_missing(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-missing-token-1']);

        [$user, $mapping] = $this->createConnectedMapping('missingtokenuser', 'psid-missing-token-1');

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'missing token',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_FAILED, $draft->status);
        $this->assertSame('missing_page_token', $draft->failure_reason);
    }

    public function test_send_test_succeeds_when_flag_allowlist_and_page_token_are_present(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-send-1']);

        $this->app->instance(FacebookService::class, new class extends FacebookService {
            public function sendPageMessageDetailed(string $pageToken, string $psid, string $text): array
            {
                return [
                    'ok' => $pageToken === 'page-token-1' && $psid === 'psid-send-1' && $text === 'hello send',
                    'status' => 200,
                    'response' => ['recipient_id' => $psid, 'message_id' => 'mid.test.1'],
                    'provider_message_id' => 'mid.test.1',
                ];
            }

            public function sendPageMessage(string $pageToken, string $psid, string $text): bool
            {
                return $pageToken === 'page-token-1' && $psid === 'psid-send-1' && $text === 'hello send';
            }
        });

        [$user, $mapping] = $this->createConnectedMapping('senduser', 'psid-send-1');

        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'page-token-1',
            'is_active' => true,
        ]);

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'hello send',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_SENT, $draft->status);
        $this->assertNotNull($draft->sent_at);
        $this->assertDatabaseHas('admin_action_logs', [
            'action' => 'messenger_reply_draft_send_test',
            'target_id' => $draft->id,
        ]);
    }

    public function test_send_control_page_renders_disabled_status_and_masks_allowlist_without_token_leak(): void
    {
        config()->set('facebook.send_enabled', false);
        config()->set('facebook.send_test_psid_allowlist', ['psid-secret-123456']);

        [$user, $mapping] = $this->createConnectedMapping('pilotcontroluser', 'psid-secret-123456');
        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'super-secret-page-token',
            'is_active' => true,
        ]);

        MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'summary draft',
            'status' => MessengerReplyDraft::STATUS_BLOCKED_BY_FLAG,
            'send_enabled' => false,
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.send-control'));

        $response->assertOk();
        $response->assertSee('Pilot Send Control');
        $response->assertSee('Real send disabled');
        $response->assertSee('psid****3456');
        $response->assertSee('blocked_by_flag');
        $response->assertDontSee('super-secret-page-token');
        $response->assertDontSee('psid-secret-123456');
    }

    public function test_send_control_marks_allowed_mapping_as_eligible_when_flag_allowlist_and_token_are_ready(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-eligible-1']);

        [$user, $mapping] = $this->createConnectedMapping('eligibleuser', 'psid-eligible-1');
        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token-ready',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.send-control'));

        $response->assertOk();
        $response->assertSee('Eligible for test send');
        $response->assertSee('available');
        $response->assertDontSee('token-ready');
    }

    public function test_send_control_marks_mapping_blocked_by_allowlist(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['different-psid']);

        [$user, $mapping] = $this->createConnectedMapping('notalloweduser', 'psid-not-allowed-1');
        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'token-ready',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger.send-control'));

        $response->assertOk();
        $response->assertSee('Blocked by allowlist');
        $response->assertDontSee('token-ready');
    }

    public function test_send_control_marks_missing_token_unavailable(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-no-token-1']);

        [$user] = $this->createConnectedMapping('notokenuser', 'psid-no-token-1');

        $response = $this->actingAs($user)->get(route('customers.messenger.send-control'));

        $response->assertOk();
        $response->assertSee('Send unavailable');
        $response->assertSee('missing token');
    }

    public function test_mapping_detail_shows_send_checklist_and_reply_draft_history(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-history-1']);

        [$user, $mapping] = $this->createConnectedMapping('historyuser', 'psid-history-1');
        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'history-token',
            'is_active' => true,
        ]);

        MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => 'history draft text',
            'status' => MessengerReplyDraft::STATUS_SENT,
            'send_enabled' => true,
            'sent_at' => now(),
            'response_payload' => ['provider_message_id' => 'mid.history.1'],
        ]);

        $response = $this->actingAs($user)->get(route('customers.messenger-mappings.show', $mapping->id));

        $response->assertOk();
        $response->assertSee('Safe Send Checklist');
        $response->assertSee('Eligible for test send');
        $response->assertSee('history draft text');
        $response->assertSee('mid.history.1');
        $response->assertSee('Message history');
        $response->assertDontSee('history-token');
    }

    public function test_send_test_fails_when_draft_message_is_empty(): void
    {
        config()->set('facebook.send_enabled', true);
        config()->set('facebook.send_test_psid_allowlist', ['psid-empty-message-1']);

        [$user, $mapping] = $this->createConnectedMapping('emptymessageuser', 'psid-empty-message-1');
        MessengerSetting::create([
            'shop_id' => $mapping->shop_id,
            'fb_page_id' => '103832441332425',
            'fb_page_token' => 'message-token',
            'is_active' => true,
        ]);

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => $user->id,
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => '   ',
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]), [
            'action' => 'send_test',
        ]);

        $response->assertRedirect();
        $draft->refresh();

        $this->assertSame(MessengerReplyDraft::STATUS_FAILED, $draft->status);
        $this->assertSame('empty_message', $draft->failure_reason);
    }

    protected function createConnectedMapping(string $username, string $psid): array
    {
        [$user, $shop] = $this->createUserAndShop();
        $mapping = $this->createMapping($shop, [
            'tiktok_username' => $username,
            'status' => CustomerMapping::STATUS_CONNECTED,
            'connected_source' => 'messenger_ref',
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => $psid,
            'connected_at' => now(),
        ]);

        MessengerMessage::create([
            'shop_id' => $mapping->shop_id,
            'page_id' => '103832441332425',
            'psid' => $psid,
            'sender_name' => ucfirst($username),
            'message_text' => 'latest hello',
            'direction' => 'inbound',
            'payload' => ['message' => ['text' => 'latest hello']],
            'sent_at' => now(),
        ]);

        return [$user, $mapping];
    }

    protected function createUserAndShop(): array
    {
        $user = User::create([
            'name' => 'Conflict Tester',
            'email' => 'conflict'.uniqid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Conflict Shop',
            'slug' => 'conflict-shop-'.uniqid(),
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    protected function createMapping(Shop $shop, array $overrides = []): CustomerMapping
    {
        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => 'tiktok',
            'live_url' => 'https://tiktok.test/live/' . uniqid(),
            'status' => 'active',
        ]);

        $session = PortalSession::create([
            'shop_id' => $shop->id,
            'live_stream_id' => $stream->id,
            'sid' => 'conflict-'.uniqid(),
            'is_active' => true,
            'connected_count' => 0,
            'expires_at' => now()->addHours(12),
        ]);

        return CustomerMapping::create(array_merge([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'conflictuser',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
        ], $overrides));
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
