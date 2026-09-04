@extends('layouts.admin')
@section('title', 'CF Manager Readiness')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">CF Manager Readiness Dashboard</h4>
                <p class="text-muted mb-0">รวมสถานะ mapping, message, order candidate และ order link เพื่อให้แอดมินคุม downstream ได้จากหน้ารวม</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">กลับหน้า Customers</a>
                <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-outline-danger">Conflict Center</a>
                <a href="{{ route('customers.messenger.readiness') }}" class="btn btn-primary">รีเฟรช</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @php
                $filters = [
                    '' => 'ทั้งหมด',
                    'ready' => 'Ready',
                    'needs_review' => 'Needs review',
                    'not_ready' => 'Not ready',
                    'connected' => 'Connected',
                    'no_order' => 'No order',
                    'attached' => 'Attached',
                    'ambiguous' => 'Ambiguous',
                    'expired' => 'Expired',
                ];
            @endphp
            @foreach($filters as $value => $label)
                <a href="{{ route('customers.messenger.readiness', $value === '' ? [] : ['readiness' => $value]) }}" class="btn btn-sm {{ $filter === $value ? 'btn-dark' : 'btn-outline-dark' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mapping</th>
                        <th>TikTok / Messenger</th>
                        <th>Latest message</th>
                        <th>Candidates</th>
                        <th>Attached</th>
                        <th>Conflicts</th>
                        <th>Readiness</th>
                        <th>Last activity</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mappings as $mapping)
                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $mapping->id }}</div>
                                <div class="small text-muted">session {{ $mapping->portal_session_id }}</div>
                                <div class="small text-muted">messenger status {{ $mapping->display_status }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $mapping->tiktok_username ? '@' . $mapping->tiktok_username : '-' }}</div>
                                <div class="small"><code>{{ $mapping->facebook_psid ?: '-' }}</code></div>
                            </td>
                            <td>
                                @if($mapping->latest_message)
                                    <div>{{ \Illuminate\Support\Str::limit($mapping->latest_message->message_text, 50) }}</div>
                                    <div class="small text-muted">{{ $mapping->latest_message->created_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <div class="text-muted">ยังไม่มีข้อความล่าสุด</div>
                                @endif
                            </td>
                            <td>
                                <div class="small">orders: {{ $mapping->order_candidates->count() }}</div>
                                <div class="small">chat: {{ $mapping->chat_candidates->count() }}</div>
                            </td>
                            <td>
                                <div class="small">attached orders: {{ $mapping->attached_order_links->count() }}</div>
                            </td>
                            <td>
                                @if(($mapping->conflicts ?? collect())->isNotEmpty())
                                    <span class="badge bg-danger-subtle text-danger-emphasis">{{ $mapping->conflicts->count() }}</span>
                                @else
                                    <span class="badge bg-success-subtle text-success-emphasis">0</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $mapping->readiness['badge'] }}">{{ $mapping->readiness['label'] }}</span>
                                <div class="small text-muted mt-1">{{ $mapping->readiness['detail'] }}</div>
                            </td>
                            <td class="small">
                                {{ optional($mapping->last_activity_at)->format('d/m/Y H:i:s') ?: '-' }}
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="btn btn-outline-primary btn-sm">ดู detail</a>
                                    @if($mapping->latest_message)
                                        <a href="{{ route('customers.messenger.messages.show', $mapping->latest_message->id) }}" class="btn btn-outline-secondary btn-sm">message</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">ยังไม่มี mapping สำหรับ readiness list</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $mappings->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
