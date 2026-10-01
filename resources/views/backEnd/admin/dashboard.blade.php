@extends('backEnd.layouts.master')
@section('title','Dashboard')

@section('css')
<!-- Plugins css -->
<link href="{{asset('backEnd/assets/libs/flatpickr/flatpickr.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/selectize/css/selectize.bootstrap3.css')}}" rel="stylesheet" type="text/css" />

<style>
/* ==========================================================================
   MODERN E-COMMERCE DASHBOARD DESIGN SYSTEM (MondolShopBD)
   ========================================================================== */
:root {
    --m-primary:          #059669;
    --m-primary-soft:     #ecfdf5;
    --m-primary-border:   #a7f3d0;
    --m-secondary:        #0284c7;
    --m-secondary-soft:   #f0f9ff;
    --m-secondary-border: #bae6fd;
    --m-accent:           #6366f1;
    --m-accent-soft:      #eef2ff;
    --m-accent-border:    #c7d2fe;
    --m-warning:          #f59e0b;
    --m-warning-soft:     #fffbeb;
    --m-warning-border:   #fde68a;
    --m-danger:           #ef4444;
    --m-danger-soft:      #fef2f2;
    --m-danger-border:    #fecaca;
    --m-info:             #0ea5e9;
    --m-info-soft:        #f0f9ff;
    --m-info-border:      #a5f3fc;
    --m-purple:           #8b5cf6;
    --m-purple-soft:      #f3e8ff;
    --m-purple-border:    #d8b4fe;
    --m-slate:            #475569;
    --m-slate-soft:       #f8fafc;
    --m-slate-border:     #e2e8f0;

    --m-card-bg:          #ffffff;
    --m-card-border:      #e2e8f0;
    --m-text-main:        #0f172a;
    --m-text-body:        #334155;
    --m-text-muted:       #64748b;
    --m-radius:           14px;
    --m-radius-sm:        10px;
    --m-radius-pill:      9999px;
    --m-shadow-sm:        0 1px 3px rgba(15,23,42,0.04), 0 1px 2px rgba(15,23,42,0.02);
    --m-shadow-md:        0 4px 12px rgba(15,23,42,0.06), 0 2px 4px rgba(15,23,42,0.02);
    --m-shadow-hover:     0 12px 24px -4px rgba(15,23,42,0.08), 0 4px 8px -2px rgba(15,23,42,0.03);
}

/* 1. Welcome Greeting Banner */
.dash-welcome-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #064e3b 100%);
    border-radius: var(--m-radius);
    padding: 24px 28px;
    margin-bottom: 24px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(15,23,42,0.3);
    border: 1px solid rgba(255,255,255,0.08);
}
.dash-welcome-card::before {
    content: "";
    position: absolute;
    top: -80px;
    right: -80px;
    width: 280px;
    height: 280px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.28) 0%, rgba(16, 185, 129, 0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.dash-welcome-title {
    font-size: 1.45rem;
    font-weight: 700;
    margin-bottom: 6px;
    letter-spacing: -0.02em;
    color: #ffffff;
}
.dash-welcome-subtitle {
    font-size: 0.88rem;
    color: #94a3b8;
    margin-bottom: 0;
    line-height: 1.5;
}
.dash-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 14px;
    border-radius: var(--m-radius-pill);
    font-size: 0.8rem;
    font-weight: 600;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: #34d399;
    backdrop-filter: blur(4px);
}
.dash-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #34d399;
    box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
    animation: dashPulse 2s infinite;
}
@keyframes dashPulse {
    0%   { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7); }
    70%  { box-shadow: 0 0 0 9px rgba(52, 211, 153, 0); }
    100% { box-shadow: 0 0 0 0 rgba(52, 211, 153, 0); }
}
.dash-banner-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: var(--m-radius-sm);
    font-size: 0.84rem;
    font-weight: 600;
    text-decoration: none !important;
    transition: all 0.2s ease;
}
.dash-banner-btn-primary {
    background: #059669;
    color: #ffffff !important;
    border: 1px solid #047857;
    box-shadow: 0 4px 12px rgba(5,150,105,0.35);
}
.dash-banner-btn-primary:hover {
    background: #047857;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(5,150,105,0.45);
}
.dash-banner-btn-ghost {
    background: rgba(255,255,255,0.12);
    color: #ffffff !important;
    border: 1px solid rgba(255,255,255,0.24);
    backdrop-filter: blur(4px);
}
.dash-banner-btn-ghost:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

/* 2. Modern KPI Metric Cards */
.dash-kpi-card {
    background: var(--m-card-bg);
    border: 1px solid var(--m-card-border);
    border-radius: var(--m-radius);
    padding: 20px 22px;
    margin-bottom: 24px;
    box-shadow: var(--m-shadow-sm);
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    height: calc(100% - 24px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.dash-kpi-card::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--card-accent, var(--m-primary));
    opacity: 0.9;
}
.dash-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--m-shadow-hover);
    border-color: #cbd5e1;
}
.dash-kpi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.dash-kpi-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 21px;
    transition: transform 0.2s ease;
}
.dash-kpi-card:hover .dash-kpi-icon-wrap {
    transform: scale(1.08);
}
.dash-kpi-card--emerald { --card-accent: var(--m-primary); }
.dash-kpi-card--emerald .dash-kpi-icon-wrap { background: var(--m-primary-soft); color: var(--m-primary); border: 1px solid var(--m-primary-border); }

.dash-kpi-card--blue { --card-accent: var(--m-secondary); }
.dash-kpi-card--blue .dash-kpi-icon-wrap { background: var(--m-secondary-soft); color: var(--m-secondary); border: 1px solid var(--m-secondary-border); }

.dash-kpi-card--indigo { --card-accent: var(--m-accent); }
.dash-kpi-card--indigo .dash-kpi-icon-wrap { background: var(--m-accent-soft); color: var(--m-accent); border: 1px solid var(--m-accent-border); }

.dash-kpi-card--amber { --card-accent: var(--m-warning); }
.dash-kpi-card--amber .dash-kpi-icon-wrap { background: var(--m-warning-soft); color: var(--m-warning); border: 1px solid var(--m-warning-border); }

