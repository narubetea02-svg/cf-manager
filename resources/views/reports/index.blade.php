@extends('layouts.admin')
@section('title', 'รายงาน')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">📊 รายงาน</h4></div></div></div>
<div class="card"><div class="card-body">
<ul class="nav nav-tabs nav-tabs-custom mb-3">
<li class="nav-item"><a class="nav-link {{ $type=='all'?'active':'' }}" href="{{ url('/reports') }}">ทั้งหมด</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='pending'?'active':'' }}" href="{{ url('/reports?type=pending') }}">รอชำระ</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='paid'?'active':'' }}" href="{{ url('/reports?type=paid') }}">ชำระแล้ว</a></li>
<li class="nav-item"><a class="nav-link {{ $type=='shipping'?'active':'' }}" href="{{ url('/reports?type=shipping') }}">กำลังจัดส่ง</a></li>
</ul>
<div class="table-responsive"><table class="table table-hover">
<thead><tr><th>#</th><th>ลูกค้า</th><th>โค้ด</th><th>ยอด</th><th>สถานะ</th><th>วันที่</th></tr></thead>
<tbody>@forelse($orders as $o)
<tr><td>{{ $o->id }}</td><td>{{ $o->customer_username }}</td><td>{{ $o->code }}</td><td>{{ number_format($o->total_price,2) }} ฿</td>
<td><span class="badge bg-{{ $statusColors[$o->status] ?? 'secondary' }}">{{ $statusLabels[$o->status] ?? $o->status }}</span></td>
<td>{{ $o->created_at->format('d/m H:i') }}</td></tr>
@empty<tr><td colspan="6" class="text-center text-muted">ไม่มีข้อมูล</td></tr>@endforelse
</tbody></table></div>
{{ $orders->links() }}
</div></div>
@endsection
