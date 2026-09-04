@extends('layouts.admin')
@section('title', 'ข้อมูลสินค้า')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">ข้อมูลสินค้า</h4>
                <p class="text-muted mb-0">รายละเอียดสินค้าแบบ safe view สำหรับตรวจสอบข้อมูลก่อนแก้ไขหรือเพิ่มตัวเลือก</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">แก้ไข</a>
                <a href="{{ route('products.options', $product->id) }}" class="btn btn-light border">เพิ่มตัวเลือก</a>
                <a href="{{ route('products.index') }}" class="btn btn-light border">กลับไปหน้าสินค้า</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted">ชื่อ</div>
                <div class="fw-semibold">{{ $product->name }}</div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted">ร้านค้า</div>
                <div class="fw-semibold">{{ $product->shop->name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">ราคา</div>
                <div class="fw-semibold">{{ number_format($product->price, 2) }} ฿</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">สต็อก</div>
                <div class="fw-semibold">{{ $product->stock }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">รหัส CF</div>
                <div class="fw-semibold">{{ $product->code_pattern ?: '-' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">สถานะ</div>
                <div class="fw-semibold">{{ $product->is_active ? 'พร้อมขาย' : 'ปิดใช้งาน' }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">ออเดอร์ที่เกี่ยวข้อง</div>
                <div class="fw-semibold">{{ $product->orders->count() }}</div>
            </div>
            <div class="col-md-4">
                <div class="small text-muted">อัปเดตล่าสุด</div>
                <div class="fw-semibold">{{ optional($product->updated_at)->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
