<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'สถิติ') | CF MANAGER</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        :root{--shell:#f8f8fc;--surface:#fff;--sidebar:#2b3046;--sidebar-width:198px;--text:#495057;--muted:#98a6b8;--primary:#556ee6;--border:#eff0f5}
        *{box-sizing:border-box}body{margin:0;background:var(--shell);color:var(--text);font-family:Prompt,system-ui,sans-serif;font-size:14px}
        #layout-wrapper{min-height:100vh}.main-content{margin-left:var(--sidebar-width);min-height:100vh}.page-content{padding:94px 24px 30px}.container-fluid{max-width:1600px;margin:auto}
        #page-topbar{position:fixed;top:0;right:0;left:var(--sidebar-width);height:70px;background:var(--surface);z-index:1001;border-bottom:1px solid var(--border)}
        .navbar-header{height:70px;display:flex;align-items:center;justify-content:space-between;padding:0 24px}.navbar-brand-box{position:fixed;left:0;top:0;width:var(--sidebar-width);height:70px;background:var(--sidebar);z-index:1003;display:flex;align-items:center;justify-content:center}.logo{color:#fff;text-decoration:none;font-weight:700;letter-spacing:.04em;white-space:nowrap}.logo-chip{display:inline-flex;width:30px;height:30px;border-radius:8px;background:var(--primary);align-items:center;justify-content:center;margin-right:8px;font-size:12px;letter-spacing:0}.top-actions{display:flex;align-items:center;gap:6px}.header-item{border:0;background:transparent;color:var(--text);padding:9px 10px;border-radius:8px;display:inline-flex;align-items:center;gap:7px}.header-item:hover{background:#f2f3f8}.header-avatar{width:34px;height:34px;border-radius:50%;background:#eee9f1;color:#9a7b99;display:inline-flex;align-items:center;justify-content:center;font-weight:700}.vertical-menu{position:fixed;top:0;bottom:0;left:0;width:var(--sidebar-width);background:var(--sidebar);z-index:1002;overflow:auto}.vertical-menu #side-menu{padding:80px 10px 24px;margin:0}.vertical-menu ul{list-style:none}.vertical-menu a{color:#c8cde0;text-decoration:none}.vertical-menu .menu-title{padding:16px 12px 7px;color:#7f88a6;font-size:11px;text-transform:uppercase;letter-spacing:.05em}.vertical-menu li>a{display:flex;align-items:center;gap:9px;min-height:39px;padding:8px 11px;border-radius:6px;white-space:nowrap}.vertical-menu li>a:hover,.vertical-menu li>a.is-current{color:#fff;background:rgba(85,110,230,.34)}.sidebar-icon{width:18px;height:18px;display:inline-flex;flex:0 0 18px}.sidebar-icon svg{width:18px;height:18px}.sidebar-label{flex:1;overflow:hidden;text-overflow:ellipsis}.sidebar-badge{font-size:9px;background:#f46a6a;color:#fff;padding:2px 5px;border-radius:4px}.side-quick{margin:0 4px 8px;padding:9px 8px;border:1px solid rgba(255,255,255,.08);border-radius:7px;background:rgba(255,255,255,.035)}.side-quick-title{padding:0 5px 5px;color:#8992af;font-size:10px}.side-quick a{min-height:31px;padding:5px 6px;font-size:12px}.side-quick .sidebar-icon{width:16px;height:16px;flex-basis:16px}.side-quick .sidebar-icon svg{width:16px;height:16px}.sub-menu{display:none;padding:3px 0 5px 25px}.sub-menu.show{display:block}.sub-menu a{font-size:12px;min-height:34px;padding:7px 9px}.has-arrow:after{content:'›';font-size:19px;line-height:1;transition:transform .2s}.has-arrow.open:after{transform:rotate(90deg)}.alert{border:0;border-radius:8px}.footer{padding:20px 24px;color:#98a6b8;font-size:12px}.dropdown-menu{border:1px solid var(--border);box-shadow:0 8px 24px #2026331a}
        @media(max-width:991.98px){.navbar-brand-box{position:static;width:auto;background:transparent}.logo{color:var(--text)}#page-topbar{left:0}.main-content{margin-left:0}.vertical-menu{left:calc(-1 * var(--sidebar-width));transition:left .2s}.vertical-menu.open{left:0}.page-content{padding:94px 15px 24px}}
    </style>
    @stack('styles')
</head>
<body>
<div id="layout-wrapper">
    @include('layouts.header')
    @include('layouts.sidebar')
    <main class="main-content"><div class="page-content"><div id="mainContent" class="container-fluid">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @yield('content')
    </div></div>@include('layouts.footer')</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.min.js') }}"></script>
@stack('scripts')
</body>
</html>
