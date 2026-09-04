@extends('layouts.admin')
@section('title', 'เพิ่มตัวเลือกสินค้า')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="page-title mb-1">เพิ่มตัวเลือกสินค้า</h4>
                <p class="text-muted mb-0">สินค้า: {{ $product->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('products.show', $product->id) }}" class="btn btn-light border">ดูข้อมูล</a>
                <a href="{{ route('products.index') }}" class="btn btn-light border">กลับไปหน้าสินค้า</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <div class="text-muted small">สต็อกรวมจากตัวเลือก</div>
                <div class="h4 mb-0" id="variant-total-stock">0</div>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVariantModal">+ เพิ่มตัวเลือก</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="select-all-variants"></th>
                        <th>รหัส</th>
                        <th>ราคา</th>
                        <th>ต้นทุน</th>
                        <th>สต็อก</th>
                        <th>น้ำหนัก</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="variants-table-body">
                    <tr><td colspan="8" class="text-center text-muted py-4">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-outline-danger btn-sm" id="bulk-delete-btn" disabled>ลบที่เลือก</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="bulk-stock-btn" disabled>ปรับสต็อก</button>
        </div>
    </div>
</div>

<div class="modal fade" id="addVariantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="add-variant-form">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มตัวเลือก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-12">
                    <label class="form-label">รหัส (code)</label>
                    <input type="text" name="code" class="form-control" placeholder="a123">
                </div>
                <div class="col-6">
                    <label class="form-label">ราคา</label>
                    <input type="number" name="price" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-6">
                    <label class="form-label">ต้นทุน</label>
                    <input type="number" name="cost" class="form-control" min="0" step="0.01">
                </div>
                <div class="col-6">
                    <label class="form-label">สต็อก</label>
                    <input type="number" name="quantity" class="form-control" min="0" value="0">
                </div>
                <div class="col-6">
                    <label class="form-label">น้ำหนัก (kg)</label>
                    <input type="number" name="weight" class="form-control" min="0" step="0.01">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productId = {{ $product->id }};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const tbody = document.getElementById('variants-table-body');
    const totalEl = document.getElementById('variant-total-stock');
    const selected = new Set();

    function fmt(n) { return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }); }

    async function loadVariants() {
        const res = await fetch(`/products/${productId}/variants`);
        const data = await res.json();
        totalEl.textContent = data.product?.total_stock ?? 0;
        if (!data.variants?.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีตัวเลือก</td></tr>';
            return;
        }
        tbody.innerHTML = data.variants.map(v => `
            <tr data-id="${v.id}">
                <td><input type="checkbox" class="variant-check" value="${v.id}"></td>
                <td><code>${v.code || '-'}</code></td>
                <td>${fmt(v.price)}</td>
                <td>${fmt(v.cost)}</td>
                <td>${v.quantity ?? 0}</td>
                <td>${fmt(v.weight)}</td>
                <td>${v.is_active ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger delete-variant" data-id="${v.id}">ลบ</button></td>
            </tr>
        `).join('');
        bindRowEvents();
    }

    function bindRowEvents() {
        document.querySelectorAll('.variant-check').forEach(el => {
            el.addEventListener('change', () => {
                if (el.checked) selected.add(el.value); else selected.delete(el.value);
                document.getElementById('bulk-delete-btn').disabled = selected.size === 0;
                document.getElementById('bulk-stock-btn').disabled = selected.size === 0;
            });
        });
        document.querySelectorAll('.delete-variant').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('ลบตัวเลือกนี้?')) return;
                await fetch(`/products/${productId}/variants/${btn.dataset.id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                });
                selected.delete(btn.dataset.id);
                loadVariants();
            });
        });
    }

    document.getElementById('add-variant-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = Object.fromEntries(fd.entries());
        await fetch(`/products/${productId}/variants`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify(payload),
        });
        e.target.reset();
        bootstrap.Modal.getInstance(document.getElementById('addVariantModal'))?.hide();
        loadVariants();
    });

    document.getElementById('bulk-delete-btn')?.addEventListener('click', async () => {
        if (!selected.size || !confirm('ลบตัวเลือกที่เลือก?')) return;
        await fetch(`/products/${productId}/variants/bulk-delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ ids: Array.from(selected).map(Number) }),
        });
        selected.clear();
        loadVariants();
    });

    document.getElementById('select-all-variants')?.addEventListener('change', (e) => {
        document.querySelectorAll('.variant-check').forEach(el => {
            el.checked = e.target.checked;
            if (e.target.checked) selected.add(el.value); else selected.delete(el.value);
        });
        document.getElementById('bulk-delete-btn').disabled = selected.size === 0;
        document.getElementById('bulk-stock-btn').disabled = selected.size === 0;
    });

    loadVariants();
});
</script>
@endpush
@endsection
