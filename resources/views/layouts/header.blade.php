<header id="page-topbar"><div class="navbar-header">
    <div class="d-flex align-items-center gap-3">
        <div class="navbar-brand-box"><a href="{{ url('/dashboard') }}" class="logo"><span class="logo-chip">CF</span><span>CF MANAGER</span></a></div>
        <button id="vertical-menu-btn" class="header-item" type="button" aria-label="เมนู"><x-ui-icon name="menu-sidebar" size="18" /></button>
    </div>
    <div class="top-actions">
        <button class="header-item" type="button" aria-label="ภาษาไทย"><span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:31px;height:31px">🇹🇭</span></button>
        <button class="header-item" type="button" aria-label="ทางลัด"><x-ui-icon name="flag" size="19" /></button>
        <button class="header-item" type="button" aria-label="ประกาศ"><x-ui-icon name="theme" size="19" /></button>
        <a class="header-item text-decoration-none" href="{{ url('/help') }}" aria-label="ช่วยเหลือ"><x-ui-icon name="help" size="19" /><span class="d-none d-md-inline">ช่วยเหลือ</span></a>
        <div class="dropdown"><button class="header-item dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><span class="header-avatar">{{ mb_substr(auth()->user()->name ?? 'U', 0, 1) }}</span><span class="d-none d-md-inline">{{ auth()->user()->name ?? 'ผู้ใช้งาน' }}</span></button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ url('/accounts') }}">บัญชี</a></li><li><a class="dropdown-item" href="{{ url('/logout') }}">ออกจากระบบ</a></li></ul></div>
    </div>
</div></header>
