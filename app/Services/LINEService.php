<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LINEService
{
    public function enabled(): bool { return config('line.enabled') && !empty(config('line.channel_token')); }
    public function pushMessage(string $to, string $text): bool
    {
        if (!$this->enabled()) return false;
        try {
            $resp = Http::withToken(config('line.channel_token'))
                ->post('https://api.line.me/v2/bot/message/push', [
                    'to' => $to, 'messages' => [['type' => 'text', 'text' => $text]],
                ]);
            return $resp->successful();
        } catch (\Exception $e) { Log::error('LINE push: '.$e->getMessage()); return false; }
    }
    public function multicast(array $tos, string $text): bool
    {
        if (!$this->enabled() || empty($tos)) return false;
        foreach (array_chunk($tos, 5) as $chunk) {
            try {
                Http::withToken(config('line.channel_token'))
                    ->post('https://api.line.me/v2/bot/message/multicast', [
                        'to' => $chunk, 'messages' => [['type' => 'text', 'text' => $text]],
                    ]);
            } catch (\Exception $e) { Log::error('LINE multicast: '.$e->getMessage()); }
        }
        return true;
    }
}