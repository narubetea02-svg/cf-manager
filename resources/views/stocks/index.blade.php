@extends('layouts.admin')
@section('title', 'สต็อก')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">📦 สต็อกสินค้า</h4></div></div></div>
<div class="card"><div class="card-body">
<div class="table-responsive"><table class="table table-hover">
<thead><tr><th>สินค้า</th><th>ราคา</th><th>สต็อก</th><th>สถานะ</th></tr></thead>
<tbody>@forelse($products as $p)
<tr><td>{{ $p->name }}</td><td>{{ number_format($p->price,2) }} ฿</td>
<td><span class="badge bg-{{ $p->stock > 10 ? 'success' : ($p->stock > 0 ? 'warning' : 'danger') }}">{{ $p->stock }}</span></td>
<td>@if($p->is_active)<span class="badge bg-success">พร้อมขาย</span>@else<span class="badge bg-secondary">ปิด</span>@endif</td></tr>
@empty<tr><td colspan="4" class="text-center text-muted">ไม่มีสินค้า</td></tr>@endforelse
</tbody></table></div>
{{ $products->links() }}
</div></div>
@endsection
