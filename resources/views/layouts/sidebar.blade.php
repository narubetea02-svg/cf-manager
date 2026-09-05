@php
    $productOpen = request()->is('products*') || request()->is('stocks*');
    $orderOpen = request()->is('orders*');
    $reportOpen = request()->is('reports*') || request()->is('reportOrder*');
    $is = fn (string $path) => request()->is(ltrim($path, '/') . '*');
@endphp
<aside class="vertical-menu">
    <ul id="side-menu" class="list-unstyled">
        <li class="side-quick">
            <div class="side-quick-title">เข้าใช้บ่อย</div>
            <a href="{{ url('/live') }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'live'])</span><span class="sidebar-label">ไลฟ์</span></a>
            <a href="{{ url('/dashboard') }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'dashboard'])</span><span class="sidebar-label">สถิติ</span></a>
            <a href="{{ url('/credits') }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'credit'])</span><span class="sidebar-label">เครดิต</span></a>
            <a href="{{ url('/post') }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'post'])</span><span class="sidebar-label">โพสต์</span></a>
            <a href="{{ url('/customers') }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'customer'])</span><span class="sidebar-label">ข้อมูลลูกค้า</span></a>
        </li>

        <li class="menu-title">เมนูหลัก</li>
        <li><a href="{{ url('/dashboard') }}" class="{{ $is('dashboard') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'dashboard'])</span><span class="sidebar-label">สถิติ</span></a></li>
        <li><a href="{{ url('/live') }}" class="{{ $is('live') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'live'])</span><span class="sidebar-label">ไลฟ์</span></a></li>
        <li><a href="{{ url('/post') }}" class="{{ $is('post') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'post'])</span><span class="sidebar-label">โพสต์</span></a></li>
        <li><a href="{{ url('/chatOrders') }}" class="{{ request()->is('chatOrders*') || request()->is('new_chat*') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'chat'])</span><span class="sidebar-label">ดูดแชท</span><span class="sidebar-badge">NEW</span></a></li>
        <li><a href="{{ url('/my-videos') }}" class="{{ $is('my-videos') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'play'])</span><span class="sidebar-label">วิดีโอของฉัน</span></a></li>
        <li><a href="{{ url('/broadcasts') }}" class="{{ $is('broadcasts') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'broadcast'])</span><span class="sidebar-label">บรอดแคสต์</span></a></li>

        <li><a href="#" class="has-arrow {{ $productOpen ? 'open' : '' }}" data-submenu="products"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'product'])</span><span class="sidebar-label">สินค้า</span></a><ul class="sub-menu {{ $productOpen ? 'show' : '' }}" data-menu="products">
            <li><a href="{{ url('/products') }}" class="{{ request()->is('products*') ? 'is-current' : '' }}">รายการสินค้า</a></li>
            <li><a href="{{ url('/stocks') }}" class="{{ request()->is('stocks*') ? 'is-current' : '' }}">คลังสินค้า</a></li>
        </ul></li>
        <li><a href="#" class="has-arrow {{ $orderOpen ? 'open' : '' }}" data-submenu="orders"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'order'])</span><span class="sidebar-label">คำสั่งซื้อ</span></a><ul class="sub-menu {{ $orderOpen ? 'show' : '' }}" data-menu="orders">
            <li><a href="{{ url('/orders?type=all') }}">ทั้งหมด</a></li>
            <li><a href="{{ url('/orders?type=wait_payment') }}">รอชำระเงิน</a></li>
            <li><a href="{{ url('/orders?type=pending_payment,reject_payment') }}">รอตรวจสอบ</a></li>
            <li><a href="{{ url('/orders?type=hold') }}">ฝากของ</a></li>
            <li><a href="{{ url('/orders?type=paid') }}">ต้องจัดส่ง</a></li>
            <li><a href="{{ url('/orders?type=packing') }}">พิมพ์/แพ็ค</a></li>
            <li><a href="{{ url('/orders?type=cod') }}">รอชำระ COD</a></li>
            <li><a href="{{ url('/orders?type=delivered') }}">สำเร็จแล้ว</a></li>
            <li><a href="{{ url('/orders?type=merchant_cancel,customer_cancel') }}">ยกเลิกแล้ว</a></li>
        </ul></li>
        <li><a href="#" class="has-arrow {{ $reportOpen ? 'open' : '' }}" data-submenu="reports"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'report'])</span><span class="sidebar-label">รายงาน</span></a><ul class="sub-menu {{ $reportOpen ? 'show' : '' }}" data-menu="reports">
            <li><a href="{{ url('/reportOrderDetails') }}">คำสั่งซื้อสินค้า</a></li>
            <li><a href="{{ url('/reportOrderShipping') }}">ขนส่ง</a></li>
        </ul></li>
        <li><a href="{{ url('/packing') }}" class="{{ $is('packing') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'packing'])</span><span class="sidebar-label">แพ็คของ</span></a></li>
        <li><a href="{{ url('/customers') }}" class="{{ $is('customers') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'customer'])</span><span class="sidebar-label">ข้อมูลลูกค้า</span></a></li>
        <li><a href="{{ url('/chat') }}" class="{{ $is('chat') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'inbox'])</span><span class="sidebar-label">กล่องข้อความ</span></a></li>
        <li class="menu-title">&nbsp;</li>
        <li><a href="{{ url('/credits') }}" class="{{ $is('credits') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'credit'])</span><span class="sidebar-label">เครดิต</span></a></li>
        <li><a href="{{ url('/financial') }}" class="{{ $is('financial') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'money'])</span><span class="sidebar-label">การเงิน</span></a></li>

        <li class="menu-title">การตั้งค่า</li>
        <li><a href="{{ url('/shops') }}" class="{{ request()->is('shops*') || request()->is('settings*') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'settings'])</span><span class="sidebar-label">ร้านค้า</span></a></li>
        <li><a href="{{ url('/accounts') }}" class="{{ $is('accounts') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'customer'])</span><span class="sidebar-label">บัญชี</span></a></li>
        <li><a href="{{ url('/userAccess') }}" class="{{ $is('userAccess') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'team'])</span><span class="sidebar-label">การเข้าถึง</span></a></li>
        <li><a href="{{ url('/utility-templates') }}" class="{{ $is('utility-templates') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'report'])</span><span class="sidebar-label">เทมเพลต</span></a></li>
        <li class="menu-title">วีดีโอสอนใช้งาน</li>
        <li><a href="{{ url('/tutorial') }}" class="{{ $is('tutorial') ? 'is-current' : '' }}"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'play'])</span><span class="sidebar-label">รวมวีดีโอสอนใช้งาน</span></a></li>
        <li class="menu-title">ช่วยเหลือ</li>
        <li><a href="https://tutorials.cf-shops.com" target="_blank" rel="noopener"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'help'])</span><span class="sidebar-label">วิธีใช้งาน</span></a></li>
        <li><a href="https://cf-shops.com/remote-support" target="_blank" rel="noopener"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'integration'])</span><span class="sidebar-label">รีโมทช่วยเหลือ (AnyDesk)</span></a></li>
        <li><a href="https://lin.ee/xfZH0TL" target="_blank" rel="noopener"><span class="sidebar-icon">@include('components.sidebar-icon', ['name' => 'send'])</span><span class="sidebar-label">แจ้งปัญหา/ติดต่อแอดมิน</span></a></li>
    </ul>
</aside>
<script>
document.addEventListener('click', function (event) {
    const link = event.target.closest('[data-submenu]');
    if (!link) return;
    event.preventDefault();
    link.classList.toggle('open');
    const menu = document.querySelector('[data-menu="' + link.dataset.submenu + '"]');
    if (menu) menu.classList.toggle('show');
});
</script>
