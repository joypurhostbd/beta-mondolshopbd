@php
    $cartCount = Cart::instance('shopping')->count();
    $subtotal = Cart::instance('shopping')->subtotal();
    $subtotal = str_replace(',', '', $subtotal);
    $subtotal = str_replace('.00', '', $subtotal);
@endphp

<!-- Side Cart Overlay -->
<div id="side-cart-overlay" class="side-cart-overlay"></div>

<!-- Side Cart Drawer -->
<div id="side-cart" class="side-cart-drawer" role="dialog" aria-modal="true" aria-labelledby="side-cart-title">
    <!-- Header -->
    <div class="side-cart-header d-flex align-items-center justify-content-between px-3 py-3 border-bottom">
        <div class="d-flex align-items-center">
            <span class="side-cart-header-icon me-2 d-flex align-items-center justify-content-center">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </span>
            <h5 id="side-cart-title" class="side-cart-title mb-0 font-weight-bold text-dark">শপিং কার্ট</h5>
            <span class="side-cart-badge badge rounded-pill ms-2" id="side-cart-badge">{{ $cartCount }}</span>
        </div>
        <button type="button" class="side-cart-close-btn side-cart-close-trigger" id="close-side-cart" aria-label="Close cart" title="কার্ট বন্ধ করুন">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <!-- Notice / Announcement Bar -->
    <div class="side-cart-notice px-3 py-2 border-bottom d-flex align-items-center">
        <svg class="me-2 flex-shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fe5200" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="3" width="15" height="13"></rect>
            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
            <circle cx="5.5" cy="18.5" r="2.5"></circle>
            <circle cx="18.5" cy="18.5" r="2.5"></circle>
        </svg>
        <span class="font-12 text-muted">সারাদেশে হোম ডেলিভারি ও ক্যাশ অন ডেলিভারি সুবিধা</span>
    </div>

    <!-- Scrollable Body -->
    <div class="side-cart-body" id="side-cart-body">
        @include('frontEnd.layouts.partials.side_cart_items')
    </div>

    <!-- Sticky Footer -->
    <div class="side-cart-footer border-top p-3" id="side-cart-footer" style="{{ $cartCount == 0 ? 'display: none;' : '' }}">
        <div class="side-cart-summary pb-2 mb-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted font-14">সাবটোটাল (মোট)</span>
                <span class="font-weight-bold text-dark font-18" id="side-cart-subtotal">৳{{ number_format((float)$subtotal, 0) }}</span>
            </div>
            <small class="text-muted font-11 d-block mt-1">ডেলিভারি চার্জ ও প্রযোজ্য ভাউচার চেকআউট পেজে যুক্ত হবে</small>
        </div>
        
        <div class="side-cart-actions">
            <a href="{{ route('customer.checkout') }}" class="btn side-cart-checkout-btn w-100 font-weight-bold d-flex align-items-center justify-content-center py-2">
                <span>অর্ডার সম্পন্ন করুন</span>
                <svg class="ms-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
            <a href="{{ route('cart.show') }}" class="btn side-cart-view-btn w-100 font-weight-bold mt-2 d-flex align-items-center justify-content-center py-2">
                <span>কার্ট ভিউ করুন</span>
            </a>
            <button type="button" class="btn side-cart-continue-btn side-cart-close-trigger w-100 mt-2 py-2 font-weight-bold d-flex align-items-center justify-content-center">
                <span>← আরো কেনাকাটা করুন</span>
            </button>
        </div>
    </div>
</div>

<style>
/* Side Cart Drawer & Overlay Styles */
#side-cart,
#side-cart * {
    box-sizing: border-box;
}

.side-cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    z-index: 99998;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.side-cart-overlay.active {
    opacity: 1;
    visibility: visible;
}

.side-cart-drawer {
    position: fixed;
    top: 0;
    right: 0;
    width: 390px;
    max-width: 100vw;
    height: 100%;
    height: 100dvh;
    max-height: 100vh;
    background: #ffffff;
    z-index: 99999;
    transform: translateX(105%);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: -10px 0 35px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow-x: hidden;
    overflow-y: hidden;
}

.side-cart-drawer.active {
    transform: translateX(0);
}

