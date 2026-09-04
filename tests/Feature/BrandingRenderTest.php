<?php

namespace Tests\Feature;

use Tests\TestCase;

class BrandingRenderTest extends TestCase
{
    public function test_login_page_renders_cf_manager_branding(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('CF Manager');
        $response->assertSee('ระบบจัดการไลฟ์ แชท และออเดอร์');
    }
}
