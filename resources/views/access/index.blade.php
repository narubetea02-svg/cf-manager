@extends('layouts.admin')
@section('title', 'การเข้าถึง')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">🛡️ การเข้าถึง</h4>
                <div class="text-muted small">การตั้งค่า / การเข้าถึง</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ url('/team') }}" class="btn btn-outline-primary">ดูรายชื่อทีมงานรวมศูนย์</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="text-body mb-1">จัดการสิทธิ์การเข้าถึงร้านค้า</h5>
                        <div class="text-muted small">ตั้งค่าให้พนักงานหรือแอดมินคนอื่นสามารถเข้าถึงร้านค้านี้ได้</div>
                    </div>
                    <button class="btn btn-primary" disabled>เพิ่มผู้ดูแลร้าน</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อผู้ใช้</th>
                                <th>อีเมล</th>
                                <th>สิทธิ์ที่ได้รับ</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $user->name ?? 'Admin' }}</td>
                                <td>{{ $user->email ?? 'admin@example.com' }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">เจ้าของร้าน (Owner)</span></td>
                                <td><span class="badge bg-success-subtle text-success">Active</span></td>
                                <td><button class="btn btn-sm btn-outline-secondary" disabled>แก้ไข</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