.side-cart-header {
    background: #ffffff;
    min-height: 56px;
    flex-shrink: 0;
}

.side-cart-header-icon {
    color: #fe5200;
}

.side-cart-title {
    font-size: 17px;
    letter-spacing: -0.2px;
}

.side-cart-badge {
    background-color: #fe5200 !important;
    color: #ffffff;
    font-size: 12px;
    padding: 3px 8px;
}

.side-cart-close-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.side-cart-close-btn:hover {
    color: #ffffff;
    background-color: #ef4444;
    border-color: #ef4444;
    transform: rotate(90deg);
}

.side-cart-notice {
    background-color: #fff7ed;
    border-color: #ffedd5 !important;
    flex-shrink: 0;
}

.side-cart-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
    padding: 12px 16px;
    min-width: 0;
}

/* Custom Scrollbar for Body */
.side-cart-body::-webkit-scrollbar {
    width: 5px;
}
.side-cart-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.side-cart-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.side-cart-items-wrapper,
.side-cart-items-list {
    width: 100%;
    min-width: 0;
}

.side-cart-item {
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    width: 100%;
    min-width: 0;
    transition: background-color 0.2s ease;
}

.side-cart-item:last-child {
    border-bottom: none;
}

.side-cart-item-thumb {
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.side-cart-item-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.side-cart-item-details {
    min-width: 0;
}

.side-cart-item-name {
    font-size: 13.5px;
    line-height: 1.35;
    color: #1e293b;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    transition: color 0.2s ease;
}

.side-cart-item-name:hover {
    color: #fe5200;
}

.side-cart-remove-btn {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    color: #94a3b8;
    width: 26px;
    height: 26px;
    padding: 0;
    cursor: pointer;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.side-cart-remove-btn:hover {
    color: #ef4444;
    background-color: #fee2e2;
    border-color: #fca5a5;
}

.side-cart-stepper {
    background: #f8fafc;
    border-color: #e2e8f0 !important;
}

.side-cart-stepper-btn {
    width: 26px;
    height: 26px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    font-size: 15px;
    font-weight: bold;
    color: #475569;
    cursor: pointer;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.side-cart-stepper-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.side-cart-qty-value {
    min-width: 22px;
    text-align: center;
    color: #0f172a;
}

.side-cart-item-price-wrap {
    min-width: 0;
}

.side-cart-item-price {
    color: #fe5200;
    font-size: 14.5px;
    white-space: nowrap;
}

.side-cart-footer {
    background: #ffffff;
    box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
    flex-shrink: 0;
}

.side-cart-checkout-btn {
    background-color: #fe5200;
    border: 1px solid #fe5200;
    color: #ffffff !important;
    border-radius: 6px;
    font-size: 15px;
    box-shadow: 0 4px 12px rgba(254, 82, 0, 0.35);
    transition: all 0.2s ease;
}

.side-cart-checkout-btn:hover {
    background-color: #e04600;
    border-color: #e04600;
    color: #ffffff !important;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(254, 82, 0, 0.45);
}

.side-cart-view-btn {
    background-color: #fff7ed;
    border: 1px solid #fed7aa;
    color: #fe5200 !important;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.side-cart-view-btn:hover {
    background-color: #ffedd5;
    border-color: #fdba74;
    color: #ea580c !important;
}

.side-cart-continue-btn {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b !important;
    border-radius: 6px;
    font-size: 13.5px;
    transition: all 0.2s ease;
}

.side-cart-continue-btn:hover {
    background-color: #e2e8f0;
    color: #0f172a !important;
}

@media (max-width: 767.98px) {
    .side-cart-drawer {
        width: 100% !important;
        max-width: 100vw !important;
        left: 0 !important;
        right: 0 !important;
    }
    .side-cart-body {
        padding: 10px 12px;
    }
    .side-cart-footer {
        padding: 12px;
        padding-bottom: max(12px, env(safe-area-inset-bottom));
    }
    .side-cart-item-thumb {
        width: 52px;
        height: 52px;
    }
    .side-cart-item-name {
        font-size: 13px;
    }
    .side-cart-stepper-btn {
        width: 24px;
        height: 24px;
    }
    .side-cart-close-btn {
        width: 32px;
        height: 32px;
    }
}
</style>