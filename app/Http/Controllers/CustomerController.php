<?php

namespace App\Http\Controllers;

use App\Models\AdminActionLog;
use App\Models\CustomerMapping;
use App\Models\MessengerMessage;
use App\Models\MessengerOrderLink;
use App\Models\MessengerReplyDraft;
use App\Models\Order;
use App\Services\MessengerSendService;
use App\Support\CustomerMetrics;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    protected const DEFAULT_PAGE_ID = '103832441332425';

    public function index(Request $req)
    {
        $shopIds = $this->shopIds();

        $search = trim($req->string('q')->toString());
        $customerSegment = $req->string('customer_segment')->toString() ?: 'all';
        $hasPhone = $req->string('has_phone')->toString();
        $hasUsername = $req->string('has_username')->toString();
        $sort = $req->string('sort')->toString() ?: 'recent';

        $customerQuery = Order::whereIn('shop_id', $shopIds)
            ->selectRaw('customer_username, customer_name, customer_phone, COUNT(*) as order_count, SUM(total_price) as total_spent, MAX(id) as latest_order_id')
            ->groupBy('customer_username', 'customer_name', 'customer_phone');

        if ($search !== '') {
            $customerQuery->where(function ($query) use ($search) {
                $query->where('customer_username', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        if ($customerSegment === 'blocked') {
            $customerQuery->whereRaw('1 = 0');
        }

        if ($hasPhone === 'yes') {
            $customerQuery->whereNotNull('customer_phone')->where('customer_phone', '!=', '');
        } elseif ($hasPhone === 'no') {
            $customerQuery->where(function ($query) {
                $query->whereNull('customer_phone')->orWhere('customer_phone', '');
            });
        }

        if ($hasUsername === 'yes') {
            $customerQuery->whereNotNull('customer_username')->where('customer_username', '!=', '');
        } elseif ($hasUsername === 'no') {
            $customerQuery->where(function ($query) {
                $query->whereNull('customer_username')->orWhere('customer_username', '');
            });
        }

        if ($sort === 'spent_desc') {
            $customerQuery->orderByDesc('total_spent');
        } elseif ($sort === 'orders_desc') {
            $customerQuery->orderByDesc('order_count');
        } elseif ($sort === 'name_asc') {
            $customerQuery->orderByRaw('COALESCE(customer_name, customer_username, customer_phone) asc');
        } else {
            $customerQuery->orderByDesc('latest_order_id');
        }

        $customers = $customerQuery->paginate(20)->withQueryString();

        $summary = [
            'total' => $customers->total(),
            'with_phone' => Order::whereIn('shop_id', $shopIds)->whereNotNull('customer_phone')->distinct('customer_phone')->count('customer_phone'),
            'with_username' => Order::whereIn('shop_id', $shopIds)->whereNotNull('customer_username')->distinct('customer_username')->count('customer_username'),
        ];

        $statusFilter = $req->string('mapping_status')->toString();
        $now = now();

        $mappingsQuery = CustomerMapping::with(['portalSession', 'shop'])
            ->whereIn('shop_id', $shopIds)
            ->latest('updated_at');

        if ($statusFilter) {
            if ($statusFilter === CustomerMapping::STATUS_EXPIRED) {
                $mappingsQuery->where('status', CustomerMapping::STATUS_PENDING_MESSENGER)
                    ->whereNotNull('messenger_link_pending_at')
                    ->where('messenger_link_pending_at', '<', $now->copy()->subMinutes(CustomerMapping::PENDING_WINDOW_MINUTES));
            } else {
                $mappingsQuery->where('status', $statusFilter);
            }
        }

        $latestMessagesByPsid = MessengerMessage::whereIn('shop_id', $shopIds)
            ->whereNotNull('psid')
            ->latest('created_at')
            ->get()
            ->groupBy('psid')
            ->map(fn ($messages) => $messages->first());

        $messengerMappings = $mappingsQuery->paginate(15, ['*'], 'mappings_page')->through(function (CustomerMapping $mapping) use ($now, $latestMessagesByPsid) {
            $mapping->display_status = $mapping->effectiveStatus($now);
            $mapping->latest_message = $mapping->facebook_psid ? $latestMessagesByPsid->get($mapping->facebook_psid) : null;
            $mapping->conflicts = $this->realConflicts($this->conflictsForMapping($mapping));
            return $mapping;
        });

        $mappingSummary = [
            'pending' => CustomerMapping::whereIn('shop_id', $shopIds)
                ->where('status', CustomerMapping::STATUS_PENDING_MESSENGER)
                ->whereNotNull('messenger_link_pending_at')
                ->where('messenger_link_pending_at', '>=', $now->copy()->subMinutes(CustomerMapping::PENDING_WINDOW_MINUTES))
                ->count(),
            'connected_real_users' => CustomerMetrics::realConnectedMessengerUsers($shopIds),
            'connected_records' => CustomerMetrics::connectedMappingRecords($shopIds),
            'ambiguous' => CustomerMapping::whereIn('shop_id', $shopIds)
                ->where('status', CustomerMapping::STATUS_AMBIGUOUS)
                ->count(),
            'expired' => CustomerMapping::whereIn('shop_id', $shopIds)
                ->where('status', CustomerMapping::STATUS_PENDING_MESSENGER)
                ->whereNotNull('messenger_link_pending_at')
                ->where('messenger_link_pending_at', '<', $now->copy()->subMinutes(CustomerMapping::PENDING_WINDOW_MINUTES))
                ->count(),
        ];

        $conflictCount = CustomerMapping::whereIn('shop_id', $shopIds)
            ->get()
            ->sum(fn (CustomerMapping $mapping) => $this->realConflicts($this->conflictsForMapping($mapping))->count());

        return view('customers.index', compact(
            'customers',
            'summary',
            'messengerMappings',
            'mappingSummary',
            'statusFilter',
            'conflictCount',
            'search',
            'customerSegment',
            'hasPhone',
            'hasUsername',
            'sort'
        ));
    }

    public function messengerReadiness(Request $request)
    {
        $shopIds = $this->shopIds();
        $filter = $request->string('readiness')->toString();

        $items = CustomerMapping::with(['portalSession', 'shop', 'activeOrderLinks.order'])
            ->whereIn('shop_id', $shopIds)
            ->latest('updated_at')
            ->get()
            ->map(function (CustomerMapping $mapping) {
                $insights = $this->buildDownstreamInsights($mapping);
                $mapping->display_status = $mapping->effectiveStatus();
                $mapping->latest_message = $insights['latest_message'];
                $mapping->readiness = $insights['readiness'];
                $mapping->order_candidates = $insights['order_candidates'];
                $mapping->chat_candidates = $insights['chat_candidates'];
                $mapping->attached_order_links = $insights['attached_order_links'];
                $mapping->last_activity_at = $insights['last_activity_at'];
                $mapping->conflicts = $insights['conflicts'];
                return $mapping;
            });

        if ($filter) {
            $items = $items->filter(fn (CustomerMapping $mapping) => $this->matchesReadinessFilter($mapping, $filter))->values();
        }

        $mappings = $this->paginateCollection($items, 15, $request, 'page');

        return view('customers.messenger-readiness', [
            'mappings' => $mappings,
            'filter' => $filter,
        ]);
    }

    public function messengerConflicts(Request $request)
    {
        $shopIds = $this->shopIds();
        $typeFilter = $request->string('type')->toString();
        $statusFilter = $request->string('status')->toString();

        $items = CustomerMapping::with(['portalSession', 'shop', 'activeOrderLinks.order'])
            ->whereIn('shop_id', $shopIds)
            ->latest('updated_at')
            ->get()
            ->flatMap(function (CustomerMapping $mapping) {
                $insights = $this->buildDownstreamInsights($mapping);

                return $insights['conflicts']->map(function (array $conflict) use ($mapping, $insights) {
                    return (object) [
                        'mapping' => $mapping,
                        'conflict' => $conflict,
                        'display_status' => $mapping->effectiveStatus(),
                        'latest_message' => $insights['latest_message'],
                        'readiness' => $insights['readiness'],
                        'attached_order_links' => $insights['attached_order_links'],
                    ];
                });
            })
            ->filter(fn ($item) => $item->conflict['type'] !== 'no_conflict')
            ->values();

        if ($typeFilter) {
            $items = $items->filter(fn ($item) => $item->conflict['type'] === $typeFilter)->values();
        }

        if ($statusFilter) {
            $items = $items->filter(fn ($item) => $item->display_status === $statusFilter)->values();
        }

        $conflicts = $this->paginateCollection($items, 15, $request, 'page');

        return view('customers.messenger-conflicts', [
            'conflicts' => $conflicts,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function messengerSendControl(Request $request)
    {
        $shopIds = $this->shopIds();
        $allowlist = collect(config('facebook.send_test_psid_allowlist', []))->filter()->values();
        $messengerSettings = $this->messengerSettingsForShopIds($shopIds);
        $pageTokenStatus = $messengerSettings->contains(fn ($setting) => ! empty($setting->fb_page_token)) ? 'available' : 'missing';

        $mappings = CustomerMapping::with(['portalSession', 'shop', 'replyDrafts' => function ($query) {
            $query->latest('id')->limit(1);
        }])
            ->whereIn('shop_id', $shopIds)
            ->latest('updated_at')
            ->get()
            ->map(function (CustomerMapping $mapping) use ($allowlist) {
                $state = $this->buildPilotSendState($mapping, $allowlist);
                $mapping->pilot_send_state = $state;
                return $mapping;
            });

        $draftStats = $this->pilotDraftStats($shopIds);
        $latestSentDraft = MessengerReplyDraft::whereIn('shop_id', $shopIds)
            ->where('status', MessengerReplyDraft::STATUS_SENT)
            ->latest('sent_at')
            ->first();

        $latestAction = AdminActionLog::whereIn('customer_mapping_id', $mappings->pluck('id'))
            ->latest('id')
            ->first();

        return view('customers.messenger-send-control', [
            'sendEnabled' => (bool) config('facebook.send_enabled', false),
            'allowlistMasked' => $allowlist->map(fn ($psid) => $this->maskPsid($psid)),
            'pageTokenStatus' => $pageTokenStatus,
            'eligibleCount' => $mappings->filter(fn (CustomerMapping $mapping) => $mapping->pilot_send_state['status'] === 'eligible')->count(),
            'blockedAllowlistCount' => $mappings->filter(fn (CustomerMapping $mapping) => $mapping->pilot_send_state['status'] === 'blocked_by_allowlist')->count(),
            'blockedFlagCount' => $mappings->filter(fn (CustomerMapping $mapping) => $mapping->pilot_send_state['status'] === 'blocked_by_flag')->count(),
            'unavailableCount' => $mappings->filter(fn (CustomerMapping $mapping) => $mapping->pilot_send_state['status'] === 'unavailable_missing_token')->count(),
            'mappingCount' => $mappings->count(),
            'mappings' => $mappings->take(20),
            'draftStats' => $draftStats,
            'latestSentDraft' => $latestSentDraft,
            'latestAction' => $latestAction,
            'pilotSendEnabled' => (bool) config('facebook.send_enabled', false),
        ]);
    }

    public function showMessengerMapping(CustomerMapping $mapping)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);

        $mapping->load(['portalSession', 'shop', 'orderLinks.order', 'replyDrafts', 'adminActionLogs']);
        $insights = $this->buildDownstreamInsights($mapping);

        return view('customers.messenger-mapping-detail', [
            'mapping' => $mapping,
            'displayStatus' => $mapping->effectiveStatus(),
            'latestMessage' => $insights['latest_message'],
            'messages' => $insights['messages'],
            'orderCandidates' => $insights['order_candidates'],
            'chatCandidates' => $insights['chat_candidates'],
            'attachedOrderLinks' => $insights['attached_order_links'],
            'readiness' => $insights['readiness'],
            'conflicts' => $insights['conflicts'],
            'auditLogs' => $mapping->adminActionLogs->take(20),
            'replyDrafts' => $this->pilotReplyDraftsForMapping($mapping),
            'defaultPageId' => $mapping->facebook_page_id ?: self::DEFAULT_PAGE_ID,
            'messengerSendEnabled' => (bool) config('facebook.send_enabled', false),
            'pilotSendState' => $this->buildPilotSendState($mapping),
            'sendChecklist' => $this->buildSendChecklist($mapping),
            'pilotDraftSummary' => $this->pilotDraftStats(collect([$mapping->shop_id])),
        ]);
    }

    public function updateMessengerMapping(Request $request, CustomerMapping $mapping)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);

        $action = $request->validate([
            'action' => 'required|in:reopen_pending,mark_expired,clear_psid_reset,resolve_manually,mark_needs_review,add_note',
            'facebook_page_id' => 'nullable|string|max:255',
            'facebook_psid' => 'nullable|string|max:255',
            'facebook_name' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        $before = $this->mappingState($mapping);

        if ($action['action'] === 'reopen_pending') {
            $mapping->fill([
                'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
                'messenger_link_pending_at' => now(),
                'connected_source' => 'admin_reopen_pending',
                'connected_at' => null,
            ]);
        }

        if ($action['action'] === 'mark_expired') {
            $mapping->fill([
                'status' => CustomerMapping::STATUS_EXPIRED,
                'connected_source' => $mapping->connected_source ?: 'admin_mark_expired',
            ]);
        }

        if ($action['action'] === 'clear_psid_reset') {
            $mapping->fill([
                'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
                'facebook_page_id' => null,
                'facebook_psid' => null,
                'facebook_name' => null,
                'connected_at' => null,
                'messenger_link_pending_at' => now(),
                'connected_source' => 'admin_clear_psid_reset',
            ]);
        }

        if ($action['action'] === 'mark_needs_review') {
            $mapping->fill([
                'status' => CustomerMapping::STATUS_AMBIGUOUS,
                'connected_source' => $mapping->connected_source ?: 'admin_mark_needs_review',
                'notes' => $this->appendNote($mapping->notes, $action['note'] ?: 'admin marked mapping as needs review'),
            ]);
        }

        if ($action['action'] === 'add_note') {
            $mapping->notes = $this->appendNote($mapping->notes, $action['note'] ?: 'admin note');
        }

        if ($action['action'] === 'resolve_manually') {
            $this->applyManualResolve($mapping, [
                'facebook_page_id' => $action['facebook_page_id'] ?: $mapping->facebook_page_id ?: self::DEFAULT_PAGE_ID,
                'facebook_psid' => $action['facebook_psid'] ?: $mapping->facebook_psid,
                'facebook_name' => ($action['facebook_name'] ?? null) ?: $mapping->facebook_name,
            ]);

            if (! empty($action['note'])) {
                $mapping->notes = $this->appendNote($mapping->notes, $action['note']);
            }

            $mapping->save();

            $this->logAdminAction(
                $request,
                'resolve_manually',
                'customer_mapping',
                $mapping->id,
                $mapping->id,
                $before,
                $this->mappingState($mapping)
            );

            return redirect()
                ->route('customers.messenger-mappings.show', $mapping)
                ->with('mapping_action', 'updated');
        }

        $mapping->save();

        $this->logAdminAction(
            $request,
            $action['action'],
            'customer_mapping',
            $mapping->id,
            $mapping->id,
            $before,
            $this->mappingState($mapping)
        );

        return back()->with('mapping_action', 'updated');
    }

    public function attachMessengerOrder(Request $request, CustomerMapping $mapping, Order $order)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);
        abort_unless($order->shop_id === $mapping->shop_id, 404);

        $data = $request->validate([
            'matched_by' => 'nullable|string|max:50',
            'confidence' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $existingAttached = MessengerOrderLink::where('customer_mapping_id', $mapping->id)
            ->where('order_id', $order->id)
            ->where('status', MessengerOrderLink::STATUS_ATTACHED)
            ->first();

        if ($existingAttached) {
            throw ValidationException::withMessages([
                'order_id' => 'order นี้ถูก attach กับ mapping นี้อยู่แล้ว',
            ]);
        }

        $conflict = MessengerOrderLink::where('order_id', $order->id)
            ->where('status', MessengerOrderLink::STATUS_ATTACHED)
            ->where('customer_mapping_id', '!=', $mapping->id)
            ->with('mapping')
            ->first();

        if ($conflict && $conflict->mapping && $conflict->mapping->effectiveStatus() === CustomerMapping::STATUS_CONNECTED) {
            throw ValidationException::withMessages([
                'order_id' => "order นี้ถูก attach กับ mapping #{$conflict->customer_mapping_id} อยู่แล้ว",
            ]);
        }

        $link = MessengerOrderLink::firstOrNew([
            'customer_mapping_id' => $mapping->id,
            'order_id' => $order->id,
        ]);

        $before = $link->exists ? $this->linkState($link) : null;

        $link->fill([
            'status' => MessengerOrderLink::STATUS_ATTACHED,
            'matched_by' => $data['matched_by'] ?? 'tiktok_username',
            'confidence' => $data['confidence'] ?? 'exact',
            'attached_by' => Auth::id(),
            'detached_at' => null,
            'notes' => $data['notes'] ?? null,
        ]);
        $link->save();

        $this->logAdminAction(
            $request,
            'messenger_order_attach',
            'messenger_order_link',
            $link->id,
            $mapping->id,
            $before,
            $this->linkState($link),
            ['order_id' => $order->id]
        );

        return back()->with('mapping_action', 'updated');
    }

    public function detachMessengerOrder(Request $request, MessengerOrderLink $link)
    {
        return $this->updateMessengerOrderLink($request->merge(['action' => 'detach_wrong_link']), $link);
    }

    public function updateMessengerOrderLink(Request $request, MessengerOrderLink $link)
    {
        $link->load(['mapping', 'order']);
        abort_unless($link->mapping && $this->shopIds()->contains($link->mapping->shop_id), 404);

        $data = $request->validate([
            'action' => 'required|in:detach_wrong_link,keep_primary_detach_others,mark_reviewed,mark_needs_review,add_note',
            'note' => 'nullable|string|max:2000',
        ]);

        $before = $this->linkState($link);

        if ($data['action'] === 'detach_wrong_link') {
            $link->fill([
                'status' => MessengerOrderLink::STATUS_DETACHED,
                'detached_at' => now(),
                'notes' => $this->appendNote($link->notes, ($data['note'] ?? null) ?: 'admin detached wrong link'),
            ])->save();
        }

        if ($data['action'] === 'keep_primary_detach_others') {
            $otherLinks = MessengerOrderLink::where('order_id', $link->order_id)
                ->where('status', MessengerOrderLink::STATUS_ATTACHED)
                ->where('id', '!=', $link->id)
                ->with('mapping')
                ->get()
                ->filter(fn (MessengerOrderLink $otherLink) => $otherLink->mapping && $otherLink->mapping->shop_id === $link->mapping->shop_id);

            foreach ($otherLinks as $otherLink) {
                $otherBefore = $this->linkState($otherLink);
                $otherLink->fill([
                    'status' => MessengerOrderLink::STATUS_DETACHED,
                    'detached_at' => now(),
                    'notes' => $this->appendNote($otherLink->notes, 'detached by keep_primary_detach_others'),
                ])->save();

                $this->logAdminAction(
                    $request,
                    'messenger_order_detach_other',
                    'messenger_order_link',
                    $otherLink->id,
                    $otherLink->customer_mapping_id,
                    $otherBefore,
                    $this->linkState($otherLink),
                    ['order_id' => $otherLink->order_id, 'kept_link_id' => $link->id]
                );
            }

            $link->fill([
                'status' => MessengerOrderLink::STATUS_ATTACHED,
                'detached_at' => null,
                'notes' => $this->appendNote($link->notes, ($data['note'] ?? null) ?: 'admin kept this link as primary'),
            ])->save();
        }

        if ($data['action'] === 'mark_reviewed') {
            $link->notes = $this->appendNote($link->notes, ($data['note'] ?? null) ?: 'admin reviewed conflict');
            $link->save();
        }

        if ($data['action'] === 'mark_needs_review') {
            $link->fill([
                'status' => MessengerOrderLink::STATUS_NEEDS_REVIEW,
                'notes' => $this->appendNote($link->notes, ($data['note'] ?? null) ?: 'admin marked link as needs review'),
            ])->save();
        }

        if ($data['action'] === 'add_note') {
            $link->notes = $this->appendNote($link->notes, ($data['note'] ?? null) ?: 'admin note');
            $link->save();
        }

        $this->logAdminAction(
            $request,
            $data['action'],
            'messenger_order_link',
            $link->id,
            $link->customer_mapping_id,
            $before,
            $this->linkState($link),
            ['order_id' => $link->order_id]
        );

        return back()->with('mapping_action', 'updated');
    }

    public function markMessengerOrderNeedsReview(Request $request, CustomerMapping $mapping, Order $order)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);
        abort_unless($order->shop_id === $mapping->shop_id, 404);

        $data = $request->validate([
            'matched_by' => 'nullable|string|max:50',
            'confidence' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $link = MessengerOrderLink::firstOrNew([
            'customer_mapping_id' => $mapping->id,
            'order_id' => $order->id,
        ]);

        $before = $link->exists ? $this->linkState($link) : null;

        $link->fill([
            'status' => MessengerOrderLink::STATUS_NEEDS_REVIEW,
            'matched_by' => $data['matched_by'] ?? 'tiktok_username',
            'confidence' => $data['confidence'] ?? 'ambiguous',
            'attached_by' => Auth::id(),
            'notes' => $data['notes'] ?? 'manual review requested',
        ]);
        $link->save();

        $this->logAdminAction(
            $request,
            'messenger_order_needs_review',
            'messenger_order_link',
            $link->id,
            $mapping->id,
            $before,
            $this->linkState($link),
            ['order_id' => $order->id]
        );

        return back()->with('mapping_action', 'updated');
    }

    public function storeReplyDraft(Request $request, CustomerMapping $mapping)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);

        $data = $request->validate([
            'draft_text' => 'required|string|max:2000',
        ]);

        $payload = app(MessengerSendService::class)->buildDraftPayload($mapping, $data['draft_text']);

        $draft = MessengerReplyDraft::create([
            'customer_mapping_id' => $mapping->id,
            'shop_id' => $mapping->shop_id,
            'created_by' => Auth::id(),
            'facebook_page_id' => $mapping->facebook_page_id,
            'facebook_psid' => $mapping->facebook_psid,
            'draft_text' => $data['draft_text'],
            'status' => MessengerReplyDraft::STATUS_DRAFT,
            'send_enabled' => (bool) config('facebook.send_enabled', false),
            'preview_payload' => $payload + ['mode' => 'draft'],
            'sent_at' => null,
            'response_payload' => null,
            'failure_reason' => null,
        ]);

        $this->logAdminAction(
            $request,
            'messenger_reply_draft_saved',
            'messenger_reply_draft',
            $draft->id,
            $mapping->id,
            null,
            $this->draftState($draft),
            ['draft_preview' => Str::limit($draft->draft_text, 120)]
        );

        return back()->with('mapping_action', 'updated');
    }

    public function runReplyDraft(Request $request, CustomerMapping $mapping, MessengerReplyDraft $draft, MessengerSendService $sendService)
    {
        abort_unless($this->shopIds()->contains($mapping->shop_id), 404);
        abort_unless($draft->customer_mapping_id === $mapping->id, 404);

        $data = $request->validate([
            'action' => 'required|in:dry_run,send_test',
        ]);

        $before = $this->draftState($draft);

        if ($data['action'] === 'dry_run') {
            $draft->fill([
                'status' => MessengerReplyDraft::STATUS_DRY_RUN,
                'send_enabled' => (bool) config('facebook.send_enabled', false),
                'preview_payload' => $sendService->buildDraftPayload($mapping, $draft->draft_text) + ['mode' => 'dry_run'],
                'response_payload' => null,
                'failure_reason' => null,
                'sent_at' => null,
            ])->save();
        }

        if ($data['action'] === 'send_test') {
            $result = $sendService->sendDraft($mapping, $draft);

            $draft->fill([
                'facebook_page_id' => $mapping->facebook_page_id,
                'facebook_psid' => $mapping->facebook_psid,
                'status' => $result['status'],
                'send_enabled' => (bool) config('facebook.send_enabled', false),
                'preview_payload' => $result['payload'] + ['mode' => 'send_test'],
                'response_payload' => $result['response'],
                'failure_reason' => $result['reason'],
                'sent_at' => $result['status'] === MessengerReplyDraft::STATUS_SENT ? now() : null,
            ])->save();
        }

        $this->logAdminAction(
            $request,
            'messenger_reply_draft_' . $data['action'],
            'messenger_reply_draft',
            $draft->id,
            $mapping->id,
            $before,
            $this->draftState($draft),
            ['draft_preview' => Str::limit($draft->draft_text, 120)]
        );

        return back()->with('mapping_action', 'updated');
    }

    protected function buildPilotSendState(CustomerMapping $mapping, ?Collection $allowlist = null): array
    {
        $allowlist = $allowlist ?: collect(config('facebook.send_test_psid_allowlist', []));
        $sendEnabled = (bool) config('facebook.send_enabled', false);
        $hasPageToken = (bool) ($mapping->shop?->messengerSetting?->fb_page_token);
        $hasPsid = (bool) $mapping->facebook_psid;
        $hasPageId = (bool) $mapping->facebook_page_id;
        $inAllowlist = $hasPsid && $allowlist->contains($mapping->facebook_psid);

        if (! $hasPageToken) {
            return [
                'status' => 'unavailable_missing_token',
                'label' => 'Send unavailable',
                'detail' => 'missing token',
                'badge' => 'bg-secondary-subtle text-secondary-emphasis',
            ];
        }

        if (! $sendEnabled) {
            return [
                'status' => 'blocked_by_flag',
                'label' => 'Real send disabled',
                'detail' => 'MESSENGER_SEND_ENABLED=false',
                'badge' => 'bg-warning-subtle text-warning-emphasis',
            ];
        }

        if (! $inAllowlist) {
            return [
                'status' => 'blocked_by_allowlist',
                'label' => 'Blocked by allowlist',
                'detail' => 'PSID not in pilot allowlist',
                'badge' => 'bg-danger-subtle text-danger-emphasis',
            ];
        }

        if (! $hasPsid || ! $hasPageId || $mapping->effectiveStatus() !== CustomerMapping::STATUS_CONNECTED) {
            return [
                'status' => 'blocked_by_data',
                'label' => 'Not eligible',
                'detail' => 'mapping incomplete or not connected',
                'badge' => 'bg-dark-subtle text-dark-emphasis',
            ];
        }

        return [
            'status' => 'eligible',
            'label' => 'Eligible for test send',
            'detail' => 'flag on + allowlist + token ready',
            'badge' => 'bg-success-subtle text-success-emphasis',
        ];
    }

    protected function buildSendChecklist(CustomerMapping $mapping): array
    {
        $allowlist = collect(config('facebook.send_test_psid_allowlist', []));
        return [
            [
                'label' => 'mapping status connected',
                'passed' => $mapping->effectiveStatus() === CustomerMapping::STATUS_CONNECTED,
            ],
            [
                'label' => 'has facebook_psid',
                'passed' => (bool) $mapping->facebook_psid,
            ],
            [
                'label' => 'has facebook_page_id',
                'passed' => (bool) $mapping->facebook_page_id,
            ],
            [
                'label' => 'PSID is allowlisted',
                'passed' => $mapping->facebook_psid && $allowlist->contains($mapping->facebook_psid),
            ],
            [
                'label' => 'send flag enabled',
                'passed' => (bool) config('facebook.send_enabled', false),
            ],
            [
                'label' => 'page token available',
                'passed' => (bool) ($mapping->shop?->messengerSetting?->fb_page_token),
            ],
            [
                'label' => 'draft text not empty',
                'passed' => true,
            ],
        ];
    }

    protected function pilotDraftStats(Collection $shopIds): array
    {
        $query = MessengerReplyDraft::whereIn('shop_id', $shopIds);
        return [
            'saved' => (clone $query)->where('status', MessengerReplyDraft::STATUS_DRAFT)->count(),
            'dry_run' => (clone $query)->where('status', MessengerReplyDraft::STATUS_DRY_RUN)->count(),
            'blocked_by_flag' => (clone $query)->where('status', MessengerReplyDraft::STATUS_BLOCKED_BY_FLAG)->count(),
            'blocked_by_allowlist' => (clone $query)->where('status', MessengerReplyDraft::STATUS_BLOCKED_BY_ALLOWLIST)->count(),
            'sent' => (clone $query)->where('status', MessengerReplyDraft::STATUS_SENT)->count(),
            'failed' => (clone $query)->where('status', MessengerReplyDraft::STATUS_FAILED)->count(),
        ];
    }

    protected function pilotReplyDraftsForMapping(CustomerMapping $mapping): Collection
    {
        return MessengerReplyDraft::where('customer_mapping_id', $mapping->id)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->each(function (MessengerReplyDraft $draft) {
                $draft->provider_message_id = data_get($draft->response_payload, 'provider_message_id');
                $draft->message_preview = data_get($draft->preview_payload, 'message.text', $draft->draft_text);
                $draft->last_action = match ($draft->status) {
                    MessengerReplyDraft::STATUS_SENT => 'sent',
                    MessengerReplyDraft::STATUS_DRY_RUN => 'dry_run',
                    MessengerReplyDraft::STATUS_BLOCKED_BY_FLAG => 'blocked_by_flag',
                    MessengerReplyDraft::STATUS_BLOCKED_BY_ALLOWLIST => 'blocked_by_allowlist',
                    MessengerReplyDraft::STATUS_FAILED => 'failed',
                    default => 'saved',
                };
            });
    }

    protected function messengerSettingsForShopIds(Collection $shopIds): Collection
    {
        return \App\Models\MessengerSetting::whereIn('shop_id', $shopIds)->get();
    }

    protected function maskPsid(string $psid): string
    {
        $clean = trim($psid);
        if ($clean === '') {
            return '****';
        }

        if (strlen($clean) <= 8) {
            return substr($clean, 0, 2) . '****';
        }

        return substr($clean, 0, 4) . '****' . substr($clean, -4);
    }

    public function messengerMessages(Request $request)
    {
        $shopIds = $this->shopIds();
        $psid = $request->string('psid')->toString();
        $pageId = $request->string('page_id')->toString();
        $match = $request->string('match')->toString();
        $date = $request->string('date')->toString();

        $query = MessengerMessage::whereIn('shop_id', $shopIds)->latest('created_at');

        if ($psid) {
            $query->where('psid', $psid);
        }

        if ($pageId) {
            $query->where('page_id', $pageId);
        }

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $messages = $query->paginate(20)->through(function (MessengerMessage $message) {
            $message->matched_mapping = CustomerMapping::where('shop_id', $message->shop_id)
                ->where('facebook_psid', $message->psid)
                ->latest('updated_at')
                ->first();
            return $message;
        });

        if ($match === 'mapped') {
            $messages->setCollection($messages->getCollection()->filter(fn ($message) => (bool) $message->matched_mapping)->values());
        }

        if ($match === 'unmapped') {
            $messages->setCollection($messages->getCollection()->filter(fn ($message) => ! $message->matched_mapping)->values());
        }

        return view('customers.messenger-messages', compact('messages', 'psid', 'pageId', 'match', 'date'));
    }

    public function messengerMessageDetail(MessengerMessage $message)
    {
        abort_unless($this->shopIds()->contains($message->shop_id), 404);

        $matchedMapping = CustomerMapping::with(['activeOrderLinks.order'])
            ->where('shop_id', $message->shop_id)
            ->where('facebook_psid', $message->psid)
            ->latest('updated_at')
            ->first();

        $candidateMappings = collect();
        $orderCandidates = collect();
        $attachedOrderLinks = collect();
        $readiness = null;
        $conflicts = collect();

        if ($matchedMapping) {
            $insights = $this->buildDownstreamInsights($matchedMapping);
            $orderCandidates = $insights['order_candidates'];
            $attachedOrderLinks = $insights['attached_order_links'];
            $readiness = $insights['readiness'];
            $conflicts = $insights['conflicts'];
        } else {
            $candidateMappings = CustomerMapping::with('portalSession')
                ->where('shop_id', $message->shop_id)
                ->whereIn('status', [
                    CustomerMapping::STATUS_PENDING_MESSENGER,
                    CustomerMapping::STATUS_AMBIGUOUS,
                    CustomerMapping::STATUS_EXPIRED,
                ])
                ->latest('updated_at')
                ->get()
                ->each(fn (CustomerMapping $mapping) => $mapping->display_status = $mapping->effectiveStatus());
        }

        return view('customers.messenger-message-detail', [
            'message' => $message,
            'matchedMapping' => $matchedMapping,
            'candidateMappings' => $candidateMappings,
            'orderCandidates' => $orderCandidates,
            'attachedOrderLinks' => $attachedOrderLinks,
            'readiness' => $readiness,
            'conflicts' => $conflicts,
            'defaultPageId' => $message->page_id ?: self::DEFAULT_PAGE_ID,
            'payloadJson' => json_encode($message->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function resolveMessengerMessage(Request $request, MessengerMessage $message)
    {
        abort_unless($this->shopIds()->contains($message->shop_id), 404);

        $data = $request->validate([
            'mapping_id' => 'required|integer',
            'facebook_page_id' => 'nullable|string|max:255',
            'facebook_name' => 'nullable|string|max:255',
        ]);

        $mapping = CustomerMapping::where('shop_id', $message->shop_id)
            ->where('id', $data['mapping_id'])
            ->firstOrFail();

        $before = $this->mappingState($mapping);

        $this->applyManualResolve($mapping, [
            'facebook_page_id' => $data['facebook_page_id'] ?: $message->page_id ?: self::DEFAULT_PAGE_ID,
            'facebook_psid' => $message->psid,
            'facebook_name' => $data['facebook_name'] ?: $message->sender_name,
        ]);

        $mapping->save();

        $this->logAdminAction(
            $request,
            'manual_resolve_from_message',
            'customer_mapping',
            $mapping->id,
            $mapping->id,
            $before,
            $this->mappingState($mapping),
            [
                'message_id' => $message->id,
                'message_psid' => $message->psid,
            ]
        );

        return redirect()
            ->route('customers.messenger-mappings.show', $mapping)
            ->with('mapping_action', 'updated');
    }

    protected function applyManualResolve(CustomerMapping $mapping, array $attributes): void
    {
        $psid = trim((string) ($attributes['facebook_psid'] ?? ''));

        if ($psid === '') {
            throw ValidationException::withMessages([
                'facebook_psid' => 'กรุณาระบุ PSID ก่อนทำ manual resolve',
            ]);
        }

        $existingMapping = CustomerMapping::where('shop_id', $mapping->shop_id)
            ->where('facebook_psid', $psid)
            ->where('id', '!=', $mapping->id)
            ->where('status', CustomerMapping::STATUS_CONNECTED)
            ->latest('updated_at')
            ->first();

        if ($existingMapping) {
            throw ValidationException::withMessages([
                'facebook_psid' => "PSID นี้ถูกผูกกับ mapping #{$existingMapping->id} อยู่แล้ว",
            ]);
        }

        $mapping->fill([
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => $attributes['facebook_page_id'] ?? $mapping->facebook_page_id ?? self::DEFAULT_PAGE_ID,
            'facebook_psid' => $psid,
            'facebook_name' => $attributes['facebook_name'] ?? $mapping->facebook_name,
            'connected_at' => now(),
            'messenger_link_pending_at' => null,
            'connected_source' => 'admin_manual_resolve',
        ]);
    }

    protected function buildDownstreamInsights(CustomerMapping $mapping): array
    {
        $messages = $this->messagesForMapping($mapping);
        $latestMessage = $messages->first();
        $orderCandidates = $this->orderCandidatesForMapping($mapping);
        $chatCandidates = $this->chatCandidatesForMapping($mapping, $messages);
        $attachedOrderLinks = $this->attachedOrderLinksForMapping($mapping);
        $conflicts = $this->realConflicts($this->conflictsForMapping($mapping, $orderCandidates, $attachedOrderLinks));
        $needsReviewCount = $mapping->orderLinks()->where('status', MessengerOrderLink::STATUS_NEEDS_REVIEW)->count();

        return [
            'messages' => $messages,
            'latest_message' => $latestMessage,
            'order_candidates' => $orderCandidates,
            'chat_candidates' => $chatCandidates,
            'attached_order_links' => $attachedOrderLinks,
            'last_activity_at' => $latestMessage?->created_at ?: $mapping->updated_at,
            'conflicts' => $conflicts,
            'readiness' => $this->buildReadinessStatus($mapping, $latestMessage, $orderCandidates, $chatCandidates, $attachedOrderLinks, $needsReviewCount, $conflicts),
        ];
    }

    protected function conflictsForMapping(
        CustomerMapping $mapping,
        ?Collection $orderCandidates = null,
        ?Collection $attachedOrderLinks = null
    ): Collection {
        $orderCandidates = $orderCandidates ?: $this->orderCandidatesForMapping($mapping);
        $attachedOrderLinks = $attachedOrderLinks ?: $this->attachedOrderLinksForMapping($mapping);
        $conflicts = collect();

        $exactCandidates = $orderCandidates->where('confidence', 'exact')->values();

        if ($exactCandidates->count() > 1) {
            $conflicts->push([
                'type' => 'multiple_order_candidates',
                'severity' => 'warning',
                'summary' => 'พบ order candidate มากกว่า 1 รายการ',
                'detail' => 'mapping นี้ match exact order ได้หลายรายการ ต้องให้แอดมินเลือกเอง',
                'order_ids' => $exactCandidates->pluck('id')->all(),
            ]);
        }

        if ($mapping->facebook_psid) {
            $psidConflicts = CustomerMapping::where('shop_id', $mapping->shop_id)
                ->where('facebook_psid', $mapping->facebook_psid)
                ->where('id', '!=', $mapping->id)
                ->where('status', CustomerMapping::STATUS_CONNECTED)
                ->get(['id', 'portal_session_id', 'tiktok_username']);

            if ($psidConflicts->isNotEmpty()) {
                $conflicts->push([
                    'type' => 'psid_conflict',
                    'severity' => 'danger',
                    'summary' => 'PSID นี้ถูกผูกกับ mapping อื่นแล้ว',
                    'detail' => 'มี mapping connected อื่นใช้ facebook_psid เดียวกัน',
                    'mapping_ids' => $psidConflicts->pluck('id')->all(),
                ]);
            }
        }

        $normalizedUsername = $this->normalizeUsername($mapping->tiktok_username);
        if ($normalizedUsername) {
            $usernameConflicts = CustomerMapping::where('shop_id', $mapping->shop_id)
                ->where('id', '!=', $mapping->id)
                ->get(['id', 'tiktok_username', 'status', 'portal_session_id'])
                ->filter(function (CustomerMapping $candidate) use ($normalizedUsername) {
                    return $this->normalizeUsername($candidate->tiktok_username) === $normalizedUsername;
                })
                ->values();

            if ($usernameConflicts->isNotEmpty()) {
                $conflicts->push([
                    'type' => 'username_conflict',
                    'severity' => 'warning',
                    'summary' => 'TikTok username นี้ซ้ำกับ mapping อื่น',
                    'detail' => 'มี mapping อื่นใช้ tiktok_username เดียวกันในร้านเดียวกัน',
                    'mapping_ids' => $usernameConflicts->pluck('id')->all(),
                ]);
            }
        }

        foreach ($attachedOrderLinks as $attachedLink) {
            $otherLinks = MessengerOrderLink::where('order_id', $attachedLink->order_id)
                ->where('status', MessengerOrderLink::STATUS_ATTACHED)
                ->where('id', '!=', $attachedLink->id)
                ->with('mapping')
                ->get()
                ->filter(fn (MessengerOrderLink $otherLink) => $otherLink->mapping && $otherLink->mapping->shop_id === $mapping->shop_id)
                ->values();

            if ($otherLinks->isNotEmpty()) {
                $conflicts->push([
                    'type' => 'order_conflict',
                    'severity' => 'danger',
                    'summary' => 'order นี้ถูก attach กับหลาย mapping',
                    'detail' => 'ต้องเลือก link หลักและ detach link อื่น',
                    'order_id' => $attachedLink->order_id,
                    'order_code' => $attachedLink->order?->code,
                    'primary_link_id' => $attachedLink->id,
                    'conflicting_link_ids' => $otherLinks->pluck('id')->all(),
                    'mapping_ids' => $otherLinks->pluck('customer_mapping_id')->all(),
                ]);
            }
        }

        if ($attachedOrderLinks->count() > 1) {
            $conflicts->push([
                'type' => 'mapping_conflict',
                'severity' => 'warning',
                'summary' => 'mapping นี้มี attached order มากกว่า 1 รายการ',
                'detail' => 'ต้องตรวจว่าควรผูกกับ order ไหนเป็นหลัก',
                'order_ids' => $attachedOrderLinks->pluck('order_id')->all(),
                'link_ids' => $attachedOrderLinks->pluck('id')->all(),
            ]);
        }

        if ($conflicts->isEmpty()) {
            $conflicts->push([
                'type' => 'no_conflict',
                'severity' => 'success',
                'summary' => 'ไม่พบ conflict',
                'detail' => 'mapping นี้ยังไม่พบสัญญาณขัดแย้ง',
            ]);
        }

        return $conflicts->values();
    }

    protected function realConflicts(Collection $conflicts): Collection
    {
        return $conflicts->filter(fn (array $conflict) => $conflict['type'] !== 'no_conflict')->values();
    }

    protected function orderCandidatesForMapping(CustomerMapping $mapping): Collection
    {
        $normalizedUsername = $this->normalizeUsername($mapping->tiktok_username);

        if (! $normalizedUsername) {
            return collect();
        }

        return Order::where('shop_id', $mapping->shop_id)
            ->where(function ($query) use ($normalizedUsername) {
                $query->whereRaw('LOWER(customer_username) = ?', [$normalizedUsername])
                    ->orWhereRaw('LOWER(customer_username) = ?', ['@' . $normalizedUsername]);
            })
            ->latest('created_at')
            ->get()
            ->map(function (Order $order) use ($normalizedUsername) {
                $order->matched_by = 'tiktok_username';
                $order->match_reason = "customer_username matched @{$normalizedUsername}";
                $order->confidence = 'exact';
                $order->detail_link = route('orders.show', $order);
                $order->list_link = url('/orders');
                return $order;
            });
    }

    protected function chatCandidatesForMapping(CustomerMapping $mapping, ?Collection $messages = null): Collection
    {
        if (! $mapping->facebook_psid) {
            return collect();
        }

        $messages = $messages ?: $this->messagesForMapping($mapping);

        if ($messages->isEmpty()) {
            return collect();
        }

        return collect([
            (object) [
                'type' => 'messenger_thread',
                'matched_by' => 'facebook_psid',
                'match_reason' => "psid matched {$mapping->facebook_psid}",
                'confidence' => 'exact',
                'message_count' => $messages->count(),
                'latest_message' => $messages->first(),
                'messages_link' => route('customers.messenger.messages', ['psid' => $mapping->facebook_psid]),
                'chat_link' => url('/chat?shop_id=' . $mapping->shop_id),
            ],
        ]);
    }

    protected function attachedOrderLinksForMapping(CustomerMapping $mapping): Collection
    {
        return MessengerOrderLink::with('order')
            ->where('customer_mapping_id', $mapping->id)
            ->where('status', MessengerOrderLink::STATUS_ATTACHED)
            ->latest('updated_at')
            ->get();
    }

    protected function buildReadinessStatus(
        CustomerMapping $mapping,
        ?MessengerMessage $latestMessage,
        Collection $orderCandidates,
        Collection $chatCandidates,
        Collection $attachedOrderLinks,
        int $needsReviewCount,
        Collection $conflicts
    ): array {
        $effectiveStatus = $mapping->effectiveStatus();
        $connected = $effectiveStatus === CustomerMapping::STATUS_CONNECTED && ! empty($mapping->facebook_psid);
        $exactOrderCount = $orderCandidates->where('confidence', 'exact')->count();
        $attachedCount = $attachedOrderLinks->count();
        $firstConflict = $conflicts->first();

        if (! $connected) {
            return $this->readinessPayload('not_ready', 'Not ready', 'mapping not connected');
        }

        if (! $latestMessage) {
            return $this->readinessPayload('not_ready', 'Not ready', 'no latest message');
        }

        if ($conflicts->isNotEmpty()) {
            return $this->readinessPayload('needs_review', 'Needs review', $firstConflict['detail'] ?? 'conflict detected');
        }

        if ($needsReviewCount > 0 || $exactOrderCount > 1) {
            return $this->readinessPayload('needs_review', 'Needs review', $needsReviewCount > 0 ? 'manual review requested' : 'multiple order candidates');
        }

        if ($attachedCount > 0 || $exactOrderCount === 1) {
            return $this->readinessPayload('ready', 'Ready', $attachedCount > 0 ? 'connected + latest message + attached order' : 'connected + latest message + exact order candidate');
        }

        if ($chatCandidates->count() > 0) {
            return $this->readinessPayload('not_ready', 'Not ready', 'no exact order candidate');
        }

        return $this->readinessPayload('not_ready', 'Not ready', 'no order/chat candidate found');
    }

    protected function readinessPayload(string $key, string $label, string $detail): array
    {
        $badge = match ($key) {
            'ready' => 'bg-success-subtle text-success-emphasis',
            'needs_review' => 'bg-warning-subtle text-warning-emphasis',
            default => 'bg-secondary-subtle text-secondary-emphasis',
        };

        return compact('key', 'label', 'detail', 'badge');
    }

    protected function matchesReadinessFilter(CustomerMapping $mapping, string $filter): bool
    {
        return match ($filter) {
            'ready', 'needs_review', 'not_ready' => $mapping->readiness['key'] === $filter,
            'connected' => $mapping->display_status === CustomerMapping::STATUS_CONNECTED,
            'no_order' => $mapping->order_candidates->count() === 0 && $mapping->attached_order_links->count() === 0,
            'attached' => $mapping->attached_order_links->count() > 0,
            'ambiguous' => $mapping->display_status === CustomerMapping::STATUS_AMBIGUOUS,
            'expired' => $mapping->display_status === CustomerMapping::STATUS_EXPIRED,
            default => true,
        };
    }

    protected function messagesForMapping(CustomerMapping $mapping): Collection
    {
        if (! $mapping->facebook_psid) {
            return collect();
        }

        return MessengerMessage::where('shop_id', $mapping->shop_id)
            ->where('psid', $mapping->facebook_psid)
            ->latest('created_at')
            ->limit(20)
            ->get();
    }

    protected function normalizeUsername(?string $username): ?string
    {
        $normalized = Str::of((string) $username)
            ->trim()
            ->lower()
            ->ltrim('@')
            ->value();

        return $normalized !== '' ? $normalized : null;
    }

    protected function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName = 'page'): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);
        $total = $items->count();
        $results = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => $request->query(),
            ]
        );
    }

    protected function mappingState(CustomerMapping $mapping): array
    {
        return $mapping->only([
            'status',
            'facebook_page_id',
            'facebook_psid',
            'facebook_name',
            'connected_source',
            'connected_at',
            'messenger_link_pending_at',
            'tiktok_username',
            'notes',
        ]);
    }

    protected function linkState(MessengerOrderLink $link): array
    {
        return $link->only([
            'customer_mapping_id',
            'order_id',
            'status',
            'matched_by',
            'confidence',
            'attached_by',
            'detached_at',
            'notes',
        ]);
    }

    protected function draftState(MessengerReplyDraft $draft): array
    {
        return $draft->only([
            'customer_mapping_id',
            'shop_id',
            'facebook_page_id',
            'facebook_psid',
            'status',
            'send_enabled',
            'sent_at',
            'failure_reason',
        ]);
    }

    protected function appendNote(?string $existing, ?string $note): string
    {
        $note = trim((string) $note);

        if ($note === '') {
            return (string) $existing;
        }

        $timestamped = '[' . now()->format('Y-m-d H:i:s') . '] ' . $note;

        return trim((string) $existing) !== ''
            ? trim((string) $existing) . PHP_EOL . $timestamped
            : $timestamped;
    }

    protected function logAdminAction(
        Request $request,
        string $action,
        string $targetType,
        ?int $targetId,
        ?int $mappingId,
        ?array $before,
        ?array $after,
        array $meta = []
    ): void {
        AdminActionLog::create([
            'user_id' => Auth::id(),
            'customer_mapping_id' => $mappingId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_state' => $before,
            'after_state' => $after,
            'meta' => $meta,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Log::info('messenger_mapping_admin_action', [
            'user_id' => Auth::id(),
            'action' => $action,
            'mapping_id' => $mappingId,
            'customer_mapping_id' => $mappingId,
            'message_id' => $meta['message_id'] ?? null,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before' => $before,
            'after' => $after,
            'meta' => $meta,
        ]);
    }

    protected function shopIds(): Collection
    {
        return Auth::user()?->shops()->pluck('id') ?? collect();
    }
}
