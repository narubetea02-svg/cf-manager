@extends('layouts.admin')
@section('title', 'Pilot Send Control')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">Pilot Send Control</h4>
                <p class="text-muted mb-0">ดูสถานะการส่ง Messenger จริงแบบจำกัดวง โดยไม่แสดง token และไม่เปิด/ปิด env จากหน้าเว็บ</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary btn-sm">กลับ Customers</a>
                <a href="{{ route('customers.messenger.readiness') }}" class="btn btn-outline-success btn-sm">Readiness</a>
                <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-outline-danger btn-sm">Conflicts</a>
                <a href="{{ route('customers.messenger.messages') }}" class="btn btn-outline-primary btn-sm">Messages</a>
            </div>
        </div>
    </div>
</div>

@if(! $sendEnabled)
    <div class="alert alert-warning">
        <strong>Real send disabled.</strong>
        Production ยังตั้งค่า <code>MESSENGER_SEND_ENABLED=false</code> ดังนั้นปุ่ม Send test จะไม่ส่งข้อความจริงและควรถูก block โดยระบบ
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Send Flag</div>
                <h5 class="mt-2 mb-0">
                    <span class="badge {{ $sendEnabled ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                        {{ $sendEnabled ? 'enabled' : 'disabled' }}
                    </span>
                </h5>
                <div class="small text-muted mt-2">เปลี่ยนได้จาก server env เท่านั้น</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Page Token</div>
                <h5 class="mt-2 mb-0">
                    <span class="badge {{ $pageTokenStatus === 'available' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                        {{ $pageTokenStatus }}
                    </span>
                </h5>
                <div class="small text-muted mt-2">ไม่แสดง token ใน UI</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Eligible for Test Send</div>
                <h3 class="mt-2 mb-0">{{ number_format($eligibleCount) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Blocked / Unavailable</div>
                <h6 class="mt-2 mb-1">allowlist: {{ number_format($blockedAllowlistCount) }}</h6>
                <h6 class="mb-1">flag: {{ number_format($blockedFlagCount) }}</h6>
                <h6 class="mb-0">token: {{ number_format($unavailableCount) }}</h6>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Allowlist PSID</h5>
                @forelse($allowlistMasked as $masked)
                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $masked }}</span>
                @empty
                    <div class="text-muted">ว่าง: ยังไม่มี PSID ที่อนุญาตสำหรับ pilot send</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Pilot Audit Summary</h5>
                <div class="row g-2 small">
                    <div class="col-6">draft saved</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['saved'] ?? 0) }}</div>
                    <div class="col-6">dry_run</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['dry_run'] ?? 0) }}</div>
                    <div class="col-6">blocked_by_flag</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['blocked_by_flag'] ?? 0) }}</div>
                    <div class="col-6">blocked_by_allowlist</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['blocked_by_allowlist'] ?? 0) }}</div>
                    <div class="col-6">sent</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['sent'] ?? 0) }}</div>
                    <div class="col-6">failed</div><div class="col-6 text-end fw-semibold">{{ number_format($draftStats['failed'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">Latest Pilot Activity</h5>
                @if($latestSentDraft)
                    <div class="small text-muted">latest sent draft</div>
                    <div class="fw-semibold">Draft #{{ $latestSentDraft->id }}</div>
                    <div class="small">mapping #{{ $latestSentDraft->customer_mapping_id }}</div>
                    <div class="small text-muted">{{ $latestSentDraft->sent_at?->format('d/m/Y H:i:s') }}</div>
                    <a href="{{ route('customers.messenger-mappings.show', $latestSentDraft->customer_mapping_id) }}" class="btn btn-outline-primary btn-sm mt-2">เปิด mapping detail</a>
                @else
                    <div class="text-muted">ยังไม่มี draft ที่ส่งสำเร็จ</div>
                @endif
                @if($latestAction)
                    <hr>
                    <div class="small text-muted">latest admin action</div>
                    <div class="fw-semibold">{{ $latestAction->action }}</div>
                    <div class="small text-muted">{{ $latestAction->created_at?->format('d/m/Y H:i:s') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="mb-1">Mapping Send Readiness</h5>
                <p class="text-muted mb-0">แสดง 20 mapping ล่าสุดเพื่อให้แอดมินเห็นว่าใครส่งได้ ใครถูก block และเพราะอะไร</p>
            </div>
            <span class="badge bg-light text-dark border">total mappings: {{ number_format($mappingCount) }}</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Mapping</th>
                        <th>Customer</th>
                        <th>PSID</th>
                        <th>Status</th>
                        <th>Pilot Send State</th>
                        <th>Latest Draft</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mappings as $mapping)
                        @php
                            $latestDraft = $mapping->replyDrafts->first();
                            $state = $mapping->pilot_send_state;
                            $maskedPsid = $mapping->facebook_psid ? substr($mapping->facebook_psid, 0, 4) . '****' . substr($mapping->facebook_psid, -4) : '-';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="fw-semibold text-decoration-none">#{{ $mapping->id }}</a>
                                <div class="small text-muted">session #{{ $mapping->portal_session_id ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ $mapping->tiktok_username ? '@'.$mapping->tiktok_username : '-' }}</div>
                                <div class="small text-muted">{{ $mapping->shop?->name }}</div>
                            </td>
                            <td><code>{{ $maskedPsid }}</code></td>
                            <td><span class="badge bg-light text-dark border">{{ $mapping->effectiveStatus() }}</span></td>
                            <td>
                                <span class="badge {{ $state['badge'] }}">{{ $state['label'] }}</span>
                                <div class="small text-muted">{{ $state['detail'] }}</div>
                            </td>
                            <td>
                                @if($latestDraft)
                                    <div>Draft #{{ $latestDraft->id }}</div>
                                    <span class="badge bg-light text-dark border">{{ $latestDraft->status }}</span>
                                @else
                                    <span class="text-muted">none</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                                    <a href="{{ route('customers.messenger.messages') }}" class="btn btn-outline-secondary btn-sm">Messages</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">ยังไม่มี mapping สำหรับ pilot control</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
