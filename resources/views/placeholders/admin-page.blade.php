@extends('layouts.admin')
@section('title', $title)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">{{ $title }}</h4>
                <p class="text-muted mb-0">{{ $description }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">กลับหน้าแดชบอร์ด</a>
                <a href="{{ $primaryUrl ?? url('/customers') }}" class="btn btn-primary">{{ $primaryLabel ?? 'กลับหน้าหลัก' }}</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                <x-ui-icon :name="$icon ?? 'settings'" size="28" />
            </div>
            <div>
                <h5 class="mb-2">หน้านี้กำลังเตรียมใช้งาน</h5>
                <p class="text-muted mb-3">เราเปิด route และ layout ไว้ให้ใช้งานได้จริงก่อน เพื่อไม่ให้เมนูพาไปหน้าเสียหรือหน้าไม่พบ ระหว่างนี้ยังไม่แตะ logic เสี่ยงหรือการส่งข้อความจริง</p>
                @if(!empty($tips))
                    <ul class="mb-0 text-muted">
                        @foreach($tips as $tip)
                            <li>{{ $tip }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
