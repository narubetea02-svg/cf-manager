@extends('layouts.admin')
@section('title', 'LIVE')

@php
    $platformLabels = [
        'tiktok' => 'TikTok Live',
        'facebook' => 'Facebook Live',
    ];
    $currentLivePortalUrl = $currentStream?->portalSession ? url('/pt?sid=' . $currentStream->portalSession->sid) : null;
    $currentLiveMessengerUrl = $currentStream?->portalSession && $currentStream?->shop?->messengerSetting?->is_active && $currentStream?->shop?->messengerSetting?->fb_page_id
        ? 'https://m.me/' . $currentStream->shop->messengerSetting->fb_page_id . '?ref=' . $currentStream->portalSession->sid
        : null;
    $prefillShopId = old('shop_id', $prefillStream?->shop_id ?? ($shops->first()?->id ?? null));
    $prefillPlatform = old('platform', $prefillStream?->platform ?? 'tiktok');
    $prefillLiveUrl = old('live_url', $prefillStream?->live_url ?? '');
    $currentTikTokStatus = ($currentShop?->settings['tiktok']['username_status'] ?? 'unchecked');
    $canOpenCurrentPortal = $tiktokConfigured && $currentStream && $currentStream->status === 'active' && ! empty($currentTikTokUsername) && $currentTikTokStatus === 'verified';
    $canCheckCurrentLive = ! empty($currentTikTokUsername);
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">LIVE</h4>
                <p class="text-muted mb-0">จัดการลิงก์ไลฟ์ ปักหมุด Portal และติดตามสถานะการเชื่อมต่อของลูกค้า</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="#create-live-form" class="btn btn-primary">
                    <x-ui-icon name="plus" class="me-1" size="16" />สร้าง LIVE
                </a>
                <a href="{{ route('live.copy-latest') }}#create-live-form" class="btn btn-light border">
                    <x-ui-icon name="transfer" class="me-1" size="16" />สร้างจาก LIVE ล่าสุด
                </a>
                <a href="{{ route('live.print') }}" target="_blank" class="btn btn-light border">
                    <x-ui-icon name="printer" class="me-1" size="16" />พิมพ์
                </a>
                <form action="{{ route('live.check-current') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $canCheckCurrentLive ? 'btn-outline-primary' : 'btn-outline-secondary' }}" @disabled(! $canCheckCurrentLive)>
                        <x-ui-icon name="search" class="me-1" size="16" />ตรวจสอบ TikTok Live
                    </button>
                </form>
                <form action="{{ route('live.connect-current') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $canOpenCurrentPortal ? 'btn-success' : 'btn-outline-secondary' }}" @disabled(! $canOpenCurrentPortal)>
                        <x-ui-icon name="link" class="me-1" size="16" />เชื่อมต่อ LIVE ปัจจุบัน
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">LIVE ทั้งหมด</span>
                <h3 class="mt-2 mb-1">{{ number_format($summary['all'] ?? 0) }}</h3>
                <div class="text-success small">สร้างไว้ทั้งหมดในระบบของคุณ</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">กำลังออนไลน์</span>
                <h3 class="mt-2 mb-1">{{ number_format($summary['active'] ?? 0) }}</h3>
                <div class="text-success small">สตรีมที่พร้อมรับคอมเมนต์ตอนนี้</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <span class="text-muted small">TikTok / Facebook</span>
                <h3 class="mt-2 mb-1">{{ number_format($summary['tiktok'] ?? 0) }} / {{ number_format($summary['facebook'] ?? 0) }}</h3>
                <div class="text-muted small">สรุปแพลตฟอร์มที่เชื่อมอยู่</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">TikTok connector</div>
                <h3 class="mt-2 mb-1">{{ $tiktokConfigured ? 'Available' : 'Unavailable' }}</h3>
                <div class="small {{ $tiktokConfigured ? 'text-success' : 'text-danger' }}">
                    {{ $tiktokConfigured ? 'มี token สำหรับตรวจสอบ LIVE' : 'ยังไม่มี TikTok connector/token' }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">TikTok username ปัจจุบัน</div>
                <h5 class="mt-2 mb-1">{{ $currentTikTokUsername ? '@' . ltrim($currentTikTokUsername, '@') : 'ยังไม่ได้ตั้งค่า' }}</h5>
                <div class="small text-muted">
                    {{ $currentTikTokUsername ? 'ใช้ username นี้กับการเชื่อม LIVE อัตโนมัติ' : 'ไปที่ตั้งค่าร้านค้าเพื่อกรอก TikTok username' }}
                </div>
                <div class="mt-2">
                    <span class="badge {{ $currentTikTokStatus === 'verified' ? 'bg-success-subtle text-success' : ($currentTikTokStatus === 'not_configured' ? 'bg-warning-subtle text-warning' : 'bg-secondary-subtle text-secondary') }}">{{ $currentTikTokStatus }}</span>
                </div>
                <div class="mt-3">
                    <a href="{{ route('settings.index') }}" class="btn btn-outline-primary btn-sm">ไปตั้งค่าร้านค้า</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">LIVE ปัจจุบัน</div>
                <h5 class="mt-2 mb-1">
                    {{ $currentStream?->shop?->name ?? 'ยังไม่มี LIVE ที่กำลังออนไลน์' }}
                </h5>
                <div class="small text-muted">
                    {{ $currentStream ? ($platformLabels[$currentStream->platform] ?? ucfirst($currentStream->platform)) : 'ระบบจะรอให้เริ่ม LIVE ก่อน' }}
                </div>
                @if($currentStream)
                    <div class="small mt-2">
                        เริ่ม {{ optional($currentStream->started_at)->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">สถานะตรวจสอบ LIVE</div>
                <h5 class="mt-2 mb-1">{{ $liveCheck['title'] ?? 'ยังตรวจสอบไม่ได้' }}</h5>
                <div class="small {{ str_contains((string) ($liveCheck['badge_class'] ?? ''), 'success') ? 'text-success' : (str_contains((string) ($liveCheck['badge_class'] ?? ''), 'danger') ? 'text-danger' : 'text-warning') }}">
                    {{ $liveCheck['message'] ?? 'ยังไม่สามารถตรวจสอบได้' }}
                </div>
                <div class="small text-muted mt-2">
                    {{ $summary['connected_real_users'] ?? 0 }} real user / {{ $summary['connected_records'] ?? 0 }} mapping records
                </div>
                <div class="mt-3">
                    <span class="badge {{ $liveCheck['badge_class'] ?? 'bg-secondary-subtle text-secondary' }}">{{ $liveCheck['state'] ?? 'unknown' }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Portal & Messenger</div>
                @if($currentStream)
                    <div class="d-flex flex-column gap-2 mt-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Portal</span>
                            <input type="text" readonly class="form-control bg-light" value="{{ $currentLivePortalUrl }}" id="current-portal-url">
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('current-portal-url').value)">คัดลอก</button>
                        </div>
                        @if($currentLiveMessengerUrl)
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Messenger</span>
                                <input type="text" readonly class="form-control bg-light" value="{{ $currentLiveMessengerUrl }}" id="current-messenger-url">
                                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('current-messenger-url').value)">คัดลอก</button>
                            </div>
                        @else
                            <div class="small text-muted">ยังไม่มี Messenger URL จนกว่าจะเชื่อมเพจจริง</div>
                        @endif
                    </div>
                @else
                    <div class="small text-muted mt-2">ยังไม่มี LIVE ปัจจุบันให้สร้างลิงก์ Portal/Messenger</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning d-flex align-items-start gap-2">
    <x-ui-icon name="alert" class="mt-1 text-warning" size="22" />
    <div>
        <div class="fw-semibold">ก่อนเริ่ม LIVE</div>
        <div class="small">สร้าง LIVE แล้วนำลิงก์ Portal หรือ Messenger ไปปักหมุดใน TikTok/Facebook เพื่อให้ลูกค้ากดเชื่อมต่อก่อนสั่งซื้อ</div>
    </div>
</div>

<div class="alert alert-info d-flex align-items-start gap-2">
    <x-ui-icon name="info" class="mt-1 text-info" size="22" />
    <div>
        <div class="fw-semibold">LINE OA กำลังใช้แพ็คเกจฟรี (0/300)</div>
        <div class="small">แสดงเป็นแถบแจ้งเตือนตามต้นฉบับเท่านั้น รอบนี้ไม่แก้การตั้งค่า LINE หรือยิงข้อความจริง</div>
    </div>
</div>

<div class="card mb-4" id="create-live-form">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">สร้าง LIVE ใหม่</h5>
                <p class="text-muted mb-0">หน้าตาและ flow ถูกจัดให้ใกล้ต้นฉบับมากขึ้น โดยยังคงใช้ระบบจริงของ clone ชุดนี้</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-soft-primary text-primary">สร้างใหม่</span>
                @if($tiktokConfigured)
                    <span class="badge bg-soft-success text-success">connector ready</span>
                @else
                    <span class="badge bg-soft-danger text-danger">connector unavailable</span>
                @endif
                @if($prefillStream)
                    <span class="badge bg-soft-info text-info">prefill จาก LIVE #{{ $prefillStream->id }}</span>
                @endif
            </div>
        </div>
        <form action="{{ route('live.start') }}" method="POST" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label">ร้านค้า</label>
                <select name="shop_id" class="form-select" required>
                    <option value="">เลือกร้านค้า</option>
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" @selected((string) $prefillShopId === (string) $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">แพลตฟอร์ม</label>
                <select name="platform" class="form-select" required>
                    <option value="tiktok" @selected($prefillPlatform === 'tiktok')>TikTok Live</option>
                    <option value="facebook" @selected($prefillPlatform === 'facebook')>Facebook Live</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">ลิงก์ LIVE</label>
                <input type="url" name="live_url" class="form-control" placeholder="https://www.tiktok.com/@user/live" value="{{ $prefillLiveUrl }}" required>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">
                    <x-ui-icon name="broadcast" class="me-1" size="16" />สร้าง LIVE
                </button>
                <button type="reset" class="btn btn-light border">ยกเลิก</button>
                <a href="{{ route('live.copy-latest') }}#create-live-form" class="btn btn-outline-primary">
                    <x-ui-icon name="transfer" class="me-1" size="16" />ใช้ค่า LIVE ล่าสุด
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="mb-1">รายการ LIVE</h5>
                <p class="text-muted mb-0">โครงตารางอิงจากหน้า LIVE ของต้นฉบับ</p>
            </div>
            <div class="text-muted small">ทั้งหมด {{ $streams->count() }} รายการ</div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="70">#</th>
                        <th>LIVE</th>
                        <th width="110">รหัส</th>
                        <th width="120">สถานะ</th>
                        <th width="360">จัดการข้อมูล</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($streams as $index => $stream)
                        @php
                            $portalUrl = $stream->portalSession ? url('/pt?sid=' . $stream->portalSession->sid) : null;
                            $messengerUrl = $stream->portalSession && $stream->shop?->messengerSetting?->is_active && $stream->shop?->messengerSetting?->fb_page_id
                                ? 'https://m.me/' . $stream->shop->messengerSetting->fb_page_id . '?ref=' . $stream->portalSession->sid
                                : null;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $stream->status === 'active' && $stream->platform === 'tiktok' ? '🎥 กำลังไลฟ์ใน Tiktok' : '🎥 Live นี้ ยังไม่ถูกเชื่อมต่อ' }}
                                </div>
                                <div class="text-muted small">{{ $stream->shop->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $platformLabels[$stream->platform] ?? ucfirst($stream->platform) }}</div>
                                <div class="small mt-1">
                                    <a href="{{ $stream->live_url }}" target="_blank" class="text-decoration-underline">{{ $stream->live_url }}</a>
                                </div>
                                <div class="small text-muted mt-2">
                                    เริ่ม {{ optional($stream->started_at)->format('d/m/Y H:i') }}
                                    @if($stream->ended_at)
                                        · จบ {{ optional($stream->ended_at)->format('d/m/Y H:i') }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($stream->portalSession)
                                    <span class="fw-semibold">{{ $stream->portalSession->sid }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($stream->status === 'active')
                                    <span class="badge bg-success-subtle text-success">online</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">offline</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    @if($portalUrl)
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Portal</span>
                                            <input type="text" readonly class="form-control bg-light" value="{{ $portalUrl }}" id="pt-link-{{ $stream->id }}">
                                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('pt-link-{{ $stream->id }}').value)">คัดลอก</button>
                                        </div>
                                    @endif
                                    @if($messengerUrl)
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">Messenger</span>
                                            <input type="text" readonly class="form-control bg-light" value="{{ $messengerUrl }}" id="msg-link-{{ $stream->id }}">
                                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('msg-link-{{ $stream->id }}').value)">คัดลอก</button>
                                        </div>
                                    @endif
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span class="badge bg-light text-dark">ลูกค้าจริง {{ $stream->real_connected_users ?? 0 }} คน</span>
                                        <span class="badge bg-secondary-subtle text-secondary">attempts {{ $stream->connected_attempts ?? 0 }}</span>
                                        <a href="{{ route('live.show', $stream->id) }}" class="btn btn-outline-secondary btn-sm">รายการ</a>
                                        @if($stream->status === 'active')
                                            <form action="{{ route('live.stop', $stream->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-warning btn-sm">หยุด</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('live.destroy', $stream->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ลบ LIVE นี้?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm">ลบ</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">ยังไม่มี LIVE ในระบบ เริ่มจากปุ่ม “สร้าง LIVE” ด้านบน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mt-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif
@endsection
