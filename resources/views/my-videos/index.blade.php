@extends('layouts.admin')
@section('title', 'วิดีโอของฉัน')
@section('content')
<div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h4 class="page-title mb-1">วิดีโอของฉัน</h4>
        <div class="text-muted small">เมนูหลัก / วิดีโอของฉัน</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-light border" onclick="window.location.reload()">รีเฟรช</button>
        <button type="button" class="btn btn-light border">โปรไฟล์</button>
        <button type="button" class="btn btn-primary">เพจ</button>
    </div>
</div>
<div class="card">
    <div class="card-body text-center py-5">
        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px"><x-ui-icon name="play" size="28" /></div>
        <h5 class="mb-2">วิดีโอของฉัน</h5>
        <p class="text-muted mb-0">วิดีโอจากโปรไฟล์หรือเพจที่เชื่อมกับร้านค้าจะแสดงที่หน้านี้</p>
    </div>
</div>
@endsection
