@extends('layouts.admin')
@section('title', 'กล่องข้อความ')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title mb-1">กล่องข้อความ</h4>
            <p class="text-muted mb-0">หน้านี้ใช้กับ Facebook Page ของร้าน ไม่ใช่ Facebook ส่วนตัวของลูกค้า</p>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">ร้านค้าที่เชื่อมเพจ</h5>
                    <span class="badge bg-light text-dark">{{ $shops->count() }} ร้าน</span>
                </div>

                @forelse($shops as $shop)
                    <a href="{{ url('/chat?shop_id=' . $shop->id) }}" class="d-flex align-items-start justify-content-between gap-2 text-reset border rounded p-3 mb-2 {{ $selectedShopId === $shop->id ? 'border-primary bg-primary-subtle' : '' }}">
                        <div>
                            <div class="fw-semibold">{{ $shop->name }}</div>
                            <div class="small text-muted">{{ $shop->messengerSetting?->fb_page_id ?: 'ยังไม่มี Page ID' }}</div>
                        </div>
                        @if($shop->messengerSetting?->is_active && $shop->messengerSetting?->fb_page_id)
                            <span class="badge bg-success-subtle text-success">พร้อมใช้</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">ตั้งค่าเพิ่ม</span>
                        @endif
                    </a>
                @empty
                    <div class="text-center text-muted py-5">
                        ยังไม่มีร้านค้า
                        <div class="mt-3">
                            <a href="{{ url('/shops/create') }}" class="btn btn-primary">สร้างร้านค้า</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <h5 class="mb-1">ข้อความล่าสุดจากเพจ</h5>
                        <div class="small text-muted">Webhook URL: <code>{{ url('/webhooks/facebook/messenger') }}</code></div>
                    </div>
                    <a href="{{ $selectedShopId ? url('/shops/' . $selectedShopId . '/edit#facebook') : url('/shops') }}" class="btn btn-light border btn-sm">ตั้งค่าเพจ Facebook</a>
                </div>
                <div class="alert alert-secondary">
                    ลูกค้าต้องทักเข้ามาที่เพจของร้านก่อน ระบบจึงจะจับคู่ Messenger กับข้อมูลจาก Portal/TikTok ได้
                </div>
                <div class="border rounded p-3" style="min-height: 420px; background: #f8f9fb;">
                    @forelse($messages as $message)
                        <div class="mb-3">
                            <div class="small text-muted mb-1">
                                {{ $message->sender_name ?: 'ลูกค้า' }} · {{ optional($message->sent_at)->format('d/m/Y H:i') }}
                            </div>
                            <div class="bg-white border rounded px-3 py-2 d-inline-block" style="max-width: 100%;">
                                {{ $message->message_text }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            ยังไม่มีข้อความจากเพจร้านนี้
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
