@extends('layouts.admin')
@section('title', 'รายการ LIVE')

@php
    $portalUrl = $stream->portalSession ? url('/pt?sid=' . $stream->portalSession->sid) : null;
    $messengerUrl = $stream->portalSession && $stream->shop?->messengerSetting?->is_active && $stream->shop?->messengerSetting?->fb_page_id
        ? 'https://m.me/' . $stream->shop->messengerSetting->fb_page_id . '?ref=' . $stream->portalSession->sid
        : null;
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">รายการ LIVE</h4>
                <p class="text-muted mb-0">รายละเอียด LIVE, ลิงก์ Portal และสถานะการเชื่อมต่อของลูกค้าสำหรับสตรีมนี้</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('live.index') }}" class="btn btn-light border">
                    <x-ui-icon name="arrow-right" class="me-1" size="16" />กลับไปหน้า LIVE
                </a>
                <a href="{{ route('live.print') }}" target="_blank" class="btn btn-outline-secondary">
                    <x-ui-icon name="printer" class="me-1" size="16" />พิมพ์
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">LIVE</div>
                        <div class="fw-semibold">{{ $stream->shop?->name ?? '-' }}</div>
                        <div class="text-muted small">{{ ucfirst($stream->platform) }} · {{ $stream->live_url }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">รหัส</div>
                        <div class="fw-semibold">{{ $stream->portalSession?->sid ?? '-' }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted">สถานะ</div>
                        @if($stream->status === 'active')
                            <span class="badge bg-success-subtle text-success">online</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">offline</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">เริ่ม LIVE</div>
                        <div class="fw-semibold">{{ optional($stream->started_at)->format('d/m/Y H:i') ?: '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">จบ LIVE</div>
                        <div class="fw-semibold">{{ optional($stream->ended_at)->format('d/m/Y H:i') ?: '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">ลูกค้าที่เชื่อมจริง</div>
                        <div class="fw-semibold">{{ $stream->real_connected_users ?? 0 }} คน</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">จำนวน attempts</div>
                        <div class="fw-semibold">{{ $stream->connected_attempts ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">ลิงก์สำหรับใช้งาน</h5>
                @if($portalUrl)
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text">Portal</span>
                        <input id="live-detail-portal" type="text" readonly class="form-control bg-light" value="{{ $portalUrl }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('live-detail-portal').value)">คัดลอก</button>
                    </div>
                @endif
                @if($messengerUrl)
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Messenger</span>
                        <input id="live-detail-messenger" type="text" readonly class="form-control bg-light" value="{{ $messengerUrl }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('live-detail-messenger').value)">คัดลอก</button>
                    </div>
                @else
                    <div class="small text-muted">ยังไม่มี Messenger URL จนกว่าจะเชื่อมเพจจริง</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
