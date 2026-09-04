<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $search = trim((string) $request->get('q', ''));
        $statusFilter = trim((string) $request->get('status', ''));
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $query = Order::with(['shop', 'product'])->whereIn('shop_id', $shopIds);
        $statusMap = OrderStatus::filterMap();

        if (array_key_exists($type, $statusMap) && ! empty($statusMap[$type])) {
            $query->whereIn('status', $statusMap[$type]);
        } elseif ($request->filled('type') && ! array_key_exists($type, $statusMap)) {
            $query->where('status', $type);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('code', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('customer_username', 'like', '%' . $search . '%')
                    ->orWhere('customer_phone', 'like', '%' . $search . '%')
                    ->orWhere('tracking_number', 'like', '%' . $search . '%');
            });
        }

        if ($statusFilter !== '') {
            $query->where('status', $statusFilter);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();
        $summary = [];
        foreach (['all', 'wait_payment', 'pending_review', 'hold', 'to_ship', 'packing', 'cod', 'completed', 'cancelled'] as $key) {
            $countQuery = Order::query()->whereIn('shop_id', $shopIds);
            if ($key !== 'all' && ! empty($statusMap[$key] ?? [])) {
                $countQuery->whereIn('status', $statusMap[$key]);
            }
            $summary[$key] = $countQuery->count();
        }

        $stats = [
            'total_orders' => Order::whereIn('shop_id', $shopIds)->count(),
            'revenue' => Order::whereIn('shop_id', $shopIds)->whereNotIn('status', ['cancelled'])->sum('total_price'),
            'avg_order' => Order::whereIn('shop_id', $shopIds)->whereNotIn('status', ['cancelled'])->avg('total_price') ?: 0,
            'latest_update' => Order::whereIn('shop_id', $shopIds)->max('updated_at'),
        ];

        $statusHighlights = Order::select('status', DB::raw('count(*) as total'))
            ->whereIn('shop_id', $shopIds)
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('orders.index', [
            'orders' => $orders,
            'type' => $type,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'summary' => $summary,
            'stats' => $stats,
            'statusHighlights' => $statusHighlights,
            'statusLabels' => OrderStatus::labels(),
            'statusColors' => OrderStatus::badgeColors(),
        ]);
    }
    public function show(Order $order)
    {
        return view('orders.show', compact('order'));
    }
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate(['status' => 'required|string']);
        $order->update($validated);
        return back()->with('success', 'อัปเดตสถานะแล้ว');
    }
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect('/orders')->with('success', 'ลบออเดอร์แล้ว');
    }
}
