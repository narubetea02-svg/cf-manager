<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_with_tabs()
    {
        $user = User::firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => bcrypt('password')
        ]);
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop'
        ]);

        $response = $this->actingAs($user)->get("/shops/{$shop->id}/edit");
        
        $response->assertStatus(200);
        $response->assertSee('ข้อมูลร้านค้า');
        $response->assertSee('การจัดส่ง');
        $response->assertSee('การชำระเงิน');
        $response->assertSee('ข้อความอัตโนมัติ');
        $response->assertSee('data-settings-tab="shops-tab"', false);
        $response->assertSee('data-settings-tab="shipping-tab"', false);
        $response->assertSee('data-settings-tab="payment-tab"', false);
        $response->assertSee('data-settings-tab="autos-tab"', false);
        $response->assertSee('ตรวจสอบ');
        $response->assertSee('ยังไม่กรอก TikTok Username');
        $response->assertSee('Social Link');
        $response->assertSee('ข้อมูลออกใบเสร็จ');
        $response->assertSee('ข้อความหลังจบไลฟ์');
        $response->assertSee('ยังไม่ได้ตั้งค่า API/Token ขนส่ง');
        $response->assertSee('เลือกเปิดใช้งาน ผู้ให้บริการจัดส่ง');
        $response->assertSee('บันทึกและนำไปใช้ทันที');
        $response->assertSee('Thailand Post eCo-Post');
        $response->assertSee('ยังไม่ได้ตั้งค่า Payment Gateway/Token');
        $response->assertSee('Dry Run');
    }

    public function test_can_save_all_settings_tabs()
    {
        $user = User::firstOrCreate(['email' => 'test@example.com'], [
            'name' => 'Test User',
            'password' => bcrypt('password')
        ]);
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop-2'
        ]);

        $response = $this->actingAs($user)->put("/shops/{$shop->id}", [
            'name' => 'Updated Name',
            'email' => 'test@example.com',
            'phone' => '0812345678',
            'country' => 'ไทย',
            'address' => '123 Test St',
            'tiktok_username' => '@my_tiktok',
            'social_primary_link' => 'https://linktr.ee/myshop',
            'receipt_tax_name' => 'My Shop Co., Ltd.',
            'receipt_tax_id' => '0105559999999',
            'receipt_tax_address' => 'Bangkok 10110',
            'receipt_phone' => '08x-123-4567',
            
            // Shipping
            'shipping_enabled' => '1',
            'shipping_default_method' => 'EMS',
            'shipping_default_cost' => '50',
            'shipping_note' => 'ส่งทุกวัน',
            'shipping_carriers' => ['EMS', 'Flash'],
            
            // Payment
            'payment_cod_enabled' => '1',
            'payment_transfer_enabled' => '1',
            'payment_bank_name' => 'KBANK',
            'payment_account_name' => 'My Shop',
            'payment_account_number' => '123-4-56789-0',
            'payment_instruction' => 'Please send slip',
            'payment_contact_channel' => 'Messenger',
            
            // Auto Message
            'auto_message_enabled' => '1',
            'auto_msg_welcome' => 'Welcome to our shop',
            'auto_msg_payment' => 'Payment confirmed',
            'auto_msg_shipping' => 'Shipping sent',
            'auto_msg_tracking' => 'Tracking sent',
            'auto_msg_after_live' => 'Thanks after live',
            
            'active_tab' => 'shipping-tab'
        ]);

        $response->assertRedirect(route('settings.index') . '#shipping-tab');
        $response->assertSessionHas('success');

        $shop->refresh();

        $this->assertEquals('Updated Name', $shop->name);
        $this->assertEquals('test@example.com', $shop->email);
        $this->assertEquals('0812345678', $shop->phone);
        $this->assertEquals('my_tiktok', $shop->tiktok_username); // @ removed
        $this->assertEquals('https://linktr.ee/myshop', $shop->settings['social']['primary_link']);
        $this->assertEquals('My Shop Co., Ltd.', $shop->settings['receipt']['tax_name']);
        $this->assertEquals('0105559999999', $shop->settings['receipt']['tax_id']);
        $this->assertEquals('Bangkok 10110', $shop->settings['receipt']['tax_address']);
        $this->assertEquals('081234567', $shop->settings['receipt']['phone']);
        
        $this->assertTrue($shop->settings['shipping']['enabled']);
        $this->assertEquals('EMS', $shop->settings['shipping']['default_method']);
        $this->assertEquals('50.00', $shop->settings['shipping']['default_cost']);
        $this->assertEquals(['EMS', 'Flash'], $shop->settings['shipping']['carriers']);
        
        $this->assertTrue($shop->settings['payment']['cod_enabled']);
        $this->assertTrue($shop->settings['payment']['transfer_enabled']);
        $this->assertEquals('KBANK', $shop->settings['payment']['bank_name']);
        $this->assertEquals('My Shop', $shop->settings['payment']['account_name']);
        $this->assertEquals('Please send slip', $shop->settings['payment']['instruction']);
        $this->assertEquals('Messenger', $shop->settings['payment']['contact_channel']);
        
        $this->assertTrue($shop->settings['auto_message']['enabled']);
        $this->assertEquals('Welcome to our shop', $shop->settings['auto_message']['welcome']);
        $this->assertEquals('Payment confirmed', $shop->settings['auto_message']['payment']);
        $this->assertEquals('Shipping sent', $shop->settings['auto_message']['shipping']);
        $this->assertEquals('Tracking sent', $shop->settings['auto_message']['tracking']);
        $this->assertEquals('Thanks after live', $shop->settings['auto_message']['after_live']);
        $response->assertSessionHas('active_tab', 'shipping-tab');
    }

    public function test_shipping_and_payment_check_routes_fail_safe_without_tokens(): void
    {
        $user = User::firstOrCreate(['email' => 'shipping@example.com'], [
            'name' => 'Shipping User',
            'password' => bcrypt('password'),
        ]);
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Shipping Shop',
            'slug' => 'shipping-shop',
        ]);

        $this->actingAs($user)
            ->post(route('shops.shipping.check', $shop->id), ['shipping_default_method' => 'EMS'])
            ->assertRedirect(route('settings.index') . '#shipping-tab')
            ->assertSessionHas('error');

        $this->actingAs($user)
            ->post(route('shops.payment.check', $shop->id))
            ->assertRedirect(route('settings.index') . '#payment-tab')
            ->assertSessionHas('error');
    }

    public function test_tiktok_username_verify_route_sets_not_configured_without_token(): void
    {
        $user = User::firstOrCreate(['email' => 'tiktok@example.com'], [
            'name' => 'TikTok User',
            'password' => bcrypt('password'),
        ]);
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'TikTok Shop',
            'slug' => 'tiktok-shop',
            'tiktok_username' => 'paritytestshop',
        ]);

        $this->actingAs($user)
            ->post(route('shops.tiktok.verify', $shop->id), ['tiktok_username' => '@paritytestshop'])
            ->assertRedirect(route('settings.index') . '#shops-tab')
            ->assertSessionHas('error');

        $shop->refresh();
        $this->assertEquals('not_configured', $shop->settings['tiktok']['username_status']);
        $this->assertEquals('paritytestshop', $shop->settings['tiktok']['checked_username']);
    }

    public function test_tiktok_verification_status_resets_when_username_changes(): void
    {
        $user = User::firstOrCreate(['email' => 'reset@example.com'], [
            'name' => 'Reset User',
            'password' => bcrypt('password'),
        ]);
        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => 'Reset Shop',
            'slug' => 'reset-shop',
            'tiktok_username' => 'oldname',
            'settings' => [
                'tiktok' => [
                    'username_status' => 'verified',
                    'checked_username' => 'oldname',
                    'verified_at' => now()->toIso8601String(),
                    'status_message' => 'verified before',
                ],
            ],
        ]);

        $this->actingAs($user)->put("/shops/{$shop->id}", [
            'name' => 'Reset Shop',
            'tiktok_username' => '@newname',
        ])->assertRedirect(route('settings.index') . '#shops-tab');

        $shop->refresh();
        $this->assertEquals('unchecked', $shop->settings['tiktok']['username_status']);
        $this->assertEquals('newname', $shop->settings['tiktok']['checked_username']);
        $this->assertNull($shop->settings['tiktok']['verified_at']);
    }
}
