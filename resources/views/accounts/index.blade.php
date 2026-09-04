@extends('layouts.admin')
@section('title', 'บัญชี')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">👤 บัญชี</h4>
                <div class="text-muted small">การตั้งค่า / บัญชี</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-4">ข้อมูลบัญชีผู้ใช้</h5>
                <form method="POST" action="{{ route('accounts.update') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">ชื่อผู้ใช้งาน</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" name="password" class="form-control" placeholder="เว้นว่างถ้าไม่เปลี่ยน">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
