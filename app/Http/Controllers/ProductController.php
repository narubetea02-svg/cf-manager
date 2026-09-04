<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $search = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', 'all'));
        $stock = trim((string) $request->get('stock', 'all'));
        $perPage = (int) ($request->get('per_page', 20) ?: 20);
        if (! in_array($perPage, [10, 20, 25, 50, 100], true)) {
            $perPage = 20;
        }

        $query = Product::with(['shop', 'orders', 'variants'])
            ->whereIn('shop_id', $shopIds);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code_pattern', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%');
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($stock === 'out') {
            $query->where('stock', '<=', 0);
        } elseif ($stock === 'low') {
            $query->whereBetween('stock', [1, 5]);
        } elseif ($stock === 'ok') {
            $query->where('stock', '>', 5);
        }

        $products = $query->latest()->paginate($perPage)->withQueryString();

        return view('products.index', compact('products', 'search', 'status', 'stock', 'perPage'));
    }

    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a new product (supports JSON for modal AJAX or regular form POST)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer',
            'code_pattern' => 'nullable|string|max:255',
            'image' => 'nullable|string',
        ]);

        $shop = Auth::user()?->shops()->latest('id')->first();
        if (! $shop) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'ยังไม่มีร้านค้า'], 422);
            }
            return redirect('/products')->with('error', 'ยังไม่มีร้านค้าให้ผูกสินค้า');
        }

        $validated['shop_id'] = $shop->id;
        $validated['is_active'] = true;
        $validated['price'] = $validated['price'] ?? 0;
        $validated['stock'] = $validated['stock'] ?? 0;

        $product = Product::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product->load('variants'),
                'message' => 'เพิ่มสินค้าเรียบร้อย',
            ]);
        }

        return redirect('/products')->with('success', 'เพิ่มสินค้าเรียบร้อย');
    }

    public function show($id)
    {
        $product = $this->findOwnedProduct($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product = $this->findOwnedProduct($id);
        return view('products.edit', compact('product'));
    }

    /**
     * Update product (supports JSON for modal AJAX)
     */
    public function update(Request $request, $id)
    {
        $product = $this->findOwnedProduct($id);
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer',
            'code_pattern' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|string',
        ]);
        $validated['is_active'] = (bool) ($request->input('is_active', $product->is_active) ?? true);
        $validated['price'] = $validated['price'] ?? $product->price;
        $product->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product->fresh()->load('variants'),
                'message' => 'อัปเดตสินค้าเรียบร้อย',
            ]);
        }

        return redirect('/products')->with('success', 'อัปเดตสินค้าเรียบร้อย');
    }

    public function destroy($id)
    {
        $product = $this->findOwnedProduct($id);
        $product->variants()->delete();
        $product->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'ลบสินค้าแล้ว']);
        }

        return redirect('/products')->with('success', 'ลบสินค้าเรียบร้อย');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $products = Product::whereIn('shop_id', $shopIds)->whereIn('id', $data['ids'])->get();
        foreach ($products as $p) {
            $p->variants()->delete();
            $p->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'ลบสินค้าที่เลือกแล้ว']);
        }
        return back()->with('success', 'ลบสินค้าที่เลือกแล้ว');
    }

    /**
     * Upload product image
     */
    public function uploadImage(Request $request, $id)
    {
        $product = $this->findOwnedProduct($id);
        $request->validate(['image' => 'required|image|max:5120']);
        $path = $request->file('image')->store('products', 'public');
        $product->update(['image' => '/storage/' . $path]);
        return response()->json([
            'success' => true,
            'url' => asset('storage/' . $path),
        ]);
    }

    /**
     * Export stock as CSV (matching /printStockReport)
     */
    public function exportExcel(): StreamedResponse
    {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $products = Product::with(['shop', 'variants'])
            ->whereIn('shop_id', $shopIds)
            ->orderBy('name')
            ->get();

        $fileName = 'stock-report-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($products) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['id', 'ชื่อสินค้า', 'รหัส CF', 'ราคาขาย', 'สต็อก', 'สถานะ', 'วันที่เพิ่ม']);
            foreach ($products as $product) {
                if ($product->variants->count() > 0) {
                    foreach ($product->variants as $v) {
                        fputcsv($output, [
                            $product->id,
                            $product->name . ' [' . $v->code . ']',
                            $v->code,
                            $v->price,
                            $v->quantity,
                            (int) (bool) $product->is_active,
                            optional($product->created_at)?->toDateTimeString(),
                        ]);
                    }
                } else {
                    fputcsv($output, [
                        $product->id,
                        $product->name,
                        $product->code_pattern,
                        $product->price,
                        $product->stock,
                        (int) (bool) $product->is_active,
                        optional($product->created_at)?->toDateTimeString(),
                    ]);
                }
            }
            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function export(): StreamedResponse
    {
        return $this->exportExcel();
    }

    public function importIndex(string $mode = 'basic')
    {
        $modes = [
            'basic' => ['title' => 'นำเข้าจากไฟล์ Excel', 'description' => 'นำเข้าสินค้าจาก CSV (คอลัมน์: name, price, stock, code_pattern)'],
            'options' => ['title' => 'นำเข้าสินค้าพร้อมตัวเลือก', 'description' => 'นำเข้าสินค้า + variant จาก CSV (product_name, variant_code, price, quantity)'],
        ];
        $modeConfig = $modes[$mode] ?? $modes['basic'];
        return view('products.import', ['mode' => $mode, 'modeConfig' => $modeConfig]);
    }

    public function importStore(Request $request)
    {
        $mode = $request->query('mode', 'basic');
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $shop = Auth::user()?->shops()->latest('id')->first();
        if (! $shop) {
            return back()->with('error', 'ยังไม่มีร้านค้าให้ผูกสินค้า');
        }

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'อ่านไฟล์ไม่ได้');
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), fgetcsv($handle) ?: []);
        $imported = 0;

        if ($mode === 'options') {
            while (($row = fgetcsv($handle)) !== false) {
                $data = $this->mapCsvRow($header, $row);
                $productName = trim((string) ($data['product_name'] ?? $data['name'] ?? ''));
                if ($productName === '') {
                    continue;
                }
                $product = Product::firstOrCreate(
                    ['shop_id' => $shop->id, 'name' => $productName],
                    ['price' => 0, 'stock' => 0, 'is_active' => true]
                );
                $product->variants()->create([
                    'code' => $data['variant_code'] ?? $data['code'] ?? null,
                    'price' => (float) ($data['price'] ?? 0),
                    'quantity' => (int) ($data['quantity'] ?? $data['stock'] ?? 0),
                ]);
                $imported++;
            }
        } else {
            while (($row = fgetcsv($handle)) !== false) {
                $data = $this->mapCsvRow($header, $row);
                $name = trim((string) ($data['name'] ?? ''));
                if ($name === '') {
                    continue;
                }
                Product::create([
                    'shop_id' => $shop->id,
                    'name' => $name,
                    'price' => (float) ($data['price'] ?? 0),
                    'stock' => (int) ($data['stock'] ?? 0),
                    'code_pattern' => $data['code_pattern'] ?? $data['code'] ?? null,
                    'is_active' => true,
                ]);
                $imported++;
            }
        }

        fclose($handle);

        return redirect()->route('products.index')->with('success', "นำเข้าสำเร็จ {$imported} รายการ");
    }

    protected function mapCsvRow(array $header, array $row): array
    {
        $data = [];
        foreach ($header as $index => $key) {
            if ($key !== '') {
                $data[$key] = $row[$index] ?? null;
            }
        }
        return $data;
    }

    public function printIndex(Request $request)
    {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $products = Product::with(['shop', 'variants'])
            ->whereIn('shop_id', $shopIds)
            ->orderBy('name')
            ->get();
        return view('products.print', compact('products'));
    }

    public function optionShell($id)
    {
        $product = $this->findOwnedProduct($id);
        return view('products.options', compact('product'));
    }

    // ==================== VARIANTS AJAX ====================

    /**
     * GET /products/{id}/variants - List variants as JSON (DataTables format)
     */
    public function variantsIndex($id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $variants = $product->variants()->get();
        $totalQuantity = $variants->sum('quantity');

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'image' => $product->image,
                'total_stock' => $totalQuantity,
            ],
            'variants' => $variants->map(fn($v) => [
                'id' => $v->id,
                'code' => $v->code,
                'cost' => $v->cost,
                'price' => $v->price,
                'quantity' => $v->quantity,
                'weight' => $v->weight,
                'is_active' => $v->is_active,
            ]),
        ]);
    }

    /**
     * POST /products/{id}/variants - Add single variant
     */
    public function variantsStore(Request $request, $id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $validated = $request->validate([
            'code' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);
        $variant = $product->variants()->create($validated);
        return response()->json(['success' => true, 'variant' => $variant]);
    }

    /**
     * POST /products/{id}/variants/bulk - Add many variants at once (e.g., patterns like ค=19,บ=9)
     */
    public function variantsBulkStore(Request $request, $id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.code' => 'nullable|string|max:255',
            'variants.*.cost' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
        ]);
        $created = [];
        foreach ($request->input('variants') as $variantData) {
            $created[] = $product->variants()->create($variantData);
        }
        return response()->json(['success' => true, 'variants' => $created, 'count' => count($created)]);
    }

    /**
     * PUT /products/{id}/variants/{variantId} - Update a single variant
     */
    public function variantsUpdate(Request $request, $id, $variantId): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $variant = ProductVariant::where('product_id', $product->id)->findOrFail($variantId);
        $validated = $request->validate([
            'code' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $variant->update($validated);
        return response()->json(['success' => true, 'variant' => $variant->fresh()]);
    }

    /**
     * DELETE /products/{id}/variants/{variantId}
     */
    public function variantsDestroy($id, $variantId): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        ProductVariant::where('product_id', $product->id)->findOrFail($variantId)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * POST /products/{id}/variants/bulk-delete - Delete multiple variants
     */
    public function variantsBulkDestroy(Request $request, $id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        ProductVariant::where('product_id', $product->id)->whereIn('id', $request->input('ids'))->delete();
        return response()->json(['success' => true]);
    }

    /**
     * POST /products/{id}/variants/bulk-stock - Update stock for selected variants
     */
    public function variantsBulkStock(Request $request, $id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'quantity' => 'required|integer',
            'mode' => 'nullable|in:set,add,subtract',
        ]);
        $variants = ProductVariant::where('product_id', $product->id)->whereIn('id', $request->input('ids'))->get();
        $qty = (int) $request->input('quantity');
        $mode = $request->input('mode', 'set');
        foreach ($variants as $v) {
            if ($mode === 'add') {
                $v->increment('quantity', $qty);
            } elseif ($mode === 'subtract') {
                $v->decrement('quantity', $qty);
            } else {
                $v->update(['quantity' => $qty]);
            }
        }
        return response()->json(['success' => true]);
    }

    /**
     * POST /products/{id}/variants/bulk-price - Update price for selected variants
     */
    public function variantsBulkPrice(Request $request, $id): JsonResponse
    {
        $product = $this->findOwnedProduct($id);
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'price' => 'required|numeric|min:0',
        ]);
        ProductVariant::where('product_id', $product->id)
            ->whereIn('id', $request->input('ids'))
            ->update(['price' => $request->input('price')]);
        return response()->json(['success' => true]);
    }

    protected function findOwnedProduct($id): Product
    {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        return Product::with(['shop', 'orders', 'variants'])
            ->whereIn('shop_id', $shopIds)
            ->findOrFail($id);
    }
}
