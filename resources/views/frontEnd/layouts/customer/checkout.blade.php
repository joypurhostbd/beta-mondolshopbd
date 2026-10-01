@extends('frontEnd.layouts.master') @section('title', 'Customer Checkout') @push('css')
<link rel="stylesheet" href="{{ asset('frontEnd/css/select2.min.css') }}" />
<style>
/* Delivery Area Radio Options (Desktop: Inline, Mobile: List) */
.delivery-area-heading {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
}

.delivery-area-group {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.delivery-area-card {
    flex: 1 1 calc(50% - 6px);
    display: inline-flex;
    align-items: center;
    padding: 12px 16px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 0;
    transition: all 0.2s ease;
    user-select: none;
    box-sizing: border-box;
}

.delivery-area-card:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.delivery-area-card.selected,
.delivery-area-card:has(input[type="radio"]:checked) {
    border-color: #DD1D26;
    background-color: #fff7ed;
    box-shadow: 0 2px 8px rgba(254, 82, 0, 0.08);
}

.delivery-area-radio {
    margin-right: 10px;
    margin-top: 0;
    accent-color: #DD1D26;
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0;
}

.delivery-area-text {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.35;
    display: inline-block;
}

.delivery-area-card.selected .delivery-area-text,
.delivery-area-card:has(input[type="radio"]:checked) .delivery-area-text {
    color: #DD1D26;
}

/* Mobile: List style (stacked vertically, full width) */
@media (max-width: 576px) {
    .delivery-area-group {
        flex-direction: column !important;
        gap: 10px !important;
    }
    .delivery-area-card {
        width: 100% !important;
        flex: none !important;
        padding: 12px 14px;
    }
    .delivery-area-text {
        font-size: 13.5px;
    }
}
</style>
@endpush @section('content')
<section class="chheckout-section">
    @php
        $subtotal = Cart::instance('shopping')->subtotal();
        $subtotal = str_replace(',', '', $subtotal);
        $subtotal = str_replace('.00', '', $subtotal);
        $shipping = Session::get('shipping') ? Session::get('shipping') : 0;
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-sm-5 cus-order-2">
                <div class="checkout-shipping">
                    
                    <form  id="myForm" action="{{ route('customer.ordersave') }}" method="POST" data-parsley-validate="">
                        @csrf
                        <div class="card">
                           <div class="card-header">
                                <h6>আপনার অর্ডারটি কনফার্ম করতে তথ্যগুলো পূরণ করে <span style="color:#DD1D26;">"অর্ডার করুন"</span> বাটন এ ক্লিক করুন </h6>
                                
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group mb-3">
                                            <label for="name">আপনার নাম লিখুন *</label>
                                            <input type="text" id="name"
                                                class="form-control name-input @error('name') is-invalid @enderror" name="name"
                                                value="{{ old('name') }}"
                                                required/>
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->
                                    <div class="col-sm-12">
                                        <div class="form-group mb-3">
                                            <label for="phone">আপনার নাম্বার লিখুন *</label>
                                            <input type="tel" minlength="11" maxlength="11" id="phone"
                                                pattern="01[3-9][0-9]{8}"
                                                data-parsley-pattern="^01[3-9][0-9]{8}$"
                                                data-parsley-pattern-message="সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)"
                                                placeholder="01XXXXXXXXX"
                                                class="form-control phone-input @error('phone') is-invalid @enderror" name="phone"
                                                value="{{ old('phone') }}"
                                                required/>
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->
                                    <div class="col-sm-12">
                                        <div class="form-group mb-3">
                                            <label for="address">ঠিকানা লিখুন * জেলা,উপজেলা,থানা,পৌরসভা</label>
                                            <input type="address" id="address"
                                                class="form-control address-input @error('address') is-invalid @enderror"
                                                name="address"
                                                value="{{ old('address') }}"
                                                required/>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group mb-3">
                                            <label class="delivery-area-heading font-weight-bold mb-2 d-block">
                                                ডেলিভারি এরিয়া নিবার্চন করুন <span class="text-danger">*</span>
                                            </label>
                                            <div class="delivery-area-group">
                                                @foreach ($shippingcharge as $key => $value)
                                                    <label for="area_{{ $value->id }}" class="delivery-area-card {{ (old('area') == $value->id || (!old('area') && $loop->first)) ? 'selected' : '' }}">
                                                        <input type="radio" 
                                                               id="area_{{ $value->id }}" 
                                                               name="area" 
                                                               value="{{ $value->id }}" 
                                                               class="delivery-area-radio"
                                                               {{ (old('area') == $value->id || (!old('area') && $loop->first)) ? 'checked' : '' }} 
                                                               required />
                                                        <span class="delivery-area-text">{{ $value->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error('area')
                                                <span class="text-danger font-12 mt-1 d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->

                                    <!-------------------->
                                    <!-- col-end -->
                                    <div class="col-sm-12">

                                        <div class="radio_payment">
                                            <label id="payment_method">পেমেন্ট মেথড</label>
                                            <div class="payment_option">
                                                
                                            </div>
                                        </div>
                                        <div class="payment-methods">
                                            
                                            <div class="form-check p_cash">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                id="inlineRadio1" value="Cash On Delivery" checked required />
                                                <label class="form-check-label" for="inlineRadio1">
                                                    Cash On Delivery
                                                </label>
                                            </div>
                                            @if($bkash_gateway)
                                            <div class="form-check p_bkash">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                id="inlineRadio2" value="bkash" required/>
                                                <label class="form-check-label" for="inlineRadio2">
                                                    Bkash
                                                </label>
                                            </div>
                                            @endif
                                            
                                            @if($shurjopay_gateway)
                                            <div class="form-check p_shurjo">
                                                <input class="form-check-input" type="radio" name="payment_method"
                                                id="inlineRadio3" value="shurjopay" required/>
                                                <label class="form-check-label" for="inlineRadio3">
                                                    Shurjopay
                                                </label>
                                            </div>
                                            @endif
                                        </div> <label for="area"> <span style="color:#DD1D26;">"★১০০% শিউর না হয়ে অহেতুক অর্ডার করবেন না দয়া করে।পছন্দ না হলে ঢাকা সিটিতে ৭০,বাইরে ১৩০ টাকা কুরিয়ার চার্জ দিয়ে ব্যাক করতে পারবেন</label
                                        
                                    </div>

                                    <!-------------------->
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <button class="order_place" id="submitBtn" type="submit">অর্ডার করুন</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card end -->




                    </form>
                </div>
            </div>
            </div>
            <!-- col end -->
            <div class="col-sm-7 cust-order-1">
                <div class="cart_details table-responsive-sm">
                    <div class="card">
                        <div class="card-header">
                            <h5>অর্ডারের তথ্য</h5>
                        </div>
                        <div class="card-body cartlist">
                            <table class="cart_table table table-bordered table-striped text-center mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">ডিলিট</th>
                                        <th style="width: 40%;">প্রোডাক্ট</th>
                                        <th style="width: 20%;">পরিমাণ</th>
                                        <th style="width: 20%;">মূল্য</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach (Cart::instance('shopping')->content() as $value)
                                        <tr>
                                            <td>
                                                <a class="cart_remove" data-id="{{ $value->rowId }}"><i
                                                        class="fas fa-trash text-danger"></i></a>
                                            </td>
                                            <td class="text-left">
                                                <a href="{{ route('product', $value->options->slug) }}"> <img
                                                        src="{{ asset($value->options->image) }}" />
                                                    {{ Str::limit($value->name, 20) }}</a>
                                                @if (!empty($value->options->product_size))
                                                    <p>Size: {{ $value->options->product_size }}</p>
                                                @endif
                                                @if (!empty($value->options->product_color))
                                                    <p>Color: {{ $value->options->product_color }}</p>
                                                @endif
                                            </td>
                                            <td class="cart_qty">
                                                <div class="qty-cart vcart-qty">
                                                    <div class="quantity">
                                                        <button class="minus cart_decrement"
                                                            data-id="{{ $value->rowId }}">-</button>
                                                        <input type="text" value="{{ $value->qty }}" readonly />
                                                        <button class="plus cart_increment"
                                                            data-id="{{ $value->rowId }}">+</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="alinur">৳ </span><strong>{{ $value->price }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">মোট</th>
                                        <td class="px-4">
                                            <span id="net_total"><span class="alinur">৳
                                                </span><strong>{{ $subtotal }}</strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">ডেলিভারি চার্জ</th>
                                        <td class="px-4">
                                            <span id="cart_shipping_cost"><span class="alinur">৳
                                                </span><strong>{{ $shipping }}</strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">সর্বমোট</th>
                                        <td class="px-4">
                                            <span id="grand_total"><span class="alinur">৳
                                                </span><strong>{{ $subtotal + $shipping }}</strong></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- col end -->
        
    </div>
</section>
@endsection @push('script')
<script src="{{ asset('frontEnd/') }}/js/parsley.min.js"></script>
<script src="{{ asset('frontEnd/') }}/js/form-validation.init.js"></script>
<script src="{{ asset('frontEnd/') }}/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $(".select2").select2();
    });
</script>
<script>
    // Auto-convert Bengali numerals to standard English digits in phone input
    $(document).on('input', '.phone-input', function() {
        var banglaDigits = {'০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9'};
        var cleanVal = $(this).val().replace(/[০-৯]/g, function(s) {
            return banglaDigits[s];
        }).replace(/[^\d]/g, '');
        $(this).val(cleanVal);
    });

    const form = document.getElementById('myForm');
    const submitBtn = document.getElementById('submitBtn');
    const nameInput = document.getElementById('name');
    const numberInput = document.getElementById('phone') || document.getElementById('number');
    const addressInput = document.getElementById('address');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); 
            let valid = true;

            if (!nameInput || nameInput.value.trim() === '') {
                valid = false;
                if (typeof toastr !== 'undefined') toastr.warning('আপনার নাম লিখুন');
            }
          
            var phoneVal = numberInput ? numberInput.value.trim() : '';
            var bdPhoneRegex = /^01[3-9]\d{8}$/;
            if (!numberInput || !bdPhoneRegex.test(phoneVal)) {
                valid = false;
                if (typeof toastr !== 'undefined') toastr.warning('সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)');
            }
        
            if (!addressInput || addressInput.value.trim() === '') {
                valid = false;
                if (typeof toastr !== 'undefined') toastr.warning('আপনার সম্পূর্ণ ঠিকানা লিখুন');
            }
          
            if (!valid) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'অর্ডার করুন';
                }
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'অর্ডার হচ্ছে ...';
            }

            var formData = new FormData(form);
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
                        // Reset cart counter badges
                        $('.mobilecart-qty, .cart-qty-text, #side-cart-badge').text('0');
                        $('#side-cart-footer').hide();
                        $('#side-cart-body').html('<div class="text-center py-4 text-muted">আপনার কার্ট খালি</div>');

                        // Redirect to dedicated order success page
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerText = 'অর্ডার সম্পন্ন হয়েছে...';
                        }
                        if (res.redirect_url) {
                            window.location.href = res.redirect_url;
                        } else if (res.order_id) {
                            window.location.href = "{{ url('customer/order-success') }}/" + res.order_id;
                        }
                    } else if (res && res.status === 'redirect') {
                        window.location.href = res.redirect_url;
                    } else {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'অর্ডার করুন';
                        }
                        if (typeof toastr !== 'undefined') {
                            toastr.error((res && res.message) ? res.message : 'অর্ডার করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                        }
                    }
                },
                error: function(xhr) {
                    console.warn('[Checkout AJAX] Order save error:', xhr);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'অর্ডার করুন';
                    }

                    var errorMsg = 'অর্ডার করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            var firstErr = Object.values(xhr.responseJSON.errors)[0];
                            if (Array.isArray(firstErr) && firstErr.length > 0) {
                                errorMsg = firstErr[0];
                            }
                        }
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                }
            });
        });
    }


    $(document).on("change", 'input[name="area"]', function() {
        $('.delivery-area-card').removeClass('selected');
        $(this).closest('.delivery-area-card').addClass('selected');
        var id = $(this).val();
        $.ajax({
            type: "GET",
            data: {
                id: id
            },
            url: "{{ route('shipping.charge') }}",
            dataType: "html",
            success: function(response) {
                $(".cartlist").html(response);
            },
        });
    });
    
 $(document).on("change", ".name-input, .phone-input, .address-input", function() {
    var name = $('.name-input').val();
    var phone = $('.phone-input').val();
    var address = $('.address-input').val();

    $.ajax({
        type: "GET",
        url: "{{ route('incomplete.order') }}",
        data: {
            name: name,
            phone: phone,
            address: address
        },
        dataType: "html",
        success: function(response) {
            // Handle success (optional)
        },
        error: function(xhr) {
            console.error("Error saving incomplete order data", xhr);
        }
    });
});

    
</script>
<script type = "text/javascript">
    window.dataLayer = window.dataLayer || [];
    dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
    dataLayer.push({
        event    : "view_cart",
        ecommerce: {
            items: [@foreach (Cart::instance('shopping')->content() as $cartInfo){
                item_name     : "{{$cartInfo->name ?? ''}}",
                item_id       : "{{$cartInfo->id ?? ''}}",
                price         : "{{$cartInfo->price ?? 0}}",
                item_brand    : "{{$cartInfo->options->brand ?? $cartInfo->options->brands ?? ''}}",
                item_category : "{{$cartInfo->options->category ?? ''}}",
                item_size     : "{{$cartInfo->options->size ?? $cartInfo->options->product_size ?? ''}}",
                item_color     : "{{$cartInfo->options->color ?? $cartInfo->options->product_color ?? ''}}",
                currency      : "BDT",
                quantity      : {{$cartInfo->qty ?? 0}}
            },@endforeach]
        }
    });
