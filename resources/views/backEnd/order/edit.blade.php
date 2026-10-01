@extends('backEnd.layouts.master') 
@section('title', 'Edit Order #' . $order->invoice_id) 
@section('css')
<link href="{{ asset('backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<style>
    .order-summary-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .sticky-summary {
        position: -webkit-sticky;
        position: sticky;
        top: 80px;
        z-index: 10;
    }
    .badge-soft-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    .badge-soft-info {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .customer-stat-pill {
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
    }
    .input-group.flex-nowrap, .table .input-group {
        flex-wrap: nowrap !important;
    }
    .fraud-kpi-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }
    .circular-gauge-container {
        position: relative;
        width: 115px;
        height: 115px;
        margin: 0 auto;
    }
    .circular-gauge-svg {
        transform: rotate(-90deg);
        width: 115px;
        height: 115px;
    }
    .circular-gauge-bg {
        fill: none;
        stroke: #e5e7eb;
        stroke-width: 9;
    }
    .circular-gauge-bar {
        fill: none;
        stroke-width: 9;
        stroke-linecap: round;
        transition: stroke-dashoffset 0.8s cubic-bezier(0.4, 0, 0.2, 1), stroke 0.3s ease;
    }
    .circular-gauge-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        width: 100%;
        line-height: 1;
    }
    .fraud-breakdown-table th {
        background-color: #f8fafc !important;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 6px 10px;
    }
    .fraud-breakdown-table td {
        padding: 6px 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .fraud-breakdown-table tr:last-child td {
        border-bottom: none;
    }
</style>
@endsection 

@section('content')
<div class="container-fluid">
    <!-- Page Header & Action Bar -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 mb-2">
                <div>
                    <h4 class="page-title mb-1 d-flex align-items-center flex-wrap gap-2">
                        <span>Edit Order #{{ $order->invoice_id }}</span>
                        @php
                            $statusSlug = strtolower($order->status?->slug ?? '');
                            $statusClass = match($statusSlug) {
                                'delivered', 'completed' => 'badge-soft-success',
                                'cancelled', 'canceled' => 'badge-soft-danger',
                                'processing', 'in-courier', 'shipped' => 'badge-soft-info',
                                default => 'badge-soft-warning'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} font-12">{{ $order->status?->name ?? 'Pending' }}</span>
                        <span class="badge bg-light text-muted border font-11">
                            <i class="fe-calendar me-1"></i> {{ date('d M, Y h:i A', strtotime($order->created_at)) }}
                        </span>
                        @if($order->isCourierDispatched())
                            <span class="badge badge-soft-info font-11">
                                <i class="fe-truck me-1"></i> {{ $order->courier_display_name }} ({{ ucwords(str_replace('_', ' ', $order->courier_status ?? 'dispatched')) }})
                            </span>
                        @endif
                    </h4>
                    <ol class="breadcrumb m-0 font-12">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.orders', $order->status?->slug ?? 'all') }}">Orders</a></li>
                        <li class="breadcrumb-item active">Edit #{{ $order->invoice_id }}</li>
                    </ol>
                </div>
                    @if(!$order->isCourierDispatched())
                        <button type="button" class="btn btn-warning btn-sm rounded-pill btn-send-courier-trigger shadow-sm" title="Dispatch Order to SteadFast Courier">
                            <i class="fe-send me-1"></i> Send to Courier
                        </button>
                    @elseif($order->courier_tracking_url)
                        <a href="{{ $order->courier_tracking_url }}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill" title="Track Parcel on Courier Portal">
                            <i class="fe-truck me-1"></i> Track Parcel
                        </a>
                    @endif
                    <a href="{{ route('admin.orders', $order->status?->slug ?? 'all') }}" class="btn btn-outline-secondary btn-sm rounded-pill" title="Back to Orders">
                        <i class="fe-arrow-left me-1"></i> Back to Orders
                    </a>
                    <a href="{{ route('admin.order.invoice', $order->invoice_id) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill" title="View Customer Invoice">
                        <i class="fe-eye me-1"></i> View Invoice
                    </a>
                    <a href="{{ route('admin.order.invoice', $order->invoice_id) }}?pos=true" target="_blank" class="btn btn-outline-purple btn-sm rounded-pill" title="Print POS Thermal Receipt">
                        <i class="fe-printer me-1"></i> POS Print
                    </a>
                    <a href="{{ route('admin.order.edit.reset', $order->invoice_id) }}" class="btn btn-outline-warning btn-sm rounded-pill" onclick="return confirm('Are you sure you want to discard unsaved cart changes and reload saved items from database?')" title="Reset Cart to Saved State">
                        <i class="fe-rotate-ccw me-1"></i> Reset Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Order Edit Form -->
    <form action="{{ route('admin.order.update') }}" method="POST" id="orderEditForm" class="pos_form" data-parsley-validate="" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{ $order->id }}" name="order_id">

        <div class="row">
            <!-- Left Column: Products & Line Items (col-lg-8) -->
            <div class="col-lg-8">
                <!-- Barcode & Product Search Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title font-14 mb-0 text-dark">
                            <i class="fe-plus-circle text-primary me-1"></i> Add Products to Order
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label for="barcode_search" class="form-label font-12 fw-bold text-dark mb-1">
                                    Barcode / SKU Quick Scan
                                </label>
                                <input type="text" id="barcode_search" class="form-control form-control-sm" placeholder="Scan Barcode / SKU & press Enter..." autocomplete="off" />
                                <small id="barcode_feedback" class="d-block mt-1 font-11"></small>
                            </div>
                            <div class="col-md-7">
                                <label for="cart_add" class="form-label font-12 fw-bold text-dark mb-1">
                                    <i class="fe-search me-1"></i> Search & Select Product
                                </label>
                                <select id="cart_add" class="form-control select2">
                                    <option value="">Search by product name, code or SKU...</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">
                                            {{ $prod->name }} [{{ $prod->product_code ?? 'ID: ' . $prod->id }}] - ৳{{ number_format($prod->new_price, 2) }} (Stock: {{ $prod->stock }})
                                        </option> 
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Line Items Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light py-2 d-flex align-items-center justify-content-between">
                        <h5 class="card-title font-14 mb-0 text-dark">
                            <i class="fe-shopping-bag text-primary me-1"></i> Order Items
                        </h5>
                        <span class="text-muted font-12">Adjust size, color, quantity or item discounts</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-light font-12 text-muted">
                                    <tr>
                                        <th style="width: 8%;" class="text-center">Image</th>
                                        <th style="width: 26%;">Product Name & SKU</th>
                                        <th style="width: 18%;">Attributes</th>
                                        <th style="width: 14%;" class="text-center">Quantity</th>
                                        <th style="width: 12%;" class="text-end">Unit Price</th>
                                        <th style="width: 10%;" class="text-center">Discount (৳)</th>
                                        <th style="width: 12%;" class="text-end">Subtotal</th>
                                        <th style="width: 4%;" class="text-center"><i class="fe-trash-2 text-danger"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="cartTable">
                                    @include('backEnd.order.cart_content', ['cartinfo' => $cartinfo, 'cartProducts' => $cartProducts ?? collect()])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customer & Shipping Info and Payment Information (Placed above Order Notes) -->
                <div class="row g-2 mb-3">
                    <!-- Customer & Shipping Info Card -->
                    <div class="col-md-7">
                        <div class="card shadow-sm mb-0 h-100">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <h5 class="card-title font-14 mb-0 text-dark">
                                    <i class="fe-user text-primary me-1"></i> Customer & Shipping Info
                                </h5>
                                @if(!empty($customerStats) && $customerStats['total_orders'] > 0)
                                    <span class="badge bg-soft-info text-info font-11" title="Customer Lifetime Orders">
                                        <i class="fe-archive me-1"></i>{{ $customerStats['total_orders'] }} Orders
                                    </span>
                                @endif
                            </div>
                            <div class="card-body p-3">
                                <!-- Customer Quick Contact & Fraud Metric -->
                                @php
                                    $customerPhone = $shippinginfo?->phone ?? $order->customer?->phone ?? '';
                                    $waPhone = \App\ValueObjects\Phone::toWhatsApp($customerPhone);
                                    $waMsg = urlencode("Hello, this is regarding your order #{$order->invoice_id} from BrandCityBD.");
                                @endphp
                                @if(!empty($customerPhone))
                                    <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded bg-light border">
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="tel:{{ $customerPhone }}" class="btn btn-xs btn-outline-primary" title="Call Customer">
                                                <i class="fe-phone me-1"></i> Call
                                            </a>
                                            <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}" target="_blank" class="btn btn-xs btn-outline-success" title="Chat on WhatsApp">
                                                <i class="fe-message-circle me-1"></i> WhatsApp
                                            </a>
                                        </div>
                                        <x-order-guard :order="$order" mode="badge" />
                                    </div>
                                @endif

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-6">
                                        <label for="name" class="form-label font-12 fw-semibold text-dark mb-1">Customer Name *</label>
                                        <input type="text" id="name" class="form-control form-control-sm @error('name') is-invalid @enderror" placeholder="Customer Name" name="name" value="{{ old('name', $shippinginfo?->name ?? $order->customer?->name ?? '') }}" required>
                                        @error('name')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="phone" class="form-label font-12 fw-semibold text-dark mb-1">Customer Phone *</label>
                                        <input type="text" id="phone" class="form-control form-control-sm @error('phone') is-invalid @enderror" placeholder="Customer Phone" name="phone" value="{{ old('phone', $customerPhone) }}" required>
                                        @error('phone')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mb-2">
                                    <label for="address" class="form-label font-12 fw-semibold text-dark mb-1">Delivery Address *</label>
                                    <textarea id="address" class="form-control form-control-sm @error('address') is-invalid @enderror" placeholder="Delivery Address" name="address" rows="2" required>{{ old('address', $shippinginfo?->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <label for="area" class="form-label font-12 fw-semibold text-dark mb-1">Delivery Area *</label>
                                        <select id="area" class="form-control form-control-sm @error('area') is-invalid @enderror" name="area" required>
                                            <option value="">Select Delivery Area...</option>
                                            @foreach($shippingcharge as $key => $value)
                                                <option value="{{ $value->id }}" @if(($shippinginfo?->area ?? '') == $value->name || (float)$order->shipping_charge == (float)$value->amount) selected @endif>
                                                    {{ $value->name }} (৳{{ number_format($value->amount, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('area')
                                            <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                        @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="custom_shipping" class="form-label font-11 text-muted mb-1">Custom Delivery Charge (৳)</label>
                                        <input type="number" step="any" min="0" name="custom_shipping" id="custom_shipping" class="form-control form-control-sm" value="{{ (float)$order->shipping_charge }}" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information Card -->
                    <div class="col-md-5">
                        <div class="card shadow-sm mb-0 h-100">
                            <div class="card-header bg-light py-2">
                                <h5 class="card-title font-14 mb-0 text-dark">
                                    <i class="fe-credit-card text-primary me-1"></i> Payment Information
                                </h5>
                            </div>
                            <div class="card-body p-3">
                                @php
                                    $paymentMethod = $order->payment?->payment_method ?? 'Cash On Delivery';
                                    $paymentStatus = $order->payment?->payment_status ?? ($order->payment_status ?? 'pending');
                                @endphp
                                <div class="form-group mb-2">
                                    <label for="payment_method" class="form-label font-12 fw-semibold text-dark mb-1">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-select form-select-sm">
                                        <option value="Cash On Delivery" {{ strcasecmp($paymentMethod, 'Cash On Delivery') === 0 ? 'selected' : '' }}>Cash On Delivery</option>
                                        <option value="bKash" {{ strcasecmp($paymentMethod, 'bKash') === 0 ? 'selected' : '' }}>bKash</option>
                                        <option value="Nagad" {{ strcasecmp($paymentMethod, 'Nagad') === 0 ? 'selected' : '' }}>Nagad</option>
                                        <option value="Rocket" {{ strcasecmp($paymentMethod, 'Rocket') === 0 ? 'selected' : '' }}>Rocket</option>
                                        <option value="Bank Transfer" {{ strcasecmp($paymentMethod, 'Bank Transfer') === 0 ? 'selected' : '' }}>Bank Transfer</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="payment_status" class="form-label font-12 fw-semibold text-dark mb-1">Payment Status</label>
                                    <select name="payment_status" id="payment_status" class="form-select form-select-sm">
                                        <option value="pending" {{ strcasecmp($paymentStatus, 'pending') === 0 ? 'selected' : '' }}>Pending</option>
                                        <option value="paid" {{ strcasecmp($paymentStatus, 'paid') === 0 ? 'selected' : '' }}>Paid</option>
                                        <option value="failed" {{ strcasecmp($paymentStatus, 'failed') === 0 ? 'selected' : '' }}>Failed</option>
                                        <option value="refunded" {{ strcasecmp($paymentStatus, 'refunded') === 0 ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                </div>
                                <div class="form-group mb-2">
                                    <label for="trx_id" class="form-label font-11 text-muted mb-1">Transaction ID (TrxID)</label>
                                    <input type="text" name="trx_id" id="trx_id" class="form-control form-control-sm" value="{{ old('trx_id', $order->payment?->trx_id ?? '') }}" placeholder="bKash/Nagad/Bank TrxID">
                                </div>
                                <div class="form-group mb-0">
                                    <label for="sender_number" class="form-label font-11 text-muted mb-1">Sender Phone Number</label>
                                    <input type="text" name="sender_number" id="sender_number" class="form-control form-control-sm" value="{{ old('sender_number', $order->payment?->sender_number ?? '') }}" placeholder="017xxxxxxxx">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fraud Checker Courier Delivery History KPI Widget -->
                <x-order-guard :order="$order" mode="card" />

                <!-- Notes Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title font-14 mb-0 text-dark">
                            <i class="fe-file-text text-primary me-1"></i> Order Notes & Instructions
                        </h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="admin_note" class="form-label font-12 fw-bold text-dark mb-1">
                                    <i class="fe-alert-circle text-warning me-1"></i> Internal Admin Note / Hold Reason
                                </label>
                                <textarea name="admin_note" id="admin_note" rows="3" class="form-control font-12" placeholder="Internal instructions, verification status, call logs, reasons for hold...">{{ old('admin_note', $order->admin_note ?? '') }}</textarea>
                                <small class="text-muted font-11">Internal staff note. Not visible to the customer.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="note" class="form-label font-12 fw-bold text-dark mb-1">
                                    <i class="fe-message-square text-info me-1"></i> Customer Order Note
                                </label>
                                <textarea name="note" id="note" rows="3" class="form-control font-12" placeholder="Customer special requests, preferred delivery time, landmarks...">{{ old('note', $order->note ?? '') }}</textarea>
                                <small class="text-muted font-11">Instructions provided by customer during checkout.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar (col-lg-4) -->
            <div class="col-lg-4 position-sticky" style="top: 75px; align-self: flex-start; z-index: 10;">
                <!-- 1. Order Status & Courier Logistics Card -->
                <div class="card shadow-sm mb-2 border-info border-top border-2">
                    <div class="card-header bg-light py-1.5 px-3">
                        <h5 class="card-title font-13 mb-0 text-dark">
                            <i class="fe-truck text-primary me-1"></i> Order Status & Courier
                        </h5>
                    </div>
                    <div class="card-body p-2.5">
                        <!-- Courier Dispatch or Tracking Box -->
                        @if($order->isCourierDispatched())
                            <div class="p-2 rounded bg-soft-info border border-info mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-12 fw-bold text-info">
                                        <i class="fe-truck me-1"></i> {{ $order->courier_display_name }} Managed
                                    </span>
                                    <i class="fe-lock text-muted font-11" title="Status managed via courier sync"></i>
                                </div>
                                <div class="font-11 text-muted mb-1.5">
                                    Live Status: <strong class="text-dark">{{ ucwords(str_replace('_', ' ', $order->courier_status ?? 'in_review')) }}</strong>
                                </div>
                                <div class="d-flex gap-1 flex-wrap">
                                    <button type="button" id="btn-check-courier-status" class="btn btn-xs btn-outline-info rounded-pill py-0 px-2">
                                        <i class="fe-refresh-cw me-1"></i> Check Live Status
                                    </button>
                                    @if($order->courier_tracking_url)
                                        <a href="{{ $order->courier_tracking_url }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill py-0 px-2">
                                            <i class="fe-external-link me-1"></i> Courier Portal
                                        </a>
                                    @endif
                                </div>
                                <div id="courier-live-status-result" class="mt-1.5 font-11" style="display:none;"></div>
                            </div>
                        @else
                            <div class="p-2 rounded bg-soft-warning border border-warning mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="font-12 fw-bold text-dark">
                                        <i class="fe-truck text-warning me-1"></i> SteadFast Courier
                                    </span>
                                    <span class="badge bg-warning text-dark font-10">Not Dispatched</span>
                                </div>
                                <p class="font-11 text-muted mb-1.5">
                                    Book directly to SteadFast. COD: <strong class="text-dark">৳{{ number_format($order->amount, 2) }}</strong>
                                </p>
                                <button type="button" id="btn-send-single-steadfast" class="btn btn-xs btn-warning w-100 fw-bold shadow-sm py-1">
                                    <i class="fe-send me-1"></i> Send to SteadFast Courier
                                </button>
                            </div>
                        @endif

                        <div class="form-group mb-1">
                            <label for="order_status" class="form-label font-11 fw-semibold text-dark mb-0.5">Order Status</label>
                            @if($order->isCourierLocked())
                                <input type="hidden" name="order_status" value="{{ $order->order_status }}">
                                <input type="text" class="form-control form-control-sm bg-light text-muted py-1 font-12" value="{{ $order->status?->name ?? 'Shipped' }} (Courier Locked)" readonly>
                                <small class="text-muted font-10 d-block mt-0.5">Status is synchronized via courier updates.</small>
                            @else
                                <select name="order_status" id="order_status" class="form-select form-select-sm py-1 font-12 @error('order_status') is-invalid @enderror">
                                    @foreach(($orderstatus ?? []) as $status)
                                        <option value="{{ $status->id }}" {{ (int)$order->order_status === (int)$status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        @if(!empty($order->tracking_code) || !empty($order->consignment_id))
                            <div class="border-top pt-1.5 mt-1.5 font-11">
                                <div class="d-flex justify-content-between mb-0.5">
                                    <span class="text-muted">Consignment ID:</span>
                                    <span class="fw-semibold">{{ $order->consignment_id ?: '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Tracking Code:</span>
                                    <span class="fw-semibold">{{ $order->tracking_code ?: '-' }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Order Financial Summary (Compact & Focused) -->
                <div class="card shadow-sm mb-2 border-primary border-top border-2 sticky-summary">
                    <div class="card-header bg-light py-1.5 px-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title font-13 mb-0 text-dark">
                            <i class="fe-dollar-sign text-success me-1"></i> Financial Summary
                        </h5>
                        <span class="badge bg-soft-primary text-primary font-10">Live Calc</span>
                    </div>
                    <div class="card-body p-2.5">
                        <table class="table table-sm table-borderless align-middle mb-1 font-12">
                            <tbody id="cart_details">
                                @include('backEnd.order.cart_details')
                            </tbody>
                        </table>

                        <hr class="my-1.5">

                        <!-- Adjustments (Flat Discount & Advance Payment) -->
                        <div class="row g-1.5 mb-2">
                            <div class="col-6">
                                <label for="order_discount" class="form-label font-10 fw-bold text-muted mb-0.5">Flat Discount (৳)</label>
                                <input type="number" step="any" min="0" name="order_discount" id="order_discount" class="form-control form-control-sm text-end py-1 font-12" value="0" placeholder="0.00">
                            </div>
                            <div class="col-6">
                                <label for="advance_amount" class="form-label font-10 fw-bold text-muted mb-0.5">Advance Paid (৳)</label>
                                <input type="number" step="any" min="0" name="advance_amount" id="advance_amount" class="form-control form-control-sm text-end py-1 font-12" value="0" placeholder="0.00">
                            </div>
                        </div>

                        <!-- Final Net Amount Display -->
                        <div class="p-1.5 rounded bg-soft-success border border-success mb-2 text-center">
                            <span class="text-muted font-11 d-block text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Net COD Collection</span>
                            <h4 class="text-success mb-0 fw-bold font-20" id="final_cod_amount">৳{{ number_format($order->amount, 2) }}</h4>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm font-14" id="submitOrderBtn">
                            <i class="fe-check-circle me-1"></i> Save & Update Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('backEnd.order.attribute_modal')
@endsection 

@section('script')
<script src="{{ asset('backEnd') }}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{ asset('backEnd') }}/assets/js/pages/form-validation.init.js"></script>
<script src="{{ asset('backEnd') }}/assets/libs/select2/js/select2.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2({ width: '100%' });
        updateFinalCODCalculation();
    });

    // Centralized AJAX content loaders
    function cart_content() {
        $.ajax({
            type: "GET",
            url: "{{ route('admin.order.cart_content') }}",
            dataType: "html",
            success: function(html) {
                $('#cartTable').html(html);
                updateFinalCODCalculation();
            }
        });
    }

    function cart_details() {
        $.ajax({
            type: "GET",
            url: "{{ route('admin.order.cart_details') }}",
            dataType: "html",
            success: function(html) {
                $('#cart_details').html(html);
                updateFinalCODCalculation();
            }
        });
    }

    function updateFinalCODCalculation() {
        var subtotal = parseFloat($('#calc_subtotal').data('val')) || 0;
        var customShipping = parseFloat($('#custom_shipping').val());
        var shipping = (!isNaN(customShipping) && customShipping >= 0) ? customShipping : (parseFloat($('#calc_shipping').data('val')) || 0);
        var itemDiscount = parseFloat($('#calc_discount').data('val')) || 0;
        var flatDiscount = parseFloat($('#order_discount').val()) || 0;
        var advancePaid = parseFloat($('#advance_amount').val()) || 0;

        var netCOD = Math.max(0, (subtotal + shipping) - (itemDiscount + flatDiscount) - advancePaid);
        $('#final_cod_amount').text('৳' + netCOD.toFixed(2));
    }

    $(document).on('input change', '#order_discount, #advance_amount, #custom_shipping', function() {
        updateFinalCODCalculation();
    });

    function addProductToCart(id, size, color, qty) {
        $.ajax({
            cache: false,
            type: "GET",
            data: {
                'id': id,
                'product_size': size || '',
                'product_color': color || '',
                'qty': qty || 1
            },
            url: "{{ route('admin.order.cart_add') }}",
            dataType: "json",
            success: function() {
                cart_content();
                cart_details();
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.error || 'Failed to add product to order');
            }
        });
    }

    function showAttributeModal(product) {
        $('#attr_modal_product_id').val(product.id);
        $('#attr_modal_name').text(product.name);
        $('#attr_modal_price').text('৳' + parseFloat(product.new_price).toFixed(2));
        $('#attr_modal_stock').text('Stock: ' + product.stock);
        if (product.image) {
            $('#attr_modal_img').attr('src', product.image).show();
        } else {
            $('#attr_modal_img').hide();
        }
        $('#attr_modal_qty').val(1);

        // Render sizes
        if (product.sizes && product.sizes.length > 0) {
            var sizeHtml = '';
            $.each(product.sizes, function(idx, s) {
                var checked = (idx === 0) ? 'checked' : '';
                sizeHtml += '<input type="radio" class="btn-check" name="attr_modal_size" id="attr_size_' + s.id + '" value="' + s.name + '" ' + checked + ' autocomplete="off">';
                sizeHtml += '<label class="btn btn-outline-primary btn-sm px-2 py-1 font-12" for="attr_size_' + s.id + '">' + s.name + '</label>';
            });
            $('#attr_modal_sizes').html(sizeHtml);
            $('#attr_modal_size_group').show();
        } else {
            $('#attr_modal_sizes').empty();
            $('#attr_modal_size_group').hide();
        }

        // Render colors
        if (product.colors && product.colors.length > 0) {
            var colorHtml = '';
            $.each(product.colors, function(idx, c) {
                var checked = (idx === 0) ? 'checked' : '';
                colorHtml += '<input type="radio" class="btn-check" name="attr_modal_color" id="attr_color_' + c.id + '" value="' + c.name + '" ' + checked + ' autocomplete="off">';
                colorHtml += '<label class="btn btn-outline-secondary btn-sm px-2 py-1 font-12" for="attr_color_' + c.id + '">' + (c.color ? '<span style="display:inline-block;width:10px;height:10px;background:' + c.color + ';border-radius:50%;margin-right:4px;"></span>' : '') + c.name + '</label>';
            });
            $('#attr_modal_colors').html(colorHtml);
            $('#attr_modal_color_group').show();
        } else {
            $('#attr_modal_colors').empty();
            $('#attr_modal_color_group').hide();
        }

        var modalEl = document.getElementById('productAttributeModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function checkAndAddProduct(id) {
        if (!id) return;
        $.ajax({
            type: "GET",
            url: "{{ route('admin.order.product_attributes') }}",
            data: { id: id },
            dataType: "json",
            success: function(res) {
                if (res.has_attributes) {
                    showAttributeModal(res);
                } else {
                    addProductToCart(id, '', '', 1);
                }
            },
            error: function() {
                addProductToCart(id, '', '', 1);
            }
        });
    }

    $(document).on('change', '#cart_add', function(e) {
        var id = $(this).val();
        if (id) {
            checkAndAddProduct(id);
            $(this).val('').trigger('change.select2');
        }
    });

    $(document).on('click', '#attr_modal_submit_btn', function(e) {
        e.preventDefault();
        var id = $('#attr_modal_product_id').val();
        var size = $('input[name="attr_modal_size"]:checked').val() || '';
        var color = $('input[name="attr_modal_color"]:checked').val() || '';
        var qty = parseInt($('#attr_modal_qty').val()) || 1;

        addProductToCart(id, size, color, qty);

        var modalEl = document.getElementById('productAttributeModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }
    });

    // Barcode & SKU Enter Key Listener
    $(document).on("keydown", "#barcode_search", function (e) {
        if (e.key === "Enter" || e.keyCode === 13) {
            e.preventDefault();
            var query = $(this).val().trim();
            if (!query) return;

            $("#barcode_feedback").html('<span class="text-primary"><i class="fa fa-spinner fa-spin"></i> Searching...</span>');

            $.ajax({
                type: "GET",
                url: "{{ route('admin.order.product_search') }}",
                data: { q: query },
                dataType: "json",
                success: function (res) {
                    if (res.exact_match && res.matched_product) {
                        checkAndAddProduct(res.matched_product.id);
                        $("#barcode_feedback").html('<span class="text-success"><i class="fa fa-check"></i> ' + res.matched_product.name + '</span>');
                        $("#barcode_search").val('').focus();
                    } else if (res.products && res.products.length === 1) {
                        checkAndAddProduct(res.products[0].id);
                        $("#barcode_feedback").html('<span class="text-success"><i class="fa fa-check"></i> ' + res.products[0].name + '</span>');
                        $("#barcode_search").val('').focus();
                    } else if (res.products && res.products.length > 1) {
                        $("#barcode_feedback").html('<span class="text-warning">Multiple matches (' + res.products.length + '). Select from dropdown.</span>');
                    } else {
                        $("#barcode_feedback").html('<span class="text-danger"><i class="fa fa-times"></i> No product found for "' + query + '"</span>');
                    }
                },
                error: function () {
                    $("#barcode_feedback").html('<span class="text-danger">Error searching product.</span>');
                }
            });
        }
    });

    // Quantity Steppers & Removal Event Listeners
    $(document).on('click', '.cart_increment', function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        var qty = $(this).val();
        if (id) {
            $.ajax({
                cache: false,
                data: { 'id': id, 'qty': qty },
                type: "GET",
                url: "{{ route('admin.order.cart_increment') }}",
                dataType: "json",
                success: function() {
                    cart_content();
                    cart_details();
                }
            });
        }
    });

    $(document).on('click', '.cart_decrement', function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        var qty = $(this).val();
        if (id) {
            $.ajax({
                cache: false,
                type: "GET",
                data: { 'id': id, 'qty': qty },
                url: "{{ route('admin.order.cart_decrement') }}",
                dataType: "json",
                success: function() {
                    cart_content();
                    cart_details();
                }
            });
        }
    });

    $(document).on('click', '.cart_remove', function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        if (id && confirm('Remove this product from order?')) {
            $.ajax({
                cache: false,
                type: "GET",
                data: { 'id': id },
                url: "{{ route('admin.order.cart_remove') }}",
                dataType: "json",
                success: function() {
                    cart_content();
                    cart_details();
                }
            });
        }
    });

    $(document).on('change', '.product_discount', function() {
        var id = $(this).data("id");
        var discount = $(this).val();
        $.ajax({
            cache: false,
            type: "GET",
            data: { 'id': id, 'discount': discount },
            url: "{{ route('admin.order.product_discount') }}",
            dataType: "json",
            success: function() {
                cart_content();
                cart_details();
            }
        });
    });

    $(document).on('change', '.cart_attribute_change', function(e) {
        var rowId = $(this).data('id');
        var type = $(this).data('type');
        var val = $(this).val();
        var data = { rowId: rowId };
        if (type === 'size') {
            data.product_size = val;
        } else if (type === 'color') {
            data.product_color = val;
        } else if (type === 'image') {
            data.image = val;
        }
        $.ajax({
            cache: false,
            type: "GET",
            data: data,
            url: "{{ route('admin.order.cart_update_attribute') }}",
            dataType: "json",
            success: function() {
                cart_content();
                cart_details();
            }
        });
    });

    $(document).on('click', '.cart_image_select', function(e) {
        e.preventDefault();
        var rowId = $(this).data('id');
        var imagePath = $(this).data('image');
        $.ajax({
            cache: false,
            type: "GET",
            data: { id: rowId, image: imagePath },
            url: "{{ route('admin.order.cart_update_attribute') }}",
            dataType: "json",
            success: function() {
                cart_content();
                cart_details();
            }
        });
    });

    $(document).on('change', '#area', function() {
        var id = $(this).val();
        $.ajax({
            type: "GET",
            data: { id: id },
            url: "{{ route('admin.order.cart_shipping') }}",
            dataType: "json",
            success: function(amount) {
                $('#custom_shipping').val(amount);
                cart_details();
            }
        });
    });

    // Courier Dispatch and Live Status Check Event Handlers
    $(document).on('click', '#btn-send-single-steadfast, .btn-send-courier-trigger', function(e) {
        e.preventDefault();
        var $mainBtn = $('#btn-send-single-steadfast');
        var $topBtn = $('.btn-send-courier-trigger');

        if (!confirm('Are you sure you want to dispatch order #{{ $order->invoice_id }} to SteadFast Courier? (COD: ৳{{ number_format($order->amount, 2) }})')) {
            return;
        }

        $mainBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Dispatching...');
        $topBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sending...');

        $.ajax({
            type: 'POST',
            url: "{{ route('admin.order.send_steadfast', $order->id) }}",
            data: {
                _token: "{{ csrf_token() }}"
            },
            dataType: 'json',
            success: function(res) {
                $mainBtn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to SteadFast Courier');
                $topBtn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to Courier');

                if (res.status === 'success') {
                    toastr.success(res.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    toastr.error(res.message || 'Failed to dispatch order to SteadFast');
                }
            },
            error: function(xhr) {
                $mainBtn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to SteadFast Courier');
                $topBtn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to Courier');
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error sending order to SteadFast';
                toastr.error(msg);
            }
        });
    });

    $(document).on('click', '#btn-check-courier-status', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $result = $('#courier-live-status-result');

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Checking...');
        $result.show().html('<span class="text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Fetching live status...</span>');

        $.ajax({
            type: 'GET',
            url: "{{ route('admin.order.courier_status', $order->id) }}",
            dataType: 'json',
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fe-refresh-cw me-1"></i> Check Live Status');
                if (res.status === 'success') {
                    var badgeClass = 'bg-info';
                    var stat = (res.delivery_status || '').toLowerCase();
                    if (stat === 'delivered' || stat === 'completed') badgeClass = 'bg-success';
                    else if (stat === 'cancelled' || stat === 'returned') badgeClass = 'bg-danger';
                    else if (stat === 'in_transit' || stat === 'shipped') badgeClass = 'bg-primary';

                    var changeNotice = res.status_changed ? ' <small class="text-success ms-1">(Status updated!)</small>' : '';
                    $result.html('<strong>Current Status:</strong> <span class="badge ' + badgeClass + ' text-uppercase font-11 ms-1">' + res.delivery_status + '</span>' + changeNotice);
                    toastr.info('SteadFast Status: ' + res.delivery_status);
                } else {
                    $result.html('<span class="text-danger">' + (res.message || 'Failed to fetch status') + '</span>');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="fe-refresh-cw me-1"></i> Check Live Status');
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error checking courier status';
                $result.html('<span class="text-danger">' + msg + '</span>');
            }
        });
    });

    $(document).ready(function() {
        var orderId = {{ $order->id }};
        if (orderId && typeof OrderGuard !== 'undefined') {
            // Automatic non-blocking background sync if not yet evaluated
            var hasEvaluated = parseInt($('#btn-refresh-fraud-score').data('current-score') || 0) > 0;
            if (!hasEvaluated) {
                setTimeout(function() {
                    OrderGuard.refresh(orderId, { force: false, silent: true });
                }, 300);
            }
        }
    });
</script>
@endsection
