@extends('layouts.admin')
@section('title', 'ทีมงาน')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">👥 ทีมงาน</h4>
                <div class="text-muted small">เมนูหลัก / ทีมงาน</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/userAccess') }}" class="btn btn-outline-primary">ไปหน้าการเข้าถึง</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="alert alert-info mb-4">โหมดปัจจุบัน: บัญชีเดียว (Owner) — จัดการร้านค้า {{ $shops->count() }} ร้าน</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th>บทบาท</th>
                                <th>ร้านที่ดูแล</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($members as $member)
                                <tr>
                                    <td>{{ $member['name'] }}</td>
                                    <td>{{ $member['email'] }}</td>
                                    <td>{{ $member['role'] }}</td>
                                    <td>{{ $member['shops_count'] }}</td>
                                    <td><span class="badge bg-success-subtle text-success">Active</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
