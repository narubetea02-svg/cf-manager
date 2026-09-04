<?php
namespace App\Jobs;

use App\Models\Order;
use App\Models\Product;
use App\Models\LiveStream;
use App\Services\TikTokService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGrabberOrderJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public array $data, public LiveStream $stream) {}
    public function handle(TikTokService $tikTok): void
    {
        try {
            $product = Product::where('shop_id', $this->stream->shop_id)
                ->where(function($q) { $q->where('code_pattern', $this->data['code'])->orWhereNull('code_pattern'); })
                ->first();
            Order::create([
                'shop_id' => $this->stream->shop_id,
                'product_id' => $product?->id,
                'customer_username' => $this->data['username'] ?? 'unknown',
                'code' => $this->data['code'],
                'quantity' => 1,
                'total_price' => $product?->price ?? 0,
                'status' => 'pending',
            ]);
            Log::info("Grabber: order created for code {$this->data['code']}");
        } catch (\Exception $e) { Log::error("Grabber job failed: ".$e->getMessage()); }
    }
}