.dash-kpi-card--rose { --card-accent: var(--m-danger); }
.dash-kpi-card--rose .dash-kpi-icon-wrap { background: var(--m-danger-soft); color: var(--m-danger); border: 1px solid var(--m-danger-border); }

.dash-kpi-card--purple { --card-accent: var(--m-purple); }
.dash-kpi-card--purple .dash-kpi-icon-wrap { background: var(--m-purple-soft); color: var(--m-purple); border: 1px solid var(--m-purple-border); }

.dash-kpi-card--slate { --card-accent: var(--m-slate); }
.dash-kpi-card--slate .dash-kpi-icon-wrap { background: var(--m-slate-soft); color: var(--m-slate); border: 1px solid var(--m-slate-border); }

.dash-kpi-badge {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: var(--m-radius-pill);
    letter-spacing: 0.3px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dash-kpi-badge-success { background: #ecfdf5; color: #047857; }
.dash-kpi-badge-warning { background: #fffbeb; color: #b45309; }
.dash-kpi-badge-danger  { background: #fef2f2; color: #b91c1c; }
.dash-kpi-badge-info    { background: #f0f9ff; color: #0369a1; }
.dash-kpi-badge-neutral { background: #f1f5f9; color: #475569; }

.dash-kpi-label {
    font-size: 0.81rem;
    font-weight: 600;
    color: var(--m-text-muted);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.dash-kpi-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--m-text-main);
    line-height: 1.15;
    margin-bottom: 8px;
    font-variant-numeric: tabular-nums;
}
.dash-kpi-link {
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--card-accent, var(--m-primary));
    transition: gap 0.2s ease, opacity 0.2s ease;
    opacity: 0.9;
}
.dash-kpi-link:hover {
    gap: 7px;
    opacity: 1;
}

/* 2.1 Courier & Delivery Pipeline Card (Screenshot Matched) */
.dash-courier-card {
    background: linear-gradient(145deg, #0b132b 0%, #0f172a 60%, #111a3b 100%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--m-radius);
    padding: 22px 20px;
    margin-bottom: 24px;
    box-shadow: 0 10px 25px -5px rgba(11, 19, 43, 0.5);
    height: calc(100% - 24px);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.dash-courier-card::before {
    content: "";
    position: absolute;
    top: -60px;
    right: -60px;
    width: 180px;
    height: 180px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.dash-courier-header {
    margin-bottom: 16px;
}
.dash-courier-title {
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: -0.01em;
}
.dash-courier-subtitle {
    color: #94a3b8;
    font-size: 0.82rem;
    margin-bottom: 0;
}
.dash-courier-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
}
.dash-courier-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    text-decoration: none !important;
    transition: all 0.22s ease;
}
.dash-courier-row:hover {
    background: rgba(30, 41, 59, 0.9);
    transform: translateX(4px);
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3);
}

.dash-courier-row--pending { border-color: rgba(251, 191, 36, 0.25); }
.dash-courier-row--pending:hover { border-color: #fbbf24; }
.dash-courier-row--pending .dash-courier-row-label { color: #fbbf24; }
.dash-courier-row--pending .dash-courier-pill { background: rgba(251, 191, 36, 0.12); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.35); }

.dash-courier-row--processing { border-color: rgba(56, 189, 248, 0.25); }
.dash-courier-row--processing:hover { border-color: #38bdf8; }
.dash-courier-row--processing .dash-courier-row-label { color: #38bdf8; }
.dash-courier-row--processing .dash-courier-pill { background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); }

.dash-courier-row--shipped { border-color: rgba(192, 132, 252, 0.25); }
.dash-courier-row--shipped:hover { border-color: #c084fc; }
.dash-courier-row--shipped .dash-courier-row-label { color: #c084fc; }
.dash-courier-row--shipped .dash-courier-pill { background: rgba(192, 132, 252, 0.12); color: #c084fc; border: 1px solid rgba(192, 132, 252, 0.35); }

.dash-courier-row--delivered { border-color: rgba(52, 211, 153, 0.25); }
.dash-courier-row--delivered:hover { border-color: #34d399; }
.dash-courier-row--delivered .dash-courier-row-label { color: #34d399; }
.dash-courier-row--delivered .dash-courier-pill { background: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.35); }

.dash-courier-row-label {
    font-size: 0.9rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 9px;
}
.dash-courier-pill {
    font-size: 0.84rem;
    font-weight: 700;
    padding: 3px 13px;
    border-radius: var(--m-radius-pill);
    letter-spacing: 0.3px;
}
.dash-courier-btn {
    display: block;
    width: 100%;
    padding: 12px;
    text-align: center;
    border-radius: 10px;
    font-size: 0.92rem;
    font-weight: 700;
    color: #f97316 !important;
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.14);
    text-decoration: none !important;
    transition: all 0.22s ease;
}
.dash-courier-btn:hover {
    background: #f97316;
    color: #ffffff !important;
    border-color: #f97316;
    box-shadow: 0 6px 18px rgba(249, 115, 22, 0.45);
    transform: translateY(-2px);
}

/* 3. Modern Section Cards */
.dash-section-card {
    background: var(--m-card-bg);
    border: 1px solid var(--m-card-border);
    border-radius: var(--m-radius);
    margin-bottom: 24px;
    box-shadow: var(--m-shadow-sm);
    overflow: hidden;
}
.dash-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
}
.dash-card-title {
    font-size: 0.975rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dash-card-body {
    padding: 22px;
}

/* 4. Modern Table */
.dash-table {
    margin-bottom: 0;
    width: 100%;
}
.dash-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
    border-top: none;
    white-space: nowrap;
}
.dash-table tbody td {
    padding: 13px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.86rem;
}
.dash-table tbody tr:hover td {
    background: #f8fafc;
}
.dash-product-thumb {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    object-fit: cover;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    flex-shrink: 0;
}
.dash-product-thumb-placeholder {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #94a3b8;
    font-size: 18px;
    flex-shrink: 0;
}
.dash-status-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 11px;
    border-radius: var(--m-radius-pill);
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.2;
}
.dash-status-tag-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}
.dash-tag-primary   { background: #eff6ff; color: #1d4ed8; }
.dash-tag-primary .dash-status-tag-dot { background: #3b82f6; }
.dash-tag-success   { background: #ecfdf5; color: #047857; }
.dash-tag-success .dash-status-tag-dot { background: #10b981; }
.dash-tag-warning   { background: #fffbeb; color: #b45309; }
.dash-tag-warning .dash-status-tag-dot { background: #f59e0b; }
.dash-tag-danger    { background: #fef2f2; color: #b91c1c; }
.dash-tag-danger .dash-status-tag-dot  { background: #ef4444; }

/* 5. Geographic Analytics Toolbar & Summary */
.geo-filter-bar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.geo-kpi-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
    height: 100%;
}
.geo-kpi-box:hover {
    box-shadow: 0 4px 12px rgba(15,23,42,0.05);
    border-color: #cbd5e1;
}
.geo-rank-badge {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 700;
}
.geo-rank-1 { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.geo-rank-2 { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.geo-rank-3 { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
.geo-rank-other { background: #f8fafc; color: #94a3b8; }

/* 6. Action Buttons */
.dash-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    transition: all 0.18s ease;
    text-decoration: none !important;
}
.dash-action-btn:hover {
    background: #059669;
    border-color: #059669;
    color: #ffffff !important;
    transform: translateY(-1px);
}
.dash-action-btn-danger:hover {
    background: #ef4444;
    border-color: #ef4444;
    color: #ffffff !important;
}
.dash-btn-restock {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 8px;
    background: #ef4444;
    color: #ffffff !important;
    font-size: 0.78rem;
    font-weight: 600;
    border: none;
    text-decoration: none !important;
    transition: all 0.18s ease;
}
.dash-btn-restock:hover {
    background: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(239,68,68,0.3);
}

/* 7. Responsive Media Queries */
@media (max-width: 768px) {
    .dash-welcome-card { padding: 18px 20px; }
    .dash-welcome-title { font-size: 1.25rem; }
    .dash-welcome-actions { width: 100%; margin-top: 14px; }
    .dash-kpi-value { font-size: 1.45rem; }
    .geo-filter-bar { flex-direction: column; align-items: stretch; }
    .geo-filter-bar .input-group { width: 100% !important; }
}
</style>
@endsection

@section('content')
<!-- Start Content-->
<div class="container-fluid py-2">

    <!-- Modern Welcome & Header Banner -->
    <div class="dash-welcome-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="dash-status-pill">
                        <span class="dash-status-dot"></span> Store Live & Active
                    </span>
                    <span class="text-white-50 font-12 d-none d-sm-inline">
                        <i class="fe-calendar me-1"></i> {{ date('l, d F Y') }}
                    </span>
                </div>
                <h3 class="dash-welcome-title">
                    Welcome back, {{ Auth::user()->name ?? 'Admin' }}! 👋
                </h3>
                <p class="dash-welcome-subtitle">
                    Here is a comprehensive summary of your store's sales, revenue, orders, and delivery performance today.
                </p>
            </div>
            <div class="dash-welcome-actions d-flex flex-wrap align-items-center gap-2">
                <a href="{{route('products.create')}}" class="dash-banner-btn dash-banner-btn-primary">
                    <i class="fe-plus-circle"></i> Add Product
                </a>
                <a href="{{route('admin.orders', 'all')}}" class="dash-banner-btn dash-banner-btn-ghost">
                    <i class="fe-package"></i> All Orders
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Widgets Row 1: Primary Volume & Financial Metrics -->
    <div class="row">
        <!-- 1. Total Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="dash-kpi-card dash-kpi-card--emerald">
                <div>
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon-wrap">
                            <i class="fe-shopping-cart"></i>
                        </div>
                        <span class="dash-kpi-badge dash-kpi-badge-success">All Time</span>
                    </div>
                    <div class="dash-kpi-label">Total Orders</div>
                    <div class="dash-kpi-value"><span data-plugin="counterup">{{$total_order}}</span></div>
                </div>
                <div>
                    <a href="{{route('admin.orders', 'all')}}" class="dash-kpi-link">
                        Manage Orders <i class="fe-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Total Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="dash-kpi-card dash-kpi-card--blue">
                <div>
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon-wrap">
                            <i class="fe-dollar-sign"></i>
                        </div>
                        <span class="dash-kpi-badge dash-kpi-badge-info">Gross Sales</span>
                    </div>
                    <div class="dash-kpi-label">Total Revenue</div>
                    <div class="dash-kpi-value">৳<span data-plugin="counterup">{{number_format($total_revenue)}}</span></div>
                </div>
                <div>
                    <span class="text-muted font-12"><i class="fe-trending-up text-success me-1"></i> Lifetime revenue</span>
                </div>
            </div>
        </div>

        <!-- 3. Today's Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="dash-kpi-card dash-kpi-card--indigo">
                <div>
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon-wrap">
                            <i class="fe-shopping-bag"></i>
                        </div>
                        <span class="dash-kpi-badge dash-kpi-badge-info">Today</span>
                    </div>
                    <div class="dash-kpi-label">Today's Orders</div>
                    <div class="dash-kpi-value"><span data-plugin="counterup">{{$today_order}}</span></div>
                </div>
                <div>
                    <span class="text-muted font-12"><i class="fe-clock text-info me-1"></i> Today: ৳{{number_format($today_revenue)}}</span>
                </div>
            </div>
        </div>

        <!-- 4. Today's Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="dash-kpi-card dash-kpi-card--emerald">
                <div>
                    <div class="dash-kpi-top">
                        <div class="dash-kpi-icon-wrap">
                            <i class="fe-trending-up"></i>
                        </div>
                        <span class="dash-kpi-badge dash-kpi-badge-success">Today Sales</span>
                    </div>
                    <div class="dash-kpi-label">Today's Revenue</div>
                    <div class="dash-kpi-value text-success">৳<span data-plugin="counterup">{{number_format($today_revenue)}}</span></div>
                </div>
                <div>
                    <span class="text-muted font-12"><i class="fe-truck text-success me-1"></i> Today delivered: {{$today_delivery}}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Operations, Logistics & Fulfillment Hub -->
    <div class="row">
        <!-- 1. NEW: কুরিয়ার ও ডেলিভারি স্ট্যাটাস Card -->
        <div class="col-xl-4 col-md-12">
            <div class="dash-courier-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="dash-courier-header">
                        <h5 class="dash-courier-title">
                            <i class="fe-truck"></i> কুরিয়ার ও ডেলিভারি স্ট্যাটাস
                        </h5>
                        <p class="dash-courier-subtitle">
                            লাইভ অর্ডার ট্র্যাকিং ও কুরিয়ার ডিসপ্যাচ পাইপলাইন
                        </p>
                    </div>

                    <div class="dash-courier-list">
                        <!-- Pending -->
                        <a href="{{ route('admin.orders', 'pending') }}" class="dash-courier-row dash-courier-row--pending text-decoration-none">
                            <div class="dash-courier-row-label">
                                <i class="fe-clock"></i> পেন্ডিং (Pending Orders)
                            </div>
                            <span class="dash-courier-pill">{{ number_format($courier_pipeline['pending'] ?? $pending_order) }} টি</span>
                        </a>

                        <!-- Processing & Packaging -->
                        <a href="{{ route('admin.orders', 'processing') }}" class="dash-courier-row dash-courier-row--processing text-decoration-none">
                            <div class="dash-courier-row-label">
                                <i class="fe-package"></i> প্রসেসিং ও প্যাকেজিং
                            </div>
                            <span class="dash-courier-pill">{{ number_format($courier_pipeline['processing'] ?? $processing_order) }} টি</span>
                        </a>

                        <!-- In Courier / Shipped -->
                        <a href="{{ route('admin.orders', 'in-courier') }}" class="dash-courier-row dash-courier-row--shipped text-decoration-none">
                            <div class="dash-courier-row-label">
                                <i class="fe-send"></i> কুরিয়ারে চলমান (Shipped)
                            </div>
                            <span class="dash-courier-pill">{{ number_format($courier_pipeline['shipped'] ?? 0) }} টি</span>
                        </a>

                        <!-- Successfully Delivered -->
                        <a href="{{ route('admin.orders', 'completed') }}" class="dash-courier-row dash-courier-row--delivered text-decoration-none">
                            <div class="dash-courier-row-label">
                                <i class="fe-check-circle"></i> সফলভাবে ডেলিভার্ড
                            </div>
                            <span class="dash-courier-pill">{{ number_format($courier_pipeline['delivered'] ?? $total_delivery) }} টি</span>
                        </a>
                    </div>
                </div>

                <div>
                    <a href="{{ route('admin.orders', 'processing') }}" class="dash-courier-btn">
                        <span>কুরিয়ার বুকিং প্যানেলে যান</span> <i class="fe-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. Delivery Performance Radial Chart -->
        <div class="col-xl-4 col-md-6">
            <div class="dash-section-card h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="dash-card-header">
                        <h5 class="dash-card-title">
                            <i class="fe-pie-chart text-success"></i> Delivery Performance
                        </h5>
                        <span class="dash-status-tag dash-tag-success">
                            <span class="dash-status-tag-dot"></span> Delivery Success (Live Rate)
                        </span>
                    </div>
                    <div class="dash-card-body">
                        <div id="total-revenue" style="min-height: 242px;" class="apex-charts" data-colors="#10b981"></div>
                    </div>
                </div>
                <div class="text-center pt-2 pb-3 px-3 border-top">
                    <div class="row text-center mt-1">
                        <div class="col-6 border-end">
                            <p class="text-muted font-12 mb-0">Total Delivered</p>
                            <h5 class="m-0 text-success fw-bold">{{number_format($total_delivery)}}</h5>
                        </div>
                        <div class="col-6">
                            <p class="text-muted font-12 mb-0">Return / Cancel Rate</p>
                            <h5 class="m-0 text-danger fw-bold">{{$return_rate}}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Operational Health & Leads (Stacked KPI Cards) -->
        <div class="col-xl-4 col-md-6">
            <div class="d-flex flex-column gap-3 h-100 justify-content-between">
                <!-- Cart Leads -->
                <div class="dash-kpi-card dash-kpi-card--rose" style="min-height: auto; flex: 1;">
                    <div>
                        <div class="dash-kpi-top">
                            <div class="dash-kpi-icon-wrap">
                                <i class="fe-user-x"></i>
                            </div>
                            <span class="dash-kpi-badge dash-kpi-badge-danger">Potential Lead</span>
                        </div>
                        <div class="dash-kpi-label">Cart Leads (অসম্পূর্ণ অর্ডার)</div>
                        <div class="dash-kpi-value text-danger"><span data-plugin="counterup">{{$incomplete_order}}</span></div>
                    </div>
                    <div>
                        <a href="{{route('incomplete.index')}}" class="dash-kpi-link text-danger">
                            Follow-up Leads <i class="fe-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="dash-kpi-card dash-kpi-card--amber" style="min-height: auto; flex: 1;">
                    <div>
                        <div class="dash-kpi-top">
                            <div class="dash-kpi-icon-wrap">
                                <i class="fe-alert-triangle"></i>
                            </div>
                            <span class="dash-kpi-badge dash-kpi-badge-warning">Stock ≤ 5</span>
                        </div>
                        <div class="dash-kpi-label">Low Stock Alerts (মজুদ ঘাটতি)</div>
                        <div class="dash-kpi-value text-warning"><span data-plugin="counterup">{{$low_stock_count}}</span></div>
                    </div>
                    <div>
                        <a href="{{route('products.index')}}" class="dash-kpi-link text-warning">
                            Check Inventory <i class="fe-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Sales & Revenue Analytics Chart (Full Width) -->
    <div class="row">
        <div class="col-12">
            <div class="dash-section-card">
                <div class="dash-card-header">
                    <div>
                        <h5 class="dash-card-title">
                            <i class="fe-bar-chart-2 text-primary"></i> Sales & Revenue Analytics
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Daily comparison of total sales and delivered revenue over the last 30 days (দৈনিক মোট বিক্রি ও ডেলিভার্ড সেলস ট্রেন্ড)</p>
                    </div>
                    <span class="dash-status-tag dash-tag-primary">
                        <span class="dash-status-tag-dot"></span> Live Sales Trend
                    </span>
                </div>
                <div class="dash-card-body">
                    <div id="sales-analytics" style="min-height: 338px;" class="apex-charts" data-colors="#059669,#3b82f6"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Order Analytics & Location KPI -->
    <div class="row" id="geo-analytics-section">
        <div class="col-12">
            <div class="dash-section-card">
                <div class="dash-card-header">
                    <div>
                        <h5 class="dash-card-title text-primary">
                            <i class="fe-map-pin"></i> Geographic Order Analytics (জেলা ও থানা ভিত্তিক অর্ডার বিশ্লেষণ)
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">
                            গ্রাহকের ডেলিভারি ঠিকানা বিশ্লেষণ করে সর্বোচ্চ অর্ডার আসা জেলা ও থানা ভিত্তিক পরিসংখ্যান
                        </p>
                    </div>
                </div>

                <div class="dash-card-body">
                    <!-- Modern Filter Toolbar -->
                    <div class="geo-filter-bar">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted font-12 fw-bold"><i class="fe-filter me-1"></i> FILTERS:</span>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <!-- Time Period Filter -->
                            <div class="input-group input-group-sm" style="width: auto;">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fe-calendar"></i></span>
                                <select id="geo-period-filter" class="form-select form-select-sm border-start-0 ps-0">
                                    <option value="all_time" selected>All Time (সর্বমোট)</option>
                                    <option value="this_month">This Month (চলতি মাস)</option>
                                    <option value="last_month">Last Month (গত মাস)</option>
                                    <option value="last_7_days">Last 7 Days (গত ৭ দিন)</option>
                                    <option value="today">Today (আজ)</option>
                                    <option value="yesterday">Yesterday (গতকাল)</option>
                                </select>
                            </div>

                            <!-- Order Status Filter -->
                            <div class="input-group input-group-sm" style="width: auto;">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fe-tag"></i></span>
                                <select id="geo-status-filter" class="form-select form-select-sm border-start-0 ps-0">
                                    <option value="all" selected>All Statuses</option>
                                    <option value="1">Pending</option>
                                    <option value="2">Processing</option>
                                    <option value="5">In Courier</option>
                                    <option value="6">Delivered</option>
                                    <option value="7">Cancelled</option>
                                </select>
                            </div>

                            <!-- District Drilldown Filter -->
                            <div class="input-group input-group-sm" style="width: auto;">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fe-navigation"></i></span>
                                <select id="geo-district-filter" class="form-select form-select-sm border-start-0 ps-0">
                                    <option value="all" selected>All Districts (সকল জেলা)</option>
                                    @foreach($geoAnalytics['available_districts'] as $dist)
                                        <option value="{{ $dist }}">{{ $dist }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button id="geo-reset-btn" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                                <i class="fe-rotate-ccw me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- 4 Summary KPI Mini Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6 col-xl-3">
                            <div class="geo-kpi-box">
                                <div class="d-flex align-items-center">
                                    <div class="dash-kpi-icon-wrap me-3" style="background:#eff6ff; color:#2563eb; width:42px; height:42px; font-size:18px;">
                                        <i class="fe-award"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted font-11 text-uppercase fw-bold">Top District (শীর্ষ জেলা)</div>
                                        <h5 class="m-0 text-dark text-truncate fw-bold font-15" id="geo-kpi-top-district">
                                            {{ $geoAnalytics['kpis']['top_district'] }}
                                        </h5>
                                        <small class="text-muted font-11" id="geo-kpi-top-district-sub">
                                            <span class="fw-bold text-primary">{{ number_format($geoAnalytics['kpis']['top_district_count']) }}</span> orders (৳{{ number_format($geoAnalytics['kpis']['top_district_amount']) }})
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="geo-kpi-box">
                                <div class="d-flex align-items-center">
                                    <div class="dash-kpi-icon-wrap me-3" style="background:#ecfdf5; color:#059669; width:42px; height:42px; font-size:18px;">
                                        <i class="fe-map"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted font-11 text-uppercase fw-bold">Top Thana/Area (শীর্ষ থানা)</div>
                                        <h5 class="m-0 text-dark text-truncate fw-bold font-15" id="geo-kpi-top-thana">
                                            {{ $geoAnalytics['kpis']['top_thana'] }}
                                        </h5>
                                        <small class="text-muted font-11" id="geo-kpi-top-thana-sub">
                                            <span class="fw-bold text-success">{{ number_format($geoAnalytics['kpis']['top_thana_count']) }}</span> orders ({{ $geoAnalytics['kpis']['top_thana_district'] }})
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="geo-kpi-box">
                                <div class="d-flex align-items-center">
                                    <div class="dash-kpi-icon-wrap me-3" style="background:#f0fdf4; color:#16a34a; width:42px; height:42px; font-size:18px;">
                                        <i class="fe-check-square"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted font-11 text-uppercase fw-bold">Identified Rate (ঠিকানা শনাক্ত)</div>
                                        <h5 class="m-0 text-dark text-truncate fw-bold font-15" id="geo-kpi-geocoded-rate">
                                            {{ $geoAnalytics['kpis']['geocoded_rate'] }}%
                                        </h5>
                                        <small class="text-muted font-11" id="geo-kpi-geocoded-sub">
                                            <span class="fw-bold text-success">{{ number_format($geoAnalytics['kpis']['geocoded_orders']) }}</span> of {{ number_format($geoAnalytics['kpis']['total_orders']) }} orders
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="geo-kpi-box">
                                <div class="d-flex align-items-center">
                                    <div class="dash-kpi-icon-wrap me-3" style="background:#fffbeb; color:#d97706; width:42px; height:42px; font-size:18px;">
                                        <i class="fe-truck"></i>
                                    </div>
                                    <div class="overflow-hidden">
                                        <div class="text-muted font-11 text-uppercase fw-bold">Dhaka vs Outside</div>
                                        <h5 class="m-0 text-dark text-truncate fw-bold font-15" id="geo-kpi-dhaka-ratio">
                                            {{ $geoAnalytics['kpis']['inside_dhaka_pct'] }}% / {{ $geoAnalytics['kpis']['outside_dhaka_pct'] }}%
                                        </h5>
                                        <small class="text-muted font-11" id="geo-kpi-dhaka-sub">
                                            Dhaka: {{ number_format($geoAnalytics['kpis']['inside_dhaka_count']) }} | Out: {{ number_format($geoAnalytics['kpis']['outside_dhaka_count']) }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Breakdown Row: Chart + Table -->
                    <div class="row g-3">
                        <!-- Horizontal Bar Chart -->
                        <div class="col-xl-6">
                            <div class="p-3 border rounded-3 bg-white" style="min-height: 420px;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="m-0 fw-bold text-dark font-14" id="geo-chart-title">
                                        <i class="fe-bar-chart-2 me-1 text-primary"></i> Top 10 Districts by Order Volume
                                    </h6>
                                    <span class="dash-status-tag dash-tag-primary" id="geo-chart-badge">Districts</span>
                                </div>
                                <div id="geo-location-chart" style="min-height: 350px;"></div>
                            </div>
                        </div>

                        <!-- Table Column -->
                        <div class="col-xl-6">
                            <div class="p-3 border rounded-3 bg-white" style="min-height: 420px;">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <h6 class="m-0 fw-bold text-dark font-14" id="geo-table-title">
                                        <i class="fe-list me-1 text-success"></i> Location Breakdown Ranking
                                    </h6>
                                    <div style="width: 200px;">
                                        <input type="text" id="geo-table-search" class="form-control form-control-sm" placeholder="Search location...">
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="dash-table" id="geo-ranking-table">
                                        <thead class="sticky-top">
                                            <tr>
                                                <th style="width: 45px;">Rank</th>
                                                <th>Location</th>
                                                <th class="text-end">Orders</th>
                                                <th class="text-end">Revenue (৳)</th>
                                                <th class="text-end">Share</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="geo-table-body">
                                            <!-- Dynamically populated via AJAX/JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 1: Latest Orders & Top Selling Products -->
    <div class="row">
        <!-- Latest 5 Orders -->
        <div class="col-xl-6">
            <div class="dash-section-card">
                <div class="dash-card-header">
                    <div>
                        <h5 class="dash-card-title">
                            <i class="fe-shopping-bag text-primary"></i> Latest 5 Orders
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Real-time incoming customer transactions</p>
                    </div>
                    <a href="{{route('admin.orders', 'all')}}" class="btn btn-sm btn-outline-primary">
                        View All Orders <i class="fe-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice</th>
                                <th>Amount</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latest_order as $order)
                            <tr>
                                <td><span class="text-muted font-12">{{$loop->iteration}}</span></td>
                                <td>
                                    <span class="fw-bold text-dark font-13">#{{$order->invoice_id}}</span>
                                </td>
                                <td>
                                    <strong class="text-dark">৳{{number_format($order->amount, 2)}}</strong>
                                </td>
                                <td>
                                    <div class="text-dark fw-semibold font-13">
                                        {{$order->customer ? $order->customer->name : ($order->shipping->name ?? 'Guest Customer')}}
                                    </div>
                                    <small class="text-muted font-11">{{$order->shipping->phone ?? ''}}</small>
                                </td>
                                <td>
                                    @php
                                        $statusName = $order->status ? $order->status->name : 'Pending';
                                        $tagClass = match(strtolower($statusName)) {
                                            'delivered', 'completed' => 'dash-tag-success',
                                            'processing' => 'dash-tag-primary',
                                            'in-courier', 'in courier', 'shipped', 'on the way', 'on-the-way' => 'dash-tag-info',
                                            'cancelled', 'canceled' => 'dash-tag-danger',
                                            default => 'dash-tag-warning',
                                        };
                                    @endphp
                                    <span class="dash-status-tag {{$tagClass}}">
                                        <span class="dash-status-tag-dot"></span> {{$statusName}}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{route('admin.order.invoice', $order->id)}}" class="dash-action-btn" title="View Invoice">
                                        <i class="mdi mdi-eye-outline font-16"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fe-inbox font-22 d-block mb-1"></i> No recent orders recorded.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top 5 Best Selling Products -->
        <div class="col-xl-6">
            <div class="dash-section-card">
                <div class="dash-card-header">
                    <div>
                        <h5 class="dash-card-title text-success">
                            <i class="fe-trending-up"></i> Top 5 Best Selling Products
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Highest unit volume products across all sales</p>
                    </div>
                    <a href="{{route('products.index')}}" class="btn btn-sm btn-outline-success">
                        All Products <i class="fe-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Units Sold</th>
                                <th>Total Sales</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($top_selling_products as $topProd)
                            <tr>
                                <td><span class="text-muted font-12">{{$loop->iteration}}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($topProd->image && $topProd->image->image)
                                            <img src="{{asset($topProd->image->image)}}" alt="product" class="dash-product-thumb me-2">
                                        @else
                                            <div class="dash-product-thumb-placeholder me-2">
                                                <i class="fe-package"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong class="text-dark font-13">{{Str::limit($topProd->product_name, 28)}}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="dash-status-tag dash-tag-success">
                                        <i class="fe-check font-11"></i> {{$topProd->total_sold}} units
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark">৳{{number_format($topProd->total_amount, 2)}}</strong>
                                </td>
                                <td class="text-center">
                                    <a href="{{route('products.edit', $topProd->product_id)}}" class="dash-action-btn" title="Edit Product">
                                        <i class="mdi mdi-pencil-outline font-16"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fe-package font-22 d-block mb-1"></i> No sales records available yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Row 2: Low Stock Warning -->
    <div class="row">
        <div class="col-12">
            <div class="dash-section-card">
                <div class="dash-card-header">
                    <div>
                        <h5 class="dash-card-title text-danger">
                            <i class="fe-alert-octagon"></i> Low Stock Alerts (Stock ≤ 5 Units)
                        </h5>
                        <p class="text-muted font-12 mb-0 mt-1">Restock urgently to prevent out-of-stock checkouts</p>
                    </div>
                    <a href="{{route('products.index')}}" class="btn btn-sm btn-outline-danger">
                        Manage Full Inventory <i class="fe-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Details</th>
                                <th>Current Stock</th>
                                <th>Unit Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($low_stock_products as $prod)
                            <tr>
                                <td><span class="text-muted font-12">{{$loop->iteration}}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($prod->image && $prod->image->image)
                                            <img src="{{asset($prod->image->image)}}" alt="product" class="dash-product-thumb me-2">
                                        @else
                                            <div class="dash-product-thumb-placeholder me-2" style="background:#fee2e2; color:#ef4444;">
                                                <i class="fe-alert-triangle"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong class="text-dark font-13">{{Str::limit($prod->name, 48)}}</strong>
                                            <div class="text-muted font-11">SKU: {{$prod->product_code ?? 'N/A'}}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="dash-status-tag dash-tag-danger">
                                        <i class="fe-alert-circle font-11"></i> {{$prod->stock}} units left
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark">৳{{number_format($prod->new_price, 2)}}</strong>
                                </td>
                                <td class="text-center">
                                    <a href="{{route('products.edit', $prod->id)}}" class="dash-btn-restock" title="Restock Product">
                                        <i class="fe-plus-circle font-12"></i> Restock
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-success py-4">
                                    <i class="fe-check-circle font-24 d-block mb-1"></i>
                                    All products are sufficiently stocked! Zero low-stock alerts.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
</div> <!-- container -->
@endsection

@section('script')
    <!-- Plugins js-->
    <script src="{{asset('backEnd/assets/libs/flatpickr/flatpickr.min.js')}}"></script>
    <script src="{{asset('backEnd/assets/libs/apexcharts/apexcharts.min.js')}}"></script>
    <script src="{{asset('backEnd/assets/libs/selectize/js/standalone/selectize.min.js')}}"></script>

    <script>
    $(document).ready(function() {
        // 1. Delivery Radial Gauge Chart
        var totalRevEl = document.querySelector("#total-revenue");
        if (totalRevEl && typeof ApexCharts !== 'undefined') {
            var colors = ["#10b981"];
            var dataColors = $(totalRevEl).data("colors");
            if (dataColors) {
                colors = dataColors.split(",");
            }
            var deliveryOptions = {
                chart: {
                    height: 242,
                    type: "radialBar"
                },
                plotOptions: {
                    radialBar: {
                        hollow: {
                            size: "65%"
                        },
                        dataLabels: {
                            name: {
                                show: true,
                                fontSize: '13px',
                                fontWeight: 600,
                                color: '#64748b'
                            },
                            value: {
                                show: true,
                                fontSize: '22px',
                                fontWeight: 700,
                                color: '#0f172a',
                                formatter: function(val) {
                                    return val + '%';
                                }
                            }
                        }
                    }
                },
                colors: colors,
                series: [{{ $delivery_rate }}],
                labels: ["Success Rate"]
            };
            var chartDelivery = new ApexCharts(totalRevEl, deliveryOptions);
            chartDelivery.render();
        }

        // 2. Sales Analytics Chart
        var salesAnalyticsEl = document.querySelector("#sales-analytics");
        if (salesAnalyticsEl && typeof ApexCharts !== 'undefined') {
            var salesColors = ["#059669", "#3b82f6"];
            var salesDataColors = $(salesAnalyticsEl).data("colors");
            if (salesDataColors) {
                salesColors = salesDataColors.split(",");
            }
            var grossAmounts = {!! json_encode($monthly_sale->pluck('amount')->map(fn($v) => (float)$v)->values()->toArray()) !!};
            var deliveredAmounts = {!! json_encode($monthly_sale->pluck('delivered_amount')->map(fn($v) => (float)$v)->values()->toArray()) !!};
            var monthlyLabels = {!! json_encode($monthly_sale->map(fn($sale) => date('d M', strtotime($sale->date)))->values()->toArray()) !!};

            var salesOptions = {
                series: [{
                    name: "Total Sales (৳)",
                    type: "column",
                    data: grossAmounts
                }, {
                    name: "Delivered (৳)",
                    type: "line",
                    data: deliveredAmounts
                }],
                chart: {
                    height: 338,
                    type: "line",
                    toolbar: {
                        show: false
                    }
                },
                stroke: {
                    width: [0, 3],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        columnWidth: "45%",
                        borderRadius: 6
                    }
                },
                colors: salesColors,
                dataLabels: {
                    enabled: false
                },
                labels: monthlyLabels,
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    offsetY: -5,
                    markers: {
                        radius: 12
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    padding: {
                        bottom: 10
                    }
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '11px',
                            fontWeight: 500
                        }
                    }
                },
                yaxis: [{
                    title: {
                        text: "Revenue (BDT)",
                        style: {
                            color: '#64748b',
                            fontWeight: 600
                        }
                    },
                    labels: {
                        formatter: function(val) {
                            return '৳' + Number(val).toLocaleString();
                        },
                        style: {
                            colors: '#64748b',
                            fontSize: '11px'
                        }
                    }
                }]
            };
            var chartSales = new ApexCharts(salesAnalyticsEl, salesOptions);
            chartSales.render();
        }

        // 3. Date Range Flatpickr
        if ($("#dash-daterange").length && typeof $.fn.flatpickr !== 'undefined') {
            $("#dash-daterange").flatpickr({
                altInput: true,
                mode: "range"
            });
        }

        // 4. Geographic Analytics Integration
        var initialGeoData = {!! json_encode($geoAnalytics) !!};
        var geoLocationChart = null;

        function renderGeoChart(categories, seriesData, labelName) {
            var chartEl = document.querySelector("#geo-location-chart");
            if (!chartEl || typeof ApexCharts === 'undefined') return;

            var options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        barHeight: '62%',
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                colors: [
                    '#059669', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b',
                    '#14b8a6', '#0ea5e9', '#6366f1', '#10b981', '#64748b'
                ],
                dataLabels: {
                    enabled: true,
                    offsetX: 28,
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: ['#334155']
                    },
                    formatter: function (val) {
                        return Number(val).toLocaleString();
                    }
                },
                series: [{
                    name: labelName || 'Orders',
                    data: seriesData
                }],
                xaxis: {
                    categories: categories,
                    title: {
                        text: 'Number of Orders',
                        style: { color: '#64748b', fontSize: '11px', fontWeight: 600 }
                    },
                    labels: {
                        style: { colors: '#64748b', fontSize: '11px' }
                    }
                },
                yaxis: {
                    labels: {
                        show: true,
                        style: {
                            fontSize: '12px',
                            fontWeight: 600,
                            colors: ['#1e293b']
                        },
                        maxWidth: 140
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    padding: {
                        right: 35,
                        left: 10
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        title: {
                            formatter: function () { return 'Total Orders: '; }
                        },
                        formatter: function (val) {
                            return Number(val).toLocaleString() + ' orders';
                        }
                    }
                },
                legend: { show: false }
            };

            if (geoLocationChart) {
                geoLocationChart.destroy();
            }
            geoLocationChart = new ApexCharts(chartEl, options);
            geoLocationChart.render();
        }

        function populateGeoTable(items, isThana) {
            var tbody = $('#geo-table-body');
            tbody.empty();

            if (!items || items.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted py-4"><i class="fe-map-pin font-20 d-block mb-1"></i> No location data found for selected filter.</td></tr>');
                return;
            }

            items.forEach(function(item, idx) {
                var locName = isThana ? item.thana : item.district;
                var subBadge = isThana ? ('<span class="badge bg-soft-info text-info ms-1">' + (item.district || '') + '</span>') : '';
                var actionBtn = '';
                if (!isThana) {
                    actionBtn = '<button class="btn btn-xs btn-outline-primary drilldown-district-btn py-0 px-2 font-11" data-district="' + locName + '" title="View Thanas in ' + locName + '"><i class="fe-arrow-right"></i> Thanas</button>';
                } else {
                    actionBtn = '<span class="text-muted font-11">Thana</span>';
                }

                var rankBadgeClass = (idx === 0) ? 'geo-rank-1' : ((idx === 1) ? 'geo-rank-2' : ((idx === 2) ? 'geo-rank-3' : 'geo-rank-other'));
                var rankHtml = '<span class="geo-rank-badge ' + rankBadgeClass + '">' + (idx + 1) + '</span>';

                var row = '<tr>' +
                    '<td>' + rankHtml + '</td>' +
                    '<td><span class="fw-semibold text-dark">' + locName + '</span>' + subBadge + '</td>' +
                    '<td class="text-end fw-bold text-primary">' + Number(item.order_count).toLocaleString() + '</td>' +
                    '<td class="text-end">৳' + Number(item.total_amount).toLocaleString() + '</td>' +
                    '<td class="text-end"><span class="dash-status-tag dash-tag-success"><span class="dash-status-tag-dot"></span>' + item.share_percentage + '%</span></td>' +
                    '<td class="text-center">' + actionBtn + '</td>' +
                '</tr>';
                tbody.append(row);
            });
        }

        function updateGeoUI(data, isThana) {
            var kpis = data.kpis;
            $('#geo-kpi-top-district').text(kpis.top_district || 'N/A');
            $('#geo-kpi-top-district-sub').html('<span class="fw-bold text-primary">' + Number(kpis.top_district_count || 0).toLocaleString() + '</span> orders (৳' + Number(kpis.top_district_amount || 0).toLocaleString() + ')');

            $('#geo-kpi-top-thana').text(kpis.top_thana || 'N/A');
            $('#geo-kpi-top-thana-sub').html('<span class="fw-bold text-success">' + Number(kpis.top_thana_count || 0).toLocaleString() + '</span> orders (' + (kpis.top_thana_district || '') + ')');

            $('#geo-kpi-geocoded-rate').text(kpis.geocoded_rate + '%');
            $('#geo-kpi-geocoded-sub').html('<span class="fw-bold text-success">' + Number(kpis.geocoded_orders || 0).toLocaleString() + '</span> of ' + Number(kpis.total_orders || 0).toLocaleString() + ' orders');

            $('#geo-kpi-dhaka-ratio').text(kpis.inside_dhaka_pct + '% / ' + kpis.outside_dhaka_pct + '%');
            $('#geo-kpi-dhaka-sub').text('Dhaka: ' + Number(kpis.inside_dhaka_count || 0).toLocaleString() + ' | Out: ' + Number(kpis.outside_dhaka_count || 0).toLocaleString());

            var items = isThana ? (data.top_thanas || []) : (data.top_districts || []);
            var categories = [];
            var seriesData = [];

            items.slice(0, 10).forEach(function(item) {
                categories.push(isThana ? item.thana : item.district);
                seriesData.push(Number(item.order_count));
            });

            if (isThana) {
                $('#geo-chart-title').html('<i class="fe-bar-chart-2 me-1 text-success"></i> Top Thanas/Areas in ' + ($('#geo-district-filter').val() || 'All Districts'));
                $('#geo-chart-badge').text('Thanas / Upazilas').removeClass('dash-tag-primary').addClass('dash-tag-success');
                $('#geo-table-title').html('<i class="fe-list me-1 text-success"></i> Thanas Breakdown Ranking');
            } else {
                $('#geo-chart-title').html('<i class="fe-bar-chart-2 me-1 text-primary"></i> Top 10 Districts by Order Volume');
                $('#geo-chart-badge').text('Districts').removeClass('dash-tag-success').addClass('dash-tag-primary');
                $('#geo-table-title').html('<i class="fe-list me-1 text-primary"></i> District Breakdown Ranking');
            }

            renderGeoChart(categories, seriesData, isThana ? 'Thana Orders' : 'District Orders');
            populateGeoTable(items, isThana);
        }

        // Initial render
        updateGeoUI(initialGeoData, false);

        // Fetch Geo Analytics via AJAX
        function fetchGeoAnalytics() {
            var period = $('#geo-period-filter').val();
            var status = $('#geo-status-filter').val();
            var district = $('#geo-district-filter').val();
            var isThana = (district !== 'all');

            $('#geo-analytics-section .dash-card-body').css('opacity', '0.6');

            $.ajax({
                url: "{{ route('admin.dashboard.geo_analytics') }}",
                type: 'GET',
                data: {
                    period: period,
                    status: status,
                    district: district
                },
                success: function(response) {
                    $('#geo-analytics-section .dash-card-body').css('opacity', '1');
                    updateGeoUI(response, isThana);
                },
                error: function() {
                    $('#geo-analytics-section .dash-card-body').css('opacity', '1');
                    console.error('Failed to load geographic analytics.');
                }
            });
        }

        $('#geo-period-filter, #geo-status-filter, #geo-district-filter').on('change', function() {
            fetchGeoAnalytics();
        });

        // Reset Filter
        $('#geo-reset-btn').on('click', function() {
            $('#geo-period-filter').val('all_time');
            $('#geo-status-filter').val('all');
            $('#geo-district-filter').val('all');
            fetchGeoAnalytics();
        });

        // Table search filter
        $('#geo-table-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#geo-table-body tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Drilldown button click inside table
        $(document).on('click', '.drilldown-district-btn', function() {
            var dist = $(this).data('district');
            if (dist) {
                $('#geo-district-filter').val(dist).trigger('change');
            }
        });
    });
    </script>
@endsection