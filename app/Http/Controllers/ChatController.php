<?php
namespace App\Http\Controllers;
use App\Models\MessengerMessage;
use App\Models\Shop;
use Illuminate\Http\Request;
class ChatController extends Controller {
    public function index(Request $request) {
        $shops = Shop::with('messengerSetting')
            ->where('user_id', auth()->id())
            ->get();

        $selectedShopId = $request->integer('shop_id');
        if (! $selectedShopId && $shops->isNotEmpty()) {
            $selectedShopId = $shops->first()->id;
        }

        $messages = collect();
        if ($selectedShopId) {
            $messages = MessengerMessage::where('shop_id', $selectedShopId)
                ->latest('sent_at')
                ->take(50)
                ->get()
                ->sortBy('sent_at')
                ->values();
        }

        return view('chat.index', compact('shops', 'selectedShopId', 'messages'));
    }
}
