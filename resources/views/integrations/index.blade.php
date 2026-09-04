@extends('layouts.admin')
@section('title', 'การเชื่อมต่อ')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">🔗 การเชื่อมต่อ</h4>
                <div class="text-muted small">เมนูหลัก / การเชื่อมต่อ</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/settings') }}" class="btn btn-outline-primary">ไปหน้าการตั้งค่าร้านค้า</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-lg-4">
        <div class="card border h-100 mb-0">
            <div class="card-body">
                <h5 class="mb-2">Facebook Messenger</h5>
                <p class="small text-muted">Webhook: {{ $webhookEnabled ? 'enabled' : 'disabled' }}</p>
                @if($messengerConfigured)
                    <span class="badge bg-success-subtle text-success">configured (page id + active)</span>
                @elseif($messengerHasToken)
                    <span class="badge bg-warning-subtle text-warning">partial (มี token แต่ยังไม่ active/page id)</span>
                @else
                    <span class="badge bg-warning-subtle text-warning">not_configured</span>
                @endif
                <div class="mt-3">
                    <a href="{{ url('/settings#messenger-tab') }}" class="btn btn-sm btn-outline-primary">ตั้งค่า Messenger</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border h-100 mb-0">
            <div class="card-body">
                <h5 class="mb-2">TikTok Live</h5>
                <p class="small text-muted">Username: {{ $tiktokUsername ? '@'.$tiktokUsername : 'ยังไม่ตั้งค่า' }}</p>
                @if($tiktokConfigured)
                    <span class="badge bg-success-subtle text-success">api_key configured</span>
                @else
                    <span class="badge bg-warning-subtle text-warning">not_configured</span>
                @endif
                @if(($tiktokStatus['username_status'] ?? '') === 'verified')
                    <span class="badge bg-success-subtle text-success ms-1">username verified</span>
                @endif
                <div class="mt-3">
                    <a href="{{ url('/settings#shops-tab') }}" class="btn btn-sm btn-outline-primary">ตั้งค่า TikTok</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card border h-100 mb-0">
            <div class="card-body">
                <h5 class="mb-2">LINE OA</h5>
                @if($lineConfigured)
                    <span class="badge bg-success-subtle text-success">configured</span>
                @else
                    <span class="badge bg-warning-subtle text-warning">not_configured</span>
                @endif
                <p class="small text-muted mt-2">ตั้งค่า <code>LINE_CHANNEL_TOKEN</code> ใน .env</p>
            </div>
        </div>
    </div>
</div>
@endsection
