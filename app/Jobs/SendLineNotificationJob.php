<?php
namespace App\Jobs;

use App\Services\LINEService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendLineNotificationJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    public function __construct(public string $to, public string $text) {}
    public function handle(LINEService $line): void
    {
        try {
            $ok = $line->pushMessage($this->to, $this->text);
            if (!$ok) Log::warning("LINE send failed to {$this->to}");
        } catch (\Exception $e) { Log::error("LINE job: ".$e->getMessage()); }
    }
}