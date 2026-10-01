@php
    $waSetting = (!empty($generalsetting) && $generalsetting instanceof \App\Models\GeneralSetting)
        ? $generalsetting
        : \App\Models\GeneralSetting::where('status', 1)->first();
    $waContact = (!empty($contact) && $contact instanceof \App\Models\Contact)
        ? $contact
        : \App\Models\Contact::where('status', 1)->first();

    $waStatus = !empty($waSetting->whatsapp_status);
    $waRawNumber = !empty($waSetting->whatsapp_number) ? $waSetting->whatsapp_number : ($waContact->phone ?? '');
    $waCleanDigits = preg_replace('/[^0-9]/', '', (string)$waRawNumber);
    if (!empty($waCleanDigits)) {
        if (str_starts_with($waCleanDigits, '01') && strlen($waCleanDigits) === 11) {
            $waCleanDigits = '88' . $waCleanDigits;
        } elseif (str_starts_with($waCleanDigits, '1') && strlen($waCleanDigits) === 10) {
            $waCleanDigits = '880' . $waCleanDigits;
        }
    }
    $waTitle = !empty($waSetting->whatsapp_title) ? $waSetting->whatsapp_title : 'WhatsApp এ যোগাযোগ করুন';
    $isDynamicContext = isset($waSetting->whatsapp_dynamic_context) ? ((int)$waSetting->whatsapp_dynamic_context === 1) : true;
    $defaultWelcome = !empty($waSetting->whatsapp_message) ? $waSetting->whatsapp_message : 'Hello! I want to know more about your products.';

    $contextType = 'general';
    $waMessage = $defaultWelcome;

    if ($isDynamicContext) {
        if (request()->routeIs('product') && isset($details) && $details instanceof \App\Models\Product) {
            $contextType = 'product';
            $prodUrl = route('product', $details->slug ?? $details->id);
            $waMessage = "আসসালামু আলাইকুম,\nআমি এই প্রোডাক্টটি সম্পর্কে জানতে / অর্ডার করতে চাই:\n📌 প্রোডাক্ট: {$details->name}\n💰 মূল্য: ৳{$details->new_price}";
            if (!empty($details->product_code)) {
                $waMessage .= "\n🔢 কোড: {$details->product_code}";
            }
            $waMessage .= "\n🔗 লিঙ্ক: {$prodUrl}";
        } elseif (request()->routeIs('customer.checkout')) {
            $contextType = 'checkout';
            $cartCount = \Gloudemans\Shoppingcart\Facades\Cart::instance('shopping')->count();
            $cartSubtotal = \Gloudemans\Shoppingcart\Facades\Cart::instance('shopping')->subtotal();
            $checkoutUrl = route('customer.checkout');
            $waMessage = "আসসালামু আলাইকুম,\nআমি চেকআউটে অর্ডার সম্পন্ন করতে সহায়তা চাচ্ছি:\n🛍️ মোট আইটেম: {$cartCount} টি\n💳 সর্বমোট: ৳{$cartSubtotal}\n🔗 লিঙ্ক: {$checkoutUrl}";
        } elseif (request()->routeIs('cart.show')) {
            $contextType = 'cart';
            $cartCount = \Gloudemans\Shoppingcart\Facades\Cart::instance('shopping')->count();
            $cartSubtotal = \Gloudemans\Shoppingcart\Facades\Cart::instance('shopping')->subtotal();
            $cartUrl = route('cart.show');
            $waMessage = "আসসালামু আলাইকুম,\nআমি আমার কার্টের পণ্যগুলো অর্ডার করার বিষয়ে জানতে চাই:\n🛒 মোট আইটেম: {$cartCount} টি\n💳 সর্বমোট: ৳{$cartSubtotal}\n🔗 লিঙ্ক: {$cartUrl}";
        } elseif (request()->routeIs('campaign') && isset($campaign_data)) {
            $contextType = 'campaign';
            $campaignUrl = url()->current();
            $campTitle = $campaign_data->name ?? ($campaign_data->product->name ?? 'স্পেশাল অফার');
            $campPrice = $campaign_data->product->new_price ?? '';
            $waMessage = "আসসালামু আলাইকুম,\nআমি স্পেশাল অফার ক্যাম্পেইনের এই পণ্যটি অর্ডার করতে চাই:\n🔥 ক্যাম্পেইন: {$campTitle}";
            if (!empty($campPrice)) {
                $waMessage .= "\n💰 মূল্য: ৳{$campPrice}";
            }
            $waMessage .= "\n🔗 লিঙ্ক: {$campaignUrl}";
        } else {
            $currentUrl = url()->current();
            $waMessage = $defaultWelcome . "\n🔗 লিঙ্ক: " . $currentUrl;
        }
    }

    $waUrl = !empty($waCleanDigits) ? 'https://api.whatsapp.com/send?phone=' . $waCleanDigits . '&text=' . rawurlencode($waMessage) : null;
