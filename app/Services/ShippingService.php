<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    public function createTracking(string $carrier, array $data): array
    {
        $method = 'create'.ucfirst($carrier);
        if (!empty(config("shipping.{$carrier}.api_key"))) {
            return method_exists($this, $method) ? $this->$method($data) : (config('shipping.allow_mock') ? $this->mockTracking($carrier, $data) : ['status' => 'not_configured', 'message' => 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง', 'carrier' => $carrier]);
        }
        return config('shipping.allow_mock') ? $this->mockTracking($carrier, $data) : ['status' => 'not_configured', 'message' => 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง', 'carrier' => $carrier];
    }
    public function getStatus(string $carrier, string $tracking): array
    {
        $key = config("shipping.{$carrier}.api_key");
        if (empty($key)) return config('shipping.allow_mock') ? $this->mockStatus($carrier, $tracking) : ['status' => 'not_configured', 'message' => 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง', 'carrier' => $carrier];
        try {
            $url = config("shipping.{$carrier}.api_url").'/track';
            $resp = Http::withToken($key)->get($url, ['tracking' => $tracking]);
            return $resp->successful() ? $resp->json() : (config('shipping.allow_mock') ? $this->mockStatus($carrier, $tracking) : ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ API ขนส่ง', 'carrier' => $carrier]);
        } catch (\Exception $e) { 
            return config('shipping.allow_mock') ? $this->mockStatus($carrier, $tracking) : ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ API ขนส่ง', 'carrier' => $carrier]; 
        }
    }
    public function getCarriers(): array { return ['kerry', 'flash', 'jnt', 'thai_post']; }

    private function createKerry(array $d): array { return ['tracking' => 'KV'.rand(1000000000,9999999999), 'carrier' => 'kerry']; }
    private function createFlash(array $d): array { return ['tracking' => 'FL'.rand(10000000000,99999999999), 'carrier' => 'flash']; }
    private function createJnt(array $d): array { return ['tracking' => 'JT'.rand(10000000000,99999999999), 'carrier' => 'jnt']; }
    private function createThaiPost(array $d): array { return ['tracking' => 'TH'.rand(1000000000000,9999999999999), 'carrier' => 'thai_post']; }
    private function mockStatus(string $carrier, string $tn): array { return ['tracking' => $tn, 'carrier' => $carrier, 'status' => 'ในระบบ', 'updated' => now()->toIso8601String()]; }
    private function mockTracking(string $carrier, array $d): array { return $this->{'create'.ucfirst($carrier)}($d); }
}
