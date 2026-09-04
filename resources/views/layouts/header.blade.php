@php
    $headerShop = auth()->user()?->shops()?->with('messengerSetting')->first();
    $headerImage = auth()->user()?->avatar ?: ($headerShop?->logo ?: sprintf('data:image/svg+xml;utf8,%s', rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#556ee6"/><stop offset="1" stop-color="#7b8dff"/></linearGradient></defs><rect width="120" height="120" rx="60" fill="url(#g)"/><path d="M31 77c8-14 19-21 29-21s21 7 29 21" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round"/><circle cx="60" cy="48" r="15" fill="none" stroke="#fff" stroke-width="8"/></svg>')));
@endphp

<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <div class="navbar-brand-box">
                <a href="{{ url('/dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        <span class="logo-chip d-inline-flex align-items-center justify-content-center" style="margin-top:22px;">
                            <x-ui-icon name="store" size="18" />
                        </span>
                    </span>
                    <span class="logo-lg">
                        <div class="d-flex h-100 justify-content-center align-items-center gap-2" style="padding-top: 5px;">
                            <span class="logo-chip d-inline-flex align-items-center justify-content-center">
                                <x-ui-icon name="store" size="18" />
                            </span>
                            <span class="logo-wordmark text-dark font-weight-bold" style="font-size: 16px; margin-top:2px;">CF MANAGER</span>
                        </div>
                    </span>
                </a>

                <a href="{{ url('/dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        <span class="logo-chip d-inline-flex align-items-center justify-content-center" style="margin-top:22px;">
                            <x-ui-icon name="store" size="18" />
                        </span>
                    </span>
                    <span class="logo-lg">
                        <div class="d-flex h-100 justify-content-center align-items-center gap-2" style="padding-top: 5px;">
                            <span class="logo-chip d-inline-flex align-items-center justify-content-center">
                                <x-ui-icon name="store" size="18" />
                            </span>
                            <span class="logo-wordmark text-white font-weight-bold" style="font-size: 16px; margin-top:2px;">CF MANAGER</span>
                        </div>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn" data-bs-toggle="tooltip" data-bs-placement="right" title="ย่อ">
                <x-ui-icon name="menu-collapse" class="d-none d-lg-inline-flex" size="18" />
                <x-ui-icon name="menu-sidebar" class="d-inline-flex d-lg-none" size="20" />
            </button>
        </div>

        <div class="d-flex">
            <div class="dropdown mt-n1 ml-1 mr-1">
                <button type="button" class="btn btn-icon rounded-circle shortcut-icon header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-expanded="false">
                    <x-ui-icon name="globe" size="20" />
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ url('/dashboard') }}" class="dropdown-item notify-item text-muted pointer-events-none">
                        <x-ui-icon name="globe" class="me-1" size="16" />
                        <span class="align-middle">ไทย</span>
                    </a>
                </div>
            </div>

            <div class="dropdown d-inline-block ml-1 mr-1 cf-launcher">
                <button type="button" class="btn header-item noti-icon waves-effect shortcut-icon" data-bs-toggle="dropdown" aria-expanded="false" title="ทางลัดด่วน">
                    <x-ui-icon name="grid" size="20" />
                </button>
                <div class="dropdown-menu dropdown-menu-end cf-launcher-pop p-0">
                    <div class="cf-launcher-head">
                        <div>
                            <h4>ทางลัดด่วน</h4>
                            <span>เข้าถึงงานที่ใช้บ่อย</span>
                        </div>
                    </div>
                    <div class="cf-launcher-grid">
                        <a class="cf-sc-tile" href="{{ url('/live') }}">
                            <span class="cf-sc-ic blue"><x-ui-icon name="live" size="20" /><span class="cf-sc-hot"></span></span>
                            <span class="cf-sc-label">เริ่มไลฟ์</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/products') }}">
                            <span class="cf-sc-ic violet"><x-ui-icon name="product-alt" size="20" /></span>
                            <span class="cf-sc-label">เพิ่มสินค้า</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/orders?type=all') }}">
                            <span class="cf-sc-ic green"><x-ui-icon name="order-alt" size="20" /></span>
                            <span class="cf-sc-label">คำสั่งซื้อ</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/packing') }}">
                            <span class="cf-sc-ic amber"><x-ui-icon name="printer" size="20" /></span>
                            <span class="cf-sc-label">พิมพ์ใบปะหน้า</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/credits') }}">
                            <span class="cf-sc-ic rose"><x-ui-icon name="coin-stack" size="20" /></span>
                            <span class="cf-sc-label">เติมเครดิต</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/reportOrderDetails') }}">
                            <span class="cf-sc-ic blue"><x-ui-icon name="report" size="20" /></span>
                            <span class="cf-sc-label">รายงานยอดขาย</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ $headerShop ? url('/settings#messenger-tab') : url('/settings') }}">
                            <span class="cf-sc-ic info"><x-ui-icon name="link" size="20" /></span>
                            <span class="cf-sc-label">เชื่อมช่องทาง</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ $headerShop ? url('/settings#shops-tab') : url('/settings') }}">
                            <span class="cf-sc-ic slate"><x-ui-icon name="settings" size="20" /></span>
                            <span class="cf-sc-label">ตั้งค่าร้าน</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ $headerShop ? url('/settings#shipping-tab') : url('/packing') }}">
                            <span class="cf-sc-ic amber"><x-ui-icon name="truck" size="20" /></span>
                            <span class="cf-sc-label">ตั้งค่าขนส่ง</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ $headerShop ? url('/settings#payment-tab') : url('/financial') }}">
                            <span class="cf-sc-ic green"><x-ui-icon name="money" size="20" /></span>
                            <span class="cf-sc-label">ตั้งค่า COD</span>
                        </a>
                        <a class="cf-sc-tile" href="{{ url('/orders?type=hold') }}">
                            <span class="cf-sc-ic violet"><x-ui-icon name="deposit" size="20" /></span>
                            <span class="cf-sc-label">ฝากของ</span>
                        </a>
                    </div>
                    <div class="cf-launcher-foot">
                        <a href="{{ url('/orders?type=to_ship') }}">คำสั่งซื้อรอจัดส่ง <x-ui-icon name="arrow-right" class="ms-1" size="16" /></a>
                    </div>
                </div>
            </div>

            <div class="d-lg-inline-block ml-1 mr-1">
                <a href="{{ url('/help') }}" class="btn header-item waves-effect shortcut-icon" title="ขอความช่วยเหลือ">
                    <x-ui-icon name="help" class="icon-toggle" size="20" />
                </a>
            </div>

            <div class="d-lg-inline-block ml-1 mr-1">
                <button type="button" class="btn header-item waves-effect shortcut-icon theme-toggle" id="theme-toggle-btn" title="เปลี่ยนธีม">
                    <x-ui-icon name="theme" class="icon-toggle" size="20" />
                </button>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item waves-effect ps-1" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="{{ $headerImage }}" alt="{{ $headerShop?->name ?: auth()->user()->name }}">
                    <span class="d-none d-xl-inline-block ml-1">{{ $headerShop?->name ?: auth()->user()->name }}</span>
                    <x-ui-icon name="chevron-down" class="d-none d-xl-inline-flex ms-1 text-muted" size="16" />
                </button>
                <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end no-shortcut">
                    @if($headerShop)
                        <span class="dropdown-item btn-shop">
                            <x-ui-icon name="transfer" class="me-1" size="16" /> {{ $headerShop->name }}
                        </span>
                        <div class="dropdown-divider"></div>
                    @endif
                    <a class="dropdown-item text-primary create-btn text-right" href="{{ url('/shops#create') }}">
                        <x-ui-icon name="store" class="me-1" size="16" /> สร้างร้านค้า
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger logout-btn" href="{{ url('/logout') }}">
                        <x-ui-icon name="power" class="me-1" size="16" /> ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('theme-toggle-btn');
    var applyTheme = function (theme) {
        var normalized = theme === 'light' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', normalized);
        document.documentElement.style.colorScheme = normalized;
        document.body.setAttribute('data-sidebar', normalized);
        localStorage.setItem('cfmanager_theme', normalized);

        if (toggle) {
            toggle.classList.toggle('is-dark', normalized === 'dark');
            toggle.setAttribute('aria-label', normalized === 'dark' ? 'สลับเป็นโหมดสว่าง' : 'สลับเป็นโหมดมืด');
            toggle.setAttribute('title', normalized === 'dark' ? 'สลับเป็นโหมดสว่าง' : 'สลับเป็นโหมดมืด');
            toggle.setAttribute('data-original-title', normalized === 'dark' ? 'สลับเป็นโหมดสว่าง' : 'สลับเป็นโหมดมืด');
        }
    };

    applyTheme(localStorage.getItem('cfmanager_theme') || document.documentElement.getAttribute('data-theme') || 'dark');

    if (!toggle) {
        return;
    }

    toggle.addEventListener('click', function () {
        var nextTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
    });
});
</script>
@endpush
