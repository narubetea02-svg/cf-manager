@extends('layouts.admin')
@section('title', $modeConfig['title'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">{{ $modeConfig['title'] }}</h4>
                <p class="text-muted mb-0">{{ $modeConfig['description'] }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-light border">กลับไปหน้าสินค้า</a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('products.import.store', ['mode' => $mode]) }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label">ไฟล์ CSV</label>
                <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                <div class="form-text text-muted">
                    @if($mode === 'options')
                        ตัวอย่างหัวคอลัมน์: <code>product_name,variant_code,price,quantity</code>
                    @else
                        ตัวอย่างหัวคอลัมน์: <code>name,price,stock,code_pattern</code>
                    @endif
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">นำเข้าสินค้า</button>
            </div>
        </form>
    </div>
</div>
@endsection
