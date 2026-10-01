@extends('frontEnd.layouts.master')
@section('title', 'Order Success')
@section('content')
<style>
    .order-success-page {
        background: #f8fafc;
        min-height: 85vh;
    }
    .order-success-container {
        max-width: 820px;
        margin: 0 auto;
    }
    /* Sunburst / Celebration Badge */
    .celebration-badge-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }
    .celebration-rays {
        position: absolute;
        top: -16px;
        width: 120px;
        height: 52px;
        pointer-events: none;
    }
    .celebration-circle {
        width: 64px;
        height: 64px;
        background: #16a34a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35);
        position: relative;
        z-index: 2;
    }
    .celebration-circle svg {
        width: 34px;
        height: 34px;
        stroke: #ffffff;
    }
    .thankyou-title {
        font-size: 26px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 8px;
        text-align: center !important;
    }
    .thankyou-title .text-orange-accent {
        color: #fe5200;
    }
    .thankyou-subtitle-1 {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
        text-align: center !important;
    }
    .thankyou-subtitle-2 {
        font-size: 15px;
        font-weight: 600;
        color: #1e3a8a;
        margin-bottom: 24px;
        text-align: center !important;
    }

    /* Stepper Card */
    .order-stepper-card {
        background: #fffaf5;
        border: 1.5px solid #fed7aa;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .stepper-flex {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        flex: 1;
    }
    .step-icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        margin-bottom: 6px;
        transition: all 0.2s ease;
    }
    .step-icon-circle.active {
        background: #fe5200;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(254, 82, 0, 0.35);
    }
    .step-icon-circle.inactive {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .step-text {
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
    }
    .step-text.active {
        color: #fe5200;
        font-weight: 700;
    }
    .step-arrow {
        color: #94a3b8;
        font-size: 14px;
        margin-bottom: 22px;
        flex-shrink: 0;
    }

    /* Information Cards */
    .order-info-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        padding: 18px 22px;
        margin-bottom: 18px;
    }
    .card-header-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .card-icon-badge {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        background: #fe5200;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .card-header-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    /* Details Grid */
    .order-details-grid {
        background: #fafafa;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 14px 16px;
    }
    .detail-label {
        font-size: 12.5px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .detail-val {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
    .payment-method-row {
        padding-top: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Products Table */
    .order-products-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .order-products-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .order-products-table td {
        padding: 12px;
        font-size: 13.5px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .order-products-table tr:last-child td {
        border-bottom: none;
    }
    .product-thumb-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .product-size-badge {
        background: #fe5200;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        display: inline-block;
    }

    /* Summary Rows */
    .summary-line {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        font-size: 14px;
        color: #475569;
    }
    .summary-line-value {
        font-weight: 600;
        color: #1e293b;
    }
    .grand-total-box {
        background: #fff7ed;
        border: 1px solid #ffedd5;
        border-radius: 10px;
        padding: 12px 18px;
        margin-top: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .grand-total-label {
        font-size: 16px;
        font-weight: 800;
        color: #111827;
    }
    .grand-total-amount {
        font-size: 22px;
        font-weight: 800;
        color: #fe5200;
    }

    /* Delivery Address */
    .delivery-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .delivery-phone {
        font-size: 13.5px;
        color: #475569;
        margin-bottom: 4px;
    }
    .delivery-address-text {
        font-size: 13.5px;
        color: #334155;
        margin-bottom: 10px;
    }
    .delivery-badge-pill {
        display: inline-flex;
        align-items: center;
        background: #fff7ed;
        border: 1px solid #fed7aa;
        color: #c2410c;
        font-size: 12.5px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }

    /* CTA Button */
    .btn-go-to-home {
        background: #fe5200;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 14px 20px;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.25s ease;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(254, 82, 0, 0.25);
    }
    .btn-go-to-home:hover {
        background: #e04800;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(254, 82, 0, 0.35);
    }
    .footer-note-text {
        color: #94a3b8;
        font-size: 13px;
        text-align: center;
        margin-top: 24px;
        margin-bottom: 12px;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .thankyou-title {
            font-size: 21px;
        }
        .thankyou-subtitle-1 {
            font-size: 14px;
        }
        .thankyou-subtitle-2 {
            font-size: 13.5px;
        }
        .order-stepper-card {
            padding: 12px 10px;
        }
        .step-icon-circle {
            width: 32px;
            height: 32px;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .step-text {
            font-size: 11px;
        }
        .step-arrow {
            font-size: 11px;
            margin-bottom: 18px;
        }
        .order-info-card {
            padding: 14px 15px;
        }
        .order-products-table th:nth-child(3),
        .order-products-table td:nth-child(3) {
            display: none; /* Hide size column on narrow mobile if needed, or inline it */
        }
        .product-thumb-img {
            width: 52px;
            height: 52px;
        }
    }
</style>

<section class="order-success-page py-4 py-md-5">
    <div class="container">
        <div class="order-success-container">
            
            <!-- Top Celebration Header -->
            <div class="text-center mb-3">
                <div class="celebration-badge-wrapper">
                    <!-- Radiating orange dashed rays matching screenshot -->
                    <svg class="celebration-rays" viewBox="0 0 120 52" fill="none">
                        <!-- Left rays -->
                        <line x1="28" y1="38" x2="16" y2="38" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                        <line x1="33" y1="24" x2="22" y2="15" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                        <line x1="47" y1="16" x2="42" y2="4" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                        <!-- Right rays -->
                        <line x1="73" y1="16" x2="78" y2="4" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                        <line x1="87" y1="24" x2="98" y2="15" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                        <line x1="92" y1="38" x2="104" y2="38" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <!-- Solid Green Checkmark Circle -->
                    <div class="celebration-circle">
                        <svg viewBox="0 0 24 24" fill="none" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>

                <h1 class="thankyou-title text-center" style="text-align: center !important;">
                    আপনার অর্ডারটি <span class="text-orange-accent">সফলভাবে গ্রহণ করা হয়েছে!</span>
                </h1>
                <p class="thankyou-subtitle-1 text-center" style="text-align: center !important;">
                    আমাদের প্রতিনিধি খুব শীঘ্রই আপনার সাথে যোগাযোগ করবেন।
                </p>
                <p class="thankyou-subtitle-2 text-center" style="text-align: center !important;">
                    আপনার আস্থা ও ভরসার জন্য ধন্যবাদ!
                </p>
            </div>

            <!-- Stepper Timeline Card -->
            <div class="order-stepper-card">
                <div class="stepper-flex">
                    <!-- Step 1: Confirmed -->
                    <div class="step-item">
                        <div class="step-icon-circle active">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="step-text active">অর্ডার কনফার্মড</div>
                    </div>
                    
                    <div class="step-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                    <!-- Step 2: Verification -->
                    <div class="step-item">
                        <div class="step-icon-circle inactive">
                            <i class="fa-solid fa-phone"></i>
                        </div>
                        <div class="step-text">ভেরিফিকেশন</div>
                    </div>

                    <div class="step-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                    <!-- Step 3: Processing -->
                    <div class="step-item">
                        <div class="step-icon-circle inactive">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div class="step-text">প্রসেসিং</div>
                    </div>

                    <div class="step-arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>

                    <!-- Step 4: Delivery -->
                    <div class="step-item">
                        <div class="step-icon-circle inactive">
                            <i class="fa-solid fa-house"></i>
                        </div>
                        <div class="step-text">ডেলিভারি</div>
                    </div>
                </div>
            </div>

            <!-- Card 1: Your Order Details -->
            <div class="order-info-card">
                <div class="card-header-bar">
                    <div class="card-icon-badge">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h2 class="card-header-title">Your Order Details</h2>
                </div>

                <div class="order-details-grid">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="detail-label">Invoice ID</div>
                            <div class="detail-val">{{ $order->invoice_id }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-label">Date</div>
                            <div class="detail-val">{{ $order->created_at ? $order->created_at->format('d-m-y') : date('d-m-y') }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-label">Phone</div>
                            <div class="detail-val">{{ $order->shipping?->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-label">Total</div>
                            <div class="detail-val">৳ {{ number_format((float) ($order->amount ?? 0), 0) }}</div>
                        </div>
                    </div>
                </div>

                <div class="payment-method-row">
                    <div class="card-icon-badge" style="width: 26px; height: 26px;">
                        <i class="fa-solid fa-wallet" style="font-size: 12px;"></i>
                    </div>
                    <div>
                        <div class="detail-label mb-0" style="font-weight: 700; color: #1e293b;">Payment Method</div>
                        <div class="detail-val" style="font-size: 14px; font-weight: 600; color: #334155;">
                            {{ $order->payment?->payment_method ? ucwords(str_replace(['_', '-'], ' ', $order->payment->payment_method)) : 'Cash On Delivery' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Ordered Products -->
            <div class="order-info-card">
                <div class="card-header-bar">
                    <div class="card-icon-badge">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <h2 class="card-header-title">Ordered Products</h2>
                </div>

                <div class="table-responsive">
                    <table class="order-products-table">
                        <thead>
                            <tr>
                                <th style="width: 75px;">Image</th>
                                <th>Product</th>
                                <th class="text-center" style="width: 70px;">Size</th>
                                <th class="text-center" style="width: 50px;">Qty</th>
                                <th class="text-end" style="width: 90px;">Price</th>
                                <th class="text-end" style="width: 90px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderdetails as $detail)
                            @php
                                $productImage = ($detail->featuredImageRelation ?? $detail->image)?->image ?? $detail->product?->featuredImage?->image ?? $detail->product?->image?->image;
                                $displayImage = $productImage ? asset($productImage) : asset('frontEnd/images/no-image.png');
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ $displayImage }}" 
                                         alt="{{ $detail->product_name }}" 
                                         class="product-thumb-img"
                                         onerror="this.src='{{ asset('frontEnd/images/no-image.png') }}'">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-14" style="line-height: 1.35;">{{ $detail->product_name }}</div>
                                    @if($detail->product_color)
                                    <div class="text-muted font-12 mt-1">{{ $detail->product_color }}</div>
                                    @endif
                                    @if($detail->product?->category?->name)
                                    <div class="text-muted font-12">{{ $detail->product->category->name }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($detail->product_size)
                                    <span class="product-size-badge">{{ $detail->product_size }}</span>
                                    @else
                                    <span class="text-muted font-13">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-dark font-14">
                                    {{ $detail->qty }}
                                </td>
                                <td class="text-end font-14 text-muted">
                                    ৳ {{ number_format((float) $detail->sale_price, 0) }}
                                </td>
                                <td class="text-end fw-bold text-dark font-14">
                                    ৳ {{ number_format((float) (($detail->sale_price ?? 0) * ($detail->qty ?? 1)), 0) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 3: Order Summary -->
            <div class="order-info-card">
                <div class="card-header-bar">
                    <div class="card-icon-badge">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h2 class="card-header-title">Order Summary</h2>
                </div>

                @php
                    $itemsQty = $order->orderdetails->sum('qty');
                    $subtotal = $order->orderdetails->sum(function($item) {
                        return ($item->sale_price ?? 0) * ($item->qty ?? 1);
                    });
                @endphp
                <div class="summary-line">
                    <span>Subtotal ({{ $itemsQty }} {{ $itemsQty > 1 ? 'items' : 'item' }})</span>
                    <span class="summary-line-value">৳ {{ number_format((float) $subtotal, 0) }}</span>
                </div>

                @if((float)($order->discount ?? 0) > 0)
                <div class="summary-line">
                    <span class="text-success">Discount</span>
                    <span class="summary-line-value text-success">-৳ {{ number_format((float) $order->discount, 0) }}</span>
                </div>
                @endif

                <div class="summary-line">
                    <span>Shipping Cost</span>
                    <span class="summary-line-value">৳ {{ number_format((float) ($order->shipping_charge ?? 0), 0) }}</span>
                </div>

                <div class="grand-total-box">
                    <div class="grand-total-label">Grand Total</div>
                    <div class="grand-total-amount">৳ {{ number_format((float) ($order->amount ?? 0), 0) }}</div>
                </div>
            </div>

            <!-- Card 4: Delivery Address -->
            <div class="order-info-card">
                <div class="card-header-bar">
                    <div class="card-icon-badge">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h2 class="card-header-title">Delivery Address</h2>
                </div>

                <div class="delivery-name">{{ $order->shipping?->name ?? 'Mondol Shop BD' }}</div>
                <div class="delivery-phone">{{ $order->shipping?->phone ?? 'N/A' }}</div>
                <div class="delivery-address-text">
                    {{ $order->shipping?->address ?? '' }}
                </div>
                
                <div>
                    <div class="delivery-badge-pill">
                        <i class="fa-solid fa-location-dot me-1" style="color: #fe5200;"></i>
                        {{ $order->shipping?->area ?: ($order->shipping_charge > 0 ? 'ঢাকার ভিতরে ' . (int)$order->shipping_charge . ' টাকা' : 'হোম ডেলিভারি') }}
                    </div>
                </div>
            </div>

            <!-- Call to Action Buttons -->
            <div class="mb-3">
                <a href="{{ route('home') }}" class="btn-go-to-home">
                    <i class="fa-solid fa-house"></i>
                    <span>Go To Home</span>
                </a>
            </div>

            <!-- Secondary Actions -->
            <div class="text-center mb-2">
                <a href="{{ route('customer.invoice', ['id' => $order->id]) }}" class="text-muted font-13 text-decoration-none d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span>পূর্ণাঙ্গ ইনভয়েস দেখুন বা প্রিন্ট করুন</span>
                </a>
            </div>

            <!-- Footer Credit Line -->
            <div class="footer-note-text">
                — {{ $generalsetting->name ?? 'Mondol Shop BD' }} | Thank you for shopping with us —
            </div>

        </div>
    </div>
</section>
@endsection
@push('script')
<script src="{{asset('frontEnd/')}}/js/parsley.min.js"></script>
<script src="{{asset('frontEnd/')}}/js/form-validation.init.js"></script>
<script>
    (function () {
        // Clear any old checkout flags to prevent session pollution
        try {
            sessionStorage.removeItem('fb_purchase_tracked_checkout');
        } catch (e) {}

        // Reset cart counter badges on success page
        try {
            $('.mobilecart-qty, .cart-qty-text, #side-cart-badge').text('0');
            $('#side-cart-footer').hide();
            $('#side-cart-body').html('<div class="text-center py-4 text-muted">আপনার কার্ট খালি</div>');
        } catch (e) {}

        const invoiceId = '{{ $order->invoice_id }}';
        const storageKey = 'fb_purchase_tracked_' + invoiceId;

        let purchaseSent = false;
        try {
            purchaseSent = Boolean(sessionStorage.getItem(storageKey));
        } catch (e) {}

        function trackPurchase() {
            if (purchaseSent) {
                return;
            }

            try {
                // Push standard GA4 / sGTM DataLayer Purchase Event
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({ ecommerce: null });
                window.dataLayer.push({
                    event: 'purchase',
                    event_id: 'order_{{ $order->invoice_id }}',
                    ecommerce: {
                        transaction_id: '{{ $order->invoice_id }}',
                        value: {{ (float) ($order->amount ?? 0) }},
                        currency: 'BDT',
                        shipping: {{ (float) ($order->shipping_charge ?? 0) }},
                        discount: {{ (float) ($order->discount ?? 0) }},
                        items: [
                            @foreach($order->orderdetails as $detail)
                            {
                                item_id: '{{ $detail->product_id ?? $detail->id }}',
                                item_name: '{{ addslashes($detail->product_name ?? 'Product') }}',
                                price: {{ (float) ($detail->sale_price ?? 0) }},
                                quantity: {{ (int) ($detail->qty ?? 1) }}
                            },
                            @endforeach
                        ]
                    }
                });

                purchaseSent = true;
                try {
                    sessionStorage.setItem(storageKey, '1');
                } catch (e) {}
            } catch (err) {
                console.error('[Tracking] Order success Purchase track error:', err);
            }
        }

        // Fire Purchase event immediately
        trackPurchase();

        // If not yet fired, try again on DOMContentLoaded
        if (!purchaseSent) {
            document.addEventListener('DOMContentLoaded', trackPurchase);
        }
    })();
</script>
@endpush