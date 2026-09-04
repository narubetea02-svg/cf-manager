@extends('layouts.admin')
@section('title', 'การเงิน')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">💰 การเงิน</h4></div></div></div>
<div class="row">
<div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ number_format($totalRevenue, 2) }} ฿</h3><p class="text-muted mb-0">รายรับทั้งหมด</p></div></div></div>
<div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ number_format($pendingRevenue, 2) }} ฿</h3><p class="text-muted mb-0">รอชำระ</p></div></div></div>
<div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ $totalOrders }}</h3><p class="text-muted mb-0">ออเดอร์ทั้งหมด</p></div></div></div>
<div class="col-md-3"><div class="card"><div class="card-body text-center"><h3>{{ number_format($verifiedPayments, 2) }} ฿</h3><p class="text-muted mb-0">ยืนยันสลิปแล้ว</p></div></div></div>
</div>
<div class="card"><div class="card-body"><h5 class="mb-3">สลิปล่าสุด</h5>
@forelse($recentPayments as $p)
<div class="d-flex justify-content-between border-bottom py-2"><span>ออเดอร์ #{{ $p->order_id }}</span><span class="badge bg-{{ $p->status=='verified'?'success':'warning' }}">{{ $p->status }}</span><span>{{ number_format($p->amount,2) }} ฿</span></div>
@empty<p class="text-muted">ยังไม่มีสลิป</p>@endforelse
</div></div>
@endsection
