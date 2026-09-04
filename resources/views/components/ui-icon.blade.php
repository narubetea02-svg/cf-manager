@props([
    'name',
    'size' => 20,
])

@php
    $icon = trim((string) $name);
@endphp

<span {{ $attributes->class(['ui-icon'])->merge(['style' => "display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;line-height:1;flex-shrink:0;width: {$size}px; height: {$size}px;"]) }}>
    @switch($icon)
        @case('menu-collapse')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"></path><path d="M20 6v12"></path><path d="M4 8h7"></path><path d="M4 16h7"></path></svg>
            @break
        @case('menu-sidebar')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M9 4v16"></path><path d="M13 9h4"></path><path d="M13 13h4"></path></svg>
            @break
        @case('grid')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="4" width="6" height="6" rx="1.5"></rect><rect x="14" y="4" width="6" height="6" rx="1.5"></rect><rect x="4" y="14" width="6" height="6" rx="1.5"></rect><rect x="14" y="14" width="6" height="6" rx="1.5"></rect></svg>
            @break
        @case('live')
        @case('broadcast')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 8a10 10 0 0 0 0 8"></path><path d="M19 8a10 10 0 0 1 0 8"></path><path d="M8 10.5a5.5 5.5 0 0 0 0 3"></path><path d="M16 10.5a5.5 5.5 0 0 1 0 3"></path><circle cx="12" cy="12" r="2.5"></circle></svg>
            @break
        @case('product')
        @case('product-alt')
        @case('cube')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8l8-4 8 4-8 4-8-4z"></path><path d="M4 8v8l8 4 8-4V8"></path><path d="M12 12v8"></path></svg>
            @break
        @case('order')
        @case('order-alt')
        @case('cart')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="19" r="1.5"></circle><circle cx="17" cy="19" r="1.5"></circle><path d="M3 4h2l2.2 10.5a1 1 0 0 0 1 .8h8.7a1 1 0 0 0 1-.8L20 8H7"></path></svg>
            @break
        @case('printer')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 8V4h10v4"></path><rect x="6" y="14" width="12" height="6" rx="1"></rect><path d="M6 17H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><path d="M17 12h.01"></path></svg>
            @break
        @case('search')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="M20 20l-3.5-3.5"></path></svg>
            @break
        @case('upload')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4"></path><path d="M7 9l5-5 5 5"></path><path d="M5 20h14"></path></svg>
            @break
        @case('download')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v12"></path><path d="M7 11l5 5 5-5"></path><path d="M5 20h14"></path></svg>
            @break
        @case('report')
        @case('promo')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19h16"></path><path d="M7 15V9"></path><path d="M12 15V5"></path><path d="M17 15v-3"></path></svg>
            @break
        @case('link')
        @case('integration')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13"></path><path d="M14 11a5 5 0 0 1 0 7L12.5 19.5a5 5 0 0 1-7-7L7 11"></path></svg>
            @break
        @case('settings')
        @case('cog')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3.5"></circle><path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 1 1-4 0v-.2a1 1 0 0 0-.7-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 1 1 0-4h.2a1 1 0 0 0 .9-.7 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4a2 2 0 1 1 4 0v.2a1 1 0 0 0 .7.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6H20a2 2 0 1 1 0 4h-.2a1 1 0 0 0-.9.7z"></path></svg>
            @break
        @case('truck')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 17H6a2 2 0 0 1-2-2V7h10v10h-1"></path><path d="M14 10h3l3 3v2h-2"></path><circle cx="8" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
            @break
        @case('money')
        @case('revenue')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2"></rect><circle cx="12" cy="12" r="2.5"></circle><path d="M7 9h.01"></path><path d="M17 15h.01"></path></svg>
            @break
        @case('deposit')
        @case('backpack')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 7V5a3 3 0 0 1 6 0v2"></path><path d="M6 9h12l1 10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2z"></path><path d="M8 9a4 4 0 0 0 8 0"></path></svg>
            @break
        @case('arrow-right')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>
            @break
        @case('help')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M9.1 9a3 3 0 1 1 4.9 2.3c-.9.7-2 1.3-2 2.7"></path><path d="M12 17h.01"></path></svg>
            @break
        @case('theme')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"></path></svg>
            @break
        @case('chevron-down')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>
            @break
        @case('transfer')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3h4v4"></path><path d="M21 3l-7 7"></path><path d="M7 21H3v-4"></path><path d="M3 21l7-7"></path><path d="M14 7H7a4 4 0 0 0-4 4v3"></path><path d="M10 17h7a4 4 0 0 0 4-4v-3"></path></svg>
            @break
        @case('store')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9l1.5-4h13L20 9"></path><path d="M4 9h16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"></path><path d="M6 13v6h12v-6"></path><path d="M10 19v-4h4v4"></path></svg>
            @break
        @case('power')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v9"></path><path d="M7 5.5a8 8 0 1 0 10 0"></path></svg>
            @break
        @case('tune')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h8"></path><path d="M16 6h4"></path><circle cx="14" cy="6" r="2"></circle><path d="M4 12h3"></path><path d="M11 12h9"></path><circle cx="9" cy="12" r="2"></circle><path d="M4 18h10"></path><path d="M18 18h2"></path><circle cx="16" cy="18" r="2"></circle></svg>
            @break
        @case('wallet')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 7V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"></path><path d="M18 7H8a2 2 0 0 0 0 4h12v6a2 2 0 0 1-2 2"></path><circle cx="16" cy="11" r="1"></circle></svg>
            @break
        @case('info')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 10v6"></path><path d="M12 7h.01"></path></svg>
            @break
        @case('map')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l-5 2V6l5-2 6 2 5-2v14l-5 2-6-2z"></path><path d="M9 4v14"></path><path d="M15 6v14"></path></svg>
            @break
        @case('messenger')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3C7 3 3 6.7 3 11.3c0 2.6 1.3 4.9 3.4 6.4V21l3.1-1.7c.8.2 1.6.3 2.5.3 5 0 9-3.7 9-8.3S17 3 12 3z"></path><path d="M8 13l3-3 2 2 3-3-3 5-2-2-3 1z"></path></svg>
            @break
        @case('ban')
        @case('block')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M8 8l8 8"></path></svg>
            @break
        @case('plus')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
            @break
        @case('user-circle')
        @case('customer')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"></circle><path d="M5 20a7 7 0 0 1 14 0"></path></svg>
            @break
        @case('check-circle')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M8.5 12.5l2.5 2.5 5-5"></path></svg>
            @break
        @case('facebook')
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.6 1.6-1.6h1.4V4.8c-.2 0-1.1-.1-2.2-.1-2.2 0-3.8 1.4-3.8 4V11H8v3h2.5v7h3z"></path></svg>
            @break
        @case('alert')
        @case('conflict')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l9 16H3z"></path><path d="M12 9v4"></path><circle cx="12" cy="17" r="1"></circle></svg>
            @break
        @case('coin-stack')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><ellipse cx="12" cy="6" rx="7" ry="3"></ellipse><path d="M5 6v4c0 1.7 3.1 3 7 3s7-1.3 7-3V6"></path><path d="M5 10v4c0 1.7 3.1 3 7 3s7-1.3 7-3v-4"></path><path d="M5 14v4c0 1.7 3.1 3 7 3s7-1.3 7-3v-4"></path></svg>
            @break
        @case('team')
        @case('group')
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M4 19a5 5 0 0 1 10 0"></path><path d="M14 18a4 4 0 0 1 6 0"></path></svg>
            @break
        @default
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
    @endswitch
</span>
