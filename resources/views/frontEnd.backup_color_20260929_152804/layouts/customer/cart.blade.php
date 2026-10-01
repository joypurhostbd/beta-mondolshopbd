@extends('frontEnd.layouts.master') @section('title', 'Customer Checkout') @push('css')
<link rel="stylesheet" href="{{ asset('frontEnd/css/select2.min.css') }}" />
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
                      <!-- col end -->
            <div class="col-sm-12 cust-order-1">
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
              <a href="{{route('customer.checkout')}}" class="go_cart">  অর্ডার করুন </a>
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
    $("#area").on("change", function() {
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

    try {
        if (typeof fbq === 'function') {
            fbq('trackCustom', 'ViewCart', {
                content_type: 'product',
                contents: [
                    @foreach(Cart::instance('shopping')->content() as $cartInfo)
                    {
                        id: '{{ $cartInfo->id }}',
                        quantity: {{ (int) ($cartInfo->qty ?? 1) }},
                        item_price: {{ (float) ($cartInfo->price ?? 0) }}
                    },
                    @endforeach
                ],
                content_ids: [
                    @foreach(Cart::instance('shopping')->content() as $cartInfo)
                    '{{ $cartInfo->id }}',
                    @endforeach
                ],
                value: {{ (float) (str_replace(',', '', Cart::instance('shopping')->subtotal())) }},
                currency: 'BDT',
                num_items: {{ (int) Cart::instance('shopping')->count() }}
            });
        }
    } catch (e) {
        console.error('[Meta Pixel] ViewCart error:', e);
    }
</script>
<script type="text/javascript">
    window.dataLayer = window.dataLayer || [];
    // Clear the previous ecommerce object.
    dataLayer.push({ ecommerce: null });

    // Push the begin_checkout event to dataLayer.
    dataLayer.push({
        event: "begin_checkout",
        ecommerce: {
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

    $(document).on('click', '.go_cart', function () {
        try {
            if (typeof fbq === 'function') {
                fbq('track', 'InitiateCheckout', {
                    content_type: 'product',
                    contents: [
                        @foreach(Cart::instance('shopping')->content() as $cartInfo)
                        {
                            id: '{{ $cartInfo->id }}',
                            quantity: {{ (int) ($cartInfo->qty ?? 1) }},
                            item_price: {{ (float) ($cartInfo->price ?? 0) }}
                        },
                        @endforeach
                    ],
                    content_ids: [
                        @foreach(Cart::instance('shopping')->content() as $cartInfo)
                        '{{ $cartInfo->id }}',
                        @endforeach
                    ],
                    value: {{ (float) (str_replace(',', '', Cart::instance('shopping')->subtotal())) }},
                    currency: 'BDT',
                    num_items: {{ (int) Cart::instance('shopping')->count() }}
                });
            }
        } catch (e) {
            console.error('[Meta Pixel] InitiateCheckout click error:', e);
        }
    });
</script>
@endpush