</script>
<script type="text/javascript">
    window.dataLayer = window.dataLayer || [];
    // Clear the previous ecommerce object.
    dataLayer.push({ ecommerce: null });

    // Meta Browser Pixel - InitiateCheckout
    // IMPORTANT: eventID MUST match Server CAPI event_id
    try {
        if (typeof fbq === 'function') {
            fbq('track', 'InitiateCheckout', {
                value: {{ (float) str_replace(',', '', Cart::instance('shopping')->subtotal()) }},
                currency: 'BDT',
                content_type: 'product',
                content_ids: [
                    @foreach(Cart::instance('shopping')->content() as $cartInfo)
                    '{{ $cartInfo->id }}',
                    @endforeach
                ],
                contents: [
                    @foreach(Cart::instance('shopping')->content() as $cartInfo)
                    {
                        id: '{{ $cartInfo->id }}',
                        quantity: {{ (int) ($cartInfo->qty ?? 1) }},
                        item_price: {{ (float) ($cartInfo->price ?? 0) }}
                    },
                    @endforeach
                ],
                num_items: {{ (int) Cart::instance('shopping')->count() }}
            }, {
                eventID: '{{ $eventId }}'
            });

            console.log('[Meta Pixel] InitiateCheckout sent:', '{{ $eventId }}');
        }
    } catch (e) {
        console.error('[Meta Pixel] InitiateCheckout error:', e);
    }

    // Push the begin_checkout event to dataLayer.
    dataLayer.push({
        event: "begin_checkout",
        event_id: '{{ $eventId }}',
        ecommerce: {
            currency: "BDT",
            value: {{ (float) str_replace(',', '', Cart::instance('shopping')->subtotal()) }},
            items: [@foreach (Cart::instance('shopping')->content() as $cartInfo)
                {
                    item_name: "{{$cartInfo->name ?? ''}}",
                    item_id: "{{$cartInfo->id ?? ''}}",
                    price: "{{$cartInfo->price ?? 0}}",
                    item_brand: "{{$cartInfo->options->brands ?? $cartInfo->options->brand ?? ''}}",
                    item_category: "{{$cartInfo->options->category ?? ''}}",
                    item_size: "{{$cartInfo->options->size ?? $cartInfo->options->product_size ?? ''}}",
                    item_color: "{{$cartInfo->options->color ?? $cartInfo->options->product_color ?? ''}}",
                    currency: "BDT",
                    quantity: {{$cartInfo->qty ?? 0}}
                },
            @endforeach]
        }
    });
</script>
@endpush
