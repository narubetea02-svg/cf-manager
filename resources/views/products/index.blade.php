@extends('layouts.admin')
@section('title', 'ข้อมูลสินค้า')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">ข้อมูลสินค้า</h4>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">เมนูหลัก</a></li>
                    <li class="breadcrumb-item active">สินค้า</li>
                </ol>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" id="btn-add-product">
                    <i class="bx bx-plus me-1"></i> เพิ่มสินค้า
                    <i class="bx bx-chevron-down ms-1"></i>
                </button>
                <a href="{{ url('/printProductReport') }}" target="_blank" class="btn btn-light border" title="พิมพ์รายงาน">
                    <i class="bx bx-printer"></i>
                </a>
                <a href="{{ url('/printStockReport') }}" class="btn btn-light border">
                    <i class="bx bx-download me-1"></i> Export Excel
                </a>
                <button type="submit" form="product-bulk-delete-form" id="bulk-delete-products" 
                    class="btn btn-danger" disabled 
                    onclick="return confirm('ลบสินค้าที่เลือก?')">
                    <i class="bx bx-trash me-1"></i> ลบที่เลือก
                </button>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning d-flex align-items-center gap-2 py-2">
    <i class="bx bx-info-circle fs-5"></i>
    <div>
        เพื่อความสะดวกในการขาย กรุณา <a href="{{ url('/live') }}" class="fw-semibold text-decoration-underline">สร้างไลฟ์</a> 
        และเพิ่มสินค้าเข้าไลฟ์ เพื่อเตรียมรหัส CF ติดบนตัวสินค้า ก่อนทำการไลฟ์
    </div>
</div>

