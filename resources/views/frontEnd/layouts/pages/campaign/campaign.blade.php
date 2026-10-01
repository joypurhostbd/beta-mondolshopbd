<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ $generalsetting->name }}</title>
        <link rel="shortcut icon" href="{{asset($generalsetting->favicon)}}" type="image/x-icon" />
        <!-- fot awesome -->
        <link rel="stylesheet" href="{{ asset('frontEnd/campaign/css') }}/all.css" />
        <!-- core css -->
        <link rel="stylesheet" href="{{ asset('frontEnd/css') }}/bootstrap.min.css" />
        <link rel="stylesheet" href="{{ asset('frontEnd/campaign/css') }}/animate.css" />
        <!-- owl carousel -->
        <link rel="stylesheet" href="{{ asset('frontEnd/campaign/css') }}/owl.theme.default.css" />
        <link rel="stylesheet" href="{{ asset('frontEnd/css') }}/owl.carousel.min.css" />
        <!-- select2 -->
        <link rel="stylesheet" href="{{ asset('frontEnd/css') }}/select2.min.css" />
        <!-- common css -->
        <link rel="stylesheet" href="{{ asset('frontEnd/campaign/css') }}/style.css" />
        <link rel="stylesheet" href="{{ asset('frontEnd/campaign/css') }}/responsive.css" />
        @foreach($pixels as $pixel)
        <!-- Facebook Pixel Code -->
        <script>
          !function(f,b,e,v,n,t,s)
          {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};
          if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
          n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t,s)}(window, document,'script',
          'https://connect.facebook.net/en_US/fbevents.js');
          fbq('init', '{{{$pixel->code}}}');
          fbq('track', 'PageView');
        </script>
        <noscript>
          <img height="1" width="1" style="display:none" 
               src="https://www.facebook.com/tr?id={{{$pixel->code}}}&ev=PageView&noscript=1"/>
        </noscript>
        <!-- End Facebook Pixel Code -->
        @endforeach
        
        <meta name="app-url" content="{{route('campaign',$campaign_data->slug)}}" />
        <meta name="robots" content="index, follow" />
        <meta name="description" content="{{ !empty($campaign_data->meta_description) ? $campaign_data->meta_description : Str::limit(strip_tags($campaign_data->description ?? $campaign_data->name), 160) }}" />
        <meta name="keywords" content="{{ $campaign_data->meta_keywords ?? $campaign_data->slug }}" />
        
        <!-- Twitter Card data -->
        <meta name="twitter:card" content="product" />
        <meta name="twitter:site" content="{{ $campaign_data->meta_title ?? $campaign_data->name }}" />
        <meta name="twitter:title" content="{{ $campaign_data->meta_title ?? $campaign_data->name }}" />
        <meta name="twitter:description" content="{{ !empty($campaign_data->meta_description) ? $campaign_data->meta_description : Str::limit(strip_tags($campaign_data->description ?? $campaign_data->name), 160) }}" />
        <meta name="twitter:creator" content="mondolshopbd.com" />
        <meta property="og:url" content="{{route('campaign',$campaign_data->slug)}}" />
        <meta name="twitter:image" content="{{asset($campaign_data->image_one)}}" />
        
        <!-- Open Graph data -->
        <meta property="og:title" content="{{ $campaign_data->meta_title ?? $campaign_data->name }}" />
        <meta property="og:type" content="product" />
        <meta property="og:url" content="{{route('campaign',$campaign_data->slug)}}" />
        <meta property="og:image" content="{{asset($campaign_data->image_one)}}" />
        <meta property="og:description" content="{{ !empty($campaign_data->meta_description) ? $campaign_data->meta_description : Str::limit(strip_tags($campaign_data->description ?? $campaign_data->name), 160) }}" />
        <meta property="og:site_name" content="{{ $generalsetting->name ?? $campaign_data->name }}" />
    </head>

    <body>
         @php
            $subtotal = Cart::instance('shopping')->subtotal();
            $subtotal=str_replace(',','',$subtotal);
            $subtotal=str_replace('.00', '',$subtotal);
            $shipping = Session::get('shipping')?Session::get('shipping'):0;
        @endphp

        <section style="background: url('{{asset($campaign_data->image_one)}}'); background-repeat: no-repeat; background-size:cover; background-position: center;" >
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="campaign_image">
                            <div class="campaign_item">
                                <div class="banner_t">
                                    <h2>{{ !empty($campaign_data->offer_title) ? $campaign_data->offer_title : $campaign_data->name }}</h2>
                                    <a href="#order_form" class="cam_order_now" id="cam_order_now"><i class="fa-solid fa-cart-shopping"></i> অর্ডার করুন </a>
                                    <p class="megaoffer_btn">মেগা অফার ৳{{ $campaign_data->special_price ? $campaign_data->special_price : $product->new_price }} টাকা @if(!empty($campaign_data->free_shipping)) | ফ্রি ডেলিভারি @endif</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if(!empty($campaign_data->end_date) && !$campaign_data->is_expired)
        <section style="background: #e74c3c; color: #fff; padding: 12px 0; text-align: center;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12">
                        <span style="font-size: 17px; font-weight: bold; margin-right: 15px;">
                            <i class="fa-solid fa-clock"></i> অফারটি শেষ হতে বাকি:
                        </span>
                        <span id="campaign-countdown" style="font-size: 18px; font-weight: bold; letter-spacing: 1px; background: rgba(0,0,0,0.2); padding: 5px 15px; border-radius: 20px;">
                            গণনা করা হচ্ছে...
                        </span>
                    </div>
                </div>
            </div>
        </section>
        <script>
            (function() {
                var targetDate = new Date("{{ $campaign_data->end_date->format('Y-m-d H:i:s') }}").getTime();
                var timerInterval = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = targetDate - now;
                    if (distance < 0) {
                        clearInterval(timerInterval);
                        var el = document.getElementById("campaign-countdown");
                        if (el) el.innerHTML = "অফারটি শেষ হয়ে গেছে!";
                        return;
                    }
                    var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    var el = document.getElementById("campaign-countdown");
                    if (el) {
                        el.innerHTML = days + " দিন " + hours + " ঘণ্টা " + minutes + " মিনিট " + seconds + " সেকেন্ড";
                    }
                }, 1000);
            })();
        </script>
        @endif

        @if(!empty($campaign_data->youtube_embed_url) || !empty($campaign_data->video_url))
        <section class="camp_video_sec">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="camp_vid">
                            <iframe width="853" height="480" src="{{ $campaign_data->youtube_embed_url ?? $campaign_data->video_url }}" title="{{ $campaign_data->name }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen=""></iframe>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="ord_btn">
                            <a href="#order_form" class="cam_order_now" id="cam_order_now"> অর্ডার করতে ক্লিক করুন <i class="fa-solid fa-hand-point-right"></i> </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        <section>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="cont_inner">
                            <div class="cont_num">
                                <h2>আমাদের থেকে বিস্তারিত জানতে এই নাম্বারে কল করুন</h2>
                                <a href="tel:{{$contact->phone}}">{{$contact->phone}}</a>
                            </div>
                            <div class="discount_inn">
                                <h2>
                                    @if($product->old_price)
                                    <del>{{$campaign_data->name}} এর আগের দাম {{$product->old_price}}/=</del>
                                    @endif
                                    <p>{{$campaign_data->name}} এর বর্তমান দাম {{ $campaign_data->special_price ? $campaign_data->special_price : $product->new_price }}/=</p>
                                </h2>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rules_sec">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="rules_inner">

                            <div class="rules_item">
                                <div class="rules_head">
                                    <h2>মধুময় বাদাম খাওয়ার উপকারিতা</h2>
                                    <div class="rules_des">
                                        <ul>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="rules_item">
                                <div class="rules_head">
                                    <h2>মধুময় বাদাম খাওয়ার নিয়ম</h2>
                                    <div class="rules_des">
                                        <ul>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                            <li>কোষ্ঠকাঠিন্য (কষা) দূর করতে সাহায্য করে,</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="campro_inn">
                            <div class="campro_head">
                                <h2>হানি নাট (মধু ও বাদাম)</h2>
                            </div>

                            <div class="campro_img_slider owl-carousel">
                               <div class="campro_img_item">
                                   <img src="{{asset($campaign_data->image_one)}}" alt="">
                               </div> 
                               <div class="campro_img_item">
                                   <img src="{{asset($campaign_data->image_one)}}" alt="">
                               </div> 
                               <div class="campro_img_item">
                                   <img src="{{asset($campaign_data->image_one)}}" alt="">
                               </div> 
                            </div>
                            <div class="col-sm-12">
                                <div class="ord_btn">
                                    <a href="#order_form" class="cam_order_now" id="cam_order_now"> অর্ডার করতে ক্লিক করুন <i class="fa-solid fa-hand-point-right"></i> </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <section class="why_choose_sec">
            <div class="container">
                <div class="row">
                  <div class="col-sm-12">
                      <div class="why_choose_inn">
                          <div class="why_choose">
                              <div class="why_choose_head">
                                <h2>আমাদের উপর কেন আস্থা রাখবেন ??</h2>
                              </div>
                              <div class="why_choose_midd">
                                  <div class="why_choose_widget">
                                      <ul>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                          <li>
                                              এই মধু প্রাকৃতিক চাক থেকে সংগৃহীত।
                                          </li>
                                      </ul>
                                  </div>
                                  <div class="why_choose_widget">
                                      <div class="why_img">
                                          <img src="{{asset('frontEnd/campaign')}}/images/honey.png" alt="">
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                </div>
            </div>
        </section>
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="rev_inn">
                            <div class="border_line"></div>
                            <div class="review_slider owl-carousel">
                            @foreach($campaign_data->images as $key=>$value)
                            <div class="review_item">
                                <img src="{{asset($value->image)}}" alt="">
                            </div>
                            @endforeach
                           </div>
                            <div class="col-sm-12">
                                <div class="ord_btn">
                                    <a href="#order_form" class="cam_order_now" id="cam_order_now"> অর্ডার করতে ক্লিক করুন <i class="fa-solid fa-hand-point-right"></i> </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <section class="form_sec">
        <div class="container">
           <div class="row">
             <div class="col-sm-12">
                <div class="form_inn">
                    <div class="col-sm-12">
                        <div class="row">
                <div class="col-sm-12">
                    <h2 class="campaign_offer">অফারটি সীমিত সময়ের জন্য, তাই অফার শেষ হওয়ার আগেই অর্ডার করুন</h2>
                </div>
            </div>
            <div class="row order_by">
            <div class="col-sm-5 cus-order-2">
                <div class="checkout-shipping" id="order_form">
                    <form action="{{route('customer.ordersave')}}" method="POST" data-parsley-validate="">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h5 class="potro_font">আপনার ইনফরমেশন দিন  </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="name">আপনার নাম লিখুন * </label>
                                        <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{old('name')}}" placeholder="নাম" required>
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
                                        <label for="phone">আপনার মোবাইল লিখুন *</label>
                                        <input type="tel" minlength="11" maxlength="11" id="phone" pattern="01[3-9][0-9]{8}" data-parsley-pattern="^01[3-9][0-9]{8}$" data-parsley-pattern-message="সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)" class="form-control phone-input @error('phone') is-invalid @enderror" name="phone" value="{{old('phone')}}" placeholder="01XXXXXXXXX" required>
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
                                        <label for="address">আপনার ঠিকানা লিখুন   *</label>
                                        <input type="address" id="address" class="form-control @error('address') is-invalid @enderror" placeholder="জেলা, থানা, গ্রাম " name="address" value="{{old('address')}}"  required>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group mb-3">
                                        <label for="area">আপনার এরিয়া সিলেক্ট করুন  *</label>
                                        <select type="area" id="area" class="form-control @error('area') is-invalid @enderror" name="area"   required>
                                            @foreach($shippingcharge as $key=>$value)
                                            <option value="{{$value->id}}">{{$value->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <!-- col-end -->
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button class="order_place" type="submit">অর্ডার কন্ফার্ম করুন </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- card end -->
                </form>
                </div>
            </div>
            <!-- col end -->
            <div class="col-sm-7 cust-order-1">
                <div class="cart_details">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="potro_font">পণ্যের বিবরণ </h5>
                        </div>
                        <div class="card-body cartlist  table-responsive">
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
                                    @foreach(Cart::instance('shopping')->content() as $value)
                                    <tr>
                                        <td>
                                            <a href="{{route('product',$value->options->slug)}}"><i class="fas fa-trash text-danger"></i></a>
                                        </td>
                                        <td class="text-left">
                                             <a style="font-size: 14px;" href="{{route('product',$value->options->slug)}}"><img src="{{asset($value->options->image)}}" height="30" width="30"> {{Str::limit($value->name,20)}}</a>
                                        </td>
                                        <td width="15%" class="cart_qty">
                                            <div class="qty-cart vcart-qty">
                                                <div class="quantity">
                                                    <button class="minus cart_decrement"  data-id="{{$value->rowId}}">-</button>
                                                    <input type="text" value="{{$value->qty}}" readonly />
                                                    <button class="plus  cart_increment" data-id="{{$value->rowId}}">+</button>
                                                </div>
                                            </div>
                                        </td>
                                        <td>৳{{$value->price*$value->qty}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">মোট</th>
                                      <td>
                                       <span id="net_total"><span class="alinur">৳ </span><strong>{{$subtotal}}</strong></span>
                                      </td>
                                     </tr>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">ডেলিভারি চার্জ</th>
                                      <td>
                                       <span id="cart_shipping_cost"><span class="alinur">৳ </span><strong>{{$shipping}}</strong></span>
                                      </td>
                                     </tr>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">সর্বমোট</th>
                                      <td>
                                       <span id="grand_total"><span class="alinur">৳ </span><strong>{{$subtotal+$shipping}}</strong></span>
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
                    </div>
                </div>

             </div>
            </div>
        </div>
    </section>

        <script src="{{ asset('frontEnd/campaign/js') }}/jquery-2.1.4.min.js"></script>
        <script src="{{ asset('frontEnd/campaign/js') }}/all.js"></script>
        <script src="{{ asset('frontEnd/js') }}/bootstrap.min.js"></script>
        <script src="{{ asset('frontEnd/js') }}/owl.carousel.min.js"></script>
        <script src="{{ asset('frontEnd/js') }}/select2.min.js"></script>
        <script src="{{ asset('frontEnd/campaign/js') }}/script.js"></script>
        <!-- bootstrap js -->
        <script>
            $(document).ready(function () {
                $(".owl-carousel").owlCarousel({
                    margin: 15,
                    loop: true,
                    dots: false,
                    autoplay: true,
                    autoplayTimeout: 6000,
                    autoplayHoverPause: true,
                    items: 1,
                    });
                $('.owl-nav').remove();
            });
        </script>
        <script>
            $(document).ready(function() {
                $('.select2').select2();
            });
        </script>
        <script>
             $("#area").on("change", function () {
                var id = $(this).val();
                $.ajax({
                    type: "GET",
                    data: { id: id },
                    url: "{{route('shipping.charge')}}",
                    dataType: "html",
                    success: function(response){
                        $('.cartlist').html(response);
                    }
                });
            });
        </script>
           <script>
            $(".cart_remove").on("click", function () {
                var id = $(this).data("id");
                $("#loading").show();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.remove')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                $("#loading").hide();
                                return cart_count() + mobile_cart() + cart_summary();
                            }
                        },
                    });
                }
            });
            $(".cart_increment").on("click", function () {
                var id = $(this).data("id");
                $("#loading").show();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.increment')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                $("#loading").hide();
                                return cart_count() + mobile_cart();
                            }
                        },
                    });
                }
            });

            $(".cart_decrement").on("click", function () {
                var id = $(this).data("id");
                $("#loading").show();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.decrement')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                $("#loading").hide();
                                return cart_count() + mobile_cart();
                            }
                        },
                    });
                }
            });

        </script>
        <script>
            $('.review_slider').owlCarousel({   
                dots: false,
                arrow: false,
                autoplay: true,
                loop: true,
                margin: 10,
                smartSpeed: 1000,
                mouseDrag: true,
                touchDrag: true,
                items: 6,
                responsiveClass: true,
                responsive: {
                    300: {
                        items: 1,
                    },
                    480: {
                        items: 2,
                    },
                    768: {
                        items: 5,
                    },
                    1170: {
                        items: 5,
                    },
                }
            });
        </script>

        <script>
            $('.campro_img_slider').owlCarousel({   
                dots: false,
                arrow: false,
                autoplay: true,
                loop: true,
                margin: 10,
                smartSpeed: 1000,
                mouseDrag: true,
                touchDrag: true,
                items: 3,
                responsiveClass: true,
                responsive: {
                    300: {
                        items: 1,
                    },
                    480: {
                        items: 2,
                    },
                    768: {
                        items: 3,
                    },
                    1170: {
                        items: 3,
                    },
                }
            });
        </script>
        <script>
            $(document).on('input', '.phone-input', function() {
                var banglaDigits = {'০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9'};
                var cleanVal = $(this).val().replace(/[০-৯]/g, function(s) {
                    return banglaDigits[s];
                }).replace(/[^\d]/g, '');
                $(this).val(cleanVal);
            });
        </script>
        @include('frontEnd.layouts.partials.whatsapp_button')
    </body>
</html>
