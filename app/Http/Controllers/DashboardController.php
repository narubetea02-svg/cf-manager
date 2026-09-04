<?php
namespace App\Http\Controllers;
use App\Models\LiveStream;
use App\Models\CustomerMapping;
use Illuminate\Support\Facades\DB;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Product;
use App\Support\CustomerMetrics;
use App\Support\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shops = Shop::where('user_id', $user->id)->get();
        $shopIds = $shops->pluck('id');
        $ordersQuery = Order::whereIn('shop_id', $shopIds);
        $paidLikeStatuses = ['paid', 'shipped', 'packing', 'delivered', 'hold'];

        // The original dashboard changes its figures by period. Keep every card
        // on the same real order window so the controls never only change the URL.
        $range = $request->query('range', 'today');
        $now = now();
        [$periodStart, $periodEnd, $previousStart, $previousEnd, $rangeLabel] = match ($range) {
            '7d' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(13)->startOfDay(),
                $now->copy()->subDays(7)->endOfDay(),
                '7 วันที่ผ่านมา',
            ],
            'month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfDay(),
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->startOfMonth()->subSecond(),
                'เดือนนี้',
            ],
            '30d' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(59)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
                '30 วันที่ผ่านมา',
            ],
            default => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'วันนี้',
            ],
        };
        $range = in_array($range, ['today', '7d', 'month', '30d'], true) ? $range : 'today';
        $ordersInRange = (clone $ordersQuery)->whereBetween('created_at', [$periodStart, $periodEnd]);
        $previousOrders = (clone $ordersQuery)->whereBetween('created_at', [$previousStart, $previousEnd]);
        $paidOrdersInRange = (clone $ordersInRange)->whereIn('status', $paidLikeStatuses);

        $totalOrders = (clone $ordersInRange)->count();
        $todayOrders = (clone $ordersInRange)->whereDate('created_at', today())->count();
        $totalProducts = Product::whereIn('shop_id', $shopIds)->count();
        $pendingOrders = (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['wait_payment'])->count();
        $totalRevenue = (clone $paidOrdersInRange)->sum('total_price');
        $revenueNoShipping = $totalRevenue;
        $shippingIncome = 0;
        $soldQty = (clone $paidOrdersInRange)->sum('quantity');
        $soldHomes = (clone $paidOrdersInRange)->distinct('customer_username')->count('customer_username');
        $allStatusesRevenue = (clone $ordersInRange)->sum('total_price');
        $previousRevenue = (clone $previousOrders)->whereIn('status', $paidLikeStatuses)->sum('total_price');
        $revenueDelta = $previousRevenue > 0
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : null;
        $activeStreams = LiveStream::whereIn('shop_id', $shopIds)->where('status', 'active')->count();
        $customersTotal = (clone $ordersInRange)->distinct('customer_username')->count('customer_username');
        $customersWithPhone = (clone $ordersInRange)->whereNotNull('customer_phone')->where('customer_phone', '!=', '')->distinct('customer_username')->count('customer_username');
        $broadcastCustomers = 0;
        $connectedTiktokCustomers = CustomerMetrics::realConnectedMessengerUsers($shopIds);
        $bannedCustomers = 0;

        $ordersByStatus = [
            'wait_payment' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['wait_payment'])->count(),
            'pending_review' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['pending_review'])->count(),
            'hold' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['hold'])->count(),
            'to_ship' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['to_ship'])->count(),
            'packed' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['packed'])->count(),
            'completed' => (clone $ordersInRange)->whereIn('status', OrderStatus::groups()['completed'])->count(),
        ];

        $orderSummary = [
            ['label' => 'รอชำระเงิน', 'count' => $ordersByStatus['wait_payment'], 'url' => url('/orders?type=wait_payment')],
            ['label' => 'รอตรวจสอบ', 'count' => $ordersByStatus['pending_review'], 'url' => url('/orders?type=pending_review')],
            ['label' => 'ฝากของ', 'count' => $ordersByStatus['hold'], 'url' => url('/orders?type=hold')],
            ['label' => 'ต้องจัดส่ง', 'count' => $ordersByStatus['to_ship'], 'url' => url('/orders?type=to_ship')],
            ['label' => 'พิมพ์/แพ็ค', 'count' => $ordersByStatus['packed'], 'url' => url('/orders?type=packing')],
            ['label' => 'สำเร็จแล้ว', 'count' => $ordersByStatus['completed'], 'url' => url('/orders?type=completed')],
        ];

        $recentOrders = (clone $ordersInRange)->with(['shop', 'product'])->latest()->take(10)->get();
        $topCustomers = (clone $ordersInRange)
            ->select(
                DB::raw("COALESCE(NULLIF(customer_name, ''), customer_username) as display_name"),
                'customer_username',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total_price) as total_spent')
            )
            ->groupBy('display_name', 'customer_username')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
        $topProducts = Order::query()
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(orders.quantity) as qty'),
                DB::raw('SUM(orders.total_price) as revenue')
            )
            ->whereIn('orders.shop_id', $shopIds)
            ->whereBetween('orders.created_at', [$periodStart, $periodEnd])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();
        $latestOrders = (clone $ordersInRange)->with(['shop', 'product'])->latest()->take(8)->get();
        $dailySalesRows = (clone $paidOrdersInRange)
            ->selectRaw('DATE(created_at) as day, SUM(total_price) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get()
            ->keyBy(fn ($row) => substr((string) $row->day, 0, 10));
        $dailySales = collect();
        for ($day = $periodStart->copy()->startOfDay(); $day->lte($periodEnd); $day->addDay()) {
            $key = $day->toDateString();
            $dailySales->push([
                'label' => $day->format('d/m'),
                'total' => (float) ($dailySalesRows->get($key)?->total ?? 0),
            ]);
        }
        $platformBreakdown = [
            ['label' => 'Facebook', 'count' => $customersTotal],
            ['label' => 'LINE', 'count' => 0],
            ['label' => 'CF Manager App', 'count' => 0],
        ];
        $announcement = [
            'title' => 'CF Manager Dashboard · เวอร์ชันล่าสุด',
            'subtitle' => 'โครงหน้าและลำดับการ์ดถูกจัดให้พร้อมใช้งานสำหรับทีมแอดมินมากขึ้น',
            'link' => url('/dashboard'),
            'cta' => 'ดูหน้าปัจจุบัน',
        ];

        return view('dashboard', compact(
            'shops',
            'totalOrders',
            'pendingOrders',
            'todayOrders',
            'totalProducts',
            'totalRevenue',
            'revenueNoShipping',
            'shippingIncome',
            'range',
            'rangeLabel',
            'revenueDelta',
            'dailySales',
            'soldQty',
            'soldHomes',
            'allStatusesRevenue',
            'activeStreams',
            'customersTotal',
            'customersWithPhone',
            'broadcastCustomers',
            'connectedTiktokCustomers',
            'bannedCustomers',
            'ordersByStatus',
            'orderSummary',
            'recentOrders',
            'topCustomers',
            'topProducts',
            'latestOrders',
            'platformBreakdown',
            'announcement'
        ));
    }
}
