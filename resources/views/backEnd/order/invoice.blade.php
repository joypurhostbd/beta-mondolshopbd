@extends('backEnd.layouts.master')
@section('title', 'Order Invoice - #' . $order->invoice_id)
@section('content')
@php
    $invoice = $invoice ?? \Shared\Infrastructure\Views\ViewModels\InvoiceViewModel::fromOrder($order, $generalsetting ?? null, $contact ?? null);
    $isPos = request()->pos == true;
@endphp
<style>
    :root {
        --invoice-primary: #059669;
        --invoice-primary-dark: #047857;
        --invoice-primary-light: #ecfdf5;
        --invoice-slate-900: #0f172a;
        --invoice-slate-800: #1e293b;
        --invoice-slate-700: #334155;
        --invoice-slate-600: #475569;
        --invoice-slate-500: #64748b;
        --invoice-slate-200: #e2e8f0;
        --invoice-slate-100: #f1f5f9;
        --invoice-slate-50: #f8fafc;
    }

    .customer-invoice {
        margin: 20px 0 40px 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        color: var(--invoice-slate-800);
        -webkit-font-smoothing: antialiased;
    }

    /* Top Action Bar */
    .invoice-action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
        background: #ffffff;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--invoice-slate-200);
    }
    .invoice-action-group {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Standard Invoice Layout */
    .standard-invoice-card {
        max-width: 860px;
        width: 100%;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--invoice-slate-200);
        padding: 36px;
        box-sizing: border-box;
    }
    .standard-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 24px;
        padding-bottom: 24px;
        border-bottom: 2px solid var(--invoice-slate-100);
    }
    .company-brand-section {
        max-width: 380px;
    }
    .company-logo-img {
        max-height: 55px;
        max-width: 200px;
        object-fit: contain;
        margin-bottom: 12px;
    }
    .company-name-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--invoice-slate-900);
        margin: 0 0 6px 0;
        letter-spacing: -0.3px;
    }
    .company-meta-text {
        font-size: 13.5px;
        line-height: 1.6;
        color: var(--invoice-slate-600);
        margin: 0;
    }
    .invoice-meta-section {
        text-align: right;
    }
    .invoice-main-title {
        font-size: 32px;
        font-weight: 800;
        color: var(--invoice-primary);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0 0 8px 0;
        line-height: 1;
    }
    .invoice-meta-item {
        font-size: 14px;
        color: var(--invoice-slate-700);
        margin-bottom: 5px;
    }
    .courier-tracking-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13.5px;
        margin-top: 8px;
    }

    /* Billing & Shipping Section */
    .billing-section-card {
        background: var(--invoice-slate-50);
        border: 1px solid var(--invoice-slate-200);
        border-radius: 10px;
        padding: 20px 24px;
        margin: 24px 0;
    }
    .billing-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--invoice-slate-500);
        margin-bottom: 8px;
    }
    .customer-name {
        font-size: 17px;
        font-weight: 700;
        color: var(--invoice-slate-900);
        margin-bottom: 4px;
    }
    .customer-detail-text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--invoice-slate-700);
        margin: 2px 0;
    }

    /* Product Table */
    .invoice-table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 24px 0 16px 0;
        border-radius: 8px;
        border: 1px solid var(--invoice-slate-200);
    }
    .invoice-data-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 550px;
    }
    .invoice-data-table th {
        background: var(--invoice-slate-800);
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border: none;
    }
    .invoice-data-table td {
        padding: 12px 16px;
        font-size: 14.5px;
        color: var(--invoice-slate-800);
        border-bottom: 1px solid var(--invoice-slate-200);
        vertical-align: middle;
    }
    .invoice-data-table tbody tr:last-child td {
        border-bottom: none;
    }
    .invoice-data-table tbody tr:hover {
        background: #f8fafc;
    }
    .product-variant-tag {
        display: inline-block;
        font-size: 11.5px;
        padding: 2px 8px;
        border-radius: 4px;
        background: var(--invoice-slate-100);
        color: var(--invoice-slate-700);
        margin-top: 4px;
        margin-right: 4px;
    }

    /* Summary Totals */
    .invoice-summary-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 16px;
    }
    .invoice-summary-box {
        width: 320px;
        max-width: 100%;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        font-size: 14.5px;
        color: var(--invoice-slate-700);
    }
    .summary-row.total-row {
        border-top: 2px solid var(--invoice-slate-200);
        margin-top: 8px;
        padding-top: 12px;
        font-size: 18px;
        font-weight: 700;
        color: var(--invoice-slate-900);
    }
    .summary-row.total-row .total-amount {
        color: var(--invoice-primary);
    }

    /* Footer */
    .invoice-footer-section {
        margin-top: 36px;
        padding-top: 24px;
        border-top: 1px solid var(--invoice-slate-200);
        text-align: center;
    }
    .footer-thank-you {
        font-size: 15px;
        font-weight: 600;
        color: var(--invoice-slate-800);
        margin-bottom: 6px;
    }
    .footer-computer-generated {
        font-size: 13px;
        color: var(--invoice-slate-500);
        font-style: italic;
        margin: 0;
    }

    /* POS Receipt Layout */
    .pos-receipt-card {
        max-width: 420px;
        width: 100%;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--invoice-slate-200);
        padding: 24px 20px;
        box-sizing: border-box;
        font-size: 13.5px;
    }
    .pos-header {
        text-align: center;
        margin-bottom: 14px;
    }
    .pos-store-name {
        font-size: 20px;
        font-weight: 800;
        color: var(--invoice-slate-900);
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin: 0 0 4px 0;
    }
    .pos-store-contact {
        font-size: 12.5px;
        color: var(--invoice-slate-600);
        line-height: 1.4;
        margin: 0;
    }
    .pos-dashed-separator {
        border-top: 1px dashed var(--invoice-slate-300, #cbd5e1);
        margin: 12px 0;
    }
    .pos-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        font-size: 13px;
    }
    .pos-meta-full {
        grid-column: span 2;
    }
    .pos-courier-banner {
        background: #f0f9ff;
        border: 1px dashed #0284c7;
        border-radius: 6px;
        padding: 7px 10px;
        margin-top: 6px;
        color: #0369a1;
        font-size: 12.5px;
        text-align: center;
    }
    .pos-customer-box {
        font-size: 13px;
        line-height: 1.45;
    }
    .pos-table-wrapper {
        margin: 12px 0;
        overflow-x: auto;
    }
    .pos-data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .pos-data-table th {
        border-bottom: 1px solid var(--invoice-slate-300, #cbd5e1);
        border-top: 1px solid var(--invoice-slate-300, #cbd5e1);
        padding: 6px 4px;
        text-align: left;
        font-weight: 700;
        color: var(--invoice-slate-800);
    }
    .pos-data-table td {
        padding: 6px 4px;
        border-bottom: 1px dashed var(--invoice-slate-200);
        vertical-align: top;
    }
    .pos-data-table tbody tr:last-child td {
        border-bottom: none;
    }
    .pos-summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 13px;
    }
    .pos-summary-total {
        font-size: 15px;
        font-weight: 800;
        border-top: 1px dashed var(--invoice-slate-800);
        padding-top: 6px;
        margin-top: 4px;
        color: var(--invoice-slate-900);
    }
    .pos-footer-text {
        text-align: center;
        font-size: 12px;
        color: var(--invoice-slate-500);
        margin-top: 8px;
    }

    /* Print Specific Styling */
    @page {
        margin: 0;
    }
    @media print {
        @page {
            @if($isPos)
            size: 80mm auto;
            margin: 3mm;
            @else
            size: a4 portrait;
            margin: 10mm;
            @endif
        }
        body, html, .content-page, .content {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .customer-invoice {
            margin: 0 !important;
            padding: 0 !important;
        }
        .no-print,
        header,
        footer,
        .navbar-custom,
        .left-side-menu,
        .page-title-box,
        .fraud-check-wrapper {
            display: none !important;
        }
        .standard-invoice-card,
        .pos-receipt-card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            border-radius: 0 !important;
        }
        .invoice-data-table th {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
        }
        .invoice-data-table td {
            border: 1px solid #cbd5e1 !important;
        }
        @if($isPos)
        .pos-footer-text,
        .pos-receipt-card .pos-dashed-separator:last-of-type {
            display: none !important;
        }
        @endif
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }

    /* Responsive Mobile Media Queries */
    @media (max-width: 768px) {
        .customer-invoice {
            margin: 12px 0 24px 0;
        }
        .invoice-action-bar {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            padding: 12px;
        }
        .invoice-action-group {
            width: 100%;
            justify-content: space-between;
        }
        .standard-invoice-card {
            padding: 20px 14px;
            border-radius: 8px;
        }
        .standard-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .invoice-meta-section {
            text-align: left;
            width: 100%;
        }
        .courier-tracking-pill {
            width: 100%;
            box-sizing: border-box;
            justify-content: center;
        }
        .invoice-summary-box {
            width: 100%;
        }
        .pos-receipt-card {
            padding: 16px 12px;
            border-radius: 6px;
        }
    }
</style>

<section class="customer-invoice">
    <div class="container-fluid">
        <!-- Top Action Bar (No Print) -->
        <div class="invoice-action-bar no-print">
            <div class="invoice-action-group">
                <a href="{{ route('admin.orders', ['slug' => 'all']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fe-arrow-left me-1"></i> Back To Orders
                </a>
            </div>
            <div class="invoice-action-group">
                @if($isPos)
                    <a href="{{ route('admin.order.invoice', ['invoice_id' => $invoice->invoiceId]) }}" class="btn btn-sm btn-outline-primary" title="Switch to Standard Invoice">
                        <i class="fe-file-text me-1"></i> Standard Invoice View
                    </a>
                    <button type="button" onclick="printThermal()" class="btn btn-sm btn-dark waves-effect waves-light" title="Print in 3x4 Thermal Size with Bold Text">
                        <i class="fe-printer me-1"></i> Thermal Print
                    </button>
                @else
                    <a href="{{ route('admin.order.invoice', ['invoice_id' => $invoice->invoiceId, 'pos' => 'true']) }}" class="btn btn-sm btn-outline-primary" title="Switch to POS Receipt">
                        <i class="fe-printer me-1"></i> POS Receipt View
                    </a>
                @endif
                <button type="button" onclick="window.print()" class="btn btn-sm btn-success waves-effect waves-light">
                    <i class="fa fa-print me-1"></i> Print Invoice
                </button>
            </div>
        </div>

        @if($isPos)
        <!-- ==================== POS THERMAL RECEIPT VIEW (?pos=true) ==================== -->
        <div class="pos-receipt-card">
            <!-- Header -->
            <div class="pos-header">
                <h2 class="pos-store-name">{{ $invoice->companyName }}</h2>
            </div>

            <div class="pos-dashed-separator"></div>

            <!-- Order Metadata -->
            <div class="pos-meta-grid">
                <div><strong>Invoice:</strong> #{{ $invoice->invoiceId }}</div>
                <div style="text-align: right;"><strong>Date:</strong> {{ $order->created_at ? $order->created_at->format('d-m-Y') : '' }}</div>
            </div>

            <!-- Courier Tracking -->
            @if($invoice->hasCourierTracking())
            <div class="pos-courier-banner">
                <i class="fe-truck"></i> {{ $invoice->courierName }} Tracking ID: <strong>{{ $invoice->courierTrackingId }}</strong>
            </div>
            @endif

            <div class="pos-dashed-separator"></div>

            <!-- Customer Details -->
            <div class="pos-customer-box">
                <div><strong>Customer:</strong> {{ $invoice->customerName }}</div>
                <div><strong>Phone:</strong> {{ $invoice->customerPhone }}</div>
                @if($invoice->customerAddress)
                    <div><strong>Address:</strong> {{ $invoice->customerAddress }}</div>
                @endif
            </div>

            <div class="pos-dashed-separator"></div>

            <!-- Items Table -->
            <div class="pos-table-wrapper">
                <table class="pos-data-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 45%;">Item</th>
                            <th style="width: 15%; text-align: center;">Qty</th>
                            <th style="width: 30%; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item['sl'] }}</td>
                            <td>
                                <div><strong>{{ $item['name'] }}</strong></div>
                                @if($item['size']) <span style="font-size: 11px; color: #64748b;">Size: {{ $item['size'] }}</span> @endif
                                @if($item['color']) <span style="font-size: 11px; color: #64748b;">Color: {{ $item['color'] }}</span> @endif
                            </td>
                            <td style="text-align: center;">{{ $item['quantity'] }}</td>
                            <td style="text-align: right;">{{ $item['formatted_line_total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pos-dashed-separator"></div>

            <!-- Summary -->
            <div>
                <div class="pos-summary-line">
                    <span>Subtotal:</span>
                    <span>{{ $invoice->getFormattedSubtotal() }}</span>
                </div>
                <div class="pos-summary-line">
                    <span>Shipping (+):</span>
                    <span>{{ $invoice->getFormattedShipping() }}</span>
                </div>
                @if($invoice->hasDiscount())
                <div class="pos-summary-line">
                    <span>Discount (-):</span>
                    <span>{{ $invoice->getFormattedDiscount() }}</span>
                </div>
                @endif
                <div class="pos-summary-line pos-summary-total">
                    <span>Total Payable:</span>
                    <span>{{ $invoice->getFormattedTotal() }}</span>
                </div>
            </div>

            <div class="pos-dashed-separator"></div>

            <!-- Footer -->
            <div class="pos-footer-text">
                <p style="margin: 0 0 3px 0; font-weight: 600;">Thank you for shopping with us!</p>
                <p style="margin: 0; font-style: italic;">* Computer-generated receipt, no signature required.</p>
            </div>
        </div>

        @else
        <!-- ==================== STANDARD ECOMMERCE INVOICE VIEW ==================== -->
        <div class="standard-invoice-card">
            <!-- Header Section -->
            <div class="standard-header">
                <div class="company-brand-section">
                    @if($invoice->companyLogo)
                        <img src="{{ asset($invoice->companyLogo) }}" alt="{{ $invoice->companyName }}" class="company-logo-img">
                    @endif
                    <h2 class="company-name-title">{{ $invoice->companyName }}</h2>
                    @if($invoice->companyAddress)
                        <p class="company-meta-text"><i class="fe-map-pin me-1"></i> {{ $invoice->companyAddress }}</p>
                    @endif
                    @if($invoice->companyPhone)
                        <p class="company-meta-text"><i class="fe-phone me-1"></i> {{ $invoice->companyPhone }}</p>
                    @endif
                    @if($invoice->companyEmail)
                        <p class="company-meta-text"><i class="fe-mail me-1"></i> {{ $invoice->companyEmail }}</p>
                    @endif
                </div>

                <div class="invoice-meta-section">
                    <h1 class="invoice-main-title">INVOICE</h1>
                    <div class="invoice-meta-item">Invoice ID: <strong>#{{ $invoice->invoiceId }}</strong></div>
                    <div class="invoice-meta-item">Invoice Date: <strong>{{ $order->created_at ? $order->created_at->format('d-m-Y') : '' }}</strong></div>
                    <div class="invoice-meta-item">Payment Method: <strong style="text-transform: uppercase;">{{ $invoice->paymentMethod }}</strong></div>

                    <!-- Courier Tracking -->
                    @if($invoice->hasCourierTracking())
                    <div>
                        <div class="courier-tracking-pill">
                            <i class="fe-truck"></i> <span>{{ $invoice->courierName }}</span> Tracking ID: <strong>{{ $invoice->courierTrackingId }}</strong>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer & Shipping Section -->
            <div class="billing-section-card">
                <div class="billing-title">Invoice To / Shipping Address</div>
                <div class="customer-name">{{ $invoice->customerName }}</div>
                <div class="customer-detail-text"><i class="fe-phone me-1"></i> {{ $invoice->customerPhone }}</div>
                @if($invoice->customerAddress)
                    <div class="customer-detail-text"><i class="fe-map-pin me-1"></i> {{ $invoice->customerAddress }}</div>
                @endif
                @if($invoice->customerArea)
                    <div class="customer-detail-text"><i class="fe-navigation me-1"></i> {{ $invoice->customerArea }}</div>
                @endif
            </div>

            <!-- Product Items Table -->
            <div class="invoice-table-responsive">
                <table class="invoice-data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">SL</th>
                            <th style="width: 8%;">Image</th>
                            <th style="width: 42%;">Product Details</th>
                            <th style="width: 15%; text-align: right;">Unit Price</th>
                            <th style="width: 12%; text-align: center;">Qty</th>
                            <th style="width: 18%; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td style="text-align: center; font-weight: 600; color: #64748b;">{{ $item['sl'] }}</td>
                            <td>
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;" alt="" onerror="this.style.display='none'">
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a;">{{ $item['name'] }}</div>
                                @if($item['size'])
                                    <span class="product-variant-tag">Size: {{ $item['size'] }}</span>
                                @endif
                                @if($item['color'])
                                    <span class="product-variant-tag">Color: {{ $item['color'] }}</span>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ $item['formatted_unit_price'] }}</td>
                            <td style="text-align: center; font-weight: 600;">{{ $item['quantity'] }}</td>
                            <td style="text-align: right; font-weight: 700; color: #0f172a;">{{ $item['formatted_line_total'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Totals Section -->
            <div class="invoice-summary-wrapper">
                <div class="invoice-summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong style="color: #0f172a;">{{ $invoice->getFormattedSubtotal() }}</strong>
                    </div>
                    <div class="summary-row">
                        <span>Shipping (+):</span>
                        <strong style="color: #0f172a;">{{ $invoice->getFormattedShipping() }}</strong>
                    </div>
                    @if($invoice->hasDiscount())
                    <div class="summary-row">
                        <span>Discount (-):</span>
                        <strong style="color: #dc2626;">{{ $invoice->getFormattedDiscount() }}</strong>
                    </div>
                    @endif
                    <div class="summary-row total-row">
                        <span>Final Total:</span>
                        <span class="total-amount">{{ $invoice->getFormattedTotal() }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer Section -->
            <div class="invoice-footer-section">
                <p class="footer-thank-you">Thank you for choosing {{ $invoice->companyName }}!</p>
                <p class="footer-computer-generated">* This is a computer generated invoice, does not require any physical signature.</p>
                <div style="margin-top: 6px;">
                    <a href="{{ route('page', ['slug' => 'terms-condition']) }}" class="text-muted" style="font-size: 12.5px; text-decoration: underline;">
                        Terms & Conditions
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- ==================== FRAUD CHECK SECTION (NO-PRINT) ==================== -->
        <div class="no-print fraud-check-wrapper mt-4">
            @php
                $fraud_check = $order->fraud_check();
                $total_stats = $fraud_check['total_stats'] ?? null;
                $individual_response = $fraud_check['individual_response'] ?? null;
                $hasError = isset($fraud_check['error']);
            @endphp

            @if($hasError)
                <div style="padding: 15px; background: #fee2e2; border-radius: 10px; color: #dc2626; max-width: 860px; margin: 0 auto;">
                    <strong>Fraud Check Alert:</strong> {{ $fraud_check['error'] }}
                </div>
            @else
                <div style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; max-width: 860px; margin: 0 auto; background: #f8fafc; border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                    <div style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 14px 20px; border-radius: 10px; margin-bottom: 16px;">
                        <h3 style="margin: 0; color: white; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                            <i class="fe-shield"></i>
                            <span>Courier Delivery Performance (Fraud Check)</span>
                        </h3>
                    </div>

                    @if($individual_response && isset($individual_response['Summaries']))
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($individual_response['Summaries'] as $courier => $data)
                                @php
                                    $total_courier = $data['Total Parcels'] ?? $data['Total Delivery'] ?? 0;
                                    $delivered_courier = $data['Delivered Parcels'] ?? $data['Successful Delivery'] ?? 0;
                                    $canceled_courier = $data['Canceled Parcels'] ?? $data['Canceled Delivery'] ?? 0;
                                    $courier_rate = $total_courier > 0 ? round(($delivered_courier / $total_courier) * 100) : 0;
                                    $courier_logo = [
                                        'Steadfast' => 'https://steadfast.com.bd/landing-page/asset/images/logo/logo.svg',
                                        'RedX' => 'https://redx.com.bd/images/new-redx-logo.svg',
                                        'Pathao' => 'https://pathao.com/wp-content/uploads/2019/02/Pathao-logo.svg',
                                        'Carrybee' => 'https://carrybee.com/wp-content/uploads/2025/01/cropped-Carrybee-Logo-04.png.webp'
                                    ];
                                @endphp
                                <div style="background: white; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            @if(!empty($courier_logo[$courier]))
                                                <img src="{{ $courier_logo[$courier] }}" style="height: 36px; width: 36px; object-fit: contain;" alt="{{ $courier }}" />
                                            @endif
                                            <div>
                                                <div style="font-weight: 600; color: #0f172a; font-size: 14px;">{{ $courier }}</div>
                                                <div style="font-size: 12px; color: #64748b;">Success Rate: <strong>{{ $courier_rate }}%</strong></div>
                                            </div>
                                        </div>
                                        <span class="badge bg-light text-dark" style="font-size: 12px; padding: 6px 10px; border: 1px solid #e2e8f0;">
                                            {{ $total_courier }} Total Parcels
                                        </span>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div style="display: flex; height: 10px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 10px;">
                                        <div style="width: {{ $total_courier > 0 ? ($delivered_courier / $total_courier) * 100 : 0 }}%; background: #10b981; height: 100%;"></div>
                                        <div style="width: {{ $total_courier > 0 ? ($canceled_courier / $total_courier) * 100 : 0 }}%; background: #ef4444; height: 100%;"></div>
                                    </div>

                                    <!-- Mini Stats -->
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                                        <div style="background: #f8fafc; padding: 6px; border-radius: 6px; text-align: center;">
                                            <div style="font-size: 11px; color: #64748b;">Total</div>
                                            <div style="font-weight: 700; font-size: 13px; color: #0f172a;">{{ $total_courier }}</div>
                                        </div>
                                        <div style="background: #f0fdf4; padding: 6px; border-radius: 6px; text-align: center;">
                                            <div style="font-size: 11px; color: #059669;">Delivered</div>
                                            <div style="font-weight: 700; font-size: 13px; color: #059669;">{{ $delivered_courier }}</div>
                                        </div>
                                        <div style="background: #fef2f2; padding: 6px; border-radius: 6px; text-align: center;">
                                            <div style="font-size: 11px; color: #dc2626;">Canceled</div>
                                            <div style="font-weight: 700; font-size: 13px; color: #dc2626;">{{ $canceled_courier }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="padding: 20px; text-align: center; color: #64748b; font-size: 13px;">No courier history data available for this customer.</div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>

<script>
    function printThermal() {
        var existingStyle = document.getElementById('thermal-print-stylesheet');
        if (!existingStyle) {
            var thermalStyle = document.createElement('style');
            thermalStyle.id = 'thermal-print-stylesheet';
            thermalStyle.innerHTML = `
                @media print {
                    @page {
                        size: 3in 4in !important;
                        margin: 0.05in !important;
                    }
                    html, body {
                        height: auto !important;
                        max-height: 4in !important;
                        overflow: hidden !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        background: #ffffff !important;
                    }
                    .pos-receipt-card {
                        width: 2.85in !important;
                        max-width: 2.85in !important;
                        margin: 0 auto !important;
                        padding: 0 !important;
                        box-shadow: none !important;
                        border: none !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        page-break-after: avoid !important;
                        overflow: hidden !important;
                    }
                    .pos-receipt-card,
                    .pos-receipt-card * {
                        font-weight: 700 !important;
                        color: #000000 !important;
                        border-color: #000000 !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                    }
                    .pos-header {
                        margin-bottom: 2px !important;
                    }
                    .pos-store-name {
                        font-size: 15px !important;
                        margin: 0 0 1px 0 !important;
                        line-height: 1.1 !important;
                    }
                    .pos-meta-grid {
                        font-size: 9.5px !important;
                        line-height: 1.2 !important;
                    }
                    .pos-courier-banner {
                        border: 1px dashed #000000 !important;
                        border-radius: 3px !important;
                        padding: 2px 4px !important;
                        margin: 2px 0 !important;
                        font-size: 10px !important;
                        line-height: 1.2 !important;
                        color: #000000 !important;
                        text-align: center !important;
                    }
                    .pos-customer-box {
                        font-size: 9.5px !important;
                        line-height: 1.25 !important;
                    }
                    .pos-table-wrapper {
                        margin: 2px 0 !important;
                    }
                    .pos-data-table {
                        font-size: 9px !important;
                        line-height: 1.2 !important;
                        width: 100% !important;
                    }
                    .pos-data-table th {
                        border-bottom: 1.5px solid #000000 !important;
                        border-top: 1.5px solid #000000 !important;
                        padding: 2px 2px !important;
                        font-weight: 800 !important;
                    }
                    .pos-data-table td {
                        padding: 2px 2px !important;
                        border-bottom: 1px dashed #000000 !important;
                        vertical-align: top !important;
                    }
                    .pos-summary-line {
                        padding: 1px 0 !important;
                        font-size: 9.5px !important;
                    }
                    .pos-summary-total {
                        font-size: 11.5px !important;
                        font-weight: 800 !important;
                        border-top: 1.5px dashed #000000 !important;
                        padding-top: 2px !important;
                        margin-top: 2px !important;
                    }
                    .pos-dashed-separator {
                        border-top: 1px dashed #000000 !important;
                        margin: 3px 0 !important;
                    }
                    .pos-footer-text,
                    .pos-receipt-card .pos-dashed-separator:last-of-type {
                        display: none !important;
                    }
                }
            `;
            document.head.appendChild(thermalStyle);
        }

        window.print();

        window.addEventListener('afterprint', function cleanup() {
            var style = document.getElementById('thermal-print-stylesheet');
            if (style) {
                style.remove();
            }
            window.removeEventListener('afterprint', cleanup);
        }, { once: true });
    }
</script>
@endsection
