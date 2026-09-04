<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CreditsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $shopIds = $user->shops()->pluck('id');

        $orderCount = Order::whereIn('shop_id', $shopIds)->count();
        $liveCount = LiveStream::whereIn('shop_id', $shopIds)->count();
        $freeAllowance = (int) config('app.free_credit_allowance', 1000);
        $usedCredits = $orderCount + ($liveCount * 2);
        $balance = max(0, $freeAllowance - $usedCredits);

        $history = collect();

        Order::whereIn('shop_id', $shopIds)
            ->latest()
            ->take(15)
            ->get()
            ->each(function (Order $order) use ($history) {
                $history->push([
                    'at' => $order->created_at,
                    'label' => 'สร้างออเดอร์ #' . $order->id,
                    'channel' => 'Order',
                    'amount' => -1,
                ]);
            });

        LiveStream::whereIn('shop_id', $shopIds)
            ->latest()
            ->take(10)
            ->get()
            ->each(function (LiveStream $stream) use ($history) {
                $history->push([
                    'at' => $stream->started_at ?? $stream->created_at,
                    'label' => 'เปิด LIVE #' . $stream->id,
                    'channel' => 'Live',
                    'amount' => -2,
                ]);
            });

        $history = $history->sortByDesc('at')->take(20)->values();

        return view('credits.index', compact('balance', 'freeAllowance', 'usedCredits', 'history'));
    }
}
