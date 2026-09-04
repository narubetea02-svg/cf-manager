<style>
    .vertical-menu #side-menu > li > a,
    .vertical-menu #side-menu .sub-menu a {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .vertical-menu #side-menu > li > a {
        min-height: 46px;
        padding-right: 18px;
    }

    .vertical-menu #side-menu .sub-menu a {
        min-height: 40px;
        padding-left: 18px;
    }

    .vertical-menu .sidebar-icon {
        width: 22px;
        height: 22px;
        min-width: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--cf-sidebar-muted);
        transition: color .2s ease, transform .2s ease, opacity .2s ease;
        opacity: .96;
    }

    .vertical-menu .sidebar-icon svg {
        width: 20px;
        height: 20px;
        display: block;
    }

    .vertical-menu .sidebar-label {
        flex: 1 1 auto;
        min-width: 0;
        line-height: 1.25;
    }

    .vertical-menu #side-menu > li > a:hover .sidebar-icon,
    .vertical-menu #side-menu > li.mm-active > a .sidebar-icon,
    .vertical-menu #side-menu > li > a.active .sidebar-icon,
    .vertical-menu #side-menu > li > a.is-current .sidebar-icon,
    .vertical-menu #side-menu .sub-menu li a:hover .sidebar-icon,
    .vertical-menu #side-menu .sub-menu li a.active .sidebar-icon,
    .vertical-menu #side-menu .sub-menu li a.is-current .sidebar-icon {
        color: #ffffff;
        opacity: 1;
        transform: translateY(-1px);
    }

    .vertical-menu #side-menu .sub-menu .sidebar-icon {
        color: var(--cf-sidebar-muted);
    }

    .vertical-menu #side-menu > li > a.active,
    .vertical-menu #side-menu > li > a.is-current,
    .vertical-menu #side-menu > li.mm-active > a {
        background: var(--cf-sidebar-active-bg);
        color: var(--cf-sidebar-active-text);
        border-radius: 12px;
        margin: 4px 10px;
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--cf-link) 18%, transparent);
    }

    .vertical-menu #side-menu > li > a:hover {
        color: var(--cf-sidebar-active-text);
    }

    .vertical-menu #side-menu .sub-menu li a.active,
    .vertical-menu #side-menu .sub-menu li a.is-current {
        color: var(--cf-sidebar-active-text);
    }

    .vertical-menu #side-menu > li > a,
    .vertical-menu #side-menu .sub-menu a {
        color: var(--cf-sidebar-text) !important;
    }

    .vertical-menu #side-menu .sub-menu {
        background: transparent;
    }

    .vertical-menu #side-menu > li > a.has-arrow::after {
        margin-top: 0;
        right: 14px;
        transform: translateY(-50%);
        top: 50%;
    }

    .vertical-menu #side-menu > li.mm-active > a.has-arrow::after {
        transform: translateY(-50%) rotate(90deg);
    }
</style>

@php
    $isProductGroup = request()->is('products*') || request()->is('stocks*');
    $isOrderGroup = request()->is('orders*');
    $isReportGroup = request()->is('reportOrder*');
@endphp

