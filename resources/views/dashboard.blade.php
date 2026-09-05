@extends('layouts.admin')
@section('title', 'สถิติ')
@push('styles')
<style>
    .cfx {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .cfx-ribbon,
    .cfx-welcome,
    .cfx-promo,
    .cfx-card,
    .cfx-kpi,
    .cfx-tip {
        background: var(--cf-surface);
        border: 1px solid var(--cf-border);
        border-radius: 20px;
        box-shadow: var(--cf-shadow-soft);
    }

    .cfx-ribbon {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--cf-surface) 88%, #556ee6 12%), var(--cf-surface));
    }

    .cfx-top-split {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(320px, 1fr);
        gap: 16px;
    }

    .cfx-promo {
        position: relative;
        overflow: hidden;
        min-height: 180px;
        padding: 22px;
        background: linear-gradient(135deg, #556ee6, #7b8dff);
        color: #fff !important;
    }

    .cfx-promo * { color: inherit !important; }
    .cfx-promo-glow {
        position: absolute;
        inset: auto -60px -70px auto;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: rgba(255,255,255,.12);
        filter: blur(4px);
    }

    .cfx-promo-ic {
        width: 56px;
        height: 56px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        margin-bottom: 14px;
    }

    .cfx-promo-eyebrow { font-size: .75rem; letter-spacing: .18em; font-weight: 700; opacity: .85; }
    .cfx-promo-title { font-size: 1.5rem; font-weight: 700; margin-top: .25rem; }
    .cfx-promo-sub { color: rgba(255,255,255,.88); margin-top: .5rem; max-width: 40rem; }
    .cfx-promo-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; position: relative; z-index: 1; }
    .cfx-cta,
    .cfx-promo-hide,
    .cfx-btn,
    .cfx-tab,
    .cfx-mini-seg button,
    .cfx-seg button {
        border: 0;
        border-radius: 999px;
        min-height: 42px;
        padding: 0 16px;
        font-weight: 600;
        transition: transform .15s ease, background-color .15s ease, color .15s ease, border-color .15s ease;
    }

    .cfx-cta { display: inline-flex; align-items: center; justify-content: center; background: #fff; color: #556ee6 !important; text-decoration: none; }
    .cfx-promo-hide { background: rgba(255,255,255,.16); color: #fff; }
    .cfx-welcome { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; }
    .cfx-wc-av, .cfx-av { width: 44px; height: 44px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; background: var(--cf-surface-soft); font-weight: 700; }
    .cfx-wc-hi { font-size: .85rem; color: var(--cf-text-muted); }
    .cfx-wc-name { font-size: 1.4rem; font-weight: 700; }
    .cfx-seg, .cfx-mini-seg { display: flex; flex-wrap: wrap; gap: 8px; }
    .cfx-seg button, .cfx-mini-seg button, .cfx-seg a { background: var(--cf-surface-soft); color: var(--cf-text); border: 1px solid var(--cf-border); }
    .cfx-seg button.on, .cfx-mini-seg button.on, .cfx-seg a.on { background: #556ee6; color: #fff; border-color: #556ee6; }
    .cfx-seg a { display: inline-flex; align-items: center; justify-content: center; text-decoration: none; }
    .cfx-arrange-bar { display: flex; justify-content: flex-end; }
    .cfx-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--cf-surface); color: var(--cf-text); border: 1px solid var(--cf-border); box-shadow: var(--cf-shadow-soft); }
    .cfx-btn:hover, .cfx-cta:hover, .cfx-promo-hide:hover, .cfx-tab:hover, .cfx-seg button:hover, .cfx-mini-seg button:hover { transform: translateY(-1px); }
    .cfx-dash { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 16px; }
    .cfx-col-3 { grid-column: span 3; }
    .cfx-col-4 { grid-column: span 4; }
    .cfx-col-8 { grid-column: span 8; }
    .cfx-col-12 { grid-column: span 12; }
    .cfx-kpi, .cfx-card { height: 100%; padding: 18px; }
    .cfx-kpi-top, .cfx-card-head, .cfx-rank-row, .cfx-prod-row, .cfx-cust-top, .cfx-plat-item { display: flex; align-items: center; gap: 12px; }
    .cfx-kpi-top { margin-bottom: 14px; }
    .cfx-kpi-ic { width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; color: #fff; }
    .cfx-kpi-ic.green { background: linear-gradient(135deg, #2fb98a, #29a476); }
    .cfx-kpi-ic.blue { background: linear-gradient(135deg, #556ee6, #6c7ef0); }
    .cfx-kpi-ic.amber { background: linear-gradient(135deg, #f1b44c, #f8c86f); }
    .cfx-kpi-ic.violet { background: linear-gradient(135deg, #7b6ef0, #9b93f5); }
    .cfx-kpi-label { font-weight: 600; color: var(--cf-text-muted); }
    .cfx-kpi-val { font-size: 2rem; line-height: 1.05; font-weight: 800; }
    .cfx-kpi-all { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--cf-border); color: var(--cf-text-muted); }
    .cfx-kpi-foot { display: flex; gap: 8px; margin-top: 10px; color: var(--cf-text-muted); font-size: .9rem; }
    .cfx-delta.up { color: #2fb98a; font-weight: 700; }
    .cfx-card-head { justify-content: space-between; margin-bottom: 14px; }
    .cfx-card-head h3 { margin: 0; font-size: 1.05rem; font-weight: 700; }
    .cfx-card-head .sub { color: var(--cf-text-muted); font-size: .9rem; }
    .cfx-card-body { color: var(--cf-text); }
    .cfx-rank-row, .cfx-prod-row { justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--cf-table-row-border); }
    .cfx-rank-row:last-child, .cfx-prod-row:last-child { border-bottom: 0; }
    .cfx-rk { width: 28px; text-align: center; font-weight: 700; color: var(--cf-text-muted); }
    .cfx-rn { flex: 1; }
    .cfx-rv { font-weight: 700; white-space: nowrap; }
    .cfx-prod-mid { flex: 1; }
    .cfx-prod-name { font-weight: 600; margin-bottom: 8px; }
    .cfx-prod-bar, .cfx-cust-bar, .cfx-plat-stack { width: 100%; height: 10px; background: var(--cf-surface-soft); border-radius: 999px; overflow: hidden; }
    .cfx-prod-bar span, .cfx-cust-bar span, .cfx-plat-stack span { display: block; height: 100%; border-radius: inherit; }
    .cfx-prod-val { text-align: right; }
    .cfx-prod-qty { font-size: 1.1rem; font-weight: 800; }
    .cfx-prod-rev { color: var(--cf-text-muted); font-size: .92rem; }
    .cfx-cust-row + .cfx-cust-row { margin-top: 16px; }
    .cfx-cust-top { justify-content: space-between; margin-bottom: 8px; }
    .cfx-cust-v { font-weight: 700; }
    .cfx-cust-pct { margin-left: 8px; color: var(--cf-text-muted); }
    .cfx-cust-banned { margin: 16px 0; padding: 12px 14px; border-radius: 16px; background: rgba(244,106,106,.12); color: #f46a6a; display: flex; align-items: center; gap: 8px; }
    .cfx-plat-title { font-weight: 700; margin-bottom: 10px; }
    .cfx-plat-stack { display: flex; height: 14px; }
    .cfx-plat-list { display: grid; gap: 8px; margin-top: 12px; }
    .cfx-plat-item { justify-content: space-between; padding: 10px 12px; border: 1px solid var(--cf-border); border-radius: 14px; background: var(--cf-surface-soft); }
    .cfx-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
    .cfx-tab { background: var(--cf-surface-soft); color: var(--cf-text); border: 1px solid var(--cf-border); display: inline-flex; align-items: center; gap: 8px; }
    .cfx-tab.on { background: #556ee6; color: #fff; border-color: #556ee6; }
    .cfx-tab-badge { background: rgba(255,255,255,.16); border-radius: 999px; padding: 2px 8px; font-size: .78rem; }
    .cfx-tbl { width: 100%; }
    .cfx-tbl thead th { padding: 12px 10px; border-bottom: 1px solid var(--cf-table-row-border); color: var(--cf-text-muted); font-weight: 700; }
    .cfx-tbl tbody td { padding: 14px 10px; border-bottom: 1px solid var(--cf-table-row-border); vertical-align: middle; }
    .cfx-tag { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 999px; font-size: .82rem; font-weight: 700; }
    .cfx-tag.paid { background: rgba(47,185,138,.14); color: #2fb98a; }
    .cfx-tag.hold { background: rgba(241,180,76,.16); color: #f1b44c; }
    .cfx-tag.delivered { background: rgba(85,110,230,.14); color: #556ee6; }
    .cfx-tag.cancel { background: rgba(244,106,106,.14); color: #f46a6a; }
    .cfx-ord-cust { display: flex; align-items: center; gap: 10px; }
    .cfx-loadmore { display: flex; justify-content: center; margin-top: 16px; }
    .cfx-loadmore button { background: var(--cf-surface-soft); border: 1px solid var(--cf-border); color: var(--cf-text); border-radius: 999px; min-height: 42px; padding: 0 18px; }
    .cfx-chart { display: grid; gap: 14px; }
    .cfx-chart-row { display: grid; gap: 8px; }
    .cfx-chart-meta { display: flex; justify-content: space-between; gap: 12px; color: var(--cf-text-muted); font-size: .9rem; }
    .cfx-chart-bar { height: 14px; background: var(--cf-surface-soft); border-radius: 999px; overflow: hidden; }
    .cfx-chart-bar span { display:block; height:100%; border-radius: inherit; background: linear-gradient(90deg, #556ee6, #7b8dff); }
    .cfx-tip { display: flex; align-items: flex-start; gap: 10px; padding: 14px; margin-top: 14px; }
    .cfx-skel { color: var(--cf-text-muted); text-align: center; padding: 18px 0; }

    @media (max-width: 1199.98px) {
        .cfx-top-split, .cfx-dash { grid-template-columns: repeat(12, minmax(0, 1fr)); }
        .cfx-col-8 { grid-column: span 12; }
        .cfx-col-4, .cfx-col-3, .cfx-col-12 { grid-column: span 6; }
    }

    @media (max-width: 767.98px) {
        .cfx-ribbon, .cfx-welcome, .cfx-card-head { flex-direction: column; align-items: flex-start; }
        .cfx-top-split, .cfx-dash { grid-template-columns: 1fr; }
        .cfx-col-3, .cfx-col-4, .cfx-col-8, .cfx-col-12 { grid-column: span 12; }
        .cfx-seg, .cfx-mini-seg, .cfx-tabs, .cfx-promo-actions { width: 100%; }
        .cfx-seg button, .cfx-seg a, .cfx-mini-seg button, .cfx-tab, .cfx-cta, .cfx-promo-hide, .cfx-btn, .cfx-loadmore button { width: 100%; justify-content: center; }
        .cfx-promo { min-height: auto; }
        .cfx-kpi-val { font-size: 1.6rem; }
    }
</style>
@endpush

@php
    $customerPhonePct = $customersTotal > 0 ? round(($customersWithPhone / $customersTotal) * 100) : 0;
    $broadcastPct = $customersTotal > 0 ? round(($broadcastCustomers / $customersTotal) * 100) : 0;
    $tiktokPct = $customersTotal > 0 ? round(($connectedTiktokCustomers / $customersTotal) * 100) : 0;
    $platformTotal = collect($platformBreakdown)->sum('count');
    $chartMax = max(1, $dailySales->max('total'));
    $rangeDeltaLabel = is_null($revenueDelta)
        ? 'ยังไม่มีข้อมูลช่วงก่อน'
        : (($revenueDelta >= 0 ? '+' : '') . number_format($revenueDelta, 1) . '% เทียบช่วงก่อน');
@endphp

@section('content')
<div class="cfx">
    <div class="cfx-welcome">
            <div class="cfx-wc-av">N</div>
            <div>
                <div class="cfx-wc-hi">ยินดีต้อนรับกลับมา</div>
                <div class="cfx-wc-name">{{ auth()->user()->name }}</div>
            </div>
            <div class="cfx-seg" aria-label="เลือกช่วงเวลาสถิติ">
                <a class="{{ $range === 'today' ? 'on' : '' }}" href="{{ url('/dashboard?range=today') }}">วันนี้</a>
                <a class="{{ $range === '7d' ? 'on' : '' }}" href="{{ url('/dashboard?range=7d') }}">7 วัน</a>
                <a class="{{ $range === 'month' ? 'on' : '' }}" href="{{ url('/dashboard?range=month') }}">เดือนนี้</a>
                <a class="{{ $range === '30d' ? 'on' : '' }}" href="{{ url('/dashboard?range=30d') }}">30 วัน</a>
                <a href="{{ url('/reports') }}">กำหนดเอง</a>
            </div>
    </div>

    <div class="cfx-arrange-bar">
        <span class="small text-muted me-2 align-self-center">ช่วงข้อมูล: {{ $rangeLabel }}</span>
        <a class="cfx-btn text-decoration-none" href="#dashboard-cards"><x-ui-icon name="tune" class="me-1" size="18" /> จัดเรียงการ์ด</a>
    </div>

    <div class="cfx-dash" id="dashboard-cards">
        <div class="cfx-col-3">
            <div class="cfx-kpi">
                <div class="cfx-kpi-top">
                    <div class="cfx-kpi-ic green"><x-ui-icon name="wallet" size="22" /></div>
                    <div class="cfx-kpi-label">รายรับ (ไม่รวมค่าส่ง)</div>
                </div>
                <div class="cfx-kpi-val num">{{ number_format($revenueNoShipping, 0) }}<span class="u">฿</span></div>
                <div class="cfx-kpi-all">
                    <span class="cfx-kpi-all-lbl">รวมทุกสถานะ</span>
                    <span class="cfx-kpi-allnum num">{{ number_format($allStatusesRevenue, 0) }} ฿</span>
                </div>
                <div class="cfx-kpi-foot">
                    <span class="cfx-delta up">{{ $rangeDeltaLabel }}</span>
                </div>
            </div>
        </div>

        <div class="cfx-col-3">
            <div class="cfx-kpi">
                <div class="cfx-kpi-top">
                    <div class="cfx-kpi-ic blue"><x-ui-icon name="money" size="22" /></div>
                    <div class="cfx-kpi-label">รายรับ (รวมค่าส่ง)</div>
                </div>
                <div class="cfx-kpi-val num">{{ number_format($totalRevenue, 0) }}<span class="u">฿</span></div>
                <div class="cfx-kpi-all">
                    <span class="cfx-kpi-all-lbl">รวมทุกสถานะ</span>
                    <span class="cfx-kpi-allnum num">{{ number_format($allStatusesRevenue, 0) }} ฿</span>
                </div>
                <div class="cfx-kpi-foot">
                    <span class="cfx-delta up">{{ $rangeDeltaLabel }}</span>
                </div>
            </div>
        </div>

        <div class="cfx-col-3">
            <div class="cfx-kpi">
                <div class="cfx-kpi-top">
                    <div class="cfx-kpi-ic amber"><x-ui-icon name="product" size="22" /></div>
                    <div class="cfx-kpi-label">ค่าส่งที่ได้รับ</div>
                </div>
                <div class="cfx-kpi-val num">{{ number_format($shippingIncome, 0) }}<span class="u">฿</span></div>
                <div class="cfx-kpi-all">
                    <span class="cfx-kpi-all-lbl">รวมทุกสถานะ</span>
                    <span class="cfx-kpi-allnum num">{{ number_format($shippingIncome, 0) }} ฿</span>
                </div>
                <div class="cfx-kpi-foot">
                    <span class="cfx-delta up">ยังไม่มีข้อมูลค่าส่งแยกจากยอดสินค้า</span>
                </div>
            </div>
        </div>

        <div class="cfx-col-3">
            <div class="cfx-kpi" data-kpi="sold">
                <div class="cfx-kpi-top">
                    <div class="cfx-kpi-ic violet"><x-ui-icon name="cart" size="22" /></div>
                    <div class="cfx-kpi-label">ขายแล้ว</div>
                </div>
                <div class="cfx-kpi-val num">{{ number_format($soldQty, 0) }} ชิ้น / {{ number_format($soldHomes, 0) }} บ้าน</div>
                <div class="cfx-kpi-all">
                    <span class="cfx-kpi-all-lbl">รวมทุกสถานะ</span>
                    <span class="cfx-kpi-allnum num">{{ number_format($totalOrders, 0) }} ออเดอร์</span>
                </div>
                <div class="cfx-kpi-foot">
                    <span class="cfx-delta up">{{ $totalOrders ? number_format($totalOrders) . ' ออเดอร์ในช่วงนี้' : 'ยังไม่มีออเดอร์ในช่วงนี้' }}</span>
                </div>
            </div>
        </div>

        <div class="cfx-col-8">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>ยอดขายรายวัน</h3>
                        <div class="sub">{{ $rangeLabel }} · นับเฉพาะสถานะที่ขายแล้ว</div>
                    </div>
                    <div class="ms-auto cfx-mini-seg">
                        <a class="cfx-tab on text-decoration-none" href="{{ url('/dashboard?range=7d') }}">7 วัน</a>
                    </div>
                </div>
                <div class="cfx-card-body">
                    <div class="cfx-chart">
                        @foreach($dailySales as $item)
                            @php $width = round(($item['total'] / $chartMax) * 100); @endphp
                            <div class="cfx-chart-row">
                                <div class="cfx-chart-meta">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="num">{{ number_format($item['total'], 0) }} ฿</span>
                                </div>
                                <div class="cfx-chart-bar"><span style="width: {{ max(8, $width) }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="cfx-col-4">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>สถานะระบบ</h3>
                        <div class="sub">แทนส่วนเครดิตที่ตัดออก</div>
                    </div>
                </div>
                <div class="cfx-card-body">
                    <div class="cfx-rank-row">
                        <div class="cfx-rk">1</div>
                        <div class="cfx-av">L</div>
                        <div class="cfx-rn">ไลฟ์ที่กำลังทำงาน</div>
                        <div class="cfx-rv num">{{ $activeStreams }}</div>
                    </div>
                    <div class="cfx-rank-row">
                        <div class="cfx-rk">2</div>
                        <div class="cfx-av">S</div>
                        <div class="cfx-rn">ร้านค้าที่เชื่อม</div>
                        <div class="cfx-rv num">{{ $shops->count() }}</div>
                    </div>
                    <div class="cfx-rank-row">
                        <div class="cfx-rk">3</div>
                        <div class="cfx-av">P</div>
                        <div class="cfx-rn">สินค้าในระบบ</div>
                        <div class="cfx-rv num">{{ $totalProducts }}</div>
                    </div>
                    <div class="cfx-tip">
                        <x-ui-icon name="info" size="18" />
                        <div>หน้าสรุปนี้ใช้โครงแบบต้นฉบับ แต่ปิดส่วนเติมเครดิตตามที่คุณต้องการไว้</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cfx-col-4">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>ลูกค้ายอดซื้อสูงสุด</h3>
                    </div>
                    <div class="ms-auto cfx-mini-seg">
                        <button class="on" type="button" onclick="window.location.href='{{ url('/dashboard?revenue=net') }}'">ไม่รวมค่าส่ง</button>
                        <button type="button" onclick="window.location.href='{{ url('/dashboard?revenue=gross') }}'">รวมค่าส่ง</button>
                    </div>
                </div>
                <div class="cfx-card-body">
                    @forelse($topCustomers as $index => $customer)
                        <div class="cfx-rank-row {{ $index < 3 ? 'top3' : '' }}">
                            <div class="cfx-rk num">{{ $index + 1 }}</div>
                            <div class="cfx-av">{{ mb_substr($customer->display_name ?: 'C', 0, 1) }}</div>
                            <div class="cfx-rn">{{ $customer->display_name ?: ($customer->customer_username ?: '-') }}</div>
                            <div class="cfx-rv num">{{ number_format($customer->total_spent, 0) }} ฿</div>
                        </div>
                    @empty
                        <div class="cfx-skel">ยังไม่มีข้อมูลในช่วงนี้</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="cfx-col-4">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>สินค้าขายดี</h3>
                    </div>
                    <div class="ms-auto cfx-mini-seg">
                        <button class="on" type="button" onclick="window.location.href='{{ url('/dashboard?top_products=qty') }}'">ตามจำนวนชิ้น</button>
                        <button type="button" onclick="window.location.href='{{ url('/dashboard?top_products=revenue') }}'">ตามราคาที่ขายได้</button>
                    </div>
                </div>
                <div class="cfx-card-body">
                    @forelse($topProducts as $index => $product)
                        <div class="cfx-prod-row {{ $index < 3 ? 'top3' : '' }}">
                            <div class="cfx-prod-rk num">{{ $index + 1 }}</div>
                            <div class="cfx-prod-mid">
                                <div class="cfx-prod-name">{{ $product->name }}</div>
                                <div class="cfx-prod-bar"><span style="width: {{ max(12, min(100, ($topProducts->first()->qty ?? 1) > 0 ? ($product->qty / $topProducts->first()->qty) * 100 : 0)) }}%"></span></div>
                            </div>
                            <div class="cfx-prod-val">
                                <div class="cfx-prod-qty num">{{ number_format($product->qty, 0) }}<span class="cfx-prod-qtyu">ชิ้น</span></div>
                                <div class="cfx-prod-rev num">{{ number_format($product->revenue, 0) }} ฿</div>
                            </div>
                        </div>
                    @empty
                        <div class="cfx-skel">ยังไม่มีข้อมูลในช่วงนี้</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="cfx-col-4">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>ข้อมูลลูกค้า</h3>
                    </div>
                </div>
                <div class="cfx-card-body">
                    <div class="cfx-cust-row">
                        <div class="cfx-cust-top">
                            <div class="cfx-cust-k"><x-ui-icon name="map" class="me-1" size="16" />กรอกที่อยู่แล้ว</div>
                            <div class="cfx-cust-v"><b class="num">{{ number_format($customersWithPhone, 0) }}</b> คน<span class="cfx-cust-pct">{{ $customerPhonePct }}%</span></div>
                        </div>
                        <div class="cfx-cust-bar"><span style="width: {{ $customerPhonePct }}%; background:#556ee6;"></span></div>
                    </div>
                    <div class="cfx-cust-row">
                        <div class="cfx-cust-top">
                            <div class="cfx-cust-k"><x-ui-icon name="broadcast" class="me-1" size="16" />รับบรอดแคสต์</div>
                            <div class="cfx-cust-v"><b class="num">{{ number_format($broadcastCustomers, 0) }}</b> คน<span class="cfx-cust-pct">{{ $broadcastPct }}%</span></div>
                        </div>
                        <div class="cfx-cust-bar"><span style="width: {{ $broadcastPct }}%; background:#2fb98a;"></span></div>
                    </div>
                    <div class="cfx-cust-row">
                        <div class="cfx-cust-top">
                            <div class="cfx-cust-k"><x-ui-icon name="messenger" class="me-1" size="16" />เชื่อม Messenger แล้ว</div>
                            <div class="cfx-cust-v"><b class="num">{{ number_format($connectedTiktokCustomers, 0) }}</b> คน<span class="cfx-cust-pct">{{ $tiktokPct }}%</span></div>
                        </div>
                        <div class="cfx-cust-bar"><span style="width: {{ $tiktokPct }}%; background:#7b6ef0;"></span></div>
                    </div>
                    <div class="cfx-cust-banned"><x-ui-icon name="block" class="me-1" size="16" />ถูกแบน <b class="num">{{ $bannedCustomers }}</b> คน</div>

                    <div class="cfx-plat-wrap">
                        <div class="cfx-plat-title">ช่องทางลูกค้า</div>
                        <div class="cfx-plat-stack">
                            @foreach($platformBreakdown as $platform)
                                <span style="width: {{ $platformTotal > 0 ? ($platform['count'] / $platformTotal) * 100 : 0 }}%; background:
                                    {{ $loop->first ? '#556ee6' : ($loop->iteration === 2 ? '#2fb98a' : '#f1b44c') }};"></span>
                            @endforeach
                        </div>
                        <div class="cfx-plat-list">
                            @foreach($platformBreakdown as $platform)
                                <div class="cfx-plat-item">
                                    <span>{{ $platform['label'] }}</span>
                                    <span class="num">{{ number_format($platform['count'], 0) }}</span>
                                    <span class="pct">{{ $platformTotal > 0 ? round(($platform['count'] / $platformTotal) * 100, 1) : 0 }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cfx-col-12">
            <div class="cfx-card">
                <div class="cfx-card-head">
                    <div>
                        <h3>คำสั่งซื้อล่าสุด</h3>
                    </div>
                    <div class="ms-auto cfx-tabs">
                        <button class="cfx-tab on" type="button" onclick="window.location.href='{{ url('/orders') }}'">ล่าสุด</button>
                        <button class="cfx-tab" type="button" onclick="window.location.href='{{ url('/orders?type=to_ship') }}'">ต้องจัดส่ง <span class="cfx-tab-badge">{{ $ordersByStatus['to_ship'] }}</span></button>
                        <button class="cfx-tab" type="button" onclick="window.location.href='{{ url('/orders?type=wait_payment') }}'">ฝากยอด</button>
                        <button class="cfx-tab" type="button" onclick="window.location.href='{{ url('/orders?type=hold') }}'">ฝากของ</button>
                    </div>
                </div>
                <div class="cfx-card-body">
                    <div class="table-responsive">
                        <table class="cfx-tbl">
                            <thead>
                                <tr>
                                    <th>คำสั่งซื้อ</th>
                                    <th>ลูกค้า</th>
                                    <th class="ta-r">ยอดสุทธิ</th>
                                    <th class="ta-r">ชำระแล้ว</th>
                                    <th>วันที่</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($latestOrders as $order)
                                    @php
                                        $tagClass = match($order->status) {
                                            'delivered' => 'delivered',
                                            'hold' => 'hold',
                                            'cancelled', 'merchant_cancel', 'customer_cancel' => 'cancel',
                                            default => 'paid',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="cfx-ord-id">{{ $order->code ?: ('#' . $order->id) }}</td>
                                        <td>
                                            <div class="cfx-ord-cust">
                                                <div class="cfx-av">{{ mb_substr($order->customer_name ?? $order->customer_username ?? 'C', 0, 1) }}</div>
                                                <span>{{ \Illuminate\Support\Str::limit($order->customer_name ?? $order->customer_username ?? '-', 28) }}</span>
                                            </div>
                                        </td>
                                        <td class="ta-r cfx-ord-net num">{{ number_format($order->total_price, 0) }} ฿</td>
                                        <td class="ta-r cfx-ord-paid num">{{ number_format($order->total_price, 0) }}</td>
                                        <td class="cfx-ord-date">{{ optional($order->created_at)->format('d/m H:i') }}</td>
                                        <td><span class="cfx-tag {{ $tagClass }}">{{ \App\Support\OrderStatus::label($order->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="cfx-skel">ยังไม่มีออเดอร์</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="cfx-loadmore">
                        <button type="button" onclick="window.location.href='{{ url('/orders') }}'">ดูเพิ่มเติม <x-ui-icon name="arrow-right" class="ms-1" size="16" /></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
