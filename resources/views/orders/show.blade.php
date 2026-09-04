@extends('layouts.admin')
@section('title', 'Order Detail')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h4 class="page-title mb-1">Order Detail</h4>
                <p class="text-muted mb-0">ดูรายละเอียดคำสั่งซื้อแบบอ่านอย่างเดียวเพื่อใช้คู่กับ Messenger mapping</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url('/orders') }}" class="btn btn-outline-secondary">กลับไปหน้า Orders</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted">Order</div>
                <div class="fw-semibold">{{ $order->code ?: ('#' . $order->id) }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Status</div>
                <div>{{ $order->status }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Customer Name</div>
                <div>{{ $order->customer_name ?: '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Customer Username</div>
                <div>{{ $order->customer_username ?: '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Customer Phone</div>
                <div>{{ $order->customer_phone ?: '-' }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Total Price</div>
                <div>{{ number_format($order->total_price, 2) }} ฿</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Quantity</div>
                <div>{{ $order->quantity }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">Tracking Number</div>
                <div>{{ $order->tracking_number ?: '-' }}</div>
            </div>
            <div class="col-12">
                <div class="small text-muted">Notes</div>
                <div>{{ $order->notes ?: '-' }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
