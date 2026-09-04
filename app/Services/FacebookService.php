<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    public function hasPageToken(): bool { return !empty(config('facebook.page_token')); }
    public function sendPageMessageDetailed(string $pageToken, string $psid, string $text): array
    {
        try {
            $resp = Http::post('https://graph.facebook.com/v23.0/me/messages', [
                'recipient' => ['id' => $psid],
                'message' => ['text' => $text],
                'access_token' => $pageToken,
            ]);

            return [
                'ok' => $resp->successful(),
                'status' => $resp->status(),
                'response' => $resp->json() ?: [],
                'provider_message_id' => $resp->json('message_id'),
            ];
        } catch (\Exception $e) {
            Log::error('FB sendPageMessageDetailed: '.$e->getMessage());
            return [
                'ok' => false,
                'status' => null,
                'response' => [],
                'provider_message_id' => null,
            ];
        }
    }

    public function sendPageMessage(string $pageToken, string $psid, string $text): bool
    {
        return $this->sendPageMessageDetailed($pageToken, $psid, $text)['ok'];
    }

    public function getPageComments(string $pageId): array
    {
        if (!$this->hasPageToken()) return [];
        try {
            $token = config('facebook.page_token');
            $resp = Http::get("https://graph.facebook.com/v23.0/{$pageId}/feed", [
                'access_token' => $token, 'fields' => 'message,comments{message,from}',
            ]);
            return $resp->successful() ? $resp->json()['data'] ?? [] : [];
        } catch (\Exception $e) { Log::error('Facebook API: '.$e->getMessage()); return []; }
    }
    public function sendMessage(string $psid, string $text): bool
    {
        if (!$this->hasPageToken()) return false;
        return $this->sendPageMessage(config('facebook.page_token'), $psid, $text);
    }
}
