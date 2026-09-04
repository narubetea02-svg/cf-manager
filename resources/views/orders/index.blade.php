@extends('layouts.admin')
@section('title', 'คำสั่งซื้อ')

@php
    $tabs = [
        'all' => 'ทั้งหมด',
        'wait_payment' => 'รอชำระเงิน',
        'pending_review' => 'รอตรวจสอบ',
        'hold' => 'ฝากของ',
        'to_ship' => 'ต้องจัดส่ง',
        'packing' => 'รอแพ็ค',
        'cod' => 'รอชำระ COD',
        'completed' => 'สำเร็จแล้ว',
        'cancelled' => 'ยกเลิกแล้ว',
    ];

    $statusOptions = [
        'pending' => 'รอชำระเงิน',
        'confirmed' => 'รอตรวจสอบ',
        'hold' => 'ฝากของ',
        'paid' => 'ต้องจัดส่ง',
        'shipped' => 'พิมพ์/แพ็ค',
        'packing' => 'รอแพ็ค',
        'cod' => 'รอชำระ COD',
        'delivered' => 'สำเร็จแล้ว',
        'cancelled' => 'ยกเลิกแล้ว',
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h4 class="page-title mb-0">คำสั่งซื้อ</h4>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/packing') }}" class="btn btn-light border">ใบปะหน้าและเลขพัสดุ</a>
                <button type="button" class="btn btn-light border" onclick="window.print()">พิมพ์เอกสาร</button>
                <a href="{{ url('/orders/create') }}" class="btn btn-primary">สร้างคำสั่งซื้อ</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">คำสั่งซื้อทั้งหมด</div>
                <h3 class="mt-2 mb-1">{{ number_format($stats['total_orders'] ?? 0) }}</h3>
                <div class="small text-muted">ทุกสถานะรวมกัน</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ยอดขายรวม</div>
                <h3 class="mt-2 mb-1">{{ number_format($stats['revenue'] ?? 0, 2) }} ฿</h3>
                <div class="small text-muted">ไม่รวมคำสั่งซื้อที่ยกเลิก</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ค่าเฉลี่ยต่อออเดอร์</div>
                <h3 class="mt-2 mb-1">{{ number_format($stats['avg_order'] ?? 0, 2) }} ฿</h3>
                <div class="small text-muted">คำนวณจากยอดรวมจริง</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">อัปเดตล่าสุด</div>
                <h3 class="mt-2 mb-1">{{ $stats['latest_update'] ? \Illuminate\Support\Carbon::parse($stats['latest_update'])->format('d/m H:i') : '-' }}</h3>
                <div class="small text-muted">เวลามีการเปลี่ยนแปลงล่าสุด</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ url('/orders') }}" class="row g-2 align-items-end mb-3">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="col-12 col-lg-5">
                <label class="form-label small text-muted">ค้นหาออเดอร์</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="เลขออเดอร์, ลูกค้า, เบอร์โทร, เลขพัสดุ">
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small text-muted">สถานะ</label>
                <select name="status" class="form-select">
                    <option value="">ทุกสถานะ</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                    <a href="{{ url('/orders?type=' . $type) }}" class="btn btn-light border">ล้าง</a>
                </div>
            </div>
        </form>
        <div class="row g-2">
            @foreach($tabs as $key => $label)
                <div class="col-md-3 col-xl">
                    <a href="{{ url('/orders?type=' . $key) }}" class="d-flex justify-content-between align-items-center border rounded px-3 py-2 {{ $type === $key ? 'bg-primary-subtle border-primary' : '' }} text-reset">
                        <span>{{ $label }}</span>
                        <span class="badge bg-light text-dark">{{ $summary[$key] ?? 0 }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach($statusHighlights as $status => $count)
                <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
                    {{ $statusLabels[$status] ?? $status }} {{ $count }}
                </span>
            @endforeach
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>เลขคำสั่งซื้อ</th>
                        <th>ชื่อลูกค้า</th>
                        <th>วันที่ซื้อ</th>
                        <th class="text-end">รวมสุทธิ</th>
                        <th>สถานะ</th>
                        <th>วันที่อัปเดต</th>
                        <th>รายละเอียด</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $order->code ?: ('#' . $order->id) }}</div>
                                <div class="text-muted small">{{ $order->shop->name ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->customer_name ?: '-' }}</div>
                                <div class="text-muted small">{{ $order->customer_username ?: $order->customer_phone ?: '-' }}</div>
                            </td>
                            <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">{{ number_format($order->total_price, 2) }} ฿</td>
                            <td><span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            <td>{{ optional($order->updated_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="small">
                                    <div>{{ $order->product->name ?? '-' }}</div>
                                    <div class="text-muted">{{ $order->tracking_number ?: 'ยังไม่มีเลขพัสดุ' }}</div>
                                </div>
                            </td>
                            <td>
                                <form method="POST" action="{{ url('/orders/' . $order->id) }}" class="d-flex gap-2 align-items-center flex-wrap">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-select form-select-sm" style="min-width: 150px;">
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">บันทึก</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">ไม่มีคำสั่งซื้อในหมวดนี้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