@endphp

@if($waStatus && $waUrl)
<div id="whatsapp-widget-container" class="whatsapp-widget-container" data-context="{{ $contextType }}" data-phone="{{ $waCleanDigits }}">
    <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="whatsapp-floating-btn" aria-label="{{ $waTitle }}">
        <div class="whatsapp-icon-wrapper">
            <svg class="whatsapp-svg" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M16 2C8.268 2 2 8.268 2 16c0 2.58.694 5.002 1.905 7.086L2.1 29.9l7.01-1.776A13.924 13.924 0 0016 30c7.732 0 14-6.268 14-14S23.732 2 16 2zm8.016 19.865c-.334.937-1.656 1.748-2.695 1.972-.712.153-1.637.275-4.757-1.02-3.987-1.653-6.554-5.698-6.753-5.962-.199-.264-1.62-2.156-1.62-4.113 0-1.956 1.02-2.919 1.385-3.317.365-.398.795-.497 1.06-.497.265 0 .53.003.762.015.246.012.576-.093.902.69.334.803 1.135 2.766 1.234 2.966.1.199.166.431.033.696-.133.264-.2.43-.398.662-.199.232-.418.52-.598.698-.198.199-.405.416-.174.814.232.398 1.03 1.7 2.215 2.756 1.524 1.358 2.808 1.778 3.207 1.977.398.199.63.166.862-.1.232-.265.995-1.16 1.26-1.558.266-.398.53-.332.896-.199.365.133 2.32 1.094 2.718 1.293.398.199.664.298.763.464.099.166.099.962-.235 1.899z" fill="#FFFFFF"/>
            </svg>
        </div>
        <span class="whatsapp-tooltip">{{ $waTitle }}</span>
    </a>
</div>

