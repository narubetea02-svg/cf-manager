@extends('layouts.admin')
@section('title', 'สร้างร้านค้า')
@section('content')
<div class="row"><div class="col-12"><div class="page-title-box"><h4 class="page-title">สร้างร้านค้า</h4></div></div></div>
<div class="row"><div class="col-lg-6">
    <div class="card"><div class="card-body">
        <form action="{{ url('/shops') }}" method="POST">
            @csrf
            <div class="mb-3"><label class="form-label">ชื่อร้านค้า</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">คำอธิบาย</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            <div class="mb-3"><label class="form-label">TikTok Username</label><input type="text" name="tiktok_username" class="form-control"></div>
            <button type="submit" class="btn btn-primary">สร้างร้านค้า</button>
        </form>
    </div></div>
</div></div>
@endsection
