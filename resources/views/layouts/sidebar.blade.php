@php
    $groups=['products'=>request()->is('products*')||request()->is('stocks*'),'orders'=>request()->is('orders*'),'reports'=>request()->is('reportOrder*')];
    $item=function($url,$label,$icon,$active=false){return '<li><a href="'.url($url).'" class="'.($active?'is-current':'').'">'.$icon.'<span class="sidebar-label">'.$label.'</span></a></li>';};
@endphp
<aside class="vertical-menu"><ul id="side-menu" class="list-unstyled">
    <li class="menu-title">เมนูหลัก</li>
    {!! $item('/dashboard','สถิติ','▦',request()->is('dashboard*')) !!}
    {!! $item('/live','ไลฟ์','◉',request()->is('live*')) !!}
    {!! $item('/post','โพสต์','▤',request()->is('post*')) !!}
    {!! $item('/chatOrders','ดูดแชท <small>NEW</small>','☷',request()->is('chatOrders*')) !!}
    {!! $item('/broadcasts','บรอดแคสต์','◈',request()->is('broadcasts*')) !!}
    <li><a href="#" class="has-arrow" data-submenu="products">◫<span class="sidebar-label">สินค้า</span></a><ul class="sub-menu {{ $groups['products']?'show':'' }}" data-menu="products">{!! $item('/products','รายการสินค้า','',$groups['products']&&request()->is('products*')) !!}{!! $item('/stocks','คลังสินค้า','',$groups['products']&&request()->is('stocks*')) !!}</ul></li>
    <li><a href="#" class="has-arrow" data-submenu="orders">▣<span class="sidebar-label">คำสั่งซื้อ</span></a><ul class="sub-menu {{ $groups['orders']?'show':'' }}" data-menu="orders">{!! $item('/orders?type=all','ทั้งหมด','',request('type')==='all') !!}{!! $item('/orders?type=wait_payment','รอชำระเงิน','',request('type')==='wait_payment') !!}{!! $item('/orders?type=paid','ต้องจัดส่ง','',request('type')==='paid') !!}{!! $item('/orders?type=packing','พิมพ์/แพ็ค','',request('type')==='packing') !!}{!! $item('/orders?type=delivered','สำเร็จแล้ว','',request('type')==='delivered') !!}</ul></li>
    <li><a href="#" class="has-arrow" data-submenu="reports">▤<span class="sidebar-label">รายงาน</span></a><ul class="sub-menu {{ $groups['reports']?'show':'' }}" data-menu="reports">{!! $item('/reportOrderDetails','คำสั่งซื้อสินค้า','',request()->is('reportOrderDetails*')) !!}{!! $item('/reportOrderShipping','ขนส่ง','',request()->is('reportOrderShipping*')) !!}</ul></li>
    {!! $item('/packing','แพ็คของ','◇',request()->is('packing*')) !!}
    {!! $item('/customers','ข้อมูลลูกค้า','♙',request()->is('customers*')) !!}
    {!! $item('/chat','กล่องข้อความ','☷',request()->is('chat*')) !!}
    {!! $item('/credits','เครดิต','▣',request()->is('credits*')) !!}
    {!! $item('/financial','การเงิน','◒',request()->is('financial*')) !!}
    <li class="menu-title">การตั้งค่า</li>
    {!! $item('/settings','ร้านค้า','⚙',request()->is('settings*')) !!}
    {!! $item('/accounts','บัญชี','♙',request()->is('accounts*')) !!}
    {!! $item('/userAccess','การเข้าถึง','✓',request()->is('userAccess*')) !!}
    {!! $item('/utility-templates','เทมเพลต','▤',request()->is('utility-templates*')) !!}
    <li class="menu-title">ช่วยเหลือ</li>
    <li><a href="https://tutorials.cf-shops.com/" target="_blank" rel="noopener">?</span><span class="sidebar-label">วิธีใช้งาน</span></a></li>
</ul></aside>
<script>document.addEventListener('click',function(e){var link=e.target.closest('[data-submenu]');if(!link)return;e.preventDefault();link.classList.toggle('open');var menu=document.querySelector('[data-menu="'+link.dataset.submenu+'"]');if(menu)menu.classList.toggle('show')});</script>
