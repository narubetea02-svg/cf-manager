@extends('layouts.admin')
@section('title', 'CF Manager Mapping Detail')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('mapping_action') === 'updated')
            <div class="alert alert-success">อัปเดตสถานะ Messenger mapping เรียบร้อยแล้ว</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">CF Manager Mapping Detail</h4>
                <p class="text-muted mb-0">ศูนย์กลางสำหรับตรวจ mapping, order link, readiness, reply draft และ audit log</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">กลับไปหน้า Customers</a>
                <a href="{{ route('customers.messenger.send-control') }}" class="btn btn-outline-dark">Pilot Send Control</a>
                <a href="{{ route('customers.messenger.messages') }}" class="btn btn-outline-primary">ดูข้อความ Messenger</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Mapping Information</h5>
                <div class="small mb-2">mapping id: <code>{{ $mapping->id }}</code></div>
                <div class="small mb-2">portal_session_id: <code>{{ $mapping->portal_session_id }}</code></div>
                <div class="small mb-2">portal sid: <code>{{ $mapping->portalSession?->sid ?: '-' }}</code></div>
                <div class="small mb-2">shop_id: <code>{{ $mapping->shop_id }}</code></div>
                <div class="small mb-2">tiktok_username: <code>{{ $mapping->tiktok_username ?: '-' }}</code></div>
                <div class="small mb-2">status: <code>{{ $displayStatus }}</code></div>
                <div class="small mb-2">facebook_page_id: <code>{{ $mapping->facebook_page_id ?: '-' }}</code></div>
                <div class="small mb-2">facebook_psid: <code>{{ $mapping->facebook_psid ?: '-' }}</code></div>
                <div class="small mb-2">facebook_name: <code>{{ $mapping->facebook_name ?: '-' }}</code></div>
                <div class="small mb-2">connected_source: <code>{{ $mapping->connected_source ?: '-' }}</code></div>
                <div class="small mb-2">pending_messenger_at: <code>{{ $mapping->messenger_link_pending_at?->format('d/m/Y H:i:s') ?: '-' }}</code></div>
                <div class="small mb-2">connected_at: <code>{{ $mapping->connected_at?->format('d/m/Y H:i:s') ?: '-' }}</code></div>
                <div class="small mb-2">updated_at: <code>{{ $mapping->updated_at?->format('d/m/Y H:i:s') ?: '-' }}</code></div>
                <div class="small mb-0">notes: <code>{{ $mapping->notes ?: '-' }}</code></div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Downstream Readiness</h5>
                <div class="d-flex flex-wrap gap-3 align-items-start">
                    <div>
                        <span class="badge {{ $readiness['badge'] }}">{{ $readiness['label'] }}</span>
                        <div class="small text-muted mt-1">{{ $readiness['detail'] }}</div>
                    </div>
                    <div class="small">
                        <div>Messenger connected: <strong>{{ $displayStatus === 'connected' && $mapping->facebook_psid ? 'yes' : 'no' }}</strong></div>
                        <div>Latest message: <strong>{{ $latestMessage ? 'yes' : 'no' }}</strong></div>
                        <div>Order candidates: <strong>{{ $orderCandidates->count() }}</strong></div>
                        <div>Attached orders: <strong>{{ $attachedOrderLinks->count() }}</strong></div>
                        <div>Chat candidates: <strong>{{ $chatCandidates->count() }}</strong></div>
                        <div>Reply ready: <strong>{{ $mapping->facebook_psid && $mapping->facebook_page_id && $displayStatus === 'connected' ? 'yes' : 'no' }}</strong></div>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('customers.messenger.readiness') }}" class="btn btn-outline-success btn-sm">เปิด Readiness List</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">Conflict Summary</h5>
                        <p class="text-muted mb-0">ตรวจ conflict ก่อนผูก order หรือทดลองส่งข้อความกลับ</p>
                    </div>
                    <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-outline-danger btn-sm">เปิด Conflict Center</a>
                </div>
                @if(($conflicts ?? collect())->isNotEmpty())
                    @foreach($conflicts as $conflict)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between gap-2">
                                <div class="fw-semibold">{{ $conflict['type'] }}</div>
                                <span class="badge {{ $conflict['severity'] === 'danger' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">{{ $conflict['severity'] }}</span>
                            </div>
                            <div class="small mt-1">{{ $conflict['summary'] }}</div>
                            <div class="small text-muted">{{ $conflict['detail'] }}</div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-success mb-0">ยังไม่พบ conflict สำหรับ mapping นี้</div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Latest Message</h5>
                @if($latestMessage)
                    <div class="small mb-2">message id: <code>{{ $latestMessage->id }}</code></div>
                    <div class="small mb-2">page_id: <code>{{ $latestMessage->page_id ?: '-' }}</code></div>
                    <div class="small mb-2">psid: <code>{{ $latestMessage->psid }}</code></div>
                    <div class="small mb-2">created_at: <code>{{ $latestMessage->created_at?->format('d/m/Y H:i:s') }}</code></div>
                    <div class="border rounded-3 p-3 bg-light">{{ $latestMessage->message_text ?: '-' }}</div>
                    <div class="mt-3">
                        <a href="{{ route('customers.messenger.messages.show', $latestMessage->id) }}" class="btn btn-outline-primary btn-sm">เปิด Message Detail</a>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">ยังไม่มีข้อความที่ match กับ PSID นี้</div>
                @endif
            </div>
        </div>

        <div class="card" id="manual-resolve">
            <div class="card-body">
                <h5 class="mb-3">Admin Actions</h5>
                <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันบันทึก manual resolve สำหรับ mapping นี้?')">
                    @csrf
                    <input type="hidden" name="action" value="resolve_manually">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Page ID</label>
                            <input type="text" name="facebook_page_id" class="form-control" value="{{ old('facebook_page_id', $defaultPageId) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Facebook Name</label>
                            <input type="text" name="facebook_name" class="form-control" value="{{ old('facebook_name', $mapping->facebook_name) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Facebook PSID</label>
                            <input type="text" name="facebook_psid" class="form-control" value="{{ old('facebook_psid', $mapping->facebook_psid) }}" required>
                            <div class="form-text">ระบบจะปฏิเสธทันทีถ้า PSID นี้ถูกผูกกับ mapping อื่นที่ connected อยู่แล้ว</div>
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-success">Save manual resolve</button>
                    </div>
                </form>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันเปิดเคสนี้กลับเป็น pending?')">
                        @csrf
                        <input type="hidden" name="action" value="reopen_pending">
                        <button type="submit" class="btn btn-outline-warning">Re-open pending</button>
                    </form>
                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark เป็น expired?')">
                        @csrf
                        <input type="hidden" name="action" value="mark_expired">
                        <button type="submit" class="btn btn-outline-secondary">Mark expired</button>
                    </form>
                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันล้าง PSID และ reset การผูก Messenger?')">
                        @csrf
                        <input type="hidden" name="action" value="clear_psid_reset">
                        <button type="submit" class="btn btn-outline-danger">Clear PSID and reset</button>
                    </form>
                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark mapping นี้เป็น needs review?')">
                        @csrf
                        <input type="hidden" name="action" value="mark_needs_review">
                        <input type="hidden" name="note" value="manual review requested from mapping detail">
                        <button type="submit" class="btn btn-outline-warning">Mark needs review</button>
                    </form>
                </div>
                <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" class="mt-3" onsubmit="return confirm('ยืนยันบันทึก note สำหรับ mapping นี้?')">
                    @csrf
                    <input type="hidden" name="action" value="add_note">
                    <label class="form-label">Add admin note</label>
                    <textarea name="note" rows="3" class="form-control" placeholder="บันทึกเหตุผลการตรวจ/แก้ conflict หรือหมายเหตุสำหรับทีม">{{ old('note') }}</textarea>
                    <button type="submit" class="btn btn-outline-dark mt-2">Save note</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body border-bottom">
        <h5 class="mb-1">Order/Chat Candidates</h5>
        <p class="text-muted mb-0">เป็น candidate แบบ read-only และ manual action เท่านั้น ยังไม่มีการแก้ order จริงโดยอัตโนมัติ</p>
    </div>
    <div class="card-body">
        <h6 class="mb-3">Order Candidates</h6>
        @if($orderCandidates->isNotEmpty())
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>ลูกค้า</th>
                            <th>Matched By</th>
                            <th>Confidence</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orderCandidates as $order)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $order->code ?: ('#' . $order->id) }}</div>
                                    <div class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div>{{ $order->customer_name ?: '-' }}</div>
                                    <div class="small text-muted">{{ $order->customer_username }}</div>
                                </td>
                                <td>
                                    <div class="small"><code>{{ $order->matched_by }}</code></div>
                                    <div class="small text-muted">{{ $order->match_reason }}</div>
                                </td>
                                <td><span class="badge bg-success-subtle text-success-emphasis">{{ $order->confidence }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        <a href="{{ $order->detail_link }}" class="btn btn-outline-primary btn-sm">Order detail</a>
                                        <form action="{{ route('customers.messenger-mappings.orders.attach', [$mapping->id, $order->id]) }}" method="POST" onsubmit="return confirm('ยืนยัน attach order นี้กับ mapping?')">
                                            @csrf
                                            <input type="hidden" name="matched_by" value="{{ $order->matched_by }}">
                                            <input type="hidden" name="confidence" value="{{ $order->confidence }}">
                                            <button type="submit" class="btn btn-success btn-sm">Attach order</button>
                                        </form>
                                        <form action="{{ route('customers.messenger-mappings.orders.review', [$mapping->id, $order->id]) }}" method="POST" onsubmit="return confirm('ยืนยัน mark order นี้เป็น needs review?')">
                                            @csrf
                                            <input type="hidden" name="matched_by" value="{{ $order->matched_by }}">
                                            <input type="hidden" name="confidence" value="ambiguous">
                                            <button type="submit" class="btn btn-outline-warning btn-sm">Mark needs review</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border">No order/chat candidate found ในฝั่ง orders</div>
        @endif

        <h6 class="mb-3">Attached Orders</h6>
        @if($attachedOrderLinks->isNotEmpty())
            <div class="table-responsive mb-4">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Matched By</th>
                            <th>Attached At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attachedOrderLinks as $link)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $link->order?->code ?: ('#' . $link->order_id) }}</div>
                                    <div class="small text-muted">{{ $link->order?->customer_username ?: '-' }}</div>
                                </td>
                                <td><span class="badge bg-success-subtle text-success-emphasis">{{ $link->status }}</span></td>
                                <td>
                                    <div class="small"><code>{{ $link->matched_by ?: '-' }}</code></div>
                                    <div class="small text-muted">{{ $link->confidence ?: '-' }}</div>
                                </td>
                                <td class="small">{{ $link->updated_at?->format('d/m/Y H:i:s') }}</td>
                                <td class="text-end">
                                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                                        @if($link->order)
                                            <a href="{{ route('orders.show', $link->order->id) }}" class="btn btn-outline-primary btn-sm">Order detail</a>
                                        @endif
                                        <form action="{{ route('customers.messenger-order-links.detach', $link->id) }}" method="POST" onsubmit="return confirm('ยืนยัน detach order นี้ออกจาก mapping?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Detach order</button>
                                        </form>
                                        <form action="{{ route('customers.messenger-order-links.action', $link->id) }}" method="POST" onsubmit="return confirm('ยืนยันเก็บ link นี้ไว้ แล้ว detach link อื่นของ order เดียวกัน?')">
                                            @csrf
                                            <input type="hidden" name="action" value="keep_primary_detach_others">
                                            <button type="submit" class="btn btn-outline-success btn-sm">Keep this link</button>
                                        </form>
                                        <form action="{{ route('customers.messenger-order-links.action', $link->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark link นี้เป็น needs review?')">
                                            @csrf
                                            <input type="hidden" name="action" value="mark_needs_review">
                                            <button type="submit" class="btn btn-outline-warning btn-sm">Needs review</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border">ยังไม่มี attached order</div>
        @endif

        <h6 class="mb-3">Chat Candidates</h6>
        @if($chatCandidates->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Chat Candidate</th>
                            <th>Matched By</th>
                            <th>Confidence</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chatCandidates as $candidate)
                            <tr>
                                <td>
                                    <div class="fw-semibold">Messenger Thread</div>
                                    <div class="small text-muted">{{ $candidate->message_count }} message(s)</div>
                                    <div class="small text-muted">ล่าสุด: {{ \Illuminate\Support\Str::limit($candidate->latest_message?->message_text, 50) }}</div>
                                </td>
                                <td>
                                    <div class="small"><code>{{ $candidate->matched_by }}</code></div>
                                    <div class="small text-muted">{{ $candidate->match_reason }}</div>
                                </td>
                                <td><span class="badge bg-success-subtle text-success-emphasis">{{ $candidate->confidence }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="{{ $candidate->messages_link }}" class="btn btn-outline-primary btn-sm">ดูข้อความ</a>
                                        <a href="{{ $candidate->chat_link }}" class="btn btn-outline-secondary btn-sm">เปิดหน้า Chat</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-light border mb-0">No order/chat candidate found ในฝั่ง chat</div>
        @endif
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">Message History</h5>
                        <p class="text-muted mb-0">รายการข้อความที่ match ผ่าน PSID เดียวกัน</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>เวลา</th>
                                <th>ข้อความ</th>
                                <th>page_id / psid</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $message)
                                <tr>
                                    <td class="small">{{ $message->created_at?->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $message->message_text ?: '-' }}</td>
                                    <td class="small">
                                        <div><code>{{ $message->page_id ?: '-' }}</code></div>
                                        <div><code>{{ $message->psid }}</code></div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('customers.messenger.messages.show', $message->id) }}" class="btn btn-outline-primary btn-sm">รายละเอียด</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">ยังไม่มีประวัติข้อความที่ match กับ mapping นี้</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">Reply Draft Foundation</h5>
                        <p class="text-muted mb-0">บันทึก draft, dry-run และส่ง test แบบถูกควบคุมด้วย flag + allowlist</p>
                    </div>
                    <span class="badge {{ $pilotSendState['badge'] }}">
                        {{ $pilotSendState['label'] }}
                    </span>
                </div>

                @if(! $messengerSendEnabled)
                    <div class="alert alert-warning d-flex align-items-start gap-2">
                        <div class="fw-semibold">Messenger real send is disabled.</div>
                        <div class="small mb-0">ปุ่ม `Send test` จะไม่ยิงข้อความจริงใน production รอบนี้ และ draft จะถูกบันทึก/blocked เพื่อให้แอดมินตรวจ flow ได้อย่างปลอดภัย</div>
                    </div>
                @endif

                <div class="alert alert-light border">
                    <div class="fw-semibold mb-1">Pilot send state: {{ $pilotSendState['label'] }}</div>
                    <div class="small text-muted">{{ $pilotSendState['detail'] }}</div>
                    <a href="{{ route('customers.messenger.send-control') }}" class="btn btn-outline-dark btn-sm mt-2">เปิด Pilot Send Control</a>
                </div>

                <div class="alert alert-light border">
                    <div class="small">facebook_page_id: <code>{{ $mapping->facebook_page_id ?: '-' }}</code></div>
                    <div class="small">facebook_psid: <code>{{ $mapping->facebook_psid ?: '-' }}</code></div>
                    <div class="small">reply ready: <strong>{{ $mapping->facebook_psid && $mapping->facebook_page_id && $displayStatus === 'connected' ? 'yes' : 'no' }}</strong></div>
                </div>

                <div class="border rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <div class="fw-semibold">Safe Send Checklist</div>
                        <span class="badge bg-light text-dark border">admin confirm required</span>
                    </div>
                    <div class="row g-2 small">
                        @foreach($sendChecklist as $check)
                            <div class="col-md-6">
                                <span class="badge {{ $check['passed'] ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">
                                    {{ $check['passed'] ? 'pass' : 'block' }}
                                </span>
                                <span class="ms-1">{{ $check['label'] }}</span>
                            </div>
                        @endforeach
                        <div class="col-md-6">
                            <span class="badge bg-warning-subtle text-warning-emphasis">confirm</span>
                            <span class="ms-1">admin confirms before Send test</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('customers.messenger-mappings.reply-drafts.store', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันบันทึก reply draft?')">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Reply Draft</label>
                        <textarea name="draft_text" rows="4" class="form-control" required>{{ old('draft_text', 'สวัสดีค่ะ ทีม CF Manager รับเรื่องของคุณแล้ว กำลังตรวจสอบออเดอร์ให้อยู่ค่ะ') }}</textarea>
                    </div>
                    <div class="border rounded-3 p-3 bg-light mb-3">
                        <div class="small text-muted mb-1">Preview payload</div>
                        <pre class="mb-0 small">{{ json_encode(['recipient' => ['id' => $mapping->facebook_psid], 'message' => ['text' => old('draft_text', 'สวัสดีค่ะ ทีม CF Manager รับเรื่องของคุณแล้ว กำลังตรวจสอบออเดอร์ให้อยู่ค่ะ')], 'mode' => 'draft'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    <button type="submit" class="btn btn-primary">Save draft</button>
                </form>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Reply Draft History</h5>
                @forelse($replyDrafts as $draft)
                    @php
                        $draftBadge = match ($draft->status) {
                            'sent' => 'bg-success-subtle text-success-emphasis',
                            'failed' => 'bg-danger-subtle text-danger-emphasis',
                            'blocked_by_flag', 'blocked_by_allowlist' => 'bg-warning-subtle text-warning-emphasis',
                            'dry_run' => 'bg-primary-subtle text-primary-emphasis',
                            default => 'bg-info-subtle text-info-emphasis',
                        };
                    @endphp
                    <div class="border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge {{ $draftBadge }}">{{ $draft->status }}</span>
                            <span class="small text-muted">Draft #{{ $draft->id }} | {{ $draft->created_at?->format('d/m/Y H:i:s') }}</span>
                        </div>
                        <div class="mt-2">{{ $draft->message_preview ?: $draft->draft_text }}</div>
                        <div class="small text-muted mt-2">last_action: {{ $draft->last_action ?: '-' }}</div>
                        @if($draft->failure_reason)
                            <div class="small text-danger mt-2">failure_reason: {{ $draft->failure_reason }}</div>
                        @endif
                        @if($draft->sent_at)
                            <div class="small text-muted mt-2">sent_at: {{ $draft->sent_at?->format('d/m/Y H:i:s') }}</div>
                        @endif
                        <div class="small text-muted mt-2">provider_message_id: <code>{{ $draft->provider_message_id ?: '-' }}</code></div>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <form action="{{ route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]) }}" method="POST" onsubmit="return confirm('ยืนยันอัปเดต draft นี้เป็น dry-run?')">
                                @csrf
                                <input type="hidden" name="action" value="dry_run">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Run dry-run</button>
                            </form>
                            <form action="{{ route('customers.messenger-mappings.reply-drafts.action', [$mapping->id, $draft->id]) }}" method="POST" onsubmit="return confirm('ยืนยันส่ง test ผ่าน Messenger? ระบบจะยัง block หาก flag, allowlist, token หรือ checklist ไม่ผ่าน')">
                                @csrf
                                <input type="hidden" name="action" value="send_test">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Send test</button>
                            </form>
                            @if($mapping->facebook_psid)
                                <a href="{{ route('customers.messenger.messages', ['psid' => $mapping->facebook_psid]) }}" class="btn btn-outline-secondary btn-sm">Message history</a>
                            @endif
                        </div>
                        @if($draft->response_payload)
                            <pre class="small bg-light rounded p-2 mt-2 mb-0">{{ json_encode($draft->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                @empty
                    <div class="text-muted">ยังไม่มี reply draft</div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Audit Log</h5>
                @forelse($auditLogs as $log)
                    <div class="border rounded-3 p-3 mb-2">
                        <div class="d-flex justify-content-between gap-2">
                            <div class="fw-semibold">{{ $log->action }}</div>
                            <div class="small text-muted">{{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                        </div>
                        <div class="small text-muted">target: {{ $log->target_type }} #{{ $log->target_id ?: '-' }}</div>
                        @if($log->meta)
                            <pre class="small bg-light rounded p-2 mt-2 mb-0">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                        @endif
                    </div>
                @empty
                    <div class="text-muted">ยังไม่มี audit log สำหรับ mapping นี้</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
