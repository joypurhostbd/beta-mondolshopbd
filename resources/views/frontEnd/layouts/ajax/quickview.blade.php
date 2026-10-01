<div class="modal-view quick-product">
    <button type="button" class="close-modal" aria-label="Close modal">&times;</button>
    <div class="quick-product-wrapper">
        <div class="quick-product-img">
            @if ($product->old_price && $product->old_price > $product->new_price)
                <div class="sale-badge">
                    <div class="sale-badge-inner">
                        <div class="sale-badge-box">
                            <span class="sale-badge-text">
                                <span class="badge-percent">@php $discount=(((($product->old_price)-($product->new_price))*100) / ($product->old_price)) @endphp {{ number_format($discount, 0) }}%</span>
                                {{ (!empty($generalsetting->discount_badge_text)) ? $generalsetting->discount_badge_text : 'ছাড়' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
            <img src="{{ asset(($product->featuredImage ?? $product->image)?->image ?? '') }}" alt="{{ $product->name }}" />
        </div>
        <div class="quick-product-content">
            <div class="product-details-cart">
                <h2 class="name">{{ $product->name }}</h2>
                <div class="details-price-wrap">
                    <span class="details-price">৳{{ $product->new_price }}</span>
                    @if ($product->old_price && $product->old_price > $product->new_price)
                        <del class="details-old-price">৳{{ $product->old_price }}</del>
                    @endif
                </div>

                <form action="{{ route('cart.store') }}" method="POST" id="quickview-cart-form">
                    @csrf
                    <input type="hidden" name="id" value="{{ $product->id }}" />

                    @if ($productcolors && $productcolors->count() > 0)
                        <div class="pro-color" style="width: 100%;">
                            <div class="color_inner">
                                <p>Color -</p>
                                <div class="size-container">
                                    <div class="selector">
                                        @foreach ($productcolors as $procolor)
                                            <div class="selector-item">
                                                <input type="radio"
                                                    id="qv-color-{{ $procolor->id }}"
                                                    value="{{ $procolor->color ? $procolor->color->colorName : '' }}"
                                                    name="product_color"
                                                    class="selector-item_radio emptyalert"
                                                    @if($loop->first) checked @endif
                                                    required />
                                                <label for="qv-color-{{ $procolor->id }}"
                                                    style="background-color: {{ $procolor->color ? $procolor->color->color : '' }}"
                                                    class="selector-item_label">
                                                    <span>
                                                        <img src="{{ asset('frontEnd/images') }}/check-icon.svg" alt="Checked Icon" />
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($productsizes && $productsizes->count() > 0)
                        <div class="pro-size" style="width: 100%;">
                            <div class="size_inner">
                                <p>Size -</p>
                                <div class="size-container">
                                    <div class="selector">
                                        @foreach ($productsizes as $prosize)
                                            <div class="selector-item">
                                                <input type="radio"
                                                    id="qv-size-{{ $prosize->id }}"
                                                    value="{{ $prosize->size ? $prosize->size->sizeName : '' }}"
                                                    name="product_size"
                                                    class="selector-item_radio emptyalert"
                                                    @if($loop->first) checked @endif
                                                    required />
                                                <label for="qv-size-{{ $prosize->id }}"
                                                    class="selector-item_label">{{ $prosize->size ? $prosize->size->sizeName : '' }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="quick-product-actions">
                        <div class="qty-pill">
                            <button type="button" class="qv-minus" aria-label="Decrease quantity">-</button>
                            <input type="text" name="qty" class="qv-qty-input" value="1" readonly />
                            <button type="button" class="qv-plus" aria-label="Increase quantity">+</button>
                        </div>
                        <div class="quick-actions-buttons">
                            <button type="button" class="btn_quick_cart" id="qv-add-cart-btn">{{ $generalsetting->cart_btn_text ?? 'কার্টে যোগ' }}</button>
                            <button type="button" class="btn_quick_order" id="qv-order-now-btn">{{ $generalsetting->order_btn_text ?? 'অর্ডার করুন' }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        $('.close-modal').on('click', function(e) {
            e.preventDefault();
            $('#custom-modal').hide();
            $('#page-overlay').hide();
        });

        $('.qv-minus').on('click', function() {
            var $input = $(this).siblings('.qv-qty-input');
            var count = parseInt($input.val()) - 1;
            count = count < 1 ? 1 : count;
            $input.val(count).trigger('change');
        });

        $('.qv-plus').on('click', function() {
            var $input = $(this).siblings('.qv-qty-input');
            var count = parseInt($input.val()) + 1;
            $input.val(count).trigger('change');
        });

        $('#qv-add-cart-btn').on('click', function(e) {
            e.preventDefault();
            var form = document.getElementById('quickview-cart-form');
            var formData = new FormData(form);
            formData.set('order_now', 'কার্টে যোগ করুন');

            var $btn = $(this);
            var origText = $btn.text();
            $btn.prop('disabled', true).text('যোগ হচ্ছে...');

            $.ajax({
                type: "POST",
                url: form.action,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-Side-Cart': '1'
                },
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).text(origText);
                    if (res && res.status === 'success') {
                        if (res.side_cart_html) {
                            $('#side-cart-body').html(res.side_cart_html);
                            $('#side-cart-badge').text(res.cart_count);
                            $('.mobilecart-qty').text(res.cart_count);
                            $('.cart-qty-text').text(res.cart_count);
                            $('#side-cart-subtotal').text('৳' + res.subtotal);
                            if (parseInt(res.cart_count) > 0) {
                                $('#side-cart-footer').show();
                            }
                        }
                        $('#custom-modal').hide();
                        $('#page-overlay').hide();
                        if (typeof notifyOrOpenSideCart === 'function') {
                            notifyOrOpenSideCart();
                        } else if (typeof toastr !== 'undefined') {
                            toastr.success('পণ্যটি সফলভাবে কার্টে যোগ হয়েছে');
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text(origText);
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = Object.values(xhr.responseJSON.errors).flat();
                        toastr.error(errors[0] || 'কার্টে যোগ করতে সমস্যা হয়েছে');
                    } else {
                        toastr.error('কার্টে যোগ করতে সমস্যা হয়েছে');
                    }
                }
            });
        });

        $('#qv-order-now-btn').on('click', function(e) {
            e.preventDefault();
            var form = document.getElementById('quickview-cart-form');
            var formData = new FormData(form);
            formData.set('order_now', 'অর্ডার করুন');

            var $btn = $(this);
            $btn.prop('disabled', true).text('অর্ডার প্রসেস হচ্ছে...');

            $.ajax({
                type: "POST",
                url: form.action,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        window.location.href = "{{ route('customer.checkout') }}";
                    } else {
                        form.submit();
                    }
                },
                error: function() {
                    form.submit();
                }
            });
        });
    })(jQuery);
</script>