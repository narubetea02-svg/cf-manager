@extends('layouts.admin')
@section('title', 'Financial')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">Financial</h4>
                <p class="text-muted mb-0">ตัดระบบเครดิตออก แต่ยังคงหน้า COD/การเงินในสไตล์ใกล้ต้นฉบับ</p>
            </div>
            <a href="{{ url('/financial?view=history') }}" class="btn btn-light border">ประวัติ</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ยอดคงเหลือ</div>
                <h3 class="mt-2 mb-1">{{ number_format($totalRevenue, 2) }}</h3>
                <div class="small text-muted">รายรับจากออเดอร์ที่ชำระแล้ว</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ยอดเงินที่รอการชำระ</div>
                <h3 class="mt-2 mb-1">{{ number_format($pendingRevenue, 2) }}</h3>
                <div class="small text-muted">เฉพาะออเดอร์สถานะรอชำระ</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">จำนวนคำสั่งซื้อ</div>
                <h3 class="mt-2 mb-1">{{ number_format($totalOrders) }}</h3>
                <div class="small text-muted">รวมทุกออเดอร์ในร้านของคุณ</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">สลิปยืนยันแล้ว</div>
                <h3 class="mt-2 mb-1">{{ number_format($verifiedPayments, 2) }}</h3>
                <div class="small text-muted">ยอดที่ตรวจสอบแล้ว</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>เลขคำสั่งซื้อ</th>
                        <th>หมายเลขพัสดุ</th>
                        <th>ชื่อลูกค้า</th>
                        <th>วันที่ซื้อ</th>
                        <th>ยอด COD</th>
                        <th>ค่าบริการ</th>
                        <th>ยอดสุทธิ</th>
                        <th>สถานะ</th>
                        <th>วันที่อัพเดท</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                        <tr>
                            <td>{{ $payment->order?->code ?: ('#' . ($payment->order_id ?? '-')) }}</td>
                            <td>{{ $payment->order?->tracking_number ?: '-' }}</td>
                            <td>{{ $payment->order?->customer_name ?: '-' }}</td>
                            <td>{{ optional($payment->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>0.00</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'verified' ? 'success' : 'warning' }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                            <td>{{ optional($payment->updated_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">ยังไม่มีรายการการเงิน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
