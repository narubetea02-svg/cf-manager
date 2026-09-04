@extends('layouts.admin')
@section('title', 'จัดส่งพัสดุ')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">📦 จัดส่งพัสดุ</h4></div></div></div>
<div class="card"><div class="card-body">
<table class="table table-hover"><thead><tr><th>#</th><th>ร้านค้า</th><th>สินค้า</th><th>ลูกค้า</th><th>สถานะ</th><th>เลขพัสดุ</th><th>จัดการ</th></tr></thead>
<tbody>@forelse($orders as $order)
<tr>
  <td>{{ $order->id }}</td>
  <td>{{ $order->shop->name ?? '-' }}</td>
  <td>{{ $order->product->name ?? '-' }}</td>
  <td>{{ $order->customer_name ?? $order->customer_username }}</td>
  <td>@if($order->status === 'paid')<span class="badge bg-warning">รอจัดส่ง</span>@elseif($order->status === 'shipped')<span class="badge bg-info">จัดส่งแล้ว</span>@else<span class="badge bg-secondary">{{ $order->status }}</span>@endif</td>
  <td>{{ $order->tracking_number ?? '-' }}</td>
  <td>
    @if($order->status === 'paid')
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#shipModal{{ $order->id }}">จัดส่ง</button>
    @endif
  </td>
</tr>
@empty <tr><td colspan="7" class="text-center text-muted py-4">ไม่มีออเดอร์ที่ต้องจัดส่ง</td></tr> @endforelse
</tbody></table>
</div></div>
@foreach($orders->where('status','paid') as $order)
<div class="modal fade" id="shipModal{{ $order->id }}"><div class="modal-dialog"><form method="POST" action="{{ url('/packing/tracking/' . $order->id) }}" class="modal-content">
@csrf
<div class="modal-header"><h5>จัดส่งออเดอร์ #{{ $order->id }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">
<div class="mb-3"><label class="form-label">ขนส่ง</label><select name="carrier" class="form-select" required>
@foreach($carriers as $c)<option value="{{ $c }}">{{ ucfirst(str_replace('_',' ',$c)) }}</option>@endforeach
</select></div>
<div class="mb-3"><label class="form-label">เลขพัสดุ</label><input type="text" name="tracking_number" class="form-control" required></div>
</div>
<div class="modal-footer"><button type="submit" class="btn btn-primary">บันทึก</button></div>
</form></div></div>
@endforeach
@endsection