<style>
.whatsapp-widget-container {
    position: fixed;
    bottom: 30px;
    left: 25px;
    z-index: 9998;
    display: flex;
    align-items: center;
    font-family: inherit;
}
.whatsapp-floating-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: #ffffff;
    border-radius: 50%;
    box-shadow: 0 4px 16px rgba(37, 211, 102, 0.45);
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-decoration: none;
    outline: none;
}
.whatsapp-floating-btn:hover {
    transform: scale(1.08) translateY(-3px);
    box-shadow: 0 8px 24px rgba(37, 211, 102, 0.6);
    color: #ffffff;
    text-decoration: none;
}
.whatsapp-floating-btn::before {
    content: '';
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    border: 2px solid #25D366;
    opacity: 0.8;
    animation: wa-pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    pointer-events: none;
}
.whatsapp-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
}
.whatsapp-svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.15));
}
.whatsapp-tooltip {
    position: absolute;
    left: calc(100% + 12px);
    top: 50%;
    transform: translateY(-50%) translateX(-8px);
    background: rgba(17, 24, 39, 0.95);
    color: #ffffff;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    white-space: nowrap;
    padding: 7px 14px;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s ease;
    z-index: 9999;
}
.whatsapp-tooltip::before {
    content: '';
    position: absolute;
    top: 50%;
    right: 100%;
    margin-top: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent rgba(17, 24, 39, 0.95) transparent transparent;
}
.whatsapp-floating-btn:hover .whatsapp-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(-50%) translateX(0);
}
.btn-whatsapp-order {
    background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
    color: #ffffff !important;
    font-size: 15px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 6px;
    border: none;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
    transition: all 0.25s ease;
    text-decoration: none;
}
.btn-whatsapp-order:hover {
    background: linear-gradient(135deg, #20ba5a 0%, #0e7266 100%);
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(37, 211, 102, 0.5);
    text-decoration: none;
}
@keyframes wa-pulse-ring {
    0% {
        transform: scale(0.95);
        opacity: 0.9;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.3;
    }
    100% {
        transform: scale(1.35);
        opacity: 0;
    }
}
@media (max-width: 768px) {
    .whatsapp-widget-container {
        bottom: 75px;
        left: 15px;
    }
    .whatsapp-floating-btn {
        width: 50px;
        height: 50px;
    }
    .whatsapp-icon-wrapper {
        width: 28px;
        height: 28px;
    }
    .whatsapp-tooltip {
        display: none;
    }
}
</style>

<script>
(function() {
    function resolveDynamicWhatsAppUrl() {
        var container = document.getElementById('whatsapp-widget-container');
        var phone = '{{ $waCleanDigits }}';
        var baseMessage = {!! json_encode($waMessage) !!};
        var context = '{{ $contextType }}';
        var isDynamic = {{ $isDynamicContext ? 'true' : 'false' }};

        if (!isDynamic) {
            return 'https://api.whatsapp.com/send?phone=' + encodeURIComponent(phone) + '&text=' + encodeURIComponent(baseMessage);
        }

        var message = baseMessage;

        // Dynamic Product Page Enrichment (captures selected color, size, and quantity)
        if (context === 'product' || document.querySelector('.single_product') || document.querySelector('.product-cart')) {
            var selectedColor = document.querySelector('input[name="product_color"]:checked');
            var selectedSize = document.querySelector('input[name="product_size"]:checked');
            var qtyInput = document.querySelector('input[name="qty"]') || document.querySelector('.quantity input');

            var extraInfo = '';
            if (selectedColor && selectedColor.value) {
                extraInfo += '\n🎨 কালার: ' + selectedColor.value.trim();
            }
            if (selectedSize && selectedSize.value) {
                extraInfo += '\n📏 সাইজ: ' + selectedSize.value.trim();
            }
            if (qtyInput && qtyInput.value && parseInt(qtyInput.value) > 1) {
                extraInfo += '\n🔢 পরিমাণ: ' + qtyInput.value.trim();
            }
            if (extraInfo) {
                if (message.indexOf('🔗 লিঙ্ক:') !== -1) {
                    message = message.replace('🔗 লিঙ্ক:', extraInfo.trim() + '\n🔗 লিঙ্ক:');
                } else {
                    message += extraInfo;
                }
            }
        }

        // Dynamic Checkout Page Enrichment (captures entered customer name and phone)
        if (context === 'checkout' || document.querySelector('.checkout-shipping')) {
            var nameInput = document.querySelector('input[name="name"]') || document.getElementById('name');
            var phoneInput = document.querySelector('input[name="phone"]') || document.getElementById('phone') || document.getElementById('number');
            var customerInfo = '';
            if (nameInput && nameInput.value.trim()) {
                customerInfo += '\n👤 নাম: ' + nameInput.value.trim();
            }
            if (phoneInput && phoneInput.value.trim()) {
                customerInfo += '\n📞 ফোন: ' + phoneInput.value.trim();
            }
            if (customerInfo) {
                if (message.indexOf('🔗 লিঙ্ক:') !== -1) {
                    message = message.replace('🔗 লিঙ্ক:', customerInfo.trim() + '\n🔗 লিঙ্ক:');
                } else {
                    message += customerInfo;
                }
            }
        }

        return 'https://api.whatsapp.com/send?phone=' + encodeURIComponent(phone) + '&text=' + encodeURIComponent(message);
    }

    document.addEventListener('click', function(e) {
        var floatBtn = e.target.closest('.whatsapp-floating-btn');
        if (floatBtn) {
            var dynamicUrl = resolveDynamicWhatsAppUrl();
            floatBtn.setAttribute('href', dynamicUrl);
        }

        var orderBtn = e.target.closest('#whatsapp-product-order-btn');
        if (orderBtn) {
            e.preventDefault();
            var dynamicUrl = resolveDynamicWhatsAppUrl();
            window.open(dynamicUrl, '_blank', 'noopener,noreferrer');
        }
    });
})();
</script>
@endif