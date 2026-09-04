@extends('layouts.admin')
@section('title', 'แก้ไขร้านค้า')

@php
    $tiktokUsername = old('tiktok_username', ltrim((string) ($shop->tiktok_username ?? ''), '@'));
    $tiktokLink = $tiktokUsername !== '' ? 'https://www.tiktok.com/@' . ltrim($tiktokUsername, '@') : null;
    $messengerActive = (bool) ($shop->messengerSetting?->is_active && $shop->messengerSetting?->fb_page_id);
    
    // Parse settings
    $settings = $shop->settings ?? [];
    $shipping = $settings['shipping'] ?? [];
    $payment = $settings['payment'] ?? [];
    $autoMessage = $settings['auto_message'] ?? [];
    $social = $settings['social'] ?? [];
    $receipt = $settings['receipt'] ?? [];
    $paymentAccountNumber = old('payment_account_number', $payment['account_number'] ?? '');
    $maskedPaymentAccountNumber = $paymentAccountNumber !== ''
        ? str_repeat('•', max(strlen(preg_replace('/\D+/', '', $paymentAccountNumber)) - 4, 0)) . substr(preg_replace('/\D+/', '', $paymentAccountNumber), -4)
        : null;
    $statusTone = [
        'missing_token' => ['badge' => 'bg-warning-subtle text-warning', 'card' => 'border-warning-subtle bg-warning-subtle'],
        'not_implemented' => ['badge' => 'bg-info-subtle text-info', 'card' => 'border-info-subtle bg-info-subtle'],
        'configured' => ['badge' => 'bg-success-subtle text-success', 'card' => 'border-success-subtle bg-success-subtle'],
    ];
    $shippingTone = $statusTone[$shippingStatus['state'] ?? 'missing_token'] ?? $statusTone['missing_token'];
    $paymentTone = $statusTone[$paymentStatus['state'] ?? 'missing_token'] ?? $statusTone['missing_token'];
    $selectedShippingCarriers = old('shipping_carriers', $shipping['carriers'] ?? []);
    $shippingProviderCards = [
        [
            'key' => 'SELF',
            'label' => 'รับเอง',
            'description' => '* ร้านค้าจัดส่งเอง ไม่ผ่านผู้ให้บริการจัดส่ง',
            'supported' => true,
            'type' => 'manual',
        ],
        [
            'key' => 'EMS',
            'label' => 'Thailand Post eCo-Post',
            'description' => 'ใช้ค่า default cost และนำไปใช้ในขั้นตอนแพ็กของ',
            'supported' => true,
            'type' => 'carrier',
        ],
        [
            'key' => 'Flash',
            'label' => 'Flash Express',
            'description' => 'พร้อมสำหรับ flow แพ็กของ แต่ยังไม่แสดงว่าเชื่อมสำเร็จจนกว่าจะมี token จริง',
            'supported' => true,
            'type' => 'carrier',
        ],
        [
            'key' => 'J&T',
            'label' => 'J&T Express',
            'description' => 'พร้อมสำหรับ flow แพ็กของ แต่ยังไม่มี safe verification endpoint',
            'supported' => true,
            'type' => 'carrier',
        ],
        [
            'key' => 'Kerry',
            'label' => 'Kerry Express',
            'description' => 'พร้อมสำหรับ flow แพ็กของ แต่ยังไม่แสดงว่าเชื่อมต่อสำเร็จหากไม่มี API/Token',
            'supported' => true,
            'type' => 'carrier',
        ],
        [
            'key' => 'DHL',
            'label' => 'DHL Domestic',
            'description' => 'ยังเป็น shell สำหรับ parity เท่านั้น เพราะยังไม่มี connector/API credential',
            'supported' => false,
            'type' => 'shell',
        ],
        [
            'key' => 'SPX',
            'label' => 'SPX Express',
            'description' => 'ยังเป็น shell สำหรับ parity เท่านั้น เพราะยังไม่มี connector/API credential',
            'supported' => false,
            'type' => 'shell',
        ],
    ];
@endphp

