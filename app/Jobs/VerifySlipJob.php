<?php
namespace App\Jobs;

use App\Models\Payment;
use App\Services\SlipOcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class VerifySlipJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    public function __construct(public Payment $payment) {}
    public function handle(SlipOcrService $ocr): void
    {
        try {
            if (!$this->payment->slip_image) return;
            $result = $ocr->verify(storage_path('app/public/'.$this->payment->slip_image));
            if ($result['verified'] && isset($result['amount'])) {
                $this->payment->update(['status' => 'verified', 'verified_at' => now()]);
                $this->payment->order->update(['status' => 'paid']);
                Log::info("Slip verified for order #{$this->payment->order_id}");
            } else {
                $this->payment->update(['notes' => 'OCR: amount not found']);
            }
        } catch (\Exception $e) { Log::error("Slip job #{$this->payment->id}: ".$e->getMessage()); }
    }
}