<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') | CF Manager - ระบบจัดการไลฟ์และแชท</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function () {
            var theme = localStorage.getItem('cfmanager_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme === 'light' ? 'light' : 'dark';
        })();
    </script>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/metismenu@3.0.7/dist/metisMenu.min.css" rel="stylesheet" />
    <link href="https://cf-shops.com/assets/css/app.min.css" rel="stylesheet" />
    <style>
        :root {
            --cf-shell-bg: #f3f5fb;
            --cf-surface: #ffffff;
            --cf-surface-soft: #f8faff;
            --cf-surface-alt: #eef2ff;
            --cf-border: #e6e9f0;
            --cf-text: #2f3448;
            --cf-text-muted: #72809d;
            --cf-heading: #20263a;
            --cf-topbar-bg: #ffffff;
            --cf-topbar-text: #495057;
            --cf-sidebar-bg: #2a3042;
            --cf-sidebar-text: #f8f9fa;
            --cf-sidebar-muted: #97a6cc;
            --cf-sidebar-active-bg: rgba(85, 110, 230, 0.14);
            --cf-sidebar-active-text: #ffffff;
            --cf-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --cf-shadow-soft: 0 1px 3px rgba(20, 28, 50, 0.05);
            --cf-input-bg: #ffffff;
            --cf-input-text: #2f3448;
            --cf-input-placeholder: #8b97af;
            --cf-input-disabled-bg: #eef2f8;
            --cf-input-disabled-text: #6f7d98;
            --cf-input-border: #d8deea;
            --cf-dropdown-bg: #ffffff;
            --cf-dropdown-text: #2f3448;
            --cf-dropdown-border: #e0e5ef;
            --cf-dropdown-shadow: 0 16px 48px rgba(20, 28, 50, 0.12);
            --cf-table-head-bg: #f6f8fc;
            --cf-table-head-text: #53617c;
            --cf-table-text: #2f3448;
            --cf-table-row-border: #ebeff6;
            --cf-link: #556ee6;
            --cf-link-strong: #4259c9;
            --cf-success-soft: #e8f7f1;
            --cf-success-text: #167a56;
            --cf-warning-soft: #fff6df;
            --cf-warning-text: #9a6500;
            --cf-danger-soft: #fdecec;
            --cf-danger-text: #b83232;
            --cf-info-soft: #eaf5ff;
            --cf-info-text: #1f6fae;
            --cf-primary-soft: #eef1ff;
            --cf-primary-text: #4259c9;
            --cf-secondary-soft: #eef2f7;
            --cf-secondary-text: #53617c;
        }

        html[data-theme="dark"] {
            --cf-shell-bg: #222736;
            --cf-surface: #2a3042;
            --cf-surface-soft: #30384d;
            --cf-surface-alt: #343c54;
            --cf-border: #32394e;
            --cf-text: #dbe4ff;
            --cf-text-muted: #9ba9cc;
            --cf-heading: #f3f6ff;
            --cf-topbar-bg: #262b3c;
            --cf-topbar-text: #d7def1;
            --cf-sidebar-bg: #2a3042;
            --cf-sidebar-text: #f8f9fa;
            --cf-sidebar-muted: #97a6cc;
            --cf-sidebar-active-bg: rgba(123, 142, 235, 0.18);
            --cf-sidebar-active-text: #ffffff;
            --cf-shadow: 0 18px 48px rgba(0, 0, 0, 0.28);
            --cf-shadow-soft: 0 1px 3px rgba(0, 0, 0, 0.18);
            --cf-input-bg: #2e3548;
            --cf-input-text: #e8eeff;
            --cf-input-placeholder: #8f9bb9;
            --cf-input-disabled-bg: #283044;
            --cf-input-disabled-text: #9ba9cc;
            --cf-input-border: #32394e;
            --cf-dropdown-bg: #2a3042;
            --cf-dropdown-text: #e8eeff;
            --cf-dropdown-border: #32394e;
            --cf-dropdown-shadow: 0 16px 48px rgba(0, 0, 0, 0.26);
            --cf-table-head-bg: #31384d;
            --cf-table-head-text: #c2ccef;
            --cf-table-text: #dbe4ff;
            --cf-table-row-border: #32394e;
            --cf-link: #8aa0ff;
            --cf-link-strong: #a5b7ff;
            --cf-success-soft: rgba(47, 185, 138, .18);
            --cf-success-text: #63d9ad;
            --cf-warning-soft: rgba(241, 180, 76, .20);
            --cf-warning-text: #ffd27b;
            --cf-danger-soft: rgba(244, 106, 106, .18);
            --cf-danger-text: #ff9a9a;
            --cf-info-soft: rgba(80, 165, 241, .18);
            --cf-info-text: #9fd0ff;
            --cf-primary-soft: rgba(138, 160, 255, .18);
            --cf-primary-text: #b9c6ff;
            --cf-secondary-soft: rgba(155, 169, 204, .16);
            --cf-secondary-text: #c3cdec;
        }

        .ui-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
            line-height: 1;
            color: currentColor;
            flex-shrink: 0;
        }

        .ui-icon svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        body {
            background: var(--cf-shell-bg) !important;
            color: var(--cf-text) !important;
            transition: background-color .2s ease, color .2s ease;
        }

        h1, h2, h3, h4, h5, h6,
        .h1, .h2, .h3, .h4, .h5, .h6,
        .card-title,
        .fw-semibold,
        label,
        .form-label,
        .col-form-label,
        .control-label {
            color: var(--cf-heading) !important;
        }

        p,
        li,
        td,
        th,
        .card-body,
        .tab-content,
        .modal-body {
            color: var(--cf-text) !important;
        }

        body[data-sidebar="dark"],
        body[data-sidebar="light"] {
            background: var(--cf-shell-bg) !important;
        }

        #page-topbar {
            background: var(--cf-topbar-bg) !important;
            color: var(--cf-topbar-text) !important;
            box-shadow: var(--cf-shadow-soft);
            transition: background-color .2s ease, color .2s ease;
        }

        .navbar-header,
        .navbar-brand-box,
        .main-content,
        .page-content {
            background: transparent !important;
        }

        .vertical-menu {
            background: var(--cf-sidebar-bg) !important;
            box-shadow: var(--cf-shadow-soft);
            transition: background-color .2s ease, color .2s ease;
        }

        .vertical-menu .menu-title {
            color: var(--cf-sidebar-muted) !important;
            letter-spacing: .08em;
        }

        .page-content {
            padding-top: 94px !important;
        }

        .page-title-box,
        .page-title-box h4,
        .page-title-box .page-title,
        .page-title-box p,
        .page-title-box .text-muted {
            color: var(--cf-text) !important;
        }

        .page-title-box .text-muted,
        .text-muted,
        .sub,
        .small.text-muted {
            color: var(--cf-text-muted) !important;
        }

        .card,
        .cfx-card,
        .cfx-kpi,
        .dropdown-menu,
        .modal-content,
        .alert,
        .list-group-item,
        .input-group-text,
        .cf-launcher-pop {
            background: var(--cf-surface) !important;
            color: var(--cf-text) !important;
            border-color: var(--cf-border) !important;
            box-shadow: var(--cf-shadow-soft);
        }

        .cf-launcher-pop,
        .dropdown-menu {
            box-shadow: var(--cf-dropdown-shadow) !important;
            border-radius: 16px !important;
        }

        .dropdown-item,
        .dropdown-item span,
        .dropdown-item .ui-icon,
        .cf-launcher-head h4,
        .cf-launcher-head span,
        .cf-launcher-foot a,
        .cf-sc-label {
            color: var(--cf-text-muted) !important;
        }

        .dropdown-menu {
            background: var(--cf-dropdown-bg) !important;
            color: var(--cf-dropdown-text) !important;
        }

        .dropdown-menu .dropdown-header,
        .dropdown-menu .dropdown-item {
            color: var(--cf-dropdown-text) !important;
        }

        .dropdown-item:hover,
        .dropdown-item:focus,
        .cf-launcher-foot a:hover {
            background: color-mix(in srgb, var(--cf-dropdown-bg) 86%, #556ee6 14%) !important;
            color: #ffffff !important;
        }

        .dropdown-item.text-primary,
        .dropdown-item.text-danger,
        .dropdown-item.btn-shop {
            color: var(--cf-text) !important;
        }

        .table,
        .table td,
        .table th,
        .cfx-tbl,
        .cfx-tbl td,
        .cfx-tbl th {
            color: var(--cf-table-text) !important;
            border-color: var(--cf-table-row-border) !important;
        }

        .table > :not(caption) > * > * {
            background-color: transparent !important;
            color: var(--cf-table-text) !important;
            border-color: var(--cf-table-row-border) !important;
        }

        .table-light,
        .table-light > th,
        .table-light > td,
        .table thead,
        .table thead th,
        .cfx-tbl thead,
        .cfx-tbl thead th {
            background: var(--cf-table-head-bg) !important;
            color: var(--cf-table-head-text) !important;
            border-color: var(--cf-table-row-border) !important;
        }

        .form-control,
        .form-select,
        textarea.form-control,
        .input-group-text {
            background: var(--cf-input-bg) !important;
            color: var(--cf-input-text) !important;
            border-color: var(--cf-input-border) !important;
        }

        .form-control:disabled,
        .form-control[readonly],
        .form-select:disabled,
        .form-control[readonly].bg-light {
            background: var(--cf-input-disabled-bg) !important;
            color: var(--cf-input-disabled-text) !important;
            opacity: 1 !important;
        }

        .form-control:focus,
        .form-select:focus {
            color: var(--cf-input-text) !important;
            background: var(--cf-input-bg) !important;
            border-color: var(--cf-link) !important;
            box-shadow: 0 0 0 .18rem color-mix(in srgb, var(--cf-link) 22%, transparent) !important;
        }

        .form-control::placeholder,
        textarea.form-control::placeholder,
        input::placeholder {
            color: var(--cf-input-placeholder) !important;
            opacity: 1 !important;
        }

        .btn-light,
        .btn-light.border,
        .btn-outline-secondary,
        .btn-outline-dark {
            background: var(--cf-surface-soft) !important;
            color: var(--cf-text) !important;
            border-color: var(--cf-border) !important;
        }

        .btn-light:hover,
        .btn-light.border:hover,
        .btn-outline-secondary:hover,
        .btn-outline-dark:hover {
            background: var(--cf-surface-alt) !important;
            color: var(--cf-text) !important;
            border-color: var(--cf-link) !important;
        }

        .btn-dark {
            background: var(--cf-topbar-bg) !important;
            border-color: var(--cf-topbar-bg) !important;
            color: var(--cf-topbar-text) !important;
        }

        a,
        .btn-link {
            color: var(--cf-link);
        }

        a:hover,
        .btn-link:hover {
            color: var(--cf-link-strong);
        }

        .badge.bg-light,
        .badge.bg-light.text-dark,
        .badge.border {
            background: var(--cf-surface-soft) !important;
            color: var(--cf-text) !important;
            border-color: var(--cf-border) !important;
        }

        .bg-success-subtle,
        .badge.bg-success-subtle,
        .alert-success {
            background: var(--cf-success-soft) !important;
            color: var(--cf-success-text) !important;
            border-color: color-mix(in srgb, var(--cf-success-text) 30%, transparent) !important;
        }

        .bg-warning-subtle,
        .badge.bg-warning-subtle,
        .alert-warning {
            background: var(--cf-warning-soft) !important;
            color: var(--cf-warning-text) !important;
            border-color: color-mix(in srgb, var(--cf-warning-text) 30%, transparent) !important;
        }

        .bg-danger-subtle,
        .badge.bg-danger-subtle,
        .alert-danger {
            background: var(--cf-danger-soft) !important;
            color: var(--cf-danger-text) !important;
            border-color: color-mix(in srgb, var(--cf-danger-text) 30%, transparent) !important;
        }

        .bg-info-subtle,
        .badge.bg-info-subtle,
        .alert-info {
            background: var(--cf-info-soft) !important;
            color: var(--cf-info-text) !important;
            border-color: color-mix(in srgb, var(--cf-info-text) 30%, transparent) !important;
        }

        .bg-primary-subtle,
        .bg-soft-primary,
        .badge.bg-primary-subtle,
        .badge.bg-soft-primary {
            background: var(--cf-primary-soft) !important;
            color: var(--cf-primary-text) !important;
            border-color: color-mix(in srgb, var(--cf-primary-text) 30%, transparent) !important;
        }

        .bg-secondary-subtle,
        .bg-soft-secondary,
        .badge.bg-secondary-subtle,
        .badge.bg-soft-secondary {
            background: var(--cf-secondary-soft) !important;
            color: var(--cf-secondary-text) !important;
            border-color: color-mix(in srgb, var(--cf-secondary-text) 30%, transparent) !important;
        }

        .text-success { color: var(--cf-success-text) !important; }
        .text-warning { color: var(--cf-warning-text) !important; }
        .text-danger { color: var(--cf-danger-text) !important; }
        .text-info { color: var(--cf-info-text) !important; }
        .text-primary { color: var(--cf-primary-text) !important; }
        .text-secondary { color: var(--cf-secondary-text) !important; }

        .alert-light,
        .bg-light,
        .bg-light-subtle {
            background: var(--cf-surface-soft) !important;
            color: var(--cf-text) !important;
            border-color: var(--cf-border) !important;
        }

        .text-dark,
        .text-body,
        .text-reset,
        .page-title-box .text-dark {
            color: var(--cf-text) !important;
        }

        .nav-tabs,
        .nav-tabs-custom {
            border-color: var(--cf-border) !important;
        }

        .nav-tabs .nav-link,
        .nav-tabs-custom .nav-link {
            color: var(--cf-text-muted) !important;
            border-color: transparent !important;
        }

        .nav-tabs .nav-link:hover,
        .nav-tabs-custom .nav-link:hover {
            color: var(--cf-link-strong) !important;
            border-color: var(--cf-border) var(--cf-border) transparent !important;
            background: var(--cf-surface-soft) !important;
        }

        .nav-tabs .nav-link.active,
        .nav-tabs-custom .nav-link.active {
            color: var(--cf-link-strong) !important;
            background: var(--cf-surface) !important;
            border-color: var(--cf-border) var(--cf-border) var(--cf-surface) !important;
        }

        .page-link {
            background: var(--cf-surface) !important;
            color: var(--cf-link) !important;
            border-color: var(--cf-border) !important;
        }

        .page-item.active .page-link {
            background: var(--cf-link) !important;
            color: #ffffff !important;
            border-color: var(--cf-link) !important;
        }

        .page-item.disabled .page-link,
        .disabled,
        :disabled {
            color: var(--cf-input-disabled-text) !important;
        }

        .border,
        .border-top,
        .border-bottom,
        .border-start,
        .border-end,
        .dropdown-divider {
            border-color: var(--cf-border) !important;
        }

        .logo-wordmark {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: .12em;
            white-space: nowrap;
        }

        .logo-chip {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: linear-gradient(135deg, #556ee6, #7b8dff);
            color: #fff;
            box-shadow: 0 12px 30px rgba(85, 110, 230, .28);
        }

        .header-item,
        .header-item .ui-icon,
        .shortcut-icon,
        .shortcut-icon .ui-icon {
            color: var(--cf-topbar-text) !important;
        }

        .header-item:hover,
        .header-item:focus,
        .shortcut-icon:hover,
        .shortcut-icon:focus {
            background: color-mix(in srgb, var(--cf-topbar-bg) 86%, #556ee6 14%) !important;
            color: var(--cf-topbar-text) !important;
        }

        .theme-toggle.is-dark,
        .theme-toggle.is-dark .ui-icon {
            color: #ffd76a !important;
        }

        .cf-launcher-head,
        .cf-launcher-foot {
            background: transparent !important;
        }

        .cf-sc-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 112px;
            padding: 16px 12px;
            border-radius: 14px;
            transition: transform .16s ease, background-color .16s ease;
        }

        .cf-sc-tile:hover {
            transform: translateY(-1px);
            background: var(--cf-surface-soft) !important;
        }

        .cf-sc-ic {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .cf-sc-ic.blue { background: rgba(85, 110, 230, .12); color: #556ee6; }
        .cf-sc-ic.violet { background: rgba(123, 97, 255, .12); color: #7b61ff; }
        .cf-sc-ic.green { background: rgba(47, 185, 138, .14); color: #2fb98a; }
        .cf-sc-ic.amber { background: rgba(255, 178, 74, .14); color: #ffb24a; }
        .cf-sc-ic.info { background: rgba(80, 165, 241, .14); color: #50a5f1; }
        .cf-sc-ic.slate { background: rgba(116, 120, 141, .16); color: #74788d; }
        .cf-sc-ic.rose { background: rgba(244, 106, 106, .14); color: #f46a6a; }

        .cf-sc-hot {
            position: absolute;
            top: 9px;
            right: 9px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #f46a6a;
            box-shadow: 0 0 0 4px rgba(244, 106, 106, .12);
        }

        .cf-page-panel {
            padding: 1rem;
            border: 1px solid var(--cf-border);
            border-radius: 18px;
            background: var(--cf-surface-soft);
        }

        .cf-page-panel .btn {
            min-height: 40px;
        }

        .cfx-ribbon,
        .cfx-welcome,
        .cfx-promo,
        .cfx-tip,
        .cfx-cust-bar,
        .cfx-plat-stack,
        .cfx-tab-badge {
            transition: background-color .2s ease, color .2s ease, border-color .2s ease;
        }

        html[data-theme="light"] .logo-dark,
        html[data-theme="dark"] .logo-light {
            display: inline-flex !important;
        }

        html[data-theme="light"] .logo-light,
        html[data-theme="dark"] .logo-dark {
            display: none !important;
        }

        @media (max-width: 991.98px) {
            .page-content {
                padding-top: 84px !important;
            }

            .logo-wordmark {
                font-size: .92rem;
                letter-spacing: .08em;
            }

            .cf-launcher-pop {
                width: min(92vw, 360px) !important;
            }
        }

        @media (max-width: 767.98px) {
            .page-title-box {
                gap: 1rem !important;
            }

            .page-title-box .btn,
            .cf-page-panel .btn {
                width: 100%;
            }

            .table-responsive {
                border-radius: 14px;
            }
        }
    </style>
    @stack('styles')
</head>
<body data-sidebar="dark">
    <div id="layout-wrapper">
        @include('layouts.header')
        @include('layouts.sidebar')
        <div class="main-content">
            <div class="page-content">
                <div id="mainContent" class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @yield('content')
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/metismenu@3.0.7/dist/metisMenu.min.js"></script>
    <script src="{{ asset('/assets/js/app.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
