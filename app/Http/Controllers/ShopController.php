<?php
namespace App\Http\Controllers;
use App\Models\Shop;
use App\Models\Product;
use App\Models\LiveStream;
use App\Models\MessengerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\TikTokService;
use Illuminate\Support\Arr;

class ShopController extends Controller
{
    public function settings()
    {
        $shop = Shop::with(['messengerSetting', 'liveStreams' => function ($query) {
            $query->latest('started_at');
        }])->where('user_id', Auth::id())->latest('id')->first();

        if (! $shop) {
            return redirect('/shops/create')->with('error', 'ยังไม่มีร้านค้า กรุณาสร้างร้านค้าก่อน');
        }

        $products = Product::where('shop_id', $shop->id)->get();
        $liveStreams = LiveStream::where('shop_id', $shop->id)->get();
        $tiktokVerification = $this->tiktokVerificationStatus($shop);

        $shippingStatus = $this->shippingConnectionStatus(
            $this->carrierKeyFromInput($shop->settings['shipping']['default_method'] ?? null)
        );
        $paymentStatus = $this->paymentConnectionStatus();

        return view('shops.edit', compact('shop', 'products', 'liveStreams', 'shippingStatus', 'paymentStatus', 'tiktokVerification'));
    }

    public function index()
    {
        $shops = Shop::with(['messengerSetting', 'liveStreams' => function ($query) {
            $query->latest('started_at');
        }])->where('user_id', Auth::id())->get();
        return view('shops.index', compact('shops'));
    }

    public function accounts()
    {
        $user = Auth::user();
        $shops = Shop::with('messengerSetting')->where('user_id', $user->id)->get();
        return view('accounts.index', compact('user', 'shops'));
    }

    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->save();

