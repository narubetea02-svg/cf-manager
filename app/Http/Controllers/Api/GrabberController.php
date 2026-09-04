<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;

class GrabberController extends Controller
{
    /**
     * Return active streams for the grabber service
     */
    public function getStreams(Request $request)
    {
        $streams = LiveStream::where('status', 'active')
            ->with('shop:id,name,tiktok_username')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'shop_id' => $s->shop_id,
                'live_url' => $s->live_url,
                'keyword_filter' => $s->keyword_filter ?? '',
                'price' => $s->price ?? 0,
                'status' => $s->status,
            ]);

        return response()->json(['streams' => $streams]);
    }

    /**
     * Create order from TikTok code detected by grabber
     */
    public function createOrderFromCode(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'code' => 'required|string|max:20',
            'raw_comment' => 'nullable|string',
            'stream_id' => 'nullable|exists:live_streams,id',
            'username' => 'nullable|string|max:100',
        ]);

        // Check for duplicate (same code + shop in last 5 min)
        $recent = Order::where('shop_id', $validated['shop_id'])
            ->where('code', $validated['code'])
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($recent) {
            return response()->json(['status' => 'duplicate', 'message' => 'รหัสซ้ำภายใน 5 นาที']);
        }

        $normalizedUsername = strtolower(trim(str_replace('@', '', $validated['username'] ?? '')));
        
        $customerUsername = $normalizedUsername ?: 'tiktok_user';
        $customerName = $validated['username'] ?? null;
        $mappedIdentity = null;

        if ($validated['stream_id'] ?? false) {
            $mapping = \App\Models\CustomerMapping::where('live_stream_id', $validated['stream_id'])
                ->where('tiktok_username', $normalizedUsername)
                ->where('status', 'connected')
                ->first();

            if ($mapping) {
                // If we have a connected customer mapping, we map the order to them explicitly
                // In the future we can attach facebook_psid to the order here
                $customerName = $mapping->tiktok_username;
                $mapping->update(['last_seen_at' => now()]);
            }
        }

        $order = Order::create([
            'shop_id' => $validated['shop_id'],
            'code' => strtolower($validated['code']),
            'customer_username' => $customerUsername,
            'customer_name' => $customerName,
            'quantity' => 1,
            'total_price' => 0,
            'status' => 'pending',
            'notes' => 'จาก TikTok: ' . ($validated['raw_comment'] ?? ''),
        ]);

        return response()->json([
            'status' => 'created',
            'order' => $order,
        ]);
    }

    /**
     * Report grabber health status
     */
    public function ping(Request $request)
    {
        $grabberId = $request->header('X-Grabber-Id', 'unknown');
        \Cache::put('grabber:'.$grabberId.':last_ping', now(), now()->addMinutes(5));

        return response()->json(['status' => 'ok', 'time' => now()]);
    }
}