<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">เมนูหลัก</li>

                <li>
                    <a href="{{ url('/dashboard') }}" class="waves-effect {{ request()->is('dashboard*') || request()->is('index*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'dashboard'])</span>
                        <span class="sidebar-label">สถิติ</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/live') }}" class="waves-effect {{ request()->is('live*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'live'])</span>
                        <span class="sidebar-label">ไลฟ์</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/post') }}" class="waves-effect {{ request()->is('post*') || request()->is('posts*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'post'])</span>
                        <span class="sidebar-label">โพสต์</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/chatOrders') }}" class="waves-effect {{ request()->is('chatOrders*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'chat'])</span>
                        <span class="sidebar-label">ดูดแชท</span>
                        <span class="mx-1 badge badge-success font-size-10">NEW</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/broadcasts') }}" class="waves-effect {{ request()->is('broadcasts*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'broadcast'])</span>
                        <span class="sidebar-label">บรอดแคสต์</span>
                    </a>
                </li>

                <li class="{{ $isProductGroup ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect {{ $isProductGroup ? 'is-current' : '' }}" data-sidebar-parent="products">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'product'])</span>
                        <span class="sidebar-label">สินค้า</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="{{ $isProductGroup ? 'true' : 'false' }}">
                        <li><a href="{{ url('/products') }}" class="{{ request()->is('products*') ? 'is-current' : '' }}"><span class="sidebar-label">รายการสินค้า</span></a></li>
                        <li><a href="{{ url('/stocks') }}" class="{{ request()->is('stocks*') ? 'is-current' : '' }}"><span class="sidebar-label">คลังสินค้า</span></a></li>
                    </ul>
                </li>

                <li class="{{ $isOrderGroup ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect {{ $isOrderGroup ? 'is-current' : '' }}" data-sidebar-parent="orders">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'order'])</span>
                        <span class="sidebar-label">คำสั่งซื้อ</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="{{ $isOrderGroup ? 'true' : 'false' }}">
                        <li><a href="{{ url('/orders?type=all') }}" class="{{ request('type') == 'all' ? 'is-current' : '' }}"><span class="sidebar-label">ทั้งหมด</span></a></li>
                        <li><a href="{{ url('/orders?type=wait_payment') }}" class="{{ request('type') == 'wait_payment' ? 'is-current' : '' }}"><span class="sidebar-label">รอชำระเงิน</span></a></li>
                        <li><a href="{{ url('/orders?type=pending_payment,reject_payment') }}" class="{{ in_array(request('type'), ['pending_payment', 'reject_payment', 'pending_payment,reject_payment']) ? 'is-current' : '' }}"><span class="sidebar-label">รอตรวจสอบ</span></a></li>
                        <li><a href="{{ url('/orders?type=hold') }}" class="{{ request('type') == 'hold' ? 'is-current' : '' }}"><span class="sidebar-label">ฝากของ</span></a></li>
                        <li><a href="{{ url('/orders?type=paid') }}" class="{{ request('type') == 'paid' ? 'is-current' : '' }}"><span class="sidebar-label">ต้องจัดส่ง</span></a></li>
                        <li><a href="{{ url('/orders?type=packing') }}" class="{{ request('type') == 'packing' ? 'is-current' : '' }}"><span class="sidebar-label">พิมพ์/แพ็ค</span></a></li>
                        <li><a href="{{ url('/orders?type=cod') }}" class="{{ request('type') == 'cod' ? 'is-current' : '' }}"><span class="sidebar-label">รอชำระ COD</span></a></li>
                        <li><a href="{{ url('/orders?type=delivered') }}" class="{{ request('type') == 'delivered' ? 'is-current' : '' }}"><span class="sidebar-label">สำเร็จแล้ว</span></a></li>
                        <li><a href="{{ url('/orders?type=merchant_cancel,customer_cancel') }}" class="{{ in_array(request('type'), ['merchant_cancel', 'customer_cancel', 'merchant_cancel,customer_cancel']) ? 'is-current' : '' }}"><span class="sidebar-label">ยกเลิกแล้ว</span></a></li>
                    </ul>
                </li>

                <li class="{{ $isReportGroup ? 'mm-active' : '' }}">
                    <a href="javascript: void(0);" class="has-arrow waves-effect {{ $isReportGroup ? 'is-current' : '' }}" data-sidebar-parent="reports">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'report'])</span>
                        <span class="sidebar-label">รายงาน</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="{{ $isReportGroup ? 'true' : 'false' }}">
                        <li><a href="{{ url('/reportOrderDetails') }}" class="{{ request()->is('reportOrderDetails*') ? 'is-current' : '' }}"><span class="sidebar-label">คำสั่งซื้อสินค้า</span></a></li>
                        <li><a href="{{ url('/reportOrderShipping') }}" class="{{ request()->is('reportOrderShipping*') ? 'is-current' : '' }}"><span class="sidebar-label">ขนส่ง</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="{{ url('/packing') }}" class="waves-effect {{ request()->is('packing*') && !request()->has('type') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'packing'])</span>
                        <span class="sidebar-label">แพ็คของ</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/customers') }}" class="waves-effect {{ request()->is('customers*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'customer'])</span>
                        <span class="sidebar-label">ข้อมูลลูกค้า</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/chat') }}" class="waves-effect {{ request()->is('chat*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'inbox'])</span>
                        <span class="sidebar-label">กล่องข้อความ</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/credit') }}" class="waves-effect {{ request()->is('credit*') || request()->is('credits*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'credit'])</span>
                        <span class="sidebar-label">เครดิต</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/financial') }}" class="waves-effect {{ request()->is('financial*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'report'])</span>
                        <span class="sidebar-label">การเงิน</span>
                    </a>
                </li>

                <li class="menu-title">การตั้งค่า</li>
                <li>
                    <a href="{{ url('/settings') }}" class="waves-effect {{ request()->is('settings*') || request()->is('shops*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'settings'])</span>
                        <span class="sidebar-label">ร้านค้า</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/accounts') }}" class="waves-effect {{ request()->is('accounts*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'customer'])</span>
                        <span class="sidebar-label">บัญชี</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/userAccess') }}" class="waves-effect {{ request()->is('userAccess*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'readiness'])</span>
                        <span class="sidebar-label">การเข้าถึง</span>
                    </a>
                </li>
                <li>
                    <a href="{{ url('/utility-templates') }}" class="waves-effect {{ request()->is('utility-templates*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'post'])</span>
                        <span class="sidebar-label">เทมเพลต</span>
                    </a>
                </li>

                <li class="menu-title">วีดีโอสอนใช้งาน</li>
                <li>
                    <a href="{{ url('/tutorial') }}" class="waves-effect {{ request()->is('tutorial*') ? 'is-current' : '' }}">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'help'])</span>
                        <span class="sidebar-label">รวมวีดีโอสอนใช้งาน</span>
                    </a>
                </li>

                <li class="menu-title">ช่วยเหลือ</li>
                <li>
                    <a href="https://tutorials.cf-shops.com/" target="_blank" rel="noopener" class="waves-effect">
                        <span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'help'])</span>
                        <span class="sidebar-label">วิธีใช้งาน</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sideMenu = document.getElementById('side-menu');
        if (!sideMenu) {
            return;
        }

        sideMenu.querySelectorAll('a.active').forEach(function (anchor) {
            if (!anchor.classList.contains('is-current') && !anchor.classList.contains('has-arrow')) {
                anchor.classList.remove('active');
            }
        });

        sideMenu.querySelectorAll('a.is-current').forEach(function (anchor) {
            anchor.classList.add('active');

            var parentSubMenu = anchor.closest('.sub-menu');
            if (!parentSubMenu) {
                return;
            }

            parentSubMenu.classList.add('mm-show');
            parentSubMenu.setAttribute('aria-expanded', 'true');

            var parentItem = parentSubMenu.closest('li');
            if (!parentItem) {
                return;
            }

            parentItem.classList.add('mm-active');

            var parentToggle = parentItem.querySelector(':scope > a.has-arrow');
            if (parentToggle) {
                parentToggle.classList.add('active');
            }
        });

        sideMenu.querySelectorAll('li > a.has-arrow').forEach(function (toggle) {
            if (toggle.parentElement.classList.contains('mm-active')) {
                toggle.classList.add('active');
            } else if (!toggle.classList.contains('is-current')) {
                toggle.classList.remove('active');
            }
        });
    });
</script>
