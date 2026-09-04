<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    public function enabled(): bool { return config('tiktok.enabled'); }
    public function hasApiKey(): bool { return !empty(config('tiktok.api_key')); }
    public function supportsLiveStatusCheck(): bool { return (bool) config('tiktok.live_status_check_enabled', false); }
    public function supportsUsernameVerification(): bool { return (bool) config('tiktok.username_verification_enabled', false); }

    public function getLiveComments(string $liveUrl): array
    {
        if (!$this->hasApiKey()) {
            return config('tiktok.allow_mock') ? $this->mockComments($liveUrl) : [['id' => 'err_1', 'username' => 'System', 'text' => 'ยังไม่ได้ตั้งค่า TikTok API/Token', 'code' => '']];
        }
        try {
            $resp = Http::withToken(config('tiktok.api_key'))
                ->get('https://open.tiktokapis.com/v2/live/comments', ['url' => $liveUrl]);
            return $resp->successful() ? $resp->json()['data'] ?? [] : [];
        } catch (\Exception $e) {
            Log::error('TikTok API error: '.$e->getMessage());
            return config('tiktok.allow_mock') ? $this->mockComments($liveUrl) : [['id' => 'err_1', 'username' => 'System', 'text' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ TikTok API', 'code' => '']];
        }
    }
    public function validateStream(string $url): array
    {
        preg_match('/@([\w.]+)/', $url, $m);
        $username = $m[1] ?? 'unknown';
        return ['valid' => true, 'username' => $username, 'platform' => 'tiktok'];
    }

    public function checkLiveStatus(string $username, ?string $liveUrl = null): array
    {
        $normalizedUsername = ltrim(trim($username), '@');

        if ($normalizedUsername === '') {
            return [
                'state' => 'missing_username',
                'message' => 'กรุณาตั้งค่า TikTok username ก่อน',
            ];
        }

        if (! $this->enabled() || ! $this->hasApiKey()) {
            return [
                'state' => 'missing_connector',
                'message' => 'ยังไม่สามารถตรวจสอบ TikTok Live ได้: ยังไม่มี TikTok connector/token',
            ];
        }

        if (! $this->supportsLiveStatusCheck()) {
            return [
                'state' => 'error',
                'message' => 'มี connector/token แล้ว แต่ระบบยังไม่มีตัวตรวจสอบสถานะ TikTok Live จริง จึงยังยืนยัน ready ไม่ได้',
            ];
        }

        return [
            'state' => 'error',
            'message' => 'TikTok Live status checker ยังไม่ได้เชื่อม API จริงในระบบนี้',
        ];
    }

    public function verifyUsername(string $username): array
    {
        $normalizedUsername = ltrim(trim($username), '@');

        if ($normalizedUsername === '') {
            return [
                'state' => 'unchecked',
                'message' => 'ยังไม่กรอก TikTok Username',
            ];
        }

        if (! $this->enabled() || ! $this->hasApiKey()) {
            return [
                'state' => 'not_configured',
                'message' => 'ยังไม่สามารถตรวจสอบ TikTok Username ได้: ยังไม่มี TikTok connector/token',
            ];
        }

        if (! $this->supportsUsernameVerification()) {
            return [
                'state' => 'error',
                'message' => 'มี connector/token แล้ว แต่ระบบยังไม่มีตัวตรวจสอบ TikTok Username จริง จึงยังยืนยัน verified ไม่ได้',
            ];
        }

        return [
            'state' => 'error',
            'message' => 'TikTok Username verifier ยังไม่ได้เชื่อม API จริงในระบบนี้',
        ];
    }

    private function mockComments(string $url): array
    {
        return [
            ['id' => 'mock_1', 'username' => 'ลูกค้า1', 'text' => 'a123', 'code' => 'a123'],
            ['id' => 'mock_2', 'username' => 'ลูกค้า2', 'text' => 'bc45', 'code' => 'bc45'],
        ];
    }
}
