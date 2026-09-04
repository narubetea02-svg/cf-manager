@extends('layouts.admin')
@section('title', 'แก้ไขสินค้า')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">แก้ไข - {{ $product->name }}</h4></div></div></div>
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-body">
<form action="{{ url('/products/' . $product->id) }}" method="POST">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">ชื่อสินค้า</label><input type="text" name="name" class="form-control" value="{{ $product->name }}" required></div>
<div class="mb-3"><label class="form-label">ราคา</label><input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required></div>
<div class="mb-3"><label class="form-label">สต็อก</label><input type="number" name="stock" class="form-control" value="{{ $product->stock }}"></div>
<button type="submit" class="btn btn-primary">บันทึก</button>
</form></div></div></div></div>
@endsection