        return back()->with('success', 'บันทึกข้อมูลบัญชีแล้ว');
    }

    public function access()
    {
        $user = Auth::user();
        $shops = Shop::with('messengerSetting')->where('user_id', $user->id)->get();
        return view('access.index', compact('user', 'shops'));
    }

    public function templates()
    {
        $templates = [
            ['title' => 'เปิดไลฟ์ขายของ', 'body' => 'สวัสดีค่ะ ฝากกดติดตามเพจและพิมพ์รหัสสินค้าใต้คอมเมนต์ได้เลยนะคะ'],
            ['title' => 'แจ้งเลขพัสดุ', 'body' => 'ออเดอร์จัดส่งแล้วค่ะ สามารถติดตามเลขพัสดุได้จากหน้าออเดอร์ของร้าน'],
            ['title' => 'ยืนยันการเชื่อมต่อ', 'body' => 'เชื่อมต่อสำเร็จ กลับไปที่ไลฟ์แล้วพิมพ์รหัสสินค้าได้เลยค่ะ'],
        ];

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('shops.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'tiktok_username' => 'required|max:255',
        ]);
        $validated['tiktok_username'] = $this->normalizeTikTokUsername($validated['tiktok_username'] ?? null);
        $validated['user_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['name']) . '-' . uniqid();
        Shop::create($validated);
        return redirect('/shops')->with('success', 'สร้างร้านค้าเรียบร้อย');
    }

    public function edit($id)
    {
        $shop = Shop::with('messengerSetting')->where('user_id', Auth::id())->findOrFail($id);
        $products = Product::where('shop_id', $shop->id)->get();
        $liveStreams = LiveStream::where('shop_id', $shop->id)->get();
        $tiktokVerification = $this->tiktokVerificationStatus($shop);
        $shippingStatus = $this->shippingConnectionStatus(
            $this->carrierKeyFromInput($shop->settings['shipping']['default_method'] ?? null)
        );
        $paymentStatus = $this->paymentConnectionStatus();

        return view('shops.edit', compact('shop', 'products', 'liveStreams', 'shippingStatus', 'paymentStatus', 'tiktokVerification'));
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::where('user_id', Auth::id())->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|max:255',
            'country' => 'nullable|max:255',
            'address' => 'nullable|string',
            'sub_district' => 'nullable|max:255',
            'postal_code' => 'nullable|max:255',
            'tiktok_username' => 'required|max:255',
            'logo' => 'nullable|max:255',
            'instagram' => 'nullable|max:255',
            'social_primary_link' => 'nullable|max:255',
            'receipt_tax_name' => 'nullable|max:255',
            'receipt_tax_id' => 'nullable|max:255',
            'receipt_tax_address' => 'nullable|string',
            'receipt_phone' => 'nullable|max:255',
            
            // Shipping fields
            'shipping_enabled' => 'nullable|boolean',
            'shipping_default_method' => 'nullable|string',
            'shipping_default_cost' => 'nullable|numeric',
            'shipping_note' => 'nullable|string',
            'shipping_carriers' => 'nullable|array',
            'shipping_carriers.*' => 'nullable|string',
            
            // Payment fields
            'payment_cod_enabled' => 'nullable|boolean',
            'payment_transfer_enabled' => 'nullable|boolean',
            'payment_bank_name' => 'nullable|string',
            'payment_account_name' => 'nullable|string',
            'payment_account_number' => 'nullable|string|max:255',
            'payment_note' => 'nullable|string',
            'payment_instruction' => 'nullable|string',
            'payment_contact_channel' => 'nullable|string',
            
            // Auto Message fields
            'auto_message_enabled' => 'nullable|boolean',
            'auto_msg_welcome' => 'nullable|string',
            'auto_msg_payment' => 'nullable|string',
            'auto_msg_shipping' => 'nullable|string',
            'auto_msg_tracking' => 'nullable|string',
            'auto_msg_after_live' => 'nullable|string',
        ]);
        
        $data = $validated;
        
        // Normalize TikTok username
        $data['tiktok_username'] = $this->normalizeTikTokUsername($data['tiktok_username'] ?? null);
        if (! $data['tiktok_username']) {
            return back()
                ->withInput()
                ->with('active_tab', $request->input('active_tab', 'shops-tab'))
                ->withErrors(['tiktok_username' => 'กรุณากรอก TikTok username ก่อนบันทึก']);
        }
        
        // Normalize phone number (remove spaces and dashes)
        if (!empty($data['phone'])) {
            $data['phone'] = preg_replace('/[^0-9+]/', '', $data['phone']);
        }

        // Build settings JSON
        $settings = $shop->settings ?? [];

        $socialPrimaryLink = trim((string) $request->input('social_primary_link', ''));
        $receiptPhone = trim((string) $request->input('receipt_phone', ''));
        if ($receiptPhone !== '') {
            $receiptPhone = preg_replace('/[^0-9+]/', '', $receiptPhone);
        }

        $settings['shipping'] = [
            'enabled' => (bool) ($request->input('shipping_enabled') ?? false),
            'default_method' => $request->input('shipping_default_method'),
            'default_cost' => $request->filled('shipping_default_cost')
                ? number_format((float) $request->input('shipping_default_cost'), 2, '.', '')
                : null,
            'note' => $request->input('shipping_note'),
            'carriers' => array_values(array_filter((array) $request->input('shipping_carriers', []))),
        ];

        $settings['payment'] = [
            'cod_enabled' => (bool) ($request->input('payment_cod_enabled') ?? false),
            'transfer_enabled' => (bool) ($request->input('payment_transfer_enabled') ?? false),
            'bank_name' => $request->input('payment_bank_name'),
            'account_name' => $request->input('payment_account_name'),
            'account_number' => $request->input('payment_account_number'),
            'note' => $request->input('payment_note'),
            'instruction' => $request->input('payment_instruction'),
            'contact_channel' => $request->input('payment_contact_channel'),
        ];

        $settings['auto_message'] = [
            'enabled' => (bool) ($request->input('auto_message_enabled') ?? false),
            'welcome' => $request->input('auto_msg_welcome'),
            'payment' => $request->input('auto_msg_payment'),
            'shipping' => $request->input('auto_msg_shipping'),
            'tracking' => $request->input('auto_msg_tracking'),
            'after_live' => $request->input('auto_msg_after_live'),
        ];

        $settings['social'] = [
            'primary_link' => $socialPrimaryLink !== '' ? $socialPrimaryLink : null,
        ];

        $settings['receipt'] = [
            'tax_name' => $request->input('receipt_tax_name'),
            'tax_id' => $request->input('receipt_tax_id'),
            'tax_address' => $request->input('receipt_tax_address'),
            'phone' => $receiptPhone !== '' ? $receiptPhone : null,
        ];

        $currentTikTokSettings = (array) ($settings['tiktok'] ?? []);
        $previousUsername = $this->normalizeTikTokUsername($shop->tiktok_username);
        $nextUsername = $data['tiktok_username'];
        if ($previousUsername !== $nextUsername) {
            $currentTikTokSettings['username_status'] = 'unchecked';
            $currentTikTokSettings['verified_at'] = null;
            $currentTikTokSettings['last_checked_at'] = null;
            $currentTikTokSettings['checked_username'] = $nextUsername;
            $currentTikTokSettings['status_message'] = 'ยังไม่ได้ตรวจสอบ TikTok Username';
        } else {
            $currentTikTokSettings['checked_username'] = $currentTikTokSettings['checked_username'] ?? $nextUsername;
        }
        $settings['tiktok'] = $currentTikTokSettings;
        
        $data['settings'] = $settings;
        
        // Clean up settings keys from main array
        $keysToRemove = [
            'shipping_enabled', 'shipping_default_method', 'shipping_default_cost', 'shipping_note',
            'shipping_carriers',
            'payment_cod_enabled', 'payment_transfer_enabled', 'payment_bank_name', 'payment_account_name', 'payment_account_number', 'payment_note',
            'payment_instruction', 'payment_contact_channel',
            'auto_message_enabled', 'auto_msg_welcome', 'auto_msg_payment', 'auto_msg_shipping', 'auto_msg_tracking', 'auto_msg_after_live',
            'social_primary_link',
            'receipt_tax_name', 'receipt_tax_id', 'receipt_tax_address', 'receipt_phone',
        ];
        foreach ($keysToRemove as $key) {
            unset($data[$key]);
        }

        $shop->update($data);

        $activeTab = $request->input('active_tab', 'shops-tab');

        return redirect()
            ->to(route('settings.index') . '#' . $activeTab)
            ->with('success', 'บันทึกการตั้งค่าร้านค้าเรียบร้อย')
            ->with('active_tab', $activeTab);
    }

    public function checkTikTokLive(Request $request, $id, TikTokService $tikTok)
    {
        $shop = Shop::with('messengerSetting')->where('user_id', Auth::id())->findOrFail($id);
        $username = $this->normalizeTikTokUsername($request->input('tiktok_username', $shop->tiktok_username));

        if (! $username) {
            return back()->with('error', 'กรุณากรอก TikTok username ก่อน');
        }

        if (! $tikTok->enabled() || ! $tikTok->hasApiKey()) {
            return back()->with('error', 'ยังไม่สามารถตรวจสอบ TikTok Live ได้: ยังไม่มี connector/token');
        }

        $activeStream = LiveStream::where('shop_id', $shop->id)
            ->where('status', 'active')
            ->latest('started_at')
            ->first();

        if (! $activeStream) {
            return back()->with('error', 'ยังไม่พบไลฟ์ที่กำลังออนไลน์');
        }

        $liveStatus = $tikTok->checkLiveStatus($username, $activeStream->live_url);

        return redirect('/live')->with(
            $liveStatus['state'] === 'ready' ? 'success' : 'error',
            $liveStatus['message'] ?? 'ยังไม่สามารถตรวจสอบสถานะ TikTok Live ได้'
        );
    }

    public function verifyTikTokUsername(Request $request, $id, TikTokService $tikTok)
    {
        $shop = Shop::where('user_id', Auth::id())->findOrFail($id);
        $username = $this->normalizeTikTokUsername($request->input('tiktok_username', $shop->tiktok_username));

        $status = $tikTok->verifyUsername((string) $username);
        $settings = $shop->settings ?? [];
        $settings['tiktok'] = array_merge((array) ($settings['tiktok'] ?? []), [
            'checked_username' => $username,
            'username_status' => $status['state'] ?? 'error',
            'status_message' => $status['message'] ?? 'ยังไม่สามารถตรวจสอบ TikTok Username ได้',
            'last_checked_at' => now()->toIso8601String(),
            'verified_at' => ($status['state'] ?? null) === 'verified' ? now()->toIso8601String() : null,
        ]);

        $shop->update(['settings' => $settings]);

        return redirect()
            ->to(route('settings.index') . '#shops-tab')
            ->with(($status['state'] ?? null) === 'verified' ? 'success' : 'error', $settings['tiktok']['status_message'])
            ->with('active_tab', 'shops-tab');
    }

    public function updateMessenger(Request $request, $id)
    {
        $shop = Shop::with('messengerSetting')->where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'fb_page_id' => 'nullable|string|max:255',
            'fb_page_token' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $setting = $shop->messengerSetting ?: new MessengerSetting(['shop_id' => $shop->id]);
        $setting->fb_page_id = $validated['fb_page_id'] ?? null;
        if (! empty($validated['fb_page_token'])) {
            $setting->fb_page_token = $validated['fb_page_token'];
        }
        $setting->is_active = (bool) ($validated['is_active'] ?? false);
        $setting->save();

        return redirect()
            ->to(route('settings.index') . '#messenger-tab')
            ->with('success', 'บันทึกการเชื่อมต่อเพจ Facebook แล้ว')
            ->with('active_tab', 'messenger-tab');
    }

    public function checkShippingConnection(Request $request, $id)
    {
        $shop = Shop::where('user_id', Auth::id())->findOrFail($id);
        $carrier = $this->carrierKeyFromInput(
            $request->input('shipping_default_method', $shop->settings['shipping']['default_method'] ?? null)
        );
        $status = $this->shippingConnectionStatus($carrier);

        return redirect()
            ->to(route('settings.index') . '#shipping-tab')
            ->with($status['state'] === 'configured' ? 'success' : 'error', $status['message'])
            ->with('active_tab', 'shipping-tab');
    }

    public function checkPaymentConnection(Request $request, $id)
    {
        Shop::where('user_id', Auth::id())->findOrFail($id);
        $status = $this->paymentConnectionStatus();

        return redirect()
            ->to(route('settings.index') . '#payment-tab')
            ->with($status['state'] === 'configured' ? 'success' : 'error', $status['message'])
            ->with('active_tab', 'payment-tab');
    }

    public function destroy($id)
    {
        $shop = Shop::where('user_id', Auth::id())->findOrFail($id);
        $shop->delete();
        return redirect('/shops')->with('success', 'ลบร้านค้าเรียบร้อย');
    }

    protected function normalizeTikTokUsername(?string $username): ?string
    {
        $normalized = Str::of((string) $username)->trim()->replace('@', '')->lower()->trim();

        return $normalized->isNotEmpty() ? $normalized->toString() : null;
    }

    protected function carrierKeyFromInput(?string $value): string
    {
        return match (Str::lower((string) $value)) {
            'ems', 'thai post', 'thai_post', 'thailandpost' => 'thai_post',
            'flash' => 'flash',
            'j&t', 'jnt', 'j&t express' => 'jnt',
            'kerry', 'kerry express' => 'kerry',
            default => 'thai_post',
        };
    }

    protected function shippingConnectionStatus(string $carrier): array
    {
        $carrierConfig = (array) config("shipping.{$carrier}", []);
        $apiKey = trim((string) Arr::get($carrierConfig, 'api_key', ''));

        $labels = [
            'thai_post' => 'ไปรษณีย์ไทย',
            'flash' => 'Flash Express',
            'jnt' => 'J&T Express',
            'kerry' => 'Kerry Express',
        ];

        if ($apiKey === '') {
            return [
                'state' => 'missing_token',
                'label' => 'ยังไม่ได้ตั้งค่า API/Token ขนส่ง',
                'provider' => $labels[$carrier] ?? $carrier,
                'message' => 'ยังไม่ได้ตั้งค่า API/Token ขนส่งสำหรับ ' . ($labels[$carrier] ?? $carrier),
            ];
        }

        return [
            'state' => 'not_implemented',
            'label' => 'พบ API แล้ว แต่ยังไม่มี read-only test endpoint',
            'provider' => $labels[$carrier] ?? $carrier,
            'message' => 'พบ API key ของ ' . ($labels[$carrier] ?? $carrier) . ' แล้ว แต่ระบบยังไม่มี read-only test connection ที่ปลอดภัย จึงยังไม่แสดงว่าเชื่อมสำเร็จ',
        ];
    }

    protected function paymentConnectionStatus(): array
    {
        $gatewayName = trim((string) config('payment.gateway_name', 'Payment Gateway'));
        $gatewayToken = trim((string) config('payment.gateway_token', ''));

        if ($gatewayToken === '') {
            return [
                'state' => 'missing_token',
                'label' => 'ยังไม่ได้ตั้งค่า Payment Gateway/Token',
                'provider' => $gatewayName,
                'message' => 'ยังไม่ได้ตั้งค่า Payment Gateway/Token จึงยังไม่สามารถตรวจสอบการเชื่อมต่อการชำระเงินได้',
            ];
        }

        return [
            'state' => 'not_implemented',
            'label' => 'พบ Gateway token แล้ว แต่ยังไม่มี safe verification',
            'provider' => $gatewayName,
            'message' => 'พบการตั้งค่า ' . $gatewayName . ' แล้ว แต่ระบบยังไม่มี safe verification flow จึงยังไม่แสดงว่าเชื่อมสำเร็จ',
        ];
    }

    protected function tiktokVerificationStatus(Shop $shop): array
    {
        $tiktokSettings = (array) ($shop->settings['tiktok'] ?? []);
        $status = $tiktokSettings['username_status'] ?? 'unchecked';
        $checkedUsername = $this->normalizeTikTokUsername($tiktokSettings['checked_username'] ?? $shop->tiktok_username);
        $currentUsername = $this->normalizeTikTokUsername($shop->tiktok_username);

        if ($checkedUsername !== $currentUsername) {
            $status = 'unchecked';
        }

        return match ($status) {
            'verified' => [
                'state' => 'verified',
                'label' => 'ตรวจสอบแล้ว',
                'message' => $tiktokSettings['status_message'] ?? 'TikTok Username ผ่านการตรวจสอบแล้ว',
                'badge_class' => 'bg-success-subtle text-success',
            ],
            'not_found' => [
                'state' => 'not_found',
                'label' => 'ไม่พบ Username',
                'message' => $tiktokSettings['status_message'] ?? 'ไม่พบ TikTok Username นี้ในระบบต้นทาง',
                'badge_class' => 'bg-danger-subtle text-danger',
            ],
            'not_configured' => [
                'state' => 'not_configured',
                'label' => 'ยังตรวจสอบไม่ได้',
                'message' => $tiktokSettings['status_message'] ?? 'ยังไม่สามารถตรวจสอบ TikTok Username ได้: ยังไม่มี TikTok connector/token',
                'badge_class' => 'bg-warning-subtle text-warning',
            ],
            'error' => [
                'state' => 'error',
                'label' => 'ยังยืนยันไม่ได้',
                'message' => $tiktokSettings['status_message'] ?? 'เกิดข้อผิดพลาดระหว่างตรวจสอบ TikTok Username',
                'badge_class' => 'bg-danger-subtle text-danger',
            ],
            default => [
                'state' => 'unchecked',
                'label' => 'ยังไม่ได้ตรวจสอบ',
                'message' => $currentUsername ? 'ยังไม่ได้ตรวจสอบ TikTok Username นี้' : 'ยังไม่กรอก TikTok Username',
                'badge_class' => 'bg-secondary-subtle text-secondary',
            ],
        };
    }
}
