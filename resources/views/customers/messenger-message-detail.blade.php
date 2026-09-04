@extends('layouts.admin')
@section('title', 'CF Manager Message Detail')

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
                <h4 class="page-title mb-1">CF Manager Message Detail</h4>
                <p class="text-muted mb-0">ดู payload จริง, mapping, candidates และ flow ต่อออเดอร์แบบปลอดภัย</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('customers.messenger.messages') }}" class="btn btn-outline-secondary">กลับไปรายการข้อความ</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Message Info</h5>
                <div class="small mb-2">message id: <code>{{ $message->id }}</code></div>
                <div class="small mb-2">page_id: <code>{{ $message->page_id ?: '-' }}</code></div>
                <div class="small mb-2">psid: <code>{{ $message->psid }}</code></div>
                <div class="small mb-2">created_at: <code>{{ $message->created_at?->format('d/m/Y H:i:s') }}</code></div>
                <div class="small mb-2">direction: <code>{{ $message->direction }}</code></div>
                <div class="mt-3">
                    <div class="fw-semibold mb-2">message_text</div>
                    <div class="border rounded-3 p-3 bg-light">{{ $message->message_text }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-3">Matched Customer Mapping</h5>
                @if($matchedMapping)
                    <div class="small mb-2">portal_session_id: <code>{{ $matchedMapping->portal_session_id }}</code></div>
                    <div class="small mb-2">tiktok_username: <code>{{ $matchedMapping->tiktok_username ?: '-' }}</code></div>
                    <div class="small mb-2">status: <code>{{ $matchedMapping->effectiveStatus() }}</code></div>
                    <div class="small mb-2">connected_source: <code>{{ $matchedMapping->connected_source ?: '-' }}</code></div>
                    <div class="small mb-2">facebook_psid: <code>{{ $matchedMapping->facebook_psid ?: '-' }}</code></div>
                    <div class="small mb-2">facebook_page_id: <code>{{ $matchedMapping->facebook_page_id ?: '-' }}</code></div>
                    @if($readiness)
                        <div class="mt-3">
                            <span class="badge {{ $readiness['badge'] }}">{{ $readiness['label'] }}</span>
                            <div class="small text-muted mt-1">{{ $readiness['detail'] }}</div>
                        </div>
                    @endif
                    @if(($conflicts ?? collect())->isNotEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            <div class="fw-semibold mb-1">Conflict detected</div>
                            @foreach($conflicts as $conflict)
                                <div class="small">{{ $conflict['type'] }}: {{ $conflict['detail'] }}</div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('customers.messenger-mappings.show', $matchedMapping->id) }}" class="btn btn-primary btn-sm">เปิด Mapping Detail</a>
                        <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-outline-danger btn-sm">เปิด Conflict Center</a>
                    </div>
                @else
                    <div class="alert alert-warning">ข้อความนี้ยังไม่ match กับ customer mapping ใด</div>
                    @if($candidateMappings->isNotEmpty())
                        <form action="{{ route('customers.messenger.messages.resolve', $message->id) }}" method="POST" onsubmit="return confirm('ยืนยันใช้ PSID จากข้อความนี้ไป resolve mapping ที่เลือก?')">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Resolve to mapping</label>
                                <select name="mapping_id" class="form-select" required>
                                    <option value="">เลือก mapping เป้าหมาย</option>
                                    @foreach($candidateMappings as $candidate)
                                        <option value="{{ $candidate->id }}">
                                            #{{ $candidate->id }} | session {{ $candidate->portal_session_id }} | @{{ $candidate->tiktok_username ?: '-' }} | {{ $candidate->display_status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Page ID</label>
                                    <input type="text" name="facebook_page_id" class="form-control" value="{{ old('facebook_page_id', $defaultPageId) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Facebook Name</label>
                                    <input type="text" name="facebook_name" class="form-control" value="{{ old('facebook_name', $message->sender_name) }}">
                                </div>
                            </div>
                            <div class="alert alert-light border mt-3 mb-3">
                                PSID จากข้อความนี้จะถูกใช้ทันที: <code>{{ $message->psid }}</code>
                            </div>
                            <button type="submit" class="btn btn-success">Resolve to mapping</button>
                        </form>
                    @else
                        <div class="small text-muted">ยังไม่มี candidate mapping ที่พร้อมให้แอดมินเลือกผูกจากข้อความนี้</div>
                    @endif
                @endif
            </div>
        </div>

        @if($matchedMapping)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Order Candidates</h5>
                    @if($orderCandidates->isNotEmpty())
                        @foreach($orderCandidates as $order)
                            <div class="border rounded-3 p-3 mb-2">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $order->code ?: ('#' . $order->id) }}</div>
                                        <div class="small text-muted">{{ $order->customer_name ?: '-' }} · {{ $order->customer_username }}</div>
                                        <div class="small text-muted">{{ $order->match_reason }}</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ $order->detail_link }}" class="btn btn-outline-primary btn-sm">Order detail</a>
                                        <form action="{{ route('customers.messenger-mappings.orders.attach', [$matchedMapping->id, $order->id]) }}" method="POST" onsubmit="return confirm('ยืนยัน attach order นี้จากหน้า message detail?')">
                                            @csrf
                                            <input type="hidden" name="matched_by" value="{{ $order->matched_by }}">
                                            <input type="hidden" name="confidence" value="{{ $order->confidence }}">
                                            <button type="submit" class="btn btn-success btn-sm">Attach order</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">ยังไม่มี order candidate สำหรับ mapping นี้</div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="mb-3">Attached Orders</h5>
                    @if($attachedOrderLinks->isNotEmpty())
                        @foreach($attachedOrderLinks as $link)
                            <div class="border rounded-3 p-3 mb-2">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <div class="fw-semibold">{{ $link->order?->code ?: ('#' . $link->order_id) }}</div>
                                        <div class="small text-muted">{{ $link->order?->customer_username ?: '-' }}</div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        @if($link->order)
                                            <a href="{{ route('orders.show', $link->order->id) }}" class="btn btn-outline-primary btn-sm">Order detail</a>
                                        @endif
                                        <form action="{{ route('customers.messenger-order-links.detach', $link->id) }}" method="POST" onsubmit="return confirm('ยืนยัน detach order นี้?')">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Detach order</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted">ยังไม่มี attached order</div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Raw Payload</h5>
                <pre class="mb-0 p-3 rounded-3 bg-dark text-light small" style="white-space: pre-wrap;">{{ $payloadJson }}</pre>
            </div>
        </div>
    </div>
</div>
@endsection
