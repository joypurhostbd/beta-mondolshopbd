@php
    $gs = (isset($generalsetting) && $generalsetting->exists)
        ? $generalsetting
        : (\App\Models\GeneralSetting::where('status', 1)->first() ?? ($generalsetting ?? null));

    $orderBg = $gs?->order_btn_bg_color ?: '#DD1D26';
    $orderText = $gs?->order_btn_text_color ?: '#ffffff';
    $orderHoverBg = $gs?->order_btn_hover_bg_color ?: '#e04800';

    $cartBg = $gs?->cart_btn_bg_color ?: '#2f3543';
    $cartText = $gs?->cart_btn_text_color ?: '#ffffff';
    $cartHoverBg = $gs?->cart_btn_hover_bg_color ?: '#1e222b';

    $badgeBg = $gs?->discount_badge_bg_color ?: '#ffffff';
    $badgeText = $gs?->discount_badge_text_color ?: '#DD1D26';
    $badgeBorder = $gs?->discount_badge_border_color ?: '#DD1D26';
@endphp
<style>
:root {
    --order-btn-bg: {{ $orderBg }};
    --order-btn-color: {{ $orderText }};
    --order-btn-hover-bg: {{ $orderHoverBg }};

    --cart-btn-bg: {{ $cartBg }};
    --cart-btn-color: {{ $cartText }};
    --cart-btn-hover-bg: {{ $cartHoverBg }};

    --discount-badge-bg: {{ $badgeBg }};
    --discount-badge-color: {{ $badgeText }};
    --discount-badge-border: {{ $badgeBorder }};
}

/* Order Now Action Buttons */
.btn_order_action,
.btn_quick_order,
.order_now_btn {
    background-color: var(--order-btn-bg) !important;
    border-color: var(--order-btn-bg) !important;
    color: var(--order-btn-color) !important;
}
.btn_order_action:hover,
.btn_order_action:focus,
.btn_quick_order:hover,
.btn_quick_order:focus,
.order_now_btn:hover,
.order_now_btn:active {
    background-color: var(--order-btn-hover-bg) !important;
    border-color: var(--order-btn-hover-bg) !important;
    color: var(--order-btn-color) !important;
}

/* Add to Cart Action Buttons */
.btn_cart_action,
.btn_quick_cart,
.add_cart_btn {
    background-color: var(--cart-btn-bg) !important;
    border-color: var(--cart-btn-bg) !important;
    color: var(--cart-btn-color) !important;
}
.btn_cart_action:hover,
.btn_cart_action:focus,
.btn_quick_cart:hover,
.btn_quick_cart:focus,
.add_cart_btn:hover,
.add_cart_btn:active {
    background-color: var(--cart-btn-hover-bg) !important;
    border-color: var(--cart-btn-hover-bg) !important;
    color: var(--cart-btn-color) !important;
}

/* Discount / Sale Badges (Universal Centralized Styling) */
.sale-badge-box,
.product_item_inner .sale-badge-box,
.quick-product-img .sale-badge-box,
.product-details-discount-badge .sale-badge-box {
    background: var(--discount-badge-bg) !important;
    border: 1.5px solid var(--discount-badge-border) !important;
    border-color: var(--discount-badge-border) !important;
}

.sale-badge-text,
.sale-badge-text *,
.sale-badge-box span,
.sale-badge-box p,
.product_item_inner span.sale-badge-text,
.product_item_inner span.sale-badge-text *,
.quick-product-img span.sale-badge-text,
.quick-product-img span.sale-badge-text *,
.product-details-discount-badge span.sale-badge-text,
.product-details-discount-badge span.sale-badge-text * {
    color: var(--discount-badge-color) !important;
}

/* Reset hardcoded background, border, and shadows on inner text spans from style.css */
.sale-badge-text,
.product-details-discount-badge span.sale-badge-text,
.product_item_inner span.sale-badge-text,
.quick-product-img span.sale-badge-text {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}
</style>
