@extends('layouts.admin')
@section('title', 'เครดิต')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">🪙 เครดิต</h4>
                <div class="text-muted small">เมนูหลัก / เครดิต</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/financial') }}" class="btn btn-outline-primary">ไปหน้าการเงิน</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ยอดคงเหลือ</div>
                <h3 class="mt-2 mb-1">{{ number_format($balance) }} เครดิต</h3>
                <div class="small text-muted">จากโควต้าทดลอง {{ number_format($freeAllowance) }} เครดิต</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">ใช้ไปแล้ว</div>
                <h3 class="mt-2 mb-1">{{ number_format($usedCredits) }}</h3>
                <div class="small text-muted">ออเดอร์ = 1 เครดิต, LIVE = 2 เครดิต</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="text-body mb-3">ประวัติการใช้งานเครดิต</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>วันที่/เวลา</th>
                                <th>รายการ</th>
                                <th>ช่องทาง</th>
                                <th class="text-end">จำนวนเครดิต</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $item)
                                <tr>
                                    <td>{{ optional($item['at'])->format('d/m/Y H:i') }}</td>
                                    <td>{{ $item['label'] }}</td>
                                    <td>{{ $item['channel'] }}</td>
                                    <td class="text-end {{ $item['amount'] < 0 ? 'text-danger' : 'text-success' }}">{{ $item['amount'] > 0 ? '+' : '' }}{{ number_format($item['amount']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">ยังไม่มีประวัติการใช้เครดิต</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
