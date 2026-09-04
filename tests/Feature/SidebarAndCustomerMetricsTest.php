<?php

namespace Tests\Feature;

use App\Models\CustomerMapping;
use App\Models\Order;
use App\Models\Product;
use App\Models\LiveStream;
use App\Models\PortalSession;
use App\Models\Shop;
use App\Models\User;
use App\Support\CustomerMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarAndCustomerMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_renders_all_primary_menu_labels_with_icons(): void
    {
        [$user] = $this->createUserAndShop();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('แดชบอร์ด');
        $response->assertSee('สถิติ');
        $response->assertSee('ไลฟ์');
        $response->assertSee('โพสต์');
        $response->assertSee('ดูดแชท');
        $response->assertSee('ดูแลแชท');
        $response->assertSee('บรอดแคสต์');
        $response->assertSee('แพ็คของ');
        $response->assertSee('ข้อมูลลูกค้า');
        $response->assertSee('เครดิต');
        $response->assertSee('การเงิน');
        $response->assertSee('ช่วยเหลือ');
        $response->assertSee('sidebar-icon', false);
        $response->assertSee('<svg', false);
        $response->assertSee('sidebar-label', false);
    }

    public function test_dashboard_and_login_render_inline_svg_icons_for_content_and_header(): void
    {
        [$user] = $this->createUserAndShop();

        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertOk();
        $dashboard->assertSee('ui-icon', false);
        $dashboard->assertSee('<svg', false);
        $dashboard->assertSee('data-theme', false);
        $dashboard->assertSee('cfmanager_theme', false);
        $dashboard->assertDontSee('_analytics-dashboard.css', false);
        $dashboard->assertDontSee('apexcharts.min.css', false);
        $dashboard->assertDontSee('ApexCharts', false);
        $dashboard->assertDontSee('fa-step-backward', false);
        $dashboard->assertDontSee('mdi-chevron-down', false);
        $dashboard->assertDontSee('bx bx-grid-alt', false);
        $dashboard->assertDontSee('bx bx-wallet', false);

        $login = $this->get('/login');
        $login->assertOk();
        $login->assertSee('ui-icon', false);
        $login->assertSee('<svg', false);
        $login->assertDontSee('bi bi-facebook', false);
    }

    public function test_layout_defines_centralized_theme_contrast_tokens_for_light_and_dark(): void
    {
        [$user] = $this->createUserAndShop();

        $html = $this->actingAs($user)->get('/settings')->getContent();

        $this->assertStringContainsString('--cf-heading', $html);
        $this->assertStringContainsString('--cf-input-text', $html);
        $this->assertStringContainsString('--cf-input-placeholder', $html);
        $this->assertStringContainsString('--cf-input-disabled-bg', $html);
        $this->assertStringContainsString('--cf-dropdown-text', $html);
        $this->assertStringContainsString('--cf-table-head-text', $html);
        $this->assertStringContainsString('--cf-success-soft', $html);
        $this->assertStringContainsString('html[data-theme="dark"]', $html);
        $this->assertStringContainsString('.form-label', $html);
        $this->assertStringContainsString('.table > :not(caption) > * > *', $html);
        $this->assertStringContainsString('.dropdown-menu .dropdown-item', $html);
        $this->assertStringContainsString('.nav-tabs .nav-link.active', $html);
        $this->assertStringContainsString('.page-link', $html);
    }

    public function test_quick_shortcuts_render_all_expected_links_with_svg_icons(): void
    {
        [$user] = $this->createUserAndShop();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('ทางลัดด่วน');
        $response->assertSee('เริ่มไลฟ์');
        $response->assertSee('เพิ่มสินค้า');
        $response->assertSee('คำสั่งซื้อ');
        $response->assertSee('พิมพ์ใบปะหน้า');
        $response->assertSee('เติมเครดิต');
        $response->assertSee('รายงานยอดขาย');
        $response->assertSee('เชื่อมช่องทาง');
        $response->assertSee('ตั้งค่าร้าน');
        $response->assertSee('ตั้งค่าขนส่ง');
        $response->assertSee('ตั้งค่า COD');
        $response->assertSee('ฝากของ');
        $response->assertSee('<svg', false);
        $response->assertSee('/credits');
    }

    public function test_dashboard_period_controls_filter_real_orders_and_render_daily_sales(): void
    {
        [$user, $shop] = $this->createUserAndShop();

        Order::create([
            'shop_id' => $shop->id,
            'customer_username' => 'today-customer',
            'code' => 'TODAY-100',
            'quantity' => 1,
            'total_price' => 100,
            'status' => 'paid',
            'created_at' => now()->startOfDay()->addHour(),
            'updated_at' => now()->startOfDay()->addHour(),
        ]);
        Order::create([
            'shop_id' => $shop->id,
            'customer_username' => 'week-customer',
            'code' => 'WEEK-25',
            'quantity' => 1,
            'total_price' => 25,
            'status' => 'paid',
            'created_at' => now()->subDays(2)->startOfDay()->addHour(),
            'updated_at' => now()->subDays(2)->startOfDay()->addHour(),
        ]);

        $today = $this->actingAs($user)->get('/dashboard?range=today');
        $today->assertOk();
        $today->assertSee('ช่วงข้อมูล: วันนี้');
        $today->assertSee('100<span class="u">฿</span>', false);
        $today->assertSee('ยอดขายรายวัน');
        $today->assertSee('นับเฉพาะสถานะที่ขายแล้ว');

        $week = $this->actingAs($user)->get('/dashboard?range=7d');
        $week->assertOk();
        $week->assertSee('ช่วงข้อมูล: 7 วันที่ผ่านมา');
        $week->assertSee('125<span class="u">฿</span>', false);
        $week->assertSee('href="http://localhost/dashboard?range=7d"', false);
    }

    public function test_only_original_style_parent_dropdowns_use_javascript_void_and_has_arrow(): void
    {
        [$user] = $this->createUserAndShop();

        $html = $this->actingAs($user)->get('/dashboard')->getContent();

        $this->assertSame(3, substr_count($html, 'data-sidebar-parent='));
        $this->assertStringContainsString('data-sidebar-parent="products"', $html);
        $this->assertStringContainsString('data-sidebar-parent="orders"', $html);
        $this->assertStringContainsString('data-sidebar-parent="reports"', $html);
        $this->assertStringContainsString('href="javascript: void(0);"', $html);
        $this->assertStringNotContainsString('>ดูแลแชท</span></a></li><ul', $html);
    }

    public function test_child_routes_keep_only_matching_parent_open_and_leaf_links_are_real_urls(): void
    {
        [$user] = $this->createUserAndShop();

        $readinessHtml = $this->actingAs($user)->get('/customers/messenger/readiness')->getContent();
        $this->assertStringContainsString('Pilot Send Control', $readinessHtml);
        $this->assertStringContainsString('/chatOrders', $readinessHtml);
        $this->assertStringNotContainsString('data-sidebar-parent="chat"', $readinessHtml);
        $this->assertStringContainsString('/customers/messenger/readiness', $readinessHtml);
        $this->assertSame(3, substr_count($readinessHtml, 'href="javascript: void(0);"'));

        $ordersHtml = $this->actingAs($user)->get('/packing')->getContent();
        $this->assertStringContainsString('data-sidebar-parent="orders"', $ordersHtml);
        $this->assertStringContainsString('aria-expanded="true"', $ordersHtml);
        $this->assertStringContainsString('>แพ็กของ</span>', $ordersHtml);
        $this->assertStringContainsString('/packing', $ordersHtml);
    }

    public function test_navigation_routes_render_or_redirect_without_404(): void
    {
        [$user] = $this->createUserAndShop();

        $okRoutes = [
            '/dashboard',
            '/live',
            '/post',
            '/broadcasts',
            '/products',
            '/products/import',
            '/products/export',
            '/shops',
            '/orders',
            '/reports',
            '/packing',
            '/customers',
            '/customers/messenger/messages',
            '/customers/messenger/readiness',
            '/customers/messenger/conflicts',
            '/customers/messenger/send-control',
            '/credits',
            '/financial',
            '/settings',
            '/team',
            '/integrations',
            '/help',
            '/chat',
            '/chatOrders',
            '/financial',
            '/accounts',
            '/userAccess',
            '/utility-templates',
            '/tutorial',
        ];

        foreach ($okRoutes as $path) {
            $response = $this->actingAs($user)->get($path);
            $this->assertContains($response->getStatusCode(), [200, 302], "Unexpected status for {$path}");
        }

        $this->actingAs($user)->get('/posts')->assertRedirect('/post');
        $this->actingAs($user)->get('/messages')->assertRedirect('/customers/messenger/messages');
        $this->actingAs($user)->get('/stats')->assertRedirect('/dashboard');
    }

    public function test_settings_page_renders_shop_settings_and_tiktok_controls(): void
    {
        [$user] = $this->createUserAndShop();

        $response = $this->actingAs($user)->get('/settings');
        $response->assertOk();
        $response->assertSee('ข้อมูลร้านค้า');
        $response->assertSee('TikTok Username');
        $response->assertSee('TikTok Link');
        $response->assertSee('อีเมลร้าน');
        $response->assertSee('เบอร์โทรศัพท์');
        $response->assertSee('ประเทศ');
        $response->assertSee('ตำบล/แขวง');
        $response->assertSee('ชื่อ สำหรับออกใบเสร็จรับเงิน');
        $response->assertSee('ที่อยู่ สำหรับออกใบเสร็จรับเงิน');
        $response->assertSee('ตรวจสอบ TikTok Live');
        $response->assertSee('บันทึกข้อมูล');
        $response->assertSee('ตรวจสอบการเชื่อมต่อขนส่ง');
        $response->assertSee('ตรวจสอบการเชื่อมต่อการชำระเงิน');
        $response->assertSee('เลือกเปิดใช้งาน ผู้ให้บริการจัดส่ง');
        $response->assertSee('ดูผู้ให้บริการจัดส่งรายอื่นเพิ่มเติม ...');
        $response->assertSee('บันทึกและนำไปใช้ทันที');
        $response->assertSee('ยังไม่กรอก TikTok Username');
        $response->assertSee('data-settings-tab="shipping-tab"', false);
    }

    public function test_shops_route_redirects_to_settings_without_404(): void
    {
        [$user] = $this->createUserAndShop();

        $this->actingAs($user)
            ->get('/shops')
            ->assertRedirect('/settings');

        $this->actingAs($user)
            ->get('/settings')
            ->assertOk();
    }

    public function test_shop_settings_save_normalizes_tiktok_username_and_live_page_reads_same_value(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => 'tiktok',
            'live_url' => 'https://www.tiktok.com/@example/live',
            'status' => 'active',
        ]);

        $this->actingAs($user)->put('/shops/' . $shop->id, [
            'name' => $shop->name,
            'description' => $shop->description,
            'tiktok_username' => ' @MyTiktokShop ',
        ])->assertRedirect(route('settings.index') . '#shops-tab');

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'tiktok_username' => 'mytiktokshop',
        ]);

        $settings = $this->actingAs($user)->get('/settings');
        $settings->assertOk();
        $settings->assertSee('@mytiktokshop');

        $live = $this->actingAs($user)->get('/live');
        $live->assertOk();
        $live->assertSee('@mytiktokshop');
        $live->assertSee('TikTok username ปัจจุบัน');
        $live->assertSee('ยังไม่มี TikTok connector/token');
        $live->assertSee('unchecked');

        $this->assertTrue($stream->fresh()->status === 'active');
    }

    public function test_shop_settings_rejects_empty_tiktok_username_on_save(): void
    {
        [$user, $shop] = $this->createUserAndShop();

        $this->actingAs($user)->put('/shops/' . $shop->id, [
            'name' => $shop->name,
            'description' => $shop->description,
            'tiktok_username' => '',
        ])->assertSessionHasErrors('tiktok_username');
    }

    public function test_real_connected_user_count_deduplicates_same_psid_and_ignores_fake_rows(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        [$stream, $session] = $this->createStreamContext($shop);

        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'realuserone',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088459',
            'connected_source' => 'messenger_ref',
        ]);

        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'duplicateone',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088459',
            'connected_source' => 'fallback_recent_portal_connect',
        ]);

        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'manualfake',
            'status' => CustomerMapping::STATUS_CONNECTED,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => 'PSID_ADMIN_TEST',
            'connected_source' => 'admin_manual_resolve',
        ]);

        CustomerMapping::create([
            'shop_id' => $shop->id,
            'portal_session_id' => $session->id,
            'live_stream_id' => $stream->id,
            'tiktok_username' => 'pendingreal',
            'status' => CustomerMapping::STATUS_PENDING_MESSENGER,
            'facebook_page_id' => '103832441332425',
            'facebook_psid' => '3729781057088460',
            'connected_source' => 'portal_connect_pending',
            'messenger_link_pending_at' => now(),
        ]);

        $this->assertSame(1, CustomerMetrics::realConnectedMessengerUsers(collect([$shop->id])));
        $this->assertSame(3, CustomerMetrics::connectedMappingRecords(collect([$shop->id])));
    }

    public function test_customers_page_shows_one_real_connected_user_for_duplicate_psid(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        [$stream, $session] = $this->createStreamContext($shop);

        foreach (['3729781057088459', '3729781057088459', 'manual-ui-verify-1'] as $index => $psid) {
            CustomerMapping::create([
                'shop_id' => $shop->id,
                'portal_session_id' => $session->id,
                'live_stream_id' => $stream->id,
                'tiktok_username' => 'user' . $index,
                'status' => CustomerMapping::STATUS_CONNECTED,
                'facebook_page_id' => '103832441332425',
                'facebook_psid' => $psid,
                'connected_source' => 'messenger_ref',
            ]);
        }

        $response = $this->actingAs($user)->get('/customers');

        $response->assertOk();
        $response->assertSee('ลูกค้าที่เชื่อมจริง');
        $response->assertSee('records ทั้งหมด 3');
        $this->assertMatchesRegularExpression('/ลูกค้าที่เชื่อมจริง.*?>\\s*1\\s*</s', $response->getContent());
    }

    public function test_customers_and_orders_pages_render_search_and_filter_controls(): void
    {
        [$user] = $this->createUserAndShop();

        $customers = $this->actingAs($user)->get('/customers');
        $customers->assertOk();
        $customers->assertSee('Search ...');
        $customers->assertSee('ลูกค้าปกติ');
        $customers->assertSee('ลูกค้าที่ถูกบล็อก');
        $customers->assertSee('App');
        $customers->assertSee('LINE');
        $customers->assertSee('Facebook');
        $customers->assertSee('ค้นหาลูกค้า');
        $customers->assertSee('มีเบอร์โทร');
        $customers->assertSee('มี Username');

        $orders = $this->actingAs($user)->get('/orders');
        $orders->assertOk();
        $orders->assertSee('ค้นหาออเดอร์');
        $orders->assertSee('ทุกสถานะ');
        $orders->assertSee('สร้างคำสั่งซื้อ');
    }

    public function test_live_page_renders_connector_status_and_prefill_actions(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        [$stream] = $this->createStreamContext($shop);
        config()->set('tiktok.enabled', false);
        config()->set('tiktok.api_key', '');

        $response = $this->actingAs($user)->get('/live');

        $response->assertOk();
        $response->assertSee('เชื่อมต่อ LIVE ปัจจุบัน');
        $response->assertSee('สร้างจาก LIVE ล่าสุด');
        $response->assertSee('ตรวจสอบ TikTok Live');
        $response->assertSee('TikTok connector');
        $response->assertSee('ยังไม่มี TikTok connector/token');
        $response->assertSee('LIVE ปัจจุบัน');
        $response->assertSee($stream->live_url);
        $response->assertSee('connector unavailable');
        $response->assertSee('สถานะตรวจสอบ LIVE');
        $response->assertDontSee('ระบบพร้อมใช้งานแล้ว');
        $response->assertSee('รายการ LIVE');
        $response->assertSee('จัดการข้อมูล');
        $response->assertSee(route('live.print'), false);
        $response->assertSee(route('live.copy-latest'), false);
        $response->assertSee(route('live.show', $stream->id), false);
    }

    public function test_live_page_prompts_settings_when_tiktok_username_missing(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        $shop->update(['tiktok_username' => null]);

        $response = $this->actingAs($user)->get('/live');

        $response->assertOk();
        $response->assertSee('ยังไม่ได้ตั้งค่า TikTok username');
        $response->assertSee('ไปตั้งค่าร้านค้าก่อน');
        $response->assertSee(route('settings.index'), false);
        $response->assertDontSee('ระบบพร้อมใช้งานแล้ว');
    }

    public function test_live_page_blocks_unverified_username_even_when_username_exists(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        $shop->update([
            'tiktok_username' => 'paritytestshop',
            'settings' => [
                'tiktok' => [
                    'username_status' => 'not_configured',
                    'checked_username' => 'paritytestshop',
                    'status_message' => 'ยังไม่สามารถตรวจสอบ TikTok Username ได้: ยังไม่มี TikTok connector/token',
                ],
            ],
        ]);
        [$stream] = $this->createStreamContext($shop);

        $response = $this->actingAs($user)->get('/live');

        $response->assertOk();
        $response->assertSee('@paritytestshop');
        $response->assertSee('not_configured');
        $response->assertSee('TikTok Username ยังไม่ verified');
        $response->assertDontSee('ระบบพร้อมใช้งานแล้ว');
        $response->assertSee($stream->live_url);
    }

    public function test_live_actions_render_without_404(): void
    {
        [$user, $shop] = $this->createUserAndShop();
        [$stream] = $this->createStreamContext($shop);

        $this->actingAs($user)->get(route('live.print'))->assertOk();
        $this->actingAs($user)->get(route('live.show', $stream->id))->assertOk();
        $this->actingAs($user)->get(route('live.copy-latest'))->assertRedirect(route('live.index', ['copy_from' => $stream->id]));
    }

    public function test_products_page_renders_search_filters_and_bulk_actions(): void
    {
        [$user, $shop] = $this->createUserAndShop();

        Product::create([
            'shop_id' => $shop->id,
            'name' => 'Test Product',
            'price' => 199,
            'stock' => 3,
            'is_active' => true,
            'code_pattern' => 'CF-TEST',
        ]);

        $response = $this->actingAs($user)->get('/products');

        $response->assertOk();
        $response->assertSee('ข้อมูลสินค้า');
        $response->assertSee('Search:');
        $response->assertSee('Show');
        $response->assertSee('entries');
        $response->assertSee('นำเข้า');
        $response->assertSee('นำเข้าจากไฟล์ Excel');
        $response->assertSee('นำเข้าสินค้าพร้อมตัวเลือก');
        $response->assertSee('พิมพ์');
        $response->assertSee('Export Excel');
        $response->assertSee('ลบที่เลือก');
        $response->assertSee('id="bulk-delete-products"', false);
        $response->assertSee('disabled', false);
        $response->assertSee('Test Product');
        $response->assertSee('CF-TEST');
        $response->assertSee('checkbox', false);
        $response->assertSee('จัดการข้อมูล');
        $response->assertSee(route('products.import.options'), false);
        $response->assertSee(route('products.print'), false);
        $response->assertSee(route('products.show', 1), false);
    }

    public function test_products_page_supports_search_and_page_size_without_500(): void
    {
        [$user, $shop] = $this->createUserAndShop();

        Product::create([
            'shop_id' => $shop->id,
            'name' => 'Alpha Product',
            'price' => 100,
            'stock' => 1,
            'code_pattern' => 'ALPHA',
            'is_active' => true,
        ]);

        Product::create([
            'shop_id' => $shop->id,
            'name' => 'Beta Product',
            'price' => 200,
            'stock' => 10,
            'code_pattern' => 'BETA',
            'is_active' => true,
        ]);

        $search = $this->actingAs($user)->get('/products?q=Alpha');
        $search->assertOk();
        $search->assertSee('Alpha Product');
        $search->assertDontSee('Beta Product');

        $perPage = $this->actingAs($user)->get('/products?per_page=10');
        $perPage->assertOk();
        $perPage->assertSee('Show 10');
    }

    public function test_product_shell_routes_render_without_404(): void
    {
        [$user, $shop] = $this->createUserAndShop();

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Shell Product',
            'price' => 299,
            'stock' => 5,
            'code_pattern' => 'SHELL',
            'is_active' => true,
        ]);

        $this->actingAs($user)->get(route('products.import'))->assertOk();
        $this->actingAs($user)->get(route('products.import.options'))->assertOk();
        $this->actingAs($user)->get(route('products.print'))->assertOk();
        $this->actingAs($user)->get(route('products.show', $product->id))->assertOk();
        $this->actingAs($user)->get(route('products.options', $product->id))->assertOk();
    }

    public function test_product_tool_routes_render_or_download_without_404(): void
    {
        [$user] = $this->createUserAndShop();

        $this->actingAs($user)->get('/products/import')->assertOk();
        $export = $this->actingAs($user)->get('/products/export');
        $export->assertOk();
        $this->assertStringContainsString('text/csv', $export->headers->get('content-type') ?? '');
    }

    protected function createUserAndShop(): array
    {
        $user = User::create([
            'name' => 'Sidebar Tester',
            'email' => 'sidebar' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Sidebar Shop',
            'slug' => 'sidebar-shop-' . uniqid(),
            'is_active' => true,
        ]);

        return [$user, $shop];
    }

    protected function createStreamContext(Shop $shop): array
    {
        $stream = LiveStream::create([
            'shop_id' => $shop->id,
            'platform' => 'tiktok',
            'live_url' => 'https://tiktok.test/live/' . uniqid(),
            'status' => 'active',
        ]);

        $session = PortalSession::create([
            'shop_id' => $shop->id,
            'live_stream_id' => $stream->id,
            'sid' => 'sidebar-' . uniqid(),
            'is_active' => true,
            'connected_count' => 3,
            'expires_at' => now()->addHours(12),
        ]);

        return [$stream, $session];
    }
}