<form id="product-bulk-delete-form" action="{{ route('products.bulk-delete') }}" method="POST">
    @csrf
    <div class="card">
        <div class="card-body px-2 px-md-4">
            {{-- DataTables toolbar --}}
            <div class="row align-items-center mb-3 g-2">
                <div class="col-auto">
                    <label class="d-flex align-items-center gap-2 mb-0">
                        Show
                        <select id="dt-per-page" class="form-select form-select-sm" style="width: auto;" onchange="dtReload()">
                            @foreach([10, 25, 50, 100] as $opt)
                                <option value="{{ $opt }}" @selected((int) $perPage === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        entries
                    </label>
                </div>
                <div class="col-auto ms-auto">
                    <label class="d-flex align-items-center gap-2 mb-0">
                        Search:
                        <input type="text" id="dt-search" class="form-control form-control-sm" style="width: 200px;" 
                            value="{{ $search }}" placeholder="">
                    </label>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle table-bordered" id="products-table">
                    <thead>
                        <tr>
                            <th width="42">
                                <input type="checkbox" id="select-all-products" class="form-check-input">
                            </th>
                            <th class="sortable" data-col="name">ชื่อ <span class="sort-icon">↕</span></th>
                            <th class="sortable" data-col="detail">รายละเอียด <span class="sort-icon">↕</span></th>
                            <th class="sortable text-center" data-col="stock" width="100">ตัวเลือก <span class="sort-icon">↕</span></th>
                            <th class="text-center" width="130">เพิ่มตัวเลือก</th>
                            <th class="text-center" width="80">จัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        @forelse($products as $product)
                            @php
                                $totalStock = $product->variants->count() > 0 
                                    ? $product->variants->sum('quantity') 
                                    : $product->stock;
                            @endphp
                            <tr data-product-id="{{ $product->id }}">
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="form-check-input product-check">
                                </td>
                                <td>
                                    <div class="fw-semibold text-body">{{ $product->name }}</div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $product->description ? strip_tags(substr($product->description, 0, 60)) . (strlen($product->description) > 60 ? '...' : '') : '' }}</small>
                                </td>
                                <td class="text-center fw-semibold">
                                    {{ number_format($totalStock) }}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-success btn-sm rounded-circle p-1 btn-add-variant"
                                        data-product-id="{{ $product->id }}"
                                        title="เพิ่มตัวเลือก"
                                        style="width:36px;height:36px;">
                                        <i class="bx bx-copy-alt fs-5"></i>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-primary btn-sm rounded-circle p-1 btn-edit-product"
                                        data-product-id="{{ $product->id }}"
                                        title="จัดการข้อมูล"
                                        style="width:36px;height:36px;">
                                        <i class="bx bx-edit fs-5"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">ยังไม่มีข้อมูลสินค้า</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
                <div id="dt-info" class="text-muted small">
                    Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $products->previousPageUrl() }}">Previous</a>
                        </li>
                        @foreach($products->links()->elements[0] as $page => $url)
                            <li class="page-item {{ $products->currentPage() == $page ? 'active' : '' }}">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endforeach
                        <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link" href="{{ $products->nextPageUrl() }}">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</form>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible mt-3 fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible mt-3 fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ============================================================ --}}
{{-- MODAL: เพิ่มสินค้า (Add Product) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="addProductModalLabel">เพิ่มข้อมูลสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label fw-semibold">ชื่อสินค้า หรือ ชื่อหมวดสินค้า <span class="text-danger">*</span></label>
                        <input type="text" id="add-name" class="form-control mb-3" placeholder="">
                        {{-- Image upload --}}
                        <div class="border rounded-2 p-3 text-center bg-light" id="add-image-preview-box" style="min-height:180px;">
                            <img id="add-image-preview" src="" alt="" class="img-fluid rounded" style="max-height:160px;display:none;">
                            <div id="add-image-placeholder" class="text-muted py-4">
                                <i class="bx bx-image fs-1 text-secondary"></i>
                                <div class="small mt-1">IMAGE NOT AVAILABLE</div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-upload me-1"></i> เลือกภาพสินค้า
                                <input type="file" id="add-image-file" accept="image/*" class="d-none">
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <label class="form-label fw-semibold">รายละเอียดสินค้า
                            <span class="ms-1 text-muted small" title="รายละเอียดจะแสดงเฉพาะร้านที่เห็น">ℹ</span>
                        </label>
                        <textarea id="add-description" class="form-control" rows="10" 
                            placeholder="เพิ่มรายละเอียดส่วนนี้ เพื่อลดความสับสน เฉพาะร้านที่เห็น"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top justify-content-center">
                <button type="button" class="btn btn-primary px-5" id="btn-add-product-save">
                    บันทึกข้อมูล และเพิ่มรหัส
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: จัดการข้อมูลสินค้า (Edit Product + Variants) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="editProductModalLabel">ข้อมูลตัวเลือกสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    {{-- Left: product info --}}
                    <div class="col-lg-4">
                        <label class="form-label fw-semibold">ชื่อสินค้า หรือ ชื่อหมวดสินค้า
                            <span class="ms-1 text-muted small">ℹ</span>
                        </label>
                        <input type="text" id="edit-name" class="form-control mb-3">
                        <label class="form-label fw-semibold">รายละเอียดสินค้า</label>
                        <textarea id="edit-description" class="form-control mb-3" rows="5"></textarea>
                        {{-- Image --}}
                        <div class="border rounded-2 p-2 text-center bg-light" style="min-height:140px;">
                            <img id="edit-image-preview" src="" alt="" class="img-fluid rounded" style="max-height:120px;display:none;">
                            <div id="edit-image-placeholder" class="text-muted py-3">
                                <i class="bx bx-image fs-1 text-secondary"></i>
                                <div class="small">IMAGE NOT AVAILABLE</div>
                            </div>
                        </div>
                        <div class="small text-muted mt-2" id="edit-stock-summary"></div>
                    </div>
                    {{-- Right: variants table --}}
                    <div class="col-lg-8">
                        {{-- Variant DataTable toolbar --}}
                        <div class="row align-items-center mb-2 g-2">
                            <div class="col-auto">
                                <label class="d-flex align-items-center gap-2 mb-0 small">
                                    Show
                                    <select class="form-select form-select-sm" id="variant-per-page" style="width:auto;">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                    entries
                                </label>
                            </div>
                            <div class="col-auto ms-auto">
                                <label class="d-flex align-items-center gap-2 mb-0 small">
                                    Search:
                                    <input type="text" id="variant-search" class="form-control form-control-sm" style="width:150px;">
                                </label>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-sm table-hover table-bordered align-middle" id="variants-table">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="38">
                                            <input type="checkbox" id="select-all-variants" class="form-check-input">
                                        </th>
                                        <th>ตัวเลือก</th>
                                        <th class="text-center">รหัส CF</th>
                                        <th class="text-center">ต้นทุน</th>
                                        <th class="text-center">ราคาขาย</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">น้ำหนัก</th>
                                        <th class="text-center">จัดการข้อมูล</th>
                                    </tr>
                                </thead>
                                <tbody id="variants-tbody">
                                    <tr><td colspan="8" class="text-center text-muted py-3">No data available in table</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <div class="small text-muted" id="variant-info">Showing 0 to 0 of 0 entries</div>
                            <div></div>
                        </div>
                        {{-- Action buttons --}}
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add-variant-single">
                                <i class="bx bx-plus me-1"></i> เพิ่มทีละรหัส
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btn-add-variant-bulk">
                                <i class="bx bx-list-plus me-1"></i> เพิ่มทีละหลายรหัส
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="btn-save-product">
                                บันทึกข้อมูล
                            </button>
                            <button type="button" class="btn btn-info btn-sm text-white" id="btn-bulk-stock" disabled>
                                <i class="bx bx-package me-1"></i> ปรับสต็อกที่เลือก
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" id="btn-bulk-price" disabled>
                                <i class="bx bx-dollar me-1"></i> ปรับราคาที่เลือก
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="btn-bulk-delete-variant" disabled>
                                <i class="bx bx-trash me-1"></i> ลบที่เลือก
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: เพิ่มรหัส (Add Single Variant) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="addVariantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มรหัสสินค้า</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">รหัส CF</label>
                    <input type="text" id="new-variant-code" class="form-control" placeholder="เช่น ค=19, บ=9">
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label">ต้นทุน (บาท)</label>
                        <input type="number" id="new-variant-cost" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">ราคาขาย (บาท)</label>
                        <input type="number" id="new-variant-price" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">จำนวน (ชิ้น)</label>
                        <input type="number" id="new-variant-qty" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">น้ำหนัก (กก.)</label>
                        <input type="number" id="new-variant-weight" class="form-control" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-save-variant-single">บันทึก</button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: เพิ่มหลายรหัส (Bulk Add Variants) --}}
{{-- ============================================================ --}}
<div class="modal fade" id="bulkVariantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มทีละหลายรหัส</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    กรอกรหัส CF แต่ละบรรทัดพร้อมค่า (ต้นทุน, ราคา, จำนวน, น้ำหนัก) คั่นด้วย tab หรือ comma<br>
                    <strong>ตัวอย่าง:</strong><br>
                    <code>ค=19	0	350	100	0</code><br>
                    <code>บ=9	0	350	100	0</code><br>
                    หรือใช้ช่อง Preset ด้านล่างสำหรับรูปแบบง่าย ๆ
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">ตัวอักษรนำหน้า (เช่น ค, บ, ป)</label>
                        <input type="text" id="bulk-prefix" class="form-control" placeholder="ค">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">เริ่มจาก</label>
                        <input type="number" id="bulk-start" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">ถึง</label>
                        <input type="number" id="bulk-end" class="form-control" value="20" min="1">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">ราคาขาย</label>
                        <input type="number" id="bulk-price" class="form-control" value="0">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">จำนวน</label>
                        <input type="number" id="bulk-qty" class="form-control" value="100">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">น้ำหนัก</label>
                        <input type="number" id="bulk-weight" class="form-control" value="0" step="0.01">
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-bulk-preview">
                            <i class="bx bx-refresh me-1"></i> สร้างตัวอย่าง
                        </button>
                    </div>
                </div>
                <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                    <table class="table table-sm table-bordered align-middle" id="bulk-preview-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>รหัส CF</th>
                                <th>ต้นทุน</th>
                                <th>ราคาขาย</th>
                                <th>จำนวน</th>
                                <th>น้ำหนัก</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="bulk-preview-tbody">
                            <tr><td colspan="6" class="text-center text-muted py-2">กดปุ่มสร้างตัวอย่างก่อน</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-bulk-save">
                    <i class="bx bx-save me-1"></i> บันทึกทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL: ปรับสต็อก / ราคา --}}
{{-- ============================================================ --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustModalTitle">ปรับสต็อก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="adjust-mode-group">
                    <label class="form-label">วิธีปรับ</label>
                    <select id="adjust-mode" class="form-select">
                        <option value="set">ตั้งค่าใหม่</option>
                        <option value="add">เพิ่ม</option>
                        <option value="subtract">ลด</option>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label" id="adjust-value-label">จำนวน (ชิ้น)</label>
                    <input type="number" id="adjust-value" class="form-control" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btn-adjust-save">บันทึก</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // ============================================================
    // State
    // ============================================================
    let currentProductId = null;
    let allVariants = [];
    let bulkPreviewData = [];
    let adjustType = 'stock'; // 'stock' | 'price'
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    // ============================================================
    // Helpers
    // ============================================================
    async function apiFetch(url, method = 'GET', body = null) {
        const opts = {
            method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(url, opts);
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || `HTTP ${res.status}`);
        }
        return res.json();
    }

    function showToast(msg, type = 'success') {
        const el = document.createElement('div');
        el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow-sm fade show`;
        el.style.zIndex = 9999;
        el.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 3500);
    }

    // ============================================================
    // Product list bulk-delete checkboxes
    // ============================================================
    const selectAll = document.getElementById('select-all-products');
    const bulkDeleteBtn = document.getElementById('bulk-delete-products');
    
    function syncBulk() {
        const checked = document.querySelectorAll('.product-check:checked');
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = checked.length === 0;
    }
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.product-check').forEach(cb => cb.checked = selectAll.checked);
            syncBulk();
        });
    }
    document.querySelectorAll('.product-check').forEach(cb => cb.addEventListener('change', syncBulk));
    syncBulk();

    // Search + per-page reload
    window.dtReload = function() {
        const q = document.getElementById('dt-search')?.value ?? '';
        const pp = document.getElementById('dt-per-page')?.value ?? '20';
        const url = new URL(window.location.href);
        url.searchParams.set('q', q);
        url.searchParams.set('per_page', pp);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    };
    const dtSearch = document.getElementById('dt-search');
    if (dtSearch) {
        let timer;
        dtSearch.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(dtReload, 500); });
        dtSearch.addEventListener('keydown', e => { if (e.key === 'Enter') dtReload(); });
    }

    // ============================================================
    // ADD PRODUCT MODAL
    // ============================================================
    const addModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    let addImageUrl = null;

    document.getElementById('btn-add-product')?.addEventListener('click', () => {
        document.getElementById('add-name').value = '';
        document.getElementById('add-description').value = '';
        document.getElementById('add-image-preview').style.display = 'none';
        document.getElementById('add-image-placeholder').style.display = 'block';
        addImageUrl = null;
        addModal.show();
    });

    document.getElementById('add-image-file')?.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        // Preview locally
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('add-image-preview').src = e.target.result;
            document.getElementById('add-image-preview').style.display = 'block';
            document.getElementById('add-image-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('btn-add-product-save')?.addEventListener('click', async () => {
        const name = document.getElementById('add-name').value.trim();
        if (!name) { showToast('กรุณากรอกชื่อสินค้า', 'warning'); return; }
        const desc = document.getElementById('add-description').value;
        
        try {
            const res = await apiFetch('/products', 'POST', { name, description: desc });
            
            // If there's an image file, upload it
            const fileInput = document.getElementById('add-image-file');
            if (fileInput.files[0] && res.product?.id) {
                const formData = new FormData();
                formData.append('image', fileInput.files[0]);
                await fetch(`/products/${res.product.id}/image`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData,
                });
            }
            
            showToast(res.message || 'เพิ่มสินค้าเรียบร้อย');
            addModal.hide();
            setTimeout(() => window.location.reload(), 800);
        } catch (e) {
            showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger');
        }
    });

    // ============================================================
    // EDIT PRODUCT MODAL (with variants)
    // ============================================================
    const editModalEl = document.getElementById('editProductModal');
    const editModal = new bootstrap.Modal(editModalEl);

    async function openEditModal(productId) {
        currentProductId = productId;
        try {
            const data = await apiFetch(`/products/${productId}/variants`);
            // Fill product info
            document.getElementById('edit-name').value = data.product.name ?? '';
            document.getElementById('edit-description').value = data.product.description ?? '';
            const imgEl = document.getElementById('edit-image-preview');
            const imgPlaceholder = document.getElementById('edit-image-placeholder');
            if (data.product.image) {
                imgEl.src = data.product.image;
                imgEl.style.display = 'block';
                imgPlaceholder.style.display = 'none';
            } else {
                imgEl.style.display = 'none';
                imgPlaceholder.style.display = 'block';
            }
            document.getElementById('edit-stock-summary').textContent = 
                `(หมวดนี้มีสินค้ารวมทุกรหัส คงเหลือ : ${data.product.total_stock} ชิ้น)`;
            
            allVariants = data.variants ?? [];
            renderVariantsTable(allVariants);
            syncVariantBulkButtons();
            editModal.show();
        } catch (e) {
            showToast('ไม่สามารถโหลดข้อมูลได้: ' + e.message, 'danger');
        }
    }

    document.querySelectorAll('.btn-edit-product').forEach(btn => {
        btn.addEventListener('click', () => openEditModal(btn.dataset.productId));
    });
    document.querySelectorAll('.btn-add-variant').forEach(btn => {
        btn.addEventListener('click', () => {
            currentProductId = btn.dataset.productId;
            openEditModal(btn.dataset.productId).then(() => {
                setTimeout(() => {
                    new bootstrap.Modal(document.getElementById('addVariantModal')).show();
                }, 300);
            });
        });
    });

    // Save product info
    document.getElementById('btn-save-product')?.addEventListener('click', async () => {
        const name = document.getElementById('edit-name').value.trim();
        if (!name) { showToast('กรุณากรอกชื่อสินค้า', 'warning'); return; }
        const desc = document.getElementById('edit-description').value;
        try {
            await apiFetch(`/products/${currentProductId}`, 'PUT', { name, description: desc });
            showToast('บันทึกข้อมูลสินค้าแล้ว');
            // Update row name in table
            document.querySelector(`tr[data-product-id="${currentProductId}"] td:nth-child(2) .fw-semibold`).textContent = name;
        } catch (e) {
            showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger');
        }
    });

    // ============================================================
    // VARIANTS TABLE RENDERING
    // ============================================================
    function renderVariantsTable(variants) {
        const tbody = document.getElementById('variants-tbody');
        const searchVal = (document.getElementById('variant-search')?.value ?? '').toLowerCase();
        const perPage = parseInt(document.getElementById('variant-per-page')?.value ?? '10');
        
        let filtered = variants;
        if (searchVal) {
            filtered = variants.filter(v => (v.code ?? '').toLowerCase().includes(searchVal));
        }
        
        document.getElementById('variant-info').textContent = 
            `Showing ${filtered.length > 0 ? 1 : 0} to ${Math.min(filtered.length, perPage)} of ${filtered.length} entries`;
        
        const visible = filtered.slice(0, perPage);
        
        if (visible.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No data available in table</td></tr>';
            return;
        }
        
        tbody.innerHTML = visible.map(v => `
            <tr data-variant-id="${v.id}">
                <td><input type="checkbox" class="form-check-input variant-check" value="${v.id}"></td>
                <td><input type="text" class="form-control form-control-sm" value="${v.code ?? ''}" data-field="code" style="min-width:80px;"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm text-center" value="${v.code ?? ''}" data-field="code_cf" style="width:70px;" readonly tabindex="-1"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm text-center" value="${parseFloat(v.cost).toFixed(2)}" data-field="cost" style="width:80px;"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm text-center" value="${parseFloat(v.price).toFixed(2)}" data-field="price" style="width:80px;"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm text-center" value="${v.quantity}" data-field="quantity" style="width:70px;"></td>
                <td class="text-center"><input type="number" class="form-control form-control-sm text-center" value="${v.weight ?? ''}" data-field="weight" placeholder="" style="width:70px;"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-success btn-sm rounded-1 px-2 py-0 btn-save-variant-row">💾</button>
                    <button type="button" class="btn btn-danger btn-sm rounded-1 px-2 py-0 btn-del-variant-row">🗑</button>
                </td>
            </tr>
        `).join('');
        
        // Bind inline save/delete
        tbody.querySelectorAll('.btn-save-variant-row').forEach(btn => {
            btn.addEventListener('click', async function() {
                const row = this.closest('tr');
                const variantId = row.dataset.variantId;
                const payload = {
                    code: row.querySelector('[data-field="code"]').value,
                    cost: row.querySelector('[data-field="cost"]').value,
                    price: row.querySelector('[data-field="price"]').value,
                    quantity: row.querySelector('[data-field="quantity"]').value,
                    weight: row.querySelector('[data-field="weight"]').value || null,
                };
                try {
                    await apiFetch(`/products/${currentProductId}/variants/${variantId}`, 'PUT', payload);
                    showToast('บันทึกแล้ว');
                    // Update local state
                    const idx = allVariants.findIndex(v => v.id == variantId);
                    if (idx >= 0) Object.assign(allVariants[idx], payload);
                    updateStockSummary();
                } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
            });
        });
        tbody.querySelectorAll('.btn-del-variant-row').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('ลบรหัสนี้?')) return;
                const row = this.closest('tr');
                const variantId = row.dataset.variantId;
                try {
                    await apiFetch(`/products/${currentProductId}/variants/${variantId}`, 'DELETE');
                    allVariants = allVariants.filter(v => v.id != variantId);
                    renderVariantsTable(allVariants);
                    updateStockSummary();
                    showToast('ลบรหัสแล้ว');
                } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
            });
        });
        
        // Variant checkboxes
        tbody.querySelectorAll('.variant-check').forEach(cb => cb.addEventListener('change', syncVariantBulkButtons));
        document.getElementById('select-all-variants').addEventListener('change', function() {
            tbody.querySelectorAll('.variant-check').forEach(cb => cb.checked = this.checked);
            syncVariantBulkButtons();
        });
    }

    function syncVariantBulkButtons() {
        const checked = document.querySelectorAll('#variants-tbody .variant-check:checked');
        const hasChecked = checked.length > 0;
        document.getElementById('btn-bulk-stock').disabled = !hasChecked;
        document.getElementById('btn-bulk-price').disabled = !hasChecked;
        document.getElementById('btn-bulk-delete-variant').disabled = !hasChecked;
    }

    function updateStockSummary() {
        const total = allVariants.reduce((s, v) => s + (parseInt(v.quantity) || 0), 0);
        document.getElementById('edit-stock-summary').textContent = 
            `(หมวดนี้มีสินค้ารวมทุกรหัส คงเหลือ : ${total} ชิ้น)`;
        // Update main table
        const row = document.querySelector(`tr[data-product-id="${currentProductId}"] td:nth-child(4)`);
        if (row) row.textContent = total.toLocaleString();
    }

    // Variant search/perPage
    document.getElementById('variant-search')?.addEventListener('input', () => renderVariantsTable(allVariants));
    document.getElementById('variant-per-page')?.addEventListener('change', () => renderVariantsTable(allVariants));

    // ============================================================
    // ADD SINGLE VARIANT
    // ============================================================
    const addVariantModalEl = document.getElementById('addVariantModal');
    const addVariantModal = new bootstrap.Modal(addVariantModalEl);

    document.getElementById('btn-add-variant-single')?.addEventListener('click', () => {
        document.getElementById('new-variant-code').value = '';
        document.getElementById('new-variant-cost').value = '0';
        document.getElementById('new-variant-price').value = '0';
        document.getElementById('new-variant-qty').value = '0';
        document.getElementById('new-variant-weight').value = '';
        addVariantModal.show();
    });

    document.getElementById('btn-save-variant-single')?.addEventListener('click', async () => {
        const payload = {
            code: document.getElementById('new-variant-code').value.trim(),
            cost: document.getElementById('new-variant-cost').value,
            price: document.getElementById('new-variant-price').value,
            quantity: document.getElementById('new-variant-qty').value,
            weight: document.getElementById('new-variant-weight').value || null,
        };
        if (!payload.code) { showToast('กรุณากรอกรหัส CF', 'warning'); return; }
        try {
            const res = await apiFetch(`/products/${currentProductId}/variants`, 'POST', payload);
            allVariants.push(res.variant);
            renderVariantsTable(allVariants);
            updateStockSummary();
            addVariantModal.hide();
            showToast('เพิ่มรหัสแล้ว');
        } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
    });

    // ============================================================
    // BULK ADD VARIANTS
    // ============================================================
    const bulkModalEl = document.getElementById('bulkVariantModal');
    const bulkModal = new bootstrap.Modal(bulkModalEl);

    document.getElementById('btn-add-variant-bulk')?.addEventListener('click', () => {
        bulkPreviewData = [];
        document.getElementById('bulk-preview-tbody').innerHTML = 
            '<tr><td colspan="6" class="text-center text-muted py-2">กดปุ่มสร้างตัวอย่างก่อน</td></tr>';
        bulkModal.show();
    });

    document.getElementById('btn-bulk-preview')?.addEventListener('click', () => {
        const prefix = document.getElementById('bulk-prefix').value.trim();
        const start = parseInt(document.getElementById('bulk-start').value);
        const end = parseInt(document.getElementById('bulk-end').value);
        const price = document.getElementById('bulk-price').value;
        const qty = document.getElementById('bulk-qty').value;
        const weight = document.getElementById('bulk-weight').value;
        
        if (!prefix) { showToast('กรุณากรอกตัวอักษรนำหน้า', 'warning'); return; }
        if (start > end) { showToast('ค่าเริ่มต้นต้องน้อยกว่าหรือเท่ากับค่าสิ้นสุด', 'warning'); return; }
        
        bulkPreviewData = [];
        for (let i = start; i <= Math.min(end, start + 199); i++) {
            bulkPreviewData.push({ code: `${prefix}=${i}`, cost: 0, price, quantity: qty, weight: weight || null });
        }
        
        const tbody = document.getElementById('bulk-preview-tbody');
        tbody.innerHTML = bulkPreviewData.map((v, idx) => `
            <tr>
                <td><input type="text" class="form-control form-control-sm" value="${v.code}" onchange="bulkPreviewData[${idx}].code = this.value"></td>
                <td><input type="number" class="form-control form-control-sm" value="${v.cost}" onchange="bulkPreviewData[${idx}].cost = this.value"></td>
                <td><input type="number" class="form-control form-control-sm" value="${v.price}" onchange="bulkPreviewData[${idx}].price = this.value"></td>
                <td><input type="number" class="form-control form-control-sm" value="${v.quantity}" onchange="bulkPreviewData[${idx}].quantity = this.value"></td>
                <td><input type="number" class="form-control form-control-sm" value="${v.weight ?? ''}" onchange="bulkPreviewData[${idx}].weight = this.value || null"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkPreviewData.splice(${idx},1); document.getElementById('btn-bulk-preview').click()">🗑</button></td>
            </tr>
        `).join('');
    });
    window.bulkPreviewData = bulkPreviewData;

    document.getElementById('btn-bulk-save')?.addEventListener('click', async () => {
        if (bulkPreviewData.length === 0) { showToast('ไม่มีข้อมูลให้บันทึก', 'warning'); return; }
        try {
            const res = await apiFetch(`/products/${currentProductId}/variants/bulk`, 'POST', { variants: bulkPreviewData });
            res.variants.forEach(v => allVariants.push(v));
            renderVariantsTable(allVariants);
            updateStockSummary();
            bulkModal.hide();
            showToast(`เพิ่ม ${res.count} รหัสแล้ว`);
        } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
    });

    // ============================================================
    // BULK STOCK / PRICE ADJUST
    // ============================================================
    const adjustModalEl = document.getElementById('adjustModal');
    const adjustModal = new bootstrap.Modal(adjustModalEl);

    document.getElementById('btn-bulk-stock')?.addEventListener('click', () => {
        adjustType = 'stock';
        document.getElementById('adjustModalTitle').textContent = 'ปรับสต็อกที่เลือก';
        document.getElementById('adjust-mode-group').style.display = 'block';
        document.getElementById('adjust-value-label').textContent = 'จำนวน (ชิ้น)';
        document.getElementById('adjust-value').value = '0';
        adjustModal.show();
    });

    document.getElementById('btn-bulk-price')?.addEventListener('click', () => {
        adjustType = 'price';
        document.getElementById('adjustModalTitle').textContent = 'ปรับราคาที่เลือก';
        document.getElementById('adjust-mode-group').style.display = 'none';
        document.getElementById('adjust-value-label').textContent = 'ราคาใหม่ (บาท)';
        document.getElementById('adjust-value').value = '0';
        adjustModal.show();
    });

    document.getElementById('btn-adjust-save')?.addEventListener('click', async () => {
        const ids = Array.from(document.querySelectorAll('#variants-tbody .variant-check:checked')).map(cb => parseInt(cb.value));
        const val = document.getElementById('adjust-value').value;
        try {
            if (adjustType === 'stock') {
                const mode = document.getElementById('adjust-mode').value;
                await apiFetch(`/products/${currentProductId}/variants/bulk-stock`, 'POST', { ids, quantity: val, mode });
            } else {
                await apiFetch(`/products/${currentProductId}/variants/bulk-price`, 'POST', { ids, price: val });
            }
            const res = await apiFetch(`/products/${currentProductId}/variants`);
            allVariants = res.variants;
            renderVariantsTable(allVariants);
            updateStockSummary();
            adjustModal.hide();
            showToast('ปรับข้อมูลแล้ว');
        } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
    });

    document.getElementById('btn-bulk-delete-variant')?.addEventListener('click', async () => {
        if (!confirm('ลบรหัสที่เลือก?')) return;
        const ids = Array.from(document.querySelectorAll('#variants-tbody .variant-check:checked')).map(cb => parseInt(cb.value));
        try {
            await apiFetch(`/products/${currentProductId}/variants/bulk-delete`, 'POST', { ids });
            allVariants = allVariants.filter(v => !ids.includes(v.id));
            renderVariantsTable(allVariants);
            updateStockSummary();
            showToast('ลบรหัสที่เลือกแล้ว');
        } catch (e) { showToast('เกิดข้อผิดพลาด: ' + e.message, 'danger'); }
    });

})();
</script>
@endpush
