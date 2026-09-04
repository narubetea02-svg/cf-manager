@extends('layouts.admin')
@section('title', 'แพ็คของ')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex align-items-center justify-content-between">
            <h4 class="page-title mb-0">แพ็คของ</h4>
            <div class="d-flex gap-2">
                <a href="{{ url('/orders?type=packing') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bx bx-receipt"></i> ไปหน้าคำสั่งซื้อ
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">รอจัดส่ง</div>
                <h3 class="mt-2 mb-1">{{ $orders->whereIn('status', ['paid', 'packing'])->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">จัดส่งแล้ววันนี้</div>
                <h3 class="mt-2 mb-1">{{ $orders->where('status', 'shipped')->filter(function($q){ return $q->shipped_at && \Carbon\Carbon::parse($q->shipped_at)->isToday(); })->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">ผู้ให้บริการขนส่ง</div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        @foreach ($carriers as $c)
                            <span class="badge bg-{{ $c->connection_status === 'configured' ? 'success' : 'warning' }}-subtle text-{{ $c->connection_status === 'configured' ? 'success' : 'warning' }} me-1">
                                {{ $c->short_name ?? $c->name }}
                                @if ($c->connection_status === 'not_configured')
                                    <small>(not_configured)</small>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
                <a href="{{ url('/settings#shipping-tab') }}" class="btn btn-sm btn-outline-secondary">ตั้งค่าขนส่ง</a>
            </div>
        </div>
    </div>
</div>

@if ($highlightOrder)
<div class="alert alert-info mb-3">
    <i class="bx bx-info-circle"></i> กำลัง focus: ออเดอร์ <strong>#{{ $highlightOrder->order_number }}</strong>
    — {{ $highlightOrder->customer_name ?: 'ไม่ระบุชื่อ' }}
    <a href="{{ url('/packing') }}" class="btn btn-sm btn-outline-secondary ms-2">ยกเลิก focus</a>
</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="packing-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px">
                            <input type="checkbox" class="form-check-input" id="select-all">
                        </th>
                        <th>#</th>
                        <th>สินค้า</th>
                        <th>ลูกค้า</th>
                        <th>ที่อยู่</th>
                        <th>ยอดรวม</th>
                        <th>สถานะ</th>
                        <th>เลขพัสดุ</th>
                        <th>ขนส่ง</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $isHighlighted = $highlightOrder && $highlightOrder->id === $order->id;
                            $statusLabel = App\Support\OrderStatus::label($order->status);
                            $statusColor = App\Support\OrderStatus::color($order->status);
                        @endphp
                        <tr class="{{ $isHighlighted ? 'table-primary' : '' }}">
                            <td><input type="checkbox" class="form-check-input order-checkbox" value="{{ $order->id }}"></td>
                            <td>
                                <a href="{{ url('/orders/' . $order->id) }}" class="fw-medium">{{ $order->order_number }}</a>
                                <div class="small text-muted">{{ $order->created_at ? $order->created_at->format('d/m H:i') : '' }}</div>
                            </td>
                            <td>
                                <div>{{ $order->product->name ?? '-' }}</div>
                                @if ($order->quantity)
                                    <div class="small text-muted">x{{ $order->quantity }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $order->customer_name ?: $order->customer_username ?: '-' }}</div>
                                <div class="small text-muted">{{ $order->customer_phone ?: '' }}</div>
                            </td>
                            <td class="small">{{ Str::limit($order->shipping_address ?? $order->notes ?? '-', 40) }}</td>
                            <td class="fw-medium">{{ number_format($order->net_amount, 2) }}</td>
                            <td>
                                <span class="badge badge-soft-{{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if ($order->tracking_number)
                                    <span class="text-success">{{ $order->tracking_number }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($order->shipment)
                                    {{ $order->shipment->carrier ?? '-' }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if (in_array($order->status, ['paid', 'packing']))
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#shipModal{{ $order->id }}">
                                        <i class="bx bx-package"></i> จัดส่ง
                                    </button>
                                @elseif ($order->status === 'shipped')
                                    <span class="badge bg-info">จัดส่งแล้ว</span>
                                @endif
                                <a href="{{ url('/orders/' . $order->id) }}" class="btn btn-sm btn-soft-secondary">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">ไม่มีออเดอร์ที่ต้องจัดส่ง</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Ship Modals -->
@foreach ($orders->whereIn('status', ['paid', 'packing']) as $order)
    <div class="modal fade" id="shipModal{{ $order->id }}" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ url('/packing/tracking/' . $order->id) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">จัดส่ง: #{{ $order->order_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ลูกค้า</label>
                        <div class="fw-medium">{{ $order->customer_name ?: $order->customer_username ?: '-' }}</div>
                        <div class="small text-muted">{{ $order->customer_phone ?: '' }}</div>
                        <div class="small mt-1">{{ Str::limit($order->shipping_address ?? $order->notes ?? '', 100) }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สินค้า</label>
                        <div>{{ $order->product->name ?? '-' }} x{{ $order->quantity ?: 1 }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยอดรวม</label>
                        <div class="fw-medium">{{ number_format($order->net_amount, 2) }} บาท</div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">ผู้ให้บริการขนส่ง</label>
                        <select name="carrier" class="form-select" required>
                            <option value="">— เลือกขนส่ง —</option>
                            @foreach ($carriers as $c)
                                <option value="{{ $c->short_name ?? $c->name }}"
                                    data-status="{{ $c->connection_status }}">
                                    {{ $c->name }}
                                    @if ($c->connection_status === 'not_configured')
                                        (not_configured)
                                    @elseif ($c->connection_status === 'pending_docs')
                                        (pending_docs)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1">
                            ⚠️ ถ้า carrier ยังเป็น not_configured จะบันทึกเลขพัสดุแบบ manual เท่านั้น
                            (ไม่มี label อัตโนมัติ)
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หมายเลขพัสดุ</label>
                        <input type="text" name="tracking_number" class="form-control"
                            placeholder="กรอกเลขพัสดุ" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check"></i> บันทึกและจัดส่ง
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<!-- Batch Print Form -->
<form id="batch-print-form" method="POST" action="{{ url('/packing/batch-print') }}" style="display:none">
    @csrf
    <input type="hidden" name="ids" id="batch-ids">
</form>
@endsection

@push('styles')
<style>
    .badge-soft-success { background: rgba(52,195,143,.12); color: #34c38f; }
    .badge-soft-warning { background: rgba(241,180,76,.12); color: #f1b44c; }
    .badge-soft-primary { background: rgba(85,110,230,.12); color: #556ee6; }
    .badge-soft-info { background: rgba(80,165,241,.12); color: #50a5f1; }
    .badge-soft-secondary { background: rgba(116,126,138,.12); color: #747e8a; }
    .btn-soft-secondary { background: rgba(116,126,138,.1); color: #747e8a; border: none; }
    .btn-soft-secondary:hover { background: #747e8a; color: #fff; }
    .bg-success-subtle { background: rgba(52,195,143,.15); }
    .bg-warning-subtle { background: rgba(241,180,76,.15); }
    .text-success { color: #34c38f !important; }
    .text-warning { color: #f1b44c !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox
    var selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.order-checkbox').forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
        });
    }

    // Batch print
    var batchPrintBtn = document.getElementById('batch-print-btn');
    if (batchPrintBtn) {
        batchPrintBtn.addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.order-checkbox:checked').forEach(function(cb) {
                selected.push(cb.value);
            });
            if (selected.length === 0) {
                alert('กรุณาเลือกออเดอร์ก่อน');
                return;
            }
            if (!confirm('พิมพ์ใบปะหน้า ' + selected.length + ' ออเดอร์?')) return;
            document.getElementById('batch-ids').value = selected.join(',');
            document.getElementById('batch-print-form').submit();
        });
    }
});
</script>
@endpush
