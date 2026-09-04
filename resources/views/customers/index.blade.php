@extends('layouts.admin')
@section('title', 'CF Manager Customers')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('mapping_action') === 'updated')
            <div class="alert alert-success">อัปเดตสถานะ Messenger mapping เรียบร้อยแล้ว</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">CF Manager Customers</h4>
                <p class="text-muted mb-0">ศูนย์รวมข้อมูลลูกค้า, Messenger mapping และ readiness ของทีมแอดมิน</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/customers?customer_segment=normal#customer-table') }}" class="btn {{ $customerSegment === 'normal' ? 'btn-primary' : 'btn-light border' }}">ลูกค้าปกติ</a>
                <a href="{{ url('/customers?customer_segment=blocked#customer-table') }}" class="btn {{ $customerSegment === 'blocked' ? 'btn-primary' : 'btn-light border' }}">ลูกค้าที่ถูกบล็อก</a>
                <a href="{{ url('/customers#customer-table') }}" class="btn {{ $customerSegment === 'all' ? 'btn-primary' : 'btn-light border' }}">ลูกค้าทั้งหมด</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ลูกค้าทั้งหมด</div>
                <h3 class="mt-2 mb-1">{{ number_format($summary['total'] ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">มีเบอร์โทร</div>
                <h3 class="mt-2 mb-1">{{ number_format($summary['with_phone'] ?? 0) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">มี TikTok/Facebook username</div>
                <h3 class="mt-2 mb-1">{{ number_format($summary['with_username'] ?? 0) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h5 class="mb-1">Messenger Mapping Monitor</h5>
                <p class="text-muted mb-0">ดูสถานะการผูกลูกค้าจาก Portal ไปยัง Messenger ของเพจแบบเรียลไทม์</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('customers.messenger.messages') }}" class="btn btn-outline-primary btn-sm">ดูข้อความ Messenger</a>
                <a href="{{ route('customers.messenger.send-control') }}" class="btn btn-outline-dark btn-sm">Pilot Send Control</a>
                <a href="{{ route('customers.messenger.readiness') }}" class="btn btn-outline-success btn-sm">ดู Downstream Readiness</a>
                <a href="{{ route('customers.messenger.conflicts') }}" class="btn btn-outline-danger btn-sm">ดู Conflict Center @if(($conflictCount ?? 0) > 0)<span class="ms-1">({{ $conflictCount }})</span>@endif</a>
                <a href="{{ url('/customers') }}" class="btn btn-outline-secondary btn-sm">รีเฟรช</a>
                <a href="{{ url('/customers?mapping_status=pending_messenger') }}" class="btn btn-sm {{ $statusFilter === 'pending_messenger' ? 'btn-warning' : 'btn-outline-warning' }}">Pending</a>
                <a href="{{ url('/customers?mapping_status=connected') }}" class="btn btn-sm {{ $statusFilter === 'connected' ? 'btn-success' : 'btn-outline-success' }}">Connected</a>
                <a href="{{ url('/customers?mapping_status=ambiguous') }}" class="btn btn-sm {{ $statusFilter === 'ambiguous' ? 'btn-danger' : 'btn-outline-danger' }}">Ambiguous</a>
                <a href="{{ url('/customers?mapping_status=expired') }}" class="btn btn-sm {{ $statusFilter === 'expired' ? 'btn-dark' : 'btn-outline-dark' }}">Expired</a>
                @if($statusFilter)
                    <a href="{{ url('/customers') }}" class="btn btn-sm btn-primary">ล้างตัวกรอง</a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Pending</div>
                    <div class="h4 mb-0">{{ number_format($mappingSummary['pending'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">ลูกค้าที่เชื่อมจริง</div>
                    <div class="h4 mb-0">{{ number_format($mappingSummary['connected_real_users'] ?? 0) }}</div>
                    <div class="small text-muted mt-1">records ทั้งหมด {{ number_format($mappingSummary['connected_records'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Ambiguous</div>
                    <div class="h4 mb-0">{{ number_format($mappingSummary['ambiguous'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Expired</div>
                    <div class="h4 mb-0">{{ number_format($mappingSummary['expired'] ?? 0) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 h-100">
                    <div class="text-muted small">Conflicts</div>
                    <div class="h4 mb-0">{{ number_format($conflictCount ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="table-responsive" id="customer-table">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Portal Session</th>
                        <th>ลูกค้า</th>
                        <th>Status</th>
                        <th>Page / PSID</th>
                        <th>Source</th>
                        <th>เวลา</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messengerMappings as $mapping)
                        @php
                            $badgeMap = [
                                'pending_messenger' => 'bg-warning-subtle text-warning-emphasis',
                                'connected' => 'bg-success-subtle text-success-emphasis',
                                'ambiguous' => 'bg-danger-subtle text-danger-emphasis',
                                'expired' => 'bg-secondary-subtle text-secondary-emphasis',
                            ];
                            $displayStatus = $mapping->display_status ?? $mapping->status;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="text-decoration-none">#{{ $mapping->portal_session_id }}</a>
                                </div>
                                <div class="text-muted small">{{ $mapping->portalSession?->sid ?: '-' }}</div>
                                @if($mapping->latest_message)
                                    <div class="mt-2">
                                        <a href="{{ route('customers.messenger.messages.show', $mapping->latest_message->id) }}" class="small">ข้อความล่าสุด</a>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $mapping->tiktok_username ? '@' . $mapping->tiktok_username : '-' }}</div>
                                <div class="text-muted small">{{ $mapping->facebook_name ?: 'ยังไม่ทราบชื่อ Facebook' }}</div>
                                @if(($mapping->conflicts ?? collect())->isNotEmpty())
                                    <div class="small text-danger mt-1">{{ $mapping->conflicts->count() }} conflict(s)</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $badgeMap[$displayStatus] ?? 'bg-light text-dark' }}">{{ $displayStatus }}</span>
                            </td>
                            <td>
                                <div class="small">page_id: <code>{{ $mapping->facebook_page_id ?: '-' }}</code></div>
                                <div class="small">psid: <code>{{ $mapping->facebook_psid ?: '-' }}</code></div>
                            </td>
                            <td>
                                <div class="small">{{ $mapping->connected_source ?: '-' }}</div>
                                <div class="text-muted small">shop_id {{ $mapping->shop_id }}</div>
                                <div class="text-muted small">
                                    @if($mapping->latest_message)
                                        ล่าสุด: {{ \Illuminate\Support\Str::limit($mapping->latest_message->message_text, 36) }}
                                    @else
                                        ยังไม่มีข้อความล่าสุด
                                    @endif
                                </div>
                            </td>
                            <td class="small">
                                <div>pending: {{ $mapping->messenger_link_pending_at?->format('d/m H:i:s') ?: '-' }}</div>
                                <div>connected: {{ $mapping->connected_at?->format('d/m H:i:s') ?: '-' }}</div>
                                <div>updated: {{ $mapping->updated_at?->format('d/m H:i:s') ?: '-' }}</div>
                                <div class="mt-2 d-grid gap-1">
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}" class="btn btn-primary btn-sm w-100">ดูรายละเอียด</a>
                                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันเปิดเคสนี้กลับเป็น pending?')">
                                        @csrf
                                        <input type="hidden" name="action" value="reopen_pending">
                                        <button type="submit" class="btn btn-outline-warning btn-sm w-100">Re-open pending</button>
                                    </form>
                                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยัน mark เป็น expired?')">
                                        @csrf
                                        <input type="hidden" name="action" value="mark_expired">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Mark expired</button>
                                    </form>
                                    <form action="{{ route('customers.messenger-mappings.action', $mapping->id) }}" method="POST" onsubmit="return confirm('ยืนยันล้าง PSID และ reset การผูก Messenger?')">
                                        @csrf
                                        <input type="hidden" name="action" value="clear_psid_reset">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Clear PSID and reset</button>
                                    </form>
                                    <a href="{{ route('customers.messenger-mappings.show', $mapping->id) }}#manual-resolve" class="btn btn-outline-success btn-sm w-100">Resolve manually</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">ยังไม่มี Messenger mapping สำหรับเฝ้าดู</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $messengerMappings->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="cf-page-panel mb-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h5 class="mb-1">ข้อมูลลูกค้า</h5>
                    <p class="text-muted mb-0">ค้นหา ดูยอดซื้อ และเปิดทางลัดไปยังออเดอร์หรือ Messenger mapping ได้จากหน้าเดียว</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ url('/customers?channel=app#customer-table') }}" class="btn btn-light border">App</a>
                    <a href="{{ url('/customers?channel=line#customer-table') }}" class="btn btn-light border">LINE</a>
                    <a href="{{ url('/customers?channel=facebook#customer-table') }}" class="btn btn-light border">Facebook</a>
                    <a href="{{ url('/customers#customer-table') }}" class="btn btn-light border">ทั้งหมด</a>
                </div>
            </div>

            <form method="GET" action="{{ url('/customers') }}#customer-table" class="row g-2 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label small text-muted">ค้นหาลูกค้า</label>
                    <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Search ...">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small text-muted">มีเบอร์โทร</label>
                    <select name="has_phone" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="yes" {{ $hasPhone === 'yes' ? 'selected' : '' }}>มีเบอร์</option>
                        <option value="no" {{ $hasPhone === 'no' ? 'selected' : '' }}>ไม่มีเบอร์</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small text-muted">มี Username</label>
                    <select name="has_username" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <option value="yes" {{ $hasUsername === 'yes' ? 'selected' : '' }}>มี Username</option>
                        <option value="no" {{ $hasUsername === 'no' ? 'selected' : '' }}>ไม่มี Username</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small text-muted">เรียงตาม</label>
                    <select name="sort" class="form-select">
                        <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>ล่าสุด</option>
                        <option value="spent_desc" {{ $sort === 'spent_desc' ? 'selected' : '' }}>ยอดซื้อสูงสุด</option>
                        <option value="orders_desc" {{ $sort === 'orders_desc' ? 'selected' : '' }}>สั่งซื้อบ่อยสุด</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>ชื่อตาม A-Z</option>
                    </select>
                </div>
                <div class="col-6 col-lg-2">
                    <input type="hidden" name="customer_segment" value="{{ $customerSegment }}">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                        <a href="{{ url('/customers#customer-table') }}" class="btn btn-light border">ล้าง</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ลูกค้า</th>
                        <th>ข้อมูลจัดส่ง</th>
                        <th width="260">จัดการข้อมูล</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        @php
                            $normalizedUsername = ltrim((string) $customer->customer_username, '@');
                            $mappingTarget = $normalizedUsername ? $messengerMappings->getCollection()->firstWhere('tiktok_username', strtolower($normalizedUsername)) : null;
                            $orderSearch = $customer->customer_phone ?: ($customer->customer_username ?: $customer->customer_name);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $customer->customer_name ?: '-' }}</div>
                                <div class="text-muted small">{{ $customer->customer_username ?: 'ไม่มี username' }}</div>
                            </td>
                            <td>
                                <div>{{ $customer->customer_phone ?: '-' }}</div>
                                <div class="text-muted small">สั่งซื้อ {{ number_format($customer->order_count) }} ครั้ง · รวม {{ number_format($customer->total_spent, 2) }} ฿</div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ url('/orders?q=' . urlencode((string) $orderSearch)) }}" class="btn btn-primary btn-sm">ดูออเดอร์</a>
                                    @if($mappingTarget)
                                        <a href="{{ route('customers.messenger-mappings.show', $mappingTarget->id) }}" class="btn btn-outline-success btn-sm">Messenger</a>
                                    @else
                                        <a href="{{ url('/customers?mapping_status=pending_messenger') }}" class="btn btn-outline-secondary btn-sm">ดู Pending</a>
                                    @endif
                                    <a href="{{ url('/customers?q=' . urlencode((string) ($customer->customer_phone ?: $customer->customer_name)) . '#customer-table') }}" class="btn btn-light border btn-sm">กรองรายการนี้</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">ไม่พบข้อมูลลูกค้าตามตัวกรองที่เลือก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
