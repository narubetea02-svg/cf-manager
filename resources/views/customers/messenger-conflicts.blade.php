@extends('layouts.admin')
@section('title', 'CF Manager Conflict Center')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('mapping_action') === 'updated')
            <div class="alert alert-success">อัปเดต conflict action เรียบร้อยแล้ว</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">CF Manager Conflict Center</h4>
                <p class="text-muted mb-0">รวมเคสที่ต้องตัดสินใจด้วยแอดมินก่อนผูก order หรือทดลองส่งข้อความกลับ</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">กลับหน้า Customers</a>
                <a href="{{ route('customers.messenger.readiness') }}" class="btn btn-outline-success">Readiness</a>
                <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-primary">รีเฟรช</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @php
                $typeFilters = [
                    '' => 'ทั้งหมด',
                    'order_conflict' => 'Order',
                    'mapping_conflict' => 'Mapping',
                    'psid_conflict' => 'PSID',
                    'username_conflict' => 'Username',
                    'multiple_order_candidates' => 'Multiple Orders',
                ];
            @endphp
            @foreach($typeFilters as $value => $label)
                <a href="{{ route('customers.messenger.conflicts', array_filter(['type' => $value ?: null, 'status' => $statusFilter ?: null])) }}" class="btn btn-sm {{ $typeFilter === $value ? 'btn-dark' : 'btn-outline-dark' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mapping</th>
                        <th>Conflict</th>
                        <th>Latest message</th>
                        <th>Readiness</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conflicts as $item)
                        @php
                            $mapping = $item->mapping;
                            $conflict = $item->conflict;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="text-decoration-none">#{{ $mapping->id }}</a>
                                </div>
                                <div class="small text-muted">session {{ $mapping->portal_session_id }}</div>
                                <div class="small text-muted">{{ $mapping->tiktok_username ? '@' . $mapping->tiktok_username : '-' }}</div>
                                <div class="small text-muted">psid: <code>{{ $mapping->facebook_psid ?: '-' }}</code></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold">{{ $conflict['type'] }}</div>
                                    <span class="badge {{ $conflict['severity'] === 'danger' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">{{ $conflict['severity'] }}</span>
                                </div>
                                <div class="small mt-1">{{ $conflict['summary'] }}</div>
                                <div class="small text-muted">{{ $conflict['detail'] }}</div>
                                @if(!empty($conflict['order_code']) || !empty($conflict['order_id']))
                                    <div class="small text-muted mt-1">order: <code>{{ $conflict['order_code'] ?: ('#' . $conflict['order_id']) }}</code></div>
                                @endif
                                @if(!empty($conflict['mapping_ids']))
                                    <div class="small text-muted">mapping ids: <code>{{ implode(', ', $conflict['mapping_ids']) }}</code></div>
                                @endif
                                @if(!empty($conflict['conflicting_link_ids']))
                                    <div class="small text-muted">conflicting link ids: <code>{{ implode(', ', $conflict['conflicting_link_ids']) }}</code></div>
                                @endif
                            </td>
                            <td>
                                @if($item->latest_message)
                                    <div>{{ \Illuminate\Support\Str::limit($item->latest_message->message_text, 50) }}</div>
                                    <div class="small text-muted">{{ $item->latest_message->created_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <div class="text-muted">ยังไม่มีข้อความล่าสุด</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $item->readiness['badge'] }}">{{ $item->readiness['label'] }}</span>
                                <div class="small text-muted mt-1">{{ $item->readiness['detail'] }}</div>
                            </td>
                            <td class="text-end">
                                <div class="d-grid gap-2 justify-content-end">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="btn btn-outline-primary btn-sm">เปิด Mapping Detail</a>
                                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark reviewed ด้วย note?')">
                                        @csrf
                                        <input type="hidden" name="action" value="add_note">
                                        <input type="hidden" name="note" value="conflict reviewed: {{ $conflict['type'] }}">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Mark reviewed</button>
                                    </form>
                                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark mapping นี้เป็น needs review?')">
                                        @csrf
                                        <input type="hidden" name="action" value="mark_needs_review">
                                        <input type="hidden" name="note" value="conflict requires manual review: {{ $conflict['type'] }}">
                                        <button type="submit" class="btn btn-outline-warning btn-sm">Mark needs review</button>
                                    </form>
                                    @if(($conflict['type'] ?? null) === 'order_conflict' && !empty($conflict['primary_link_id']))
                                        <form action="{{ route('customers.messenger-order-links.action', $conflict['primary_link_id']) }}" method="POST" onsubmit="return confirm('ยืนยันเก็บ link นี้ไว้ แล้ว detach link อื่นของ order เดียวกัน?')">
                                            @csrf
                                            <input type="hidden" name="action" value="keep_primary_detach_others">
                                            <button type="submit" class="btn btn-outline-success btn-sm">Keep this link and detach others</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">ยังไม่พบ conflict ที่ต้องจัดการ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $conflicts->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
