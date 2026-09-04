@extends('layouts.admin')
@section('title', 'เพิ่มสินค้า')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">เพิ่มสินค้า</h4></div></div></div>
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-body">
<form action="{{ url('/products') }}" method="POST">@csrf
<div class="mb-3"><label class="form-label">ชื่อสินค้า</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">ราคา</label><input type="number" step="0.01" name="price" class="form-control" required></div>
<div class="mb-3"><label class="form-label">สต็อก</label><input type="number" name="stock" class="form-control" value="0"></div>
<div class="mb-3"><label class="form-label">รหัสค้นหา</label><input type="text" name="code_pattern" class="form-control" placeholder="เช่น cf123"></div>
<button type="submit" class="btn btn-success">บันทึก</button>
</form></div></div></div></div>
@endsection
