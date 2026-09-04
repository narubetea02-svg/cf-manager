@extends('layouts.admin')
@section('title', 'ช่วยเหลือ')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">❓ ช่วยเหลือ</h4>
                <div class="text-muted small">ช่วยเหลือ / คู่มือการใช้งาน</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/tutorial') }}" class="btn btn-outline-primary">ดูวีดีโอสอนใช้งานทั้งหมด</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6 col-lg-4 text-center">
                        <div class="mb-3">
                            <x-ui-icon name="book-open" size="48" class="text-primary" />
                        </div>
                        <h5>คู่มือการใช้งาน</h5>
                        <p class="text-muted small mb-3">อ่านบทความและคู่มือการตั้งค่าระบบทีละขั้นตอน</p>
                        <a href="https://tutorials.cf-shops.com/" target="_blank" class="btn btn-primary btn-sm w-100">เปิดคู่มือ</a>
                    </div>
                    <div class="col-md-6 col-lg-4 text-center">
                        <div class="mb-3">
                            <x-ui-icon name="video" size="48" class="text-info" />
                        </div>
                        <h5>วีดีโอสอนใช้งาน</h5>
                        <p class="text-muted small mb-3">ดูวีดีโอแนะนำการใช้ระบบจัดการไลฟ์และการตั้งค่า</p>
                        <a href="{{ url('/tutorial') }}" class="btn btn-info text-white btn-sm w-100">ดูวีดีโอ</a>
                    </div>
                    <div class="col-md-6 col-lg-4 text-center">
                        <div class="mb-3">
                            <x-ui-icon name="message-circle" size="48" class="text-success" />
                        </div>
                        <h5>ติดต่อทีมงาน (Support)</h5>
                        <p class="text-muted small mb-3">หากพบปัญหาการใช้งาน สามารถแจ้งทีมงานผ่าน LINE OA</p>
                        <a href="https://lin.ee/xfZH0TL" target="_blank" class="btn btn-success btn-sm w-100">แจ้งปัญหา/ติดต่อแอดมิน</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
