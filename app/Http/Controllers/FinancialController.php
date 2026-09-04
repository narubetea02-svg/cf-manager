<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class FinancialController extends Controller {
    public function index() {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();
        $totalRevenue = Order::whereIn('shop_id', $shopIds)->whereIn('status', ['paid','shipped','delivered'])->sum('total_price');
        $pendingRevenue = Order::whereIn('shop_id', $shopIds)->where('status','pending')->sum('total_price');
        $totalOrders = Order::whereIn('shop_id', $shopIds)->count();
        $verifiedPayments = Payment::whereHas('order', function ($query) use ($shopIds) {
            $query->whereIn('shop_id', $shopIds);
        })->where('status','verified')->sum('amount');
        $recentPayments = Payment::with('order')
            ->whereHas('order', function ($query) use ($shopIds) {
                $query->whereIn('shop_id', $shopIds);
            })
            ->latest()
            ->take(10)
            ->get();
        return view('financial.index', compact('totalRevenue','pendingRevenue','totalOrders','verifiedPayments','recentPayments'));
    }
}
