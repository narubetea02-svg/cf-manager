<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\Shop;
use App\Services\AutoMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingController extends Controller
{
    public function __construct(
        protected AutoMessageService $autoMessageService
    ) {
    }

    public function index()
    {
        $shopIds = Auth::user()?->shops()->pluck('id') ?? collect();

        $orders = Order::whereIn('shop_id', $shopIds)
            ->whereIn('status', ['paid', 'shipped'])
            ->with(['shop', 'product', 'shipment'])
            ->orderBy('created_at', 'desc')
            ->get();
        $shops = Shop::whereIn('id', $shopIds)->get();
        $carriers = ['kerry', 'flash', 'jnt', 'thailandpost', 'dhl', 'nim_express'];
        return view('packing.index', compact('orders', 'shops', 'carriers'));
    }

    public function updateTracking(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $validated = $request->validate([
            'carrier' => 'required|string',
            'tracking_number' => 'required|string',
        ]);

        Shipment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'carrier' => $validated['carrier'],
                'tracking_number' => $validated['tracking_number'],
                'status' => 'shipped',
                'shipped_at' => now(),
            ]
        );

        $order->update([
            'status' => 'shipped',
            'tracking_number' => $validated['tracking_number'],
        ]);

        $order->load('shop');
        $this->autoMessageService->queueForOrderEvent($order, 'shipping', [
            'tracking_no' => $validated['tracking_number'],
            'shipping_method' => $validated['carrier'],
        ]);
        $this->autoMessageService->queueForOrderEvent($order, 'tracking', [
            'tracking_no' => $validated['tracking_number'],
            'shipping_method' => $validated['carrier'],
        ]);

        return back()->with('success', 'เลขพัสดุ: ' . $validated['tracking_number']);
    }
}
