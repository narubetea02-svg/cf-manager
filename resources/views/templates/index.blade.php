@extends('layouts.admin')
@section('title', 'เทมเพลต')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">📝 เทมเพลต</h4>
                <div class="text-muted small">การตั้งค่า / เทมเพลต</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" disabled>สร้างเทมเพลตใหม่</button>
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
                        <h5 class="text-body mb-1">เทมเพลตข้อความ (Utility Templates)</h5>
                        <div class="text-muted small">จัดการเทมเพลตข้อความตอบกลับด่วนเพื่อใช้งานในแชทและบรอดแคสต์</div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อเทมเพลต</th>
                                <th>ตัวอย่างข้อความ</th>
                                <th>อัปเดตล่าสุด</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates ?? [] as $template)
                                <tr>
                                    <td class="fw-semibold">{{ $template['title'] ?? 'N/A' }}</td>
                                    <td class="text-muted text-truncate" style="max-width: 300px;">{{ $template['body'] ?? '' }}</td>
                                    <td>-</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary" disabled>แก้ไข</button>
                                        <button class="btn btn-sm btn-outline-danger" disabled>ลบ</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <h4 class="text-secondary opacity-50 mb-2">ยังไม่มีเทมเพลต</h4>
                                        <p class="small mb-0">คลิก "สร้างเทมเพลตใหม่" เพื่อเพิ่มเทมเพลตข้อความตอบด่วน</p>
                                    </td>
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
