@extends('layouts.admin')
@section('title','การเงิน')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">💰 การเงิน</h4></div></div></div>
<div class="card"><div class="card-body"><h5>รายการชำระเงิน</h5>
<table class="table"><thead><tr><th>#</th><th>ออเดอร์</th><th>จำนวน</th><th>สลิป</th><th>สถานะ</th><th>จัดการ</th></tr></thead>
<tbody>@forelse($payments as $p)
<tr><td>{{ $p->id }}</td><td>#{{ $p->order_id }}</td><td>{{ number_format($p->amount,2) }} บ.</td>
<td>@if($p->slip_image)<a href='#'>ดูสลิป</a>@else - @endif</td>
<td>@if($p->status==='verified')<span class='badge bg-success'>ยืนยันแล้ว</span>@elseif($p->status==='rejected')<span class='badge bg-danger'>ปฏิเสธ</span>@else<span class='badge bg-warning'>รอตรวจ</span>@endif</td>
<td>@if($p->status==='pending')<a href='{{ url("/payments/verify/{$p->id}") }}' class='btn btn-success btn-sm'>ยืนยัน</a>@endif</td></tr>
@empty <tr><td colspan='6' class='text-center text-muted py-4'>ไม่มีรายการ</td></tr> @endforelse
</tbody></table></div></div>
@endsection