<div class="product_item wist_item {{ $extraClass ?? '' }}">
    <div class="product_item_inner">
        @if($value->old_price && $value->old_price > $value->new_price)
        <div class="sale-badge">
            <div class="sale-badge-inner">
                <div class="sale-badge-box">
                    <span class="sale-badge-text">
                        <span class="badge-percent">@php $discount=(((($value->old_price)-($value->new_price))*100) / ($value->old_price)) @endphp {{ number_format($discount, 0) }}%</span>
                        {{ (!empty($generalsetting->discount_badge_text)) ? $generalsetting->discount_badge_text : 'ছাড়' }}
                    </span>
                </div>
            </div>
        </div>
        @endif
        <div class="pro_img">
            <a href="{{ route('product', $value->slug) }}">
                <img src="{{ asset(($value->featuredImage ?? $value->image)?->image ?? '') }}"
                    alt="{{ $value->name }}" loading="lazy" />
            </a>
        </div>
        <div class="pro_des">
            <h3 class="pro_name">
                <a href="{{ route('product', $value->slug) }}">{{ Str::limit($value->name, 80) }}</a>
            </h3>
            <div class="pro_price">
                <span class="current_price">৳ {{ $value->new_price }}</span>
                @if ($value->old_price && $value->old_price > $value->new_price)
                    <del class="old_price">৳ {{ $value->old_price }}</del>
                @endif
            </div>
        </div>
    </div>
    <div class="product_item_buttons">
        <a href="{{ route('product', $value->slug) }}" class="btn_order_action">
            {{ $generalsetting->order_btn_text ?? 'অর্ডার করুন' }}
        </a>
        <button type="button" class="btn_cart_action" data-id="{{ $value->id }}" style="display:none !important;">
            {{ $generalsetting->cart_btn_text ?? 'কার্টে যোগ' }}
        </button>
    </div>
</div>
