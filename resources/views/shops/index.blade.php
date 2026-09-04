@extends('layouts.admin')
@section('title', 'ร้านค้า')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">ร้านค้า</h4>
                <p class="text-muted mb-0">รูปแบบตารางอิงจากต้นฉบับ พร้อมแสดงสถานะเพจ Facebook และ LIVE ล่าสุด</p>
            </div>
            <a href="{{ url('/shops/create') }}" class="btn btn-primary">
                <x-ui-icon name="plus" class="me-1" size="16" />สร้างร้านค้า
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="90">ลำดับ</th>
                        <th>ร้านค้า</th>
                        <th width="260">จัดการข้อมูล</th>
                        <th width="220">ไลฟ์ล่าสุด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shops as $index => $shop)
                        @php
                            $latestLive = $shop->liveStreams->sortByDesc('started_at')->first();
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="avatar-sm flex-shrink-0">
                                        <div class="avatar-title rounded-circle bg-light text-primary">
                                            @if($shop->logo)
                                                <img src="{{ $shop->logo }}" alt="{{ $shop->name }}" class="rounded-circle" style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <x-ui-icon name="store" size="22" />
                                            @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $shop->name }}</div>
                                        <div class="text-muted small">{{ $shop->description ?: 'ไม่มีคำอธิบายร้านค้า' }}</div>
                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            @if($shop->messengerSetting?->is_active && $shop->messengerSetting?->fb_page_id)
                                                <span class="badge bg-success-subtle text-success">เชื่อมเพจแล้ว</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">ยังไม่เชื่อมเพจ</span>
                                            @endif
                                            @if($shop->tiktok_username)
                                                <span class="badge bg-light text-dark">{{ '@' . ltrim($shop->tiktok_username, '@') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('shops.edit', $shop->id) }}" class="btn btn-primary btn-sm">จัดการ</a>
                                    <a href="{{ url('/live') }}" class="btn btn-light btn-sm border">ดู LIVE</a>
                                    <form action="{{ url('/shops/' . $shop->id) }}" method="POST" class="d-inline" onsubmit="return confirm('ลบร้านค้านี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">ลบ</button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                @if($latestLive)
                                    <div class="fw-semibold">{{ ucfirst($latestLive->platform) }}</div>
                                    <div class="text-muted small">{{ optional($latestLive->started_at)->format('d/m/Y H:i') }}</div>
                                    <div class="mt-2">
                                        @if($latestLive->status === 'active')
                                            <span class="badge bg-success-subtle text-success">กำลังออนไลน์</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">จบแล้ว</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">ยังไม่มี LIVE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                ยังไม่มีร้านค้าในระบบ
                                <div class="mt-3">
                                    <a href="{{ url('/shops/create') }}" class="btn btn-primary">สร้างร้านค้าแรก</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
