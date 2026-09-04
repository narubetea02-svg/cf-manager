@extends('layouts.admin')
@section('title','บรอดแคสต์')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">📢 บรอดแคสต์แจ้งเตือน</h4></div></div></div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ url('/broadcasts') }}">@csrf
<div class="mb-3"><label class="form-label">เลือกออเดอร์</label>
<select name="order_ids[]" class="form-select" multiple required size="8">
@foreach($orders as $o)<option value="{{ $o->id }}">#{{ $o->id }} - {{ $o->customer_username }} ({{ $o->shop->name ?? '-' }})</option>@endforeach
</select>
<div class="form-text">กด Ctrl/Cmd เพื่อเลือกหลายรายการ</div>
</div>
<div class="mb-3"><label class="form-label">ข้อความ</label><textarea name="message" class="form-control" rows="4" placeholder="พิมพ์ข้อความแจ้งเตือน..." required></textarea></div>
@if(config('facebook.send_enabled', false))
    <button class="btn btn-primary">📤 ส่งข้อความ</button>
@else
    <button class="btn btn-warning">📤 บันทึก dry-run</button>
    <div class="form-text text-warning mt-2">MESSENGER_SEND_ENABLED=false — บันทึกประวัติเท่านั้น ไม่ส่งจริง</div>
@endif
</form></div></div>
<div class="card"><div class="card-body">
<h5 class="mb-3">💬 ประวัติการส่ง</h5>
<div class="table-responsive">
<table class="table table-sm align-middle">
<thead><tr><th>เวลา</th><th>สถานะ</th><th>จำนวน</th><th>ข้อความ</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr>
<td>{{ $log->created_at->format('d/m H:i') }}</td>
<td><span class="badge bg-{{ $log->status === 'sent' ? 'success' : 'warning' }}-subtle text-{{ $log->status === 'sent' ? 'success' : 'warning' }}">{{ $log->status }}</span></td>
<td>{{ $log->recipient_count }}</td>
<td>{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
</tr>
@empty
<tr><td colspan="4" class="text-muted">ยังไม่มีประวัติ</td></tr>
@endforelse
</tbody>
</table>
</div>
</div></div>
@endsection