@push('styles')
<style>
    .cf-preview-modal[hidden] {
        display: none !important;
    }

    .cf-preview-modal {
        position: fixed;
        inset: 0;
        z-index: 1080;
        background: rgba(15, 23, 42, 0.58);
        padding: 1.5rem;
        overflow-y: auto;
    }

    .cf-preview-modal.show {
        display: block;
    }

    .cf-preview-modal .modal-dialog {
        margin: 3rem auto;
        max-width: 640px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1 text-body">ข้อมูลร้านค้า</h4>
                <div class="text-muted small">ร้านค้า / แก้ไขร้านค้า</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/shops') }}" class="btn btn-secondary border">
                    <x-ui-icon name="arrow-right" class="me-1" size="16" />ยกเลิก
                </a>
                <button type="submit" form="shop-settings-form" class="btn btn-primary">
                    <x-ui-icon name="check-circle" class="me-1" size="16" />บันทึกข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
            <div>
                <div class="text-muted small">รหัสร้านค้า: #{{ $shop->id }}</div>
                <h4 class="mb-1 text-body">ชื่อร้านค้า: {{ $shop->name }}</h4>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    @if($messengerActive)
                        <span class="badge bg-success-subtle text-success">เชื่อมเพจแล้ว</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning">ยังไม่เชื่อมเพจ</span>
                    @endif
                    @if($tiktokUsername)
                        <span class="badge bg-secondary text-white">{{ '@' . ltrim($tiktokUsername, '@') }}</span>
                    @else
                        <span class="badge bg-secondary text-white">ยังไม่กรอก TikTok username</span>
                    @endif
                    <span class="badge {{ $tiktokVerification['badge_class'] ?? 'bg-secondary-subtle text-secondary' }}">{{ $tiktokVerification['state'] ?? 'unchecked' }}</span>
                </div>
            </div>
            <div class="text-end">
                <div class="small text-muted">Facebook Page</div>
                @if($shop->messengerSetting?->fb_page_id)
                    <a class="fw-semibold text-primary" href="https://facebook.com/{{ $shop->messengerSetting->fb_page_id }}" target="_blank">
                        {{ $shop->messengerSetting->fb_page_id }}
                    </a>
                @else
                    <div class="fw-semibold text-body">-</div>
                @endif
                <div class="small text-muted mt-2">สถานะ LIVE</div>
                <div class="fw-semibold text-body">{{ $shop->liveStreams->firstWhere('status', 'active') ? 'กำลังออนไลน์' : 'ยังไม่มี LIVE' }}</div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist" aria-label="Shop settings tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#shops-tab" role="tab" id="nav-shops-tab" data-settings-tab="shops-tab" aria-controls="shops-tab" aria-selected="true">ข้อมูลร้านค้า</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#messenger-tab" role="tab" id="nav-messenger-tab" data-settings-tab="messenger-tab" aria-controls="messenger-tab" aria-selected="false">Messenger</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#shipping-tab" role="tab" id="nav-shipping-tab" data-settings-tab="shipping-tab" aria-controls="shipping-tab" aria-selected="false">การจัดส่ง</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#payment-tab" role="tab" id="nav-payment-tab" data-settings-tab="payment-tab" aria-controls="payment-tab" aria-selected="false">การชำระเงิน</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#autos-tab" role="tab" id="nav-autos-tab" data-settings-tab="autos-tab" aria-controls="autos-tab" aria-selected="false">ข้อความอัตโนมัติ</a>
            </li>
        </ul>

        <form id="shop-settings-form" action="{{ url('/shops/' . $shop->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" id="active-tab-input" value="shops-tab">
            
            <div class="tab-content">
                <!-- ข้อมูลร้านค้า -->
                <div class="tab-pane fade show active" id="shops-tab" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label text-body">ชื่อร้านค้า</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $shop->name) }}" required>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">อีเมลร้าน</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $shop->email) }}" placeholder="Email">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">เบอร์โทรศัพท์</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $shop->phone) }}" placeholder="08X-XXX-XXXX">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">ประเทศ</label>
                            <select name="country" class="form-select">
                                <option value="ไทย" {{ old('country', $shop->country) == 'ไทย' ? 'selected' : '' }}>ไทย</option>
                            </select>
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label text-body">ที่อยู่</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $shop->address) }}" placeholder="บ้านเลขที่ ซอย ถนน ตำบล อำเภอ จังหวัด">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">ตำบล/แขวง</label>
                            <input type="text" name="sub_district" class="form-control" value="{{ old('sub_district', $shop->sub_district) }}" placeholder="ตำบล/แขวง">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $shop->postal_code) }}" placeholder="รหัสไปรษณีย์">
                        </div>
                        
                        <div class="col-lg-12 mt-4">
                            <h5 class="text-body mb-3">การเชื่อมต่อโซเชียล</h5>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label text-body">TikTok Username</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input id="shop-tiktok-username" type="text" name="tiktok_username" class="form-control" value="{{ $tiktokUsername }}" placeholder="username">
                                <button type="submit" form="shop-tiktok-verify-form" class="btn btn-outline-warning">ตรวจสอบ</button>
                            </div>
                            <div class="form-text text-muted">username นี้จะถูกใช้เป็นตัวเชื่อม LIVE อัตโนมัติจากหน้า LIVE และจะไม่ถือว่า verified จนกว่าจะผ่านการตรวจสอบจริง</div>
                            <div class="small mt-2">
                                <span class="badge {{ $tiktokVerification['badge_class'] ?? 'bg-secondary-subtle text-secondary' }}">{{ $tiktokVerification['label'] ?? 'ยังไม่ได้ตรวจสอบ' }}</span>
                                <span class="text-muted ms-2">{{ $tiktokVerification['message'] ?? 'ยังไม่ได้ตรวจสอบ TikTok Username' }}</span>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">TikTok Link</label>
                            <div class="input-group">
                                <input type="text" class="form-control" readonly value="{{ $tiktokLink ?: 'ยังไม่กรอก TikTok username' }}" id="shop-tiktok-link">
                                <button type="button" class="btn btn-outline-secondary" @if(! $tiktokLink) disabled @endif onclick="navigator.clipboard.writeText(document.getElementById('shop-tiktok-link').value)">คัดลอก</button>
                            </div>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label text-body">Instagram Account</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="instagram" class="form-control" value="{{ old('instagram', $shop->instagram) }}" placeholder="ig_username">
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label text-body">Social Link</label>
                            <input type="text" name="social_primary_link" class="form-control" value="{{ old('social_primary_link', $social['primary_link'] ?? '') }}" placeholder="https://linktr.ee/yourshop หรือ https://lin.ee/xxxxxx">
                            <div class="form-text text-muted">รวมช่องทางติดต่อหลักของร้านในลิงก์เดียว</div>
                        </div>
                        
                        <div class="col-lg-6">
                            <label class="form-label text-body">เลือกโลโก้ร้าน</label>
                            <input type="text" name="logo" class="form-control" value="{{ old('logo', $shop->logo) }}" placeholder="https://example.com/logo.png">
                        </div>

                        <div class="col-lg-12 mt-4">
                            <h5 class="text-body mb-3">ข้อมูลออกใบเสร็จ</h5>
                        </div>

                        <div class="col-lg-6">
                            <label class="form-label text-body">ชื่อ สำหรับออกใบเสร็จรับเงิน</label>
                            <input type="text" name="receipt_tax_name" class="form-control" value="{{ old('receipt_tax_name', $receipt['tax_name'] ?? '') }}" placeholder="ชื่อบริษัท / ชื่อผู้เสียภาษี">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">เลขผู้เสียภาษี</label>
                            <input type="text" name="receipt_tax_id" class="form-control" value="{{ old('receipt_tax_id', $receipt['tax_id'] ?? '') }}" placeholder="เลขประจำตัวผู้เสียภาษี">
                        </div>
                        <div class="col-lg-8">
                            <label class="form-label text-body">ที่อยู่ สำหรับออกใบเสร็จรับเงิน</label>
                            <textarea name="receipt_tax_address" class="form-control" rows="3" placeholder="ที่อยู่สำหรับออกใบเสร็จ">{{ old('receipt_tax_address', $receipt['tax_address'] ?? '') }}</textarea>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label text-body">เบอร์โทรศัพท์สำหรับใบเสร็จ</label>
                            <input type="text" name="receipt_phone" class="form-control" value="{{ old('receipt_phone', $receipt['phone'] ?? '') }}" placeholder="08X-XXX-XXXX">
                        </div>
                    </div>
                </div>

                <!-- Messenger -->
                <div class="tab-pane fade" id="messenger-tab" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                ตั้งค่า Facebook Page สำหรับ Portal/Messenger mapping และ webhook ที่มีอยู่แล้ว ระบบจะไม่ส่งข้อความจริงจนกว่าจะเปิด <code>MESSENGER_SEND_ENABLED</code>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">Facebook Page ID</label>
                            <input type="text" name="fb_page_id" form="messenger-settings-form" class="form-control" value="{{ old('fb_page_id', $shop->messengerSetting?->fb_page_id) }}" placeholder="123456789012345">
                            <div class="form-text text-muted">ใช้สร้างลิงก์ m.me บนหน้า LIVE และ Portal</div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">Page Access Token</label>
                            <input type="password" name="fb_page_token" form="messenger-settings-form" class="form-control" placeholder="{{ $shop->messengerSetting?->fb_page_token ? '•••••••• (มี token แล้ว — กรอกใหม่เพื่อเปลี่ยน)' : 'EAAG...' }}">
                            <div class="form-text text-muted">ไม่ log token เต็ม — เก็บในฐานข้อมูลเท่านั้น</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="messenger_is_active" form="messenger-settings-form" value="1" {{ old('is_active', $shop->messengerSetting?->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label text-body" for="messenger_is_active">เปิดใช้งานการเชื่อมต่อเพจนี้</label>
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" form="messenger-settings-form" class="btn btn-primary">บันทึกการเชื่อมต่อ Messenger</button>
                            @if($shop->messengerSetting?->fb_page_id)
                                <a href="https://facebook.com/{{ $shop->messengerSetting->fb_page_id }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">เปิดเพจ Facebook</a>
                            @endif
                            <a href="{{ url('/integrations') }}" class="btn btn-outline-primary">ดูสถานะการเชื่อมต่อทั้งหมด</a>
                        </div>
                    </div>
                </div>

                <!-- การจัดส่ง -->
                <div class="tab-pane fade" id="shipping-tab" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border {{ $shippingTone['card'] }} mb-0">
                                <div class="card-body d-flex flex-wrap align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge {{ $shippingTone['badge'] }}">{{ $shippingStatus['label'] ?? 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง' }}</span>
                                            <span class="small text-muted">Provider: {{ $shippingStatus['provider'] ?? 'ไปรษณีย์ไทย' }}</span>
                                        </div>
                                        <div class="text-body">{{ $shippingStatus['message'] ?? 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง' }}</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" form="shipping-check-form" class="btn btn-outline-primary">ตรวจสอบการเชื่อมต่อขนส่ง</button>
                                        <a href="{{ url('/packing') }}" class="btn btn-outline-secondary">ไปหน้าแพ็กของ</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border mb-0" id="shipping-permissions">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                                        <div>
                                            <h5 class="mb-1 text-body">เลือกเปิดใช้งาน ผู้ให้บริการจัดส่ง</h5>
                                            <div class="text-muted small">จัดกลุ่ม provider ที่พร้อมใช้งานจริง, shell parity และ flow รับเองไว้ในหน้าตั้งค่าเดียวแบบใกล้ต้นฉบับ</div>
                                        </div>
                                        <a href="{{ url('/help') }}" class="btn btn-outline-secondary btn-sm">ดูผู้ให้บริการจัดส่งรายอื่นเพิ่มเติม ...</a>
                                    </div>
                                    <div class="row g-3">
                                        @foreach ($shippingProviderCards as $provider)
                                            @php
                                                $isSelectedProvider = $provider['key'] === 'SELF'
                                                    ? old('shipping_enabled', $shipping['enabled'] ?? false)
                                                    : in_array($provider['key'], $selectedShippingCarriers, true);
                                                $providerTone = $provider['supported']
                                                    ? ($isSelectedProvider ? 'border-success-subtle bg-success-subtle' : 'border-secondary-subtle')
                                                    : 'border-warning-subtle bg-warning-subtle';
                                                $providerBadge = $provider['supported']
                                                    ? ($isSelectedProvider ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary')
                                                    : 'bg-warning-subtle text-warning';
                                            @endphp
                                            <div class="col-xl-6">
                                                <div class="card h-100 {{ $providerTone }}">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                                                            <div>
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <h6 class="mb-0 text-body">{{ $provider['label'] }}</h6>
                                                                    <span class="badge {{ $providerBadge }}">{{ $isSelectedProvider ? 'เปิด' : 'ปิด' }}</span>
                                                                </div>
                                                                <div class="text-muted small">{{ $provider['description'] }}</div>
                                                            </div>
                                                            @if ($provider['supported'])
                                                                <a href="#shipping-capabilities-note" class="btn btn-outline-secondary btn-sm">ตั้งสิทธิ์</a>
                                                            @else
                                                                <span class="badge bg-warning-subtle text-warning">not_configured</span>
                                                            @endif
                                                        </div>

                                                        @if ($provider['key'] === 'SELF')
                                                            <div class="form-check form-switch form-switch-lg mb-3">
                                                                <input class="form-check-input" type="checkbox" name="shipping_enabled" id="shipping_enabled" value="1" {{ old('shipping_enabled', $shipping['enabled'] ?? false) ? 'checked' : '' }}>
                                                                <label class="form-check-label text-body ms-2 mt-1" for="shipping_enabled">เปิดใช้งานระบบการจัดส่ง</label>
                                                            </div>
                                                        @elseif ($provider['supported'])
                                                            <label class="form-check border rounded-3 p-3 d-flex gap-2 align-items-start mb-3">
                                                                <input class="form-check-input mt-1" type="checkbox" name="shipping_carriers[]" value="{{ $provider['key'] }}"
                                                                    {{ in_array($provider['key'], $selectedShippingCarriers, true) ? 'checked' : '' }}>
                                                                <span>
                                                                    <span class="d-block text-body fw-semibold">บันทึก carrier นี้ไว้ในขั้นตอนแพ็กของ</span>
                                                                    <span class="d-block text-muted small">ยังไม่แสดง connected จนกว่าจะมี API/Token และ safe verification จริง</span>
                                                                </span>
                                                            </label>
                                                        @else
                                                            <div class="small text-muted mb-3">ยังไม่สามารถเปิดใช้งานจาก production นี้ได้ เพราะยังไม่มี API/token/backend สำหรับผู้ให้บริการรายนี้</div>
                                                        @endif

                                                        <button type="submit" form="shop-settings-form" class="btn btn-primary btn-sm">บันทึกและนำไปใช้ทันที</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">วิธีจัดส่งเริ่มต้น</label>
                            <select name="shipping_default_method" class="form-select">
                                <option value="EMS" {{ old('shipping_default_method', $shipping['default_method'] ?? '') == 'EMS' ? 'selected' : '' }}>ไปรษณีย์ไทย EMS</option>
                                <option value="Kerry" {{ old('shipping_default_method', $shipping['default_method'] ?? '') == 'Kerry' ? 'selected' : '' }}>Kerry Express</option>
                                <option value="Flash" {{ old('shipping_default_method', $shipping['default_method'] ?? '') == 'Flash' ? 'selected' : '' }}>Flash Express</option>
                                <option value="J&T" {{ old('shipping_default_method', $shipping['default_method'] ?? '') == 'J&T' ? 'selected' : '' }}>J&T Express</option>
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">ค่าจัดส่งเริ่มต้น (บาท)</label>
                            <input type="number" name="shipping_default_cost" class="form-control" value="{{ old('shipping_default_cost', $shipping['default_cost'] ?? 0) }}" min="0" step="1">
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label text-body">หมายเหตุการจัดส่ง</label>
                            <textarea name="shipping_note" class="form-control" rows="3" placeholder="เช่น ส่งของทุกวันจันทร์-ศุกร์ ตัดรอบ 12:00 น.">{{ old('shipping_note', $shipping['note'] ?? '') }}</textarea>
                        </div>
                        <div class="col-lg-12">
                            <label class="form-label text-body">ขนส่งที่รองรับ</label>
                            <div class="row g-2">
                                @foreach (['EMS' => 'ไปรษณีย์ไทย EMS', 'Kerry' => 'Kerry Express', 'Flash' => 'Flash Express', 'J&T' => 'J&T Express'] as $carrierValue => $carrierLabel)
                                    <div class="col-sm-6 col-lg-3">
                                        <label class="form-check border rounded-3 p-3 d-flex gap-2 align-items-start">
                                            <input class="form-check-input mt-1" type="checkbox" name="shipping_carriers[]" value="{{ $carrierValue }}"
                                                {{ in_array($carrierValue, old('shipping_carriers', $shipping['carriers'] ?? []), true) ? 'checked' : '' }}>
                                            <span>
                                                <span class="d-block text-body fw-semibold">{{ $carrierLabel }}</span>
                                                <span class="d-block text-muted small">เปิดให้เลือกในขั้นตอนแพ็กของ</span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-lg-12 d-flex flex-wrap gap-2">
                            <span class="small text-muted align-self-center">ใช้ค่าที่บันทึกหน้านี้เป็น default setting ก่อนเชื่อมขนส่งจริง ถ้ายังไม่มี API/Token ระบบจะไม่แสดงว่าเชื่อมสำเร็จ</span>
                        </div>
                        <div class="col-12" id="shipping-capabilities-note">
                            <div class="alert alert-secondary mb-0">
                                <div class="fw-semibold mb-1 text-body">หมายเหตุเรื่องสิทธิ์และการเชื่อมต่อ</div>
                                <div class="small text-muted mb-0">ปุ่ม <span class="fw-semibold text-body">ตั้งสิทธิ์</span> ในรอบนี้ใช้เป็น safe navigation ไปยังคำอธิบายการใช้งานก่อน เพราะ production ยังไม่มี credential/API docs ของผู้ให้บริการขนส่งจริงครบทุกเจ้า จึงห้ามแสดง connected หรือ verified ปลอม</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- การชำระเงิน -->
                <div class="tab-pane fade" id="payment-tab" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="card border {{ $paymentTone['card'] }} mb-0">
                                <div class="card-body d-flex flex-wrap align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge {{ $paymentTone['badge'] }}">{{ $paymentStatus['label'] ?? 'ยังไม่ได้ตั้งค่า Payment Gateway/Token' }}</span>
                                            <span class="small text-muted">{{ $paymentStatus['provider'] ?? 'Payment Gateway' }}</span>
                                        </div>
                                        <div class="text-body">{{ $paymentStatus['message'] ?? 'ยังไม่ได้ตั้งค่า Payment Gateway/Token' }}</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" form="payment-check-form" class="btn btn-outline-primary">ตรวจสอบการเชื่อมต่อการชำระเงิน</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card border mb-0">
                                <div class="card-body">
                                    <div class="form-check form-switch form-switch-lg mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_transfer_enabled" id="payment_transfer_enabled" value="1" {{ old('payment_transfer_enabled', $payment['transfer_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label text-body ms-2 mt-1" for="payment_transfer_enabled">โอนเงินผ่านธนาคาร</label>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-body">ธนาคาร</label>
                                        <select name="payment_bank_name" class="form-select">
                                            <option value="">เลือกธนาคาร</option>
                                            <option value="KBANK" {{ old('payment_bank_name', $payment['bank_name'] ?? '') == 'KBANK' ? 'selected' : '' }}>กสิกรไทย</option>
                                            <option value="SCB" {{ old('payment_bank_name', $payment['bank_name'] ?? '') == 'SCB' ? 'selected' : '' }}>ไทยพาณิชย์</option>
                                            <option value="KTB" {{ old('payment_bank_name', $payment['bank_name'] ?? '') == 'KTB' ? 'selected' : '' }}>กรุงไทย</option>
                                            <option value="BBL" {{ old('payment_bank_name', $payment['bank_name'] ?? '') == 'BBL' ? 'selected' : '' }}>กรุงเทพ</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-body">ชื่อบัญชี</label>
                                        <input type="text" name="payment_account_name" class="form-control" value="{{ old('payment_account_name', $payment['account_name'] ?? '') }}">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label text-body">เลขบัญชี</label>
                                        <input type="text" name="payment_account_number" class="form-control" value="{{ $paymentAccountNumber }}" placeholder="XXX-X-XXXXX-X">
                                        <div class="form-text text-muted">
                                            ตัวเลขนี้จะถูกแสดงในหน้าแจ้งชำระเงินของลูกค้า
                                            @if($maskedPaymentAccountNumber)
                                                <span class="d-block mt-1">ตัวอย่างการแสดงแบบปลอดภัย: {{ $maskedPaymentAccountNumber }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card border mb-0 h-100">
                                <div class="card-body">
                                    <div class="form-check form-switch form-switch-lg mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_cod_enabled" id="payment_cod_enabled" value="1" {{ old('payment_cod_enabled', $payment['cod_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label text-body ms-2 mt-1" for="payment_cod_enabled">เก็บเงินปลายทาง (COD)</label>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label text-body">หมายเหตุการชำระเงิน</label>
                                        <textarea name="payment_note" class="form-control" rows="5" placeholder="เช่น กรุณาชำระเงินภายใน 24 ชม. หากเกินเวลาจะถือว่ายกเลิกออเดอร์">{{ old('payment_note', $payment['note'] ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border mb-0">
                                <div class="card-body row g-3">
                                    <div class="col-lg-8">
                                        <label class="form-label text-body">คำแนะนำการชำระเงิน</label>
                                        <textarea name="payment_instruction" class="form-control" rows="3" placeholder="ลูกค้าต้องแนบสลิปหรือแจ้งข้อมูลใดบ้าง">{{ old('payment_instruction', $payment['instruction'] ?? '') }}</textarea>
                                    </div>
                                    <div class="col-lg-4">
                                        <label class="form-label text-body">ช่องทางแจ้งชำระเงิน</label>
                                        <input type="text" name="payment_contact_channel" class="form-control" value="{{ old('payment_contact_channel', $payment['contact_channel'] ?? '') }}" placeholder="Messenger / LINE / โทรศัพท์">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ข้อความอัตโนมัติ -->
                <div class="tab-pane fade" id="autos-tab" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check form-switch form-switch-lg mb-3">
                                <input class="form-check-input" type="checkbox" name="auto_message_enabled" id="auto_message_enabled" value="1" {{ old('auto_message_enabled', $autoMessage['enabled'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label text-body ms-2 mt-1" for="auto_message_enabled">เปิดใช้งานข้อความอัตโนมัติ</label>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <label class="form-label text-body">ข้อความต้อนรับลูกค้าใหม่ / สรุปยอด</label>
                            <textarea id="msg_welcome" name="auto_msg_welcome" class="form-control mb-2" rows="6" placeholder="สวัสดีค่ะ ยินดีต้อนรับสู่ร้าน {shop_name}">{{ old('auto_msg_welcome', $autoMessage['welcome'] ?? 'สรุปยอดคำสั่งซื้อ คุณ {customer_name}\n\nยอดรวม: {total_amount} บาท\n\nสามารถชำระเงินและแจ้งสลิปได้ที่นี่เลยค่ะ') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewMessage('msg_welcome')">ดูตัวอย่าง</button>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label text-body">ข้อความยืนยันการชำระเงิน</label>
                            <textarea id="msg_payment" name="auto_msg_payment" class="form-control mb-2" rows="6" placeholder="ร้านได้รับยอดโอนเรียบร้อยแล้ว">{{ old('auto_msg_payment', $autoMessage['payment'] ?? 'ทางร้านได้รับยอดโอน {total_amount} บาท เรียบร้อยแล้วค่ะ\nจะจัดส่งสินค้าให้โดยเร็วนะคะ') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewMessage('msg_payment')">ดูตัวอย่าง</button>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label text-body">ข้อความแจ้งจัดส่ง</label>
                            <textarea id="msg_shipping" name="auto_msg_shipping" class="form-control mb-2" rows="6" placeholder="จัดส่งเรียบร้อย เลขพัสดุคือ {tracking_no}">{{ old('auto_msg_shipping', $autoMessage['shipping'] ?? 'จัดส่งเรียบร้อยแล้วนะคะ\n\nเลขพัสดุ: {tracking_no}\nขนส่ง: {shipping_method}\n\nขอบคุณที่อุดหนุนค่ะ') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewMessage('msg_shipping')">ดูตัวอย่าง</button>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">ข้อความแจ้งเลขพัสดุ</label>
                            <textarea id="msg_tracking" name="auto_msg_tracking" class="form-control mb-2" rows="5" placeholder="ออเดอร์ {order_no} ส่งแล้ว เลขพัสดุ {tracking_no}">{{ old('auto_msg_tracking', $autoMessage['tracking'] ?? 'ออเดอร์ {order_no} ของคุณถูกส่งแล้วค่ะ\nเลขพัสดุ: {tracking_no}\nสามารถติดตามสถานะได้เลยนะคะ') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewMessage('msg_tracking')">ดูตัวอย่าง</button>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label text-body">ข้อความหลังจบไลฟ์</label>
                            <textarea id="msg_after_live" name="auto_msg_after_live" class="form-control mb-2" rows="5" placeholder="ขอบคุณที่ร่วมไลฟ์วันนี้">{{ old('auto_msg_after_live', $autoMessage['after_live'] ?? 'ขอบคุณที่ร่วมไลฟ์กับ {shop_name} วันนี้นะคะ\nหากสนใจสินค้าเพิ่มเติม สามารถทักกลับมาได้เลยค่ะ') }}</textarea>
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="previewMessage('msg_after_live')">ดูตัวอย่าง</button>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">
                                ตัวแปรที่รองรับใน preview: <code>{shop_name}</code>, <code>{customer_name}</code>, <code>{order_no}</code>, <code>{total_amount}</code>, <code>{tracking_no}</code>, <code>{shipping_method}</code>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border border-info-subtle bg-info-subtle mb-0">
                                <div class="card-body d-flex flex-wrap align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-body mb-1">โหมด Preview เท่านั้น</div>
                                        <div class="text-muted">การดูตัวอย่างจะแทนค่าตัวแปรแบบ dry-run เท่านั้น และจะไม่ส่ง Messenger หรือ LINE จริงจาก production นี้</div>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary" disabled>Test Send ถูกปิดใช้งาน</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="alert alert-warning mb-0 border">
                                <strong>หมายเหตุ:</strong> การเปิดข้อความอัตโนมัติจะต้องมีการตั้งค่า Facebook Webhook ให้เรียบร้อยก่อน ฟังก์ชันนี้เป็นเพียงการตั้งรูปแบบข้อความ ไม่เปิดใช้งานส่ง Messenger จริงบนเซิร์ฟเวอร์นี้ (MESSENGER_SEND_ENABLED=false)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <form id="messenger-settings-form" action="{{ route('shops.messenger', $shop->id) }}" method="POST" class="d-none">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" value="messenger-tab">
        </form>

        <form id="shop-tiktok-verify-form" action="{{ route('shops.tiktok.verify', $shop->id) }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="tiktok_username" id="shop-tiktok-verify-value" value="{{ $tiktokUsername }}">
        </form>

        <form id="shipping-check-form" action="{{ route('shops.shipping.check', $shop->id) }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="active_tab" value="shipping-tab">
            <input type="hidden" name="shipping_default_method" id="shipping-check-method" value="{{ old('shipping_default_method', $shipping['default_method'] ?? 'EMS') }}">
        </form>

        <form id="payment-check-form" action="{{ route('shops.payment.check', $shop->id) }}" method="POST" class="d-none">
            @csrf
            <input type="hidden" name="active_tab" value="payment-tab">
        </form>
        
        <form id="shop-tiktok-check-form" action="{{ route('shops.tiktok.check', $shop->id) }}" method="POST" class="mt-4 border-top pt-3">
            @csrf
            <input type="hidden" name="tiktok_username" id="shop-tiktok-check-value" value="{{ $tiktokUsername }}">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="submit" class="btn btn-success">
                    <x-ui-icon name="broadcast" class="me-1" size="16" />ตรวจสอบ TikTok Live
                </button>
                <span class="small text-muted">ต้องบันทึกข้อมูลก่อนหากเพิ่งเปลี่ยน TikTok Username</span>
            </div>
        </form>
    </div>
</div>

<!-- Modal สำหรับ Preview Message (Dry Run) -->
<div class="modal fade cf-preview-modal" id="previewModal" tabindex="-1" aria-hidden="true" hidden>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ตัวอย่างข้อความอัตโนมัติ</h5>
                <button type="button" class="btn-close" data-preview-close aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <x-ui-icon name="chat" size="20" />
                    </div>
                    <div class="bg-light p-3 rounded-3" style="max-width: 80%;">
                        <p id="previewContent" class="mb-0 text-dark" style="white-space: pre-wrap;"></p>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <span class="badge bg-secondary">นี่คือข้อความตัวอย่าง (Dry Run) จะไม่มีการส่งหาลูกค้าจริง</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-preview-close>ปิด</button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('shop-tiktok-username');
    const checkValue = document.getElementById('shop-tiktok-check-value');
    const verifyValue = document.getElementById('shop-tiktok-verify-value');
    const link = document.getElementById('shop-tiktok-link');
    const shippingMethodSelect = document.querySelector('select[name="shipping_default_method"]');
    const shippingCheckMethod = document.getElementById('shipping-check-method');
    const activeTabInput = document.getElementById('active-tab-input');
    const previewModal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');

    if (input && checkValue) {
        const sync = function () {
            const normalized = input.value.trim().replace(/^@+/, '');
            checkValue.value = normalized;
            if (verifyValue) {
                verifyValue.value = normalized;
            }
            if (link) {
                link.value = normalized ? 'https://www.tiktok.com/@' + normalized : 'ยังไม่กรอก TikTok username';
            }
        };
        input.addEventListener('input', sync);
        sync();
    }

    if (shippingMethodSelect && shippingCheckMethod) {
        const syncShippingMethod = function () {
            shippingCheckMethod.value = shippingMethodSelect.value || 'EMS';
        };
        shippingMethodSelect.addEventListener('change', syncShippingMethod);
        syncShippingMethod();
    }

    const storageKey = 'cf_shop_active_tab_{{ $shop->id }}';
    const tabLinks = Array.from(document.querySelectorAll('[data-settings-tab]'));
    const tabPanes = Array.from(document.querySelectorAll('.tab-content .tab-pane'));
    const flashActiveTab = '{{ session("active_tab") }}';

    function setHash(tabId) {
        if (!tabId) {
            return;
        }

        const nextHash = '#' + tabId;
        if (window.location.hash !== nextHash) {
            history.replaceState(null, '', nextHash);
        }
    }

    function activateTab(tabId, persistHash = true) {
        let resolvedTabId = tabId;

        if (!tabLinks.some((linkElement) => linkElement.dataset.settingsTab === resolvedTabId)) {
            resolvedTabId = 'shops-tab';
        }

        tabLinks.forEach(function (linkElement) {
            const isActive = linkElement.dataset.settingsTab === resolvedTabId;
            linkElement.classList.toggle('active', isActive);
            linkElement.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabPanes.forEach(function (pane) {
            const isActive = pane.id === resolvedTabId;
            pane.classList.toggle('active', isActive);
            pane.classList.toggle('show', isActive);
        });

        if (activeTabInput) {
            activeTabInput.value = resolvedTabId;
        }

        localStorage.setItem(storageKey, resolvedTabId);

        if (persistHash) {
            setHash(resolvedTabId);
        }
    }

    tabLinks.forEach(function (linkElement) {
        linkElement.addEventListener('click', function (event) {
            event.preventDefault();
            activateTab(linkElement.dataset.settingsTab, true);
        });
    });

    function currentHashTab() {
        const currentHash = (window.location.hash || '').replace('#', '').trim();

        if (currentHash === 'shipping-capabilities-note') {
            return 'shipping-tab';
        }

        return currentHash;
    }

    const initialTab = currentHashTab()
        || flashActiveTab
        || localStorage.getItem(storageKey)
        || 'shops-tab';

    activateTab(initialTab, false);

    function syncTabFromHash() {
        const confirmedHashTab = currentHashTab();
        if (confirmedHashTab && confirmedHashTab !== activeTabInput?.value) {
            activateTab(confirmedHashTab, false);
        }
    }

    syncTabFromHash();

    window.setTimeout(syncTabFromHash, 0);
    window.addEventListener('load', syncTabFromHash);

    window.addEventListener('hashchange', function () {
        const nextTab = currentHashTab();
        if (nextTab) {
            activateTab(nextTab, false);
        }
    });

    function closePreviewModal() {
        if (!previewModal) {
            return;
        }

        previewModal.hidden = true;
        previewModal.classList.remove('show');
        document.body.classList.remove('modal-open');
    }

    function openPreviewModal(text) {
        if (!previewModal || !previewContent) {
            return;
        }

        previewContent.textContent = text;
        previewModal.hidden = false;
        previewModal.classList.add('show');
        document.body.classList.add('modal-open');
    }

    document.querySelectorAll('[data-preview-close]').forEach(function (button) {
        button.addEventListener('click', closePreviewModal);
    });

    if (previewModal) {
        previewModal.addEventListener('click', function (event) {
            if (event.target === previewModal) {
                closePreviewModal();
            }
        });
    }

    window.previewMessage = function (inputId) {
        const source = document.getElementById(inputId);
        if (!source) {
            return;
        }

        const shopName = @json($shop->name);
        const previewText = source.value
            .replace(/{shop_name}/g, shopName)
            .replace(/{customer_name}/g, 'คุณสมหญิง ใจดี')
            .replace(/{order_no}/g, 'ORD-20260626-001')
            .replace(/{total_amount}/g, '450.00')
            .replace(/{tracking_no}/g, 'TH123456789')
            .replace(/{shipping_method}/g, 'Kerry Express');

        openPreviewModal(previewText);
    };
});
</script>
@endpush
