<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Support\CustomerMetrics;
use App\Services\TikTokService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveStreamController extends Controller
{
    public function index(Request $request, TikTokService $tikTok)
    {
        $shops = auth()->user()->shops;
        $streams = LiveStream::whereIn('shop_id', $shops->pluck('id'))
            ->with(['shop.messengerSetting', 'portalSession'])
            ->orderBy('created_at', 'desc')
            ->get();
        $prefillStream = null;
        $copyFromId = (int) $request->integer('copy_from');
        if ($copyFromId > 0) {
            $prefillStream = LiveStream::whereIn('shop_id', $shops->pluck('id'))
                ->with(['shop.messengerSetting', 'portalSession'])
                ->find($copyFromId);
        }

        $realConnectedByStream = CustomerMetrics::realConnectedMessengerUsersByLiveStream($shops->pluck('id'));
        $streams->transform(function (LiveStream $stream) use ($realConnectedByStream) {
            $stream->real_connected_users = (int) ($realConnectedByStream->get($stream->id) ?? 0);
            $stream->connected_attempts = (int) ($stream->portalSession?->connected_count ?? 0);
            return $stream;
        });

        $currentStream = $streams->firstWhere('status', 'active');
        $currentShop = $currentStream?->shop ?? $shops->first();
        $currentTikTokUsername = $currentShop?->tiktok_username;
        $currentTikTokVerification = (array) (($currentShop?->settings ?? [])['tiktok'] ?? []);
        $latestStream = $streams->first();

        $summary = [
            'all' => $streams->count(),
            'active' => $streams->where('status', 'active')->count(),
            'tiktok' => $streams->where('platform', 'tiktok')->count(),
            'facebook' => $streams->where('platform', 'facebook')->count(),
            'connected_real_users' => $streams->sum(fn ($stream) => $stream->real_connected_users ?? 0),
            'connected_records' => $streams->sum(fn ($stream) => $stream->connected_attempts ?? 0),
        ];
        $tiktokConfigured = (bool) (config('tiktok.enabled') && ! empty(config('tiktok.api_key')));
        $liveCheck = $this->resolveLiveCheck($currentTikTokUsername, $currentStream, $tikTok, $currentTikTokVerification);

        return view('live.index', compact('streams', 'shops', 'summary', 'tiktokConfigured', 'currentStream', 'prefillStream', 'currentShop', 'currentTikTokUsername', 'latestStream', 'liveCheck'));
    }

    public function connectCurrent(Request $request, TikTokService $tikTok)
    {
        $shopIds = auth()->user()->shops()->pluck('id');
        $stream = LiveStream::whereIn('shop_id', $shopIds)
            ->where('status', 'active')
            ->with(['shop.messengerSetting', 'portalSession'])
            ->latest('started_at')
            ->first();

        if (! $stream) {
            return back()->with('error', 'ยังไม่พบ LIVE ที่กำลังออนไลน์');
        }

        $tiktokUsername = $stream->shop?->tiktok_username;
        if (! $tiktokUsername) {
            return back()->with('error', 'ยังไม่ได้ตั้งค่า TikTok username สำหรับร้านนี้');
        }

        $verification = (array) (($stream->shop?->settings ?? [])['tiktok'] ?? []);
        if (($verification['username_status'] ?? 'unchecked') !== 'verified') {
            return back()->with('error', 'ยังไม่สามารถเชื่อมต่อ LIVE ได้: TikTok Username ยังไม่ผ่านการตรวจสอบจริง');
        }

        if (! $tikTok->enabled() || ! $tikTok->hasApiKey()) {
            return back()->with('error', 'ยังไม่สามารถเชื่อมต่อ LIVE ได้: ยังไม่มี connector/token');
        }

        if (! $stream->portalSession) {
            PortalSession::create([
                'shop_id' => $stream->shop_id,
                'live_stream_id' => $stream->id,
                'sid' => Str::lower(Str::random(10)),
                'is_active' => true,
                'expires_at' => now()->addHours(12),
            ]);
        } else {
            $stream->portalSession->update([
                'is_active' => true,
                'expires_at' => now()->addHours(12),
            ]);
        }

        return back()->with('success', 'เปิด Portal สำหรับ LIVE ปัจจุบันแล้ว โดยใช้ TikTok username จากหน้าตั้งค่าร้านค้า');
    }

    public function checkCurrent(Request $request, TikTokService $tikTok)
    {
        $shops = auth()->user()->shops;
        $streams = LiveStream::whereIn('shop_id', $shops->pluck('id'))
            ->with('shop')
            ->latest('started_at')
            ->get();

        $currentStream = $streams->firstWhere('status', 'active');
        $currentShop = $currentStream?->shop ?? $shops->first();
        $currentTikTokUsername = $currentShop?->tiktok_username;
        $currentTikTokVerification = (array) (($currentShop?->settings ?? [])['tiktok'] ?? []);
        $liveCheck = $this->resolveLiveCheck($currentTikTokUsername, $currentStream, $tikTok, $currentTikTokVerification);

        return back()->with(
            $liveCheck['state'] === 'ready' ? 'success' : 'error',
            $liveCheck['message']
        );
    }

    public function copyLatest(Request $request)
    {
        $shopIds = auth()->user()->shops()->pluck('id');
        $latestStream = LiveStream::whereIn('shop_id', $shopIds)
            ->latest('created_at')
            ->first();

        if (! $latestStream) {
            return redirect()->route('live.index')->with('error', 'ยังไม่มี LIVE ล่าสุดให้คัดลอก');
        }

        return redirect()->route('live.index', ['copy_from' => $latestStream->id])->with('success', 'โหลดค่าจาก LIVE ล่าสุดมาไว้ในฟอร์มแล้ว');
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'live_url' => 'required|url',
            'platform' => 'required|in:tiktok,facebook',
        ]);

        $shop = Shop::findOrFail($validated['shop_id']);
        if ($shop->user_id !== auth()->id()) {
            return back()->with('error', 'ไม่ใช่ร้านค้าของคุณ');
        }

        // Stop any existing active stream for this shop
        LiveStream::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->update(['status' => 'ended', 'ended_at' => now()]);

        PortalSession::where('shop_id', $shop->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => $validated['platform'],
            'live_url' => $validated['live_url'],
            'status' => 'active',
            'started_at' => now(),
        ]);

        PortalSession::create([
            'shop_id' => $shop->id,
            'live_stream_id' => $stream->id,
            'sid' => Str::lower(Str::random(10)),
            'is_active' => true,
            'expires_at' => now()->addHours(12),
        ]);

        return redirect()->route('live.index')->with('success', 'เปิดไลฟ์สำหรับดูดคอมเมนต์แล้ว');
    }

    public function printIndex()
    {
        $shops = auth()->user()->shops;
        $streams = LiveStream::whereIn('shop_id', $shops->pluck('id'))
            ->with(['shop', 'portalSession'])
            ->latest('created_at')
            ->get();

        return view('live.print', compact('streams'));
    }

    public function show($id)
    {
        $stream = LiveStream::with(['shop.messengerSetting', 'portalSession'])
            ->findOrFail($id);

        if ($stream->shop?->user_id !== auth()->id()) {
            abort(403);
        }

        $realConnectedByStream = CustomerMetrics::realConnectedMessengerUsersByLiveStream(collect([$stream->shop_id]));
        $stream->real_connected_users = (int) ($realConnectedByStream->get($stream->id) ?? 0);
        $stream->connected_attempts = (int) ($stream->portalSession?->connected_count ?? 0);

        return view('live.show', compact('stream'));
    }

    public function stop($id)
    {
        $stream = LiveStream::findOrFail($id);
        $shop = $stream->shop;
        
        if ($shop->user_id !== auth()->id()) {
            return back()->with('error', 'ไม่ใช่สตรีมของคุณ');
        }

        $stream->update(['status' => 'ended', 'ended_at' => now()]);
        PortalSession::where('live_stream_id', $stream->id)->update(['is_active' => false]);

        return redirect()->route('live.index')->with('success', 'หยุดดูดคอมเมนต์แล้ว');
    }

    public function destroy($id)
    {
        $stream = LiveStream::findOrFail($id);
        $shop = $stream->shop;
        
        if ($shop->user_id !== auth()->id()) {
            return back()->with('error', 'ไม่ใช่สตรีมของคุณ');
        }

        $stream->delete();
        PortalSession::where('live_stream_id', $stream->id)->update(['is_active' => false]);
        return redirect()->route('live.index')->with('success', 'ลบสตรีมแล้ว');
    }

    protected function resolveLiveCheck(?string $currentTikTokUsername, ?LiveStream $currentStream, TikTokService $tikTok, array $currentTikTokVerification = []): array
    {
        if (! $currentTikTokUsername) {
            return [
                'state' => 'missing_username',
                'title' => 'ยังไม่ได้ตั้งค่า TikTok username',
                'message' => 'ไปตั้งค่าร้านค้าก่อน แล้วระบบจะใช้ค่าจาก Settings โดยตรง',
                'badge_class' => 'bg-secondary-subtle text-secondary',
            ];
        }

        if (($currentTikTokVerification['username_status'] ?? 'unchecked') !== 'verified') {
            return [
                'state' => 'username_not_verified',
                'title' => 'TikTok Username ยังไม่ verified',
                'message' => $tikTok->hasApiKey()
                    ? 'TikTok Username นี้ยังไม่ผ่านการตรวจสอบจริง จึงยังไม่สามารถยืนยัน LIVE ได้'
                    : 'ยังไม่สามารถตรวจสอบ TikTok Username ได้: ยังไม่มี TikTok connector/token',
                'badge_class' => $tikTok->hasApiKey() ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning',
            ];
        }

        if (! $tikTok->enabled() || ! $tikTok->hasApiKey()) {
            return [
                'state' => 'missing_connector',
                'title' => 'ยังตรวจสอบไม่ได้',
                'message' => 'ยังไม่สามารถตรวจสอบ TikTok Live ได้: ยังไม่มี TikTok connector/token',
                'badge_class' => 'bg-warning-subtle text-warning',
            ];
        }

        if (! $currentStream) {
            return [
                'state' => 'not_live',
                'title' => 'ยังไม่พบ LIVE ที่กำลังออนไลน์',
                'message' => 'มี TikTok username แล้ว แต่ยังไม่มี LIVE active ในระบบตอนนี้',
                'badge_class' => 'bg-secondary-subtle text-secondary',
            ];
        }

        $serviceStatus = $tikTok->checkLiveStatus($currentTikTokUsername, $currentStream->live_url);
        $state = $serviceStatus['state'] ?? 'error';

        return match ($state) {
            'ready' => [
                'state' => 'ready',
                'title' => 'ระบบพร้อมใช้งานแล้ว',
                'message' => $serviceStatus['message'] ?? 'ตรวจพบ LIVE จริงและพร้อมเปิดใช้งาน',
                'badge_class' => 'bg-success-subtle text-success',
            ],
            'not_live' => [
                'state' => 'not_live',
                'title' => 'ยังไม่พบ LIVE จริง',
                'message' => $serviceStatus['message'] ?? 'ยังไม่พบสถานะไลฟ์จาก connector',
                'badge_class' => 'bg-secondary-subtle text-secondary',
            ],
            'missing_connector' => [
                'state' => 'missing_connector',
                'title' => 'ยังตรวจสอบไม่ได้',
                'message' => $serviceStatus['message'] ?? 'ยังไม่มี connector/token',
                'badge_class' => 'bg-warning-subtle text-warning',
            ],
            default => [
                'state' => 'error',
                'title' => 'ยังยืนยันสถานะจริงไม่ได้',
                'message' => $serviceStatus['message'] ?? 'เกิดข้อผิดพลาดระหว่างตรวจสอบ TikTok Live',
                'badge_class' => 'bg-danger-subtle text-danger',
            ],
        };
    }
}
