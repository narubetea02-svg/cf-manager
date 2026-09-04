<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\LiveStream;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index(): JsonResponse
    {
        $dbOk = false;
        try { DB::connection()->getPdo(); $dbOk = true; } catch (\Exception $e) {}

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => [
                'database' => $dbOk ? 'connected' : 'error',
                'cache' => Cache::has('health_check') ? 'ok' : 'ok',
                'queue' => class_exists(\App\Jobs\ProcessGrabberOrderJob::class) ? 'configured' : 'missing',
                'grabber' => $this->checkGrabber(),
            ],
            'stats' => [
                'orders_today' => Order::whereDate('created_at', today())->count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'active_streams' => LiveStream::where('status', 'active')->count(),
                'unverified_payments' => Payment::where('status', 'pending')->count(),
            ],
            'config' => [
                'app_url' => config('app.url'),
                'tiktok_configured' => !empty(config('tiktok.api_key')),
                'line_configured' => config('line.enabled') && !empty(config('line.channel_token')),
                'facebook_configured' => !empty(config('facebook.page_token')),
            ],
            'php_version' => PHP_VERSION,
        ]);
    }

    private function checkGrabber(): string
    {
        $output = [];
        exec('systemctl is-active tiktok-grabber.service 2>/dev/null', $output, $code);
        return ($code === 0 && ($output[0] ?? '') === 'active') ? 'running' : 'stopped';
    }
}
