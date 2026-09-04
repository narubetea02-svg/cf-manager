<?php
namespace App\Http\Controllers;

use App\Models\BroadcastLog;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    public function index()
    {
        $orders = Order::whereNotIn('status', ['cancelled', 'delivered'])
            ->whereIn('shop_id', Auth::user()?->shops()->pluck('id') ?? [])
            ->with('shop')
            ->orderBy('created_at', 'desc')
            ->get();
        $shops = Shop::where('user_id', Auth::id())->get();
        $logs = BroadcastLog::where('user_id', Auth::id())->latest()->take(20)->get();

        return view('broadcasts.index', compact('orders', 'shops', 'logs'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'order_ids' => 'required|array|min:1',
        ]);

        $orderIds = $validated['order_ids'];
        if (in_array('all', $orderIds, true)) {
            $orderIds = Order::whereNotIn('status', ['cancelled', 'delivered'])
                ->whereIn('shop_id', Auth::user()?->shops()->pluck('id') ?? [])
                ->pluck('id')
                ->all();
        }

        $sendEnabled = (bool) config('facebook.send_enabled', false);
        $status = $sendEnabled ? 'sent' : 'dry_run';
        $detail = $sendEnabled
            ? 'ส่งผ่าน Messenger (เปิด MESSENGER_SEND_ENABLED แล้ว)'
            : 'MESSENGER_SEND_ENABLED=false — บันทึกประวัติ dry-run เท่านั้น';

        BroadcastLog::create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'order_ids' => array_map('intval', $orderIds),
            'recipient_count' => count($orderIds),
            'channel' => 'messenger',
            'status' => $status,
            'detail' => $detail,
        ]);

        if ($sendEnabled) {
            session()->flash('success', '✅ ส่งข้อความไป ' . count($orderIds) . ' รายการแล้ว');
        } else {
            session()->flash('info', 'ℹ️ บันทึกประวัติ dry-run ' . count($orderIds) . ' รายการแล้ว (ยังไม่ส่งจริง — MESSENGER_SEND_ENABLED=false)');
        }

        return back();
    }
}
