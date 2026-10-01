<!DOCTYPE html>
<html lang="bn">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>@yield('title') - {{$generalsetting->name}}</title>
        <!-- App favicon -->

        <link rel="shortcut icon" href="{{asset($generalsetting->favicon)}}" alt="{{$generalsetting->name}} Favicon" />
        <meta name="author" content="{{$generalsetting->name}}" />
        <link rel="canonical" href="@yield('canonical', url()->current())" />
        @stack('seo') 
        @stack('css')
        <link rel="stylesheet" href="{{asset('frontEnd/css/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/animate.css')}}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/all.min.css')}}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/owl.carousel.min.css')}}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/owl.theme.default.min.css')}}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/select2.min.css')}}" />
        <!-- toastr css -->
        <link rel="stylesheet" href="{{asset('backEnd/')}}/assets/css/toastr.min.css" />

        <link rel="stylesheet" href="{{asset('frontEnd/css/style.css')}}?v={{ @filemtime(public_path('frontEnd/css/style.css')) }}" />
        <link rel="stylesheet" href="{{asset('frontEnd/css/responsive.css')}}?v={{ @filemtime(public_path('frontEnd/css/responsive.css')) }}" />
        @include('frontEnd.layouts.partials.dynamic_button_styles')

        <meta name="facebook-domain-verification" content="38f1w8335btoklo88dyfl63ba3st2e" />
        <script>
            window.dataLayer = window.dataLayer || [];
        </script>
   


               

        @if(isset($pixels) && $pixels->isNotEmpty())
        @foreach($pixels as $pixel)
        <!-- Facebook Pixel Code -->
        <script>
            !(function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = "2.0";
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            })(window, document, "script", "https://connect.facebook.net/en_US/fbevents.js");
            fbq("init", "{{ $pixel->code }}");
            fbq("track", "PageView");
        </script>
        <noscript>
            <img height="1" width="1" style="display: none;" src="https://www.facebook.com/tr?id={{ $pixel->code }}&ev=PageView&noscript=1" alt="Facebook Pixel" />
        </noscript>
        <!-- End Facebook Pixel Code -->
        @endforeach
        @endif

        @if(session('fb_add_to_cart'))
        @php $fbCart = session('fb_add_to_cart'); @endphp
        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push({
                event: 'add_to_cart',
                event_id: '{{ $fbCart['event_id'] }}',
                ecommerce: {
                    currency: 'BDT',
                    value: {{ (float) ($fbCart['value'] ?? 0) }},
                    items: [{
                        item_id: '{{ $fbCart['id'] ?? '' }}',
                        item_name: '{{ addslashes($fbCart['name'] ?? '') }}',
                        price: {{ (float) ($fbCart['price'] ?? 0) }},
                        quantity: {{ (int) ($fbCart['qty'] ?? 1) }}
                    }]
                }
            });
        </script>
        @endif
        
        @if(isset($gtm_code) && $gtm_code->isNotEmpty())
        @foreach($gtm_code as $gtm)
        @php
            $gtmContainerId = Str::startsWith(strtoupper(trim($gtm->code)), 'GTM-') ? strtoupper(trim($gtm->code)) : 'GTM-' . strtoupper(trim($gtm->code));
            $gtmBaseUrl = method_exists($gtm, 'getEffectiveScriptBaseUrl') ? $gtm->getEffectiveScriptBaseUrl() : 'https://www.googletagmanager.com';
        @endphp
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        '{{ $gtmBaseUrl }}/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ $gtmContainerId }}');</script>
        <!-- End Google Tag Manager -->
        @endforeach
        @endif

        {{-- Injected Dynamic Head Snippets (WPCode Engine) --}}
        @if(!empty($injectedHeadSnippets))
            @foreach($injectedHeadSnippets as $headSnippet)
                {!! $headSnippet !!}
            @endforeach
        @endif
    </head>
    <body class="gotop">
        {{-- Injected Dynamic Body Open Snippets (WPCode Engine) --}}
        @if(!empty($injectedBodyOpenSnippets))
            @foreach($injectedBodyOpenSnippets as $bodyOpenSnippet)
                {!! $bodyOpenSnippet !!}
            @endforeach
        @endif
        @php $subtotal = Cart::instance('shopping')->subtotal(); @endphp
        <nav class="mobile-menu" aria-label="Mobile Navigation">
                <div class="mobile-menu-logo">
                    <div class="logo-image">
                        <img src="{{asset($generalsetting->white_logo)}}" alt="{{ $generalsetting->name ?? 'Mondolshopbd' }}" />
                    </div>
                    <div class="mobile-menu-close" role="button" tabindex="0" aria-label="মেনু বন্ধ করুন">
                        <i class="fa fa-times"></i>
                    </div>
                </div>
                <ul class="first-nav">
                    @foreach($menucategories as $scategory)
                    <li class="parent-category">
                        <a href="{{url('category/'.$scategory->slug)}}" class="menu-category-name">
                            <img src="{{asset($scategory->image)}}" alt="{{ $scategory->name }}" class="side_cat_img" />
                            {{$scategory->name}}
                        </a>
                        @if($scategory->subcategories->count() > 0)
                        <span class="menu-category-toggle">
                            <i class="fa fa-chevron-down"></i>
                        </span>
                        @endif
                        <ul class="second-nav" style="display: none;">
                            @foreach($scategory->subcategories as $subcategory)
                            <li class="parent-subcategory">
                                <a href="{{url('subcategory/'.$subcategory->slug)}}" class="menu-subcategory-name">{{$subcategory->subcategoryName}}</a>
                                @if($subcategory->childcategories->count() > 0)
                                <span class="menu-subcategory-toggle"><i class="fa fa-chevron-down"></i></span>
                                @endif
                                <ul class="third-nav" style="display: none;">
                                    @foreach($subcategory->childcategories as $childcat)
                                    <li class="childcategory"><a href="{{url('products/'.$childcat->slug)}}" class="menu-childcategory-name">{{$childcat->childcategoryName}}</a></li>
                                    @endforeach
                                </ul>
                            </li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach
                </ul>
            </nav>
        <header id="navbar_top">
            <div class="mobile-header sticky">
                <div class="mobile-logo">
                    <div class="menu-bar">
                        <a class="toggle" href="#" role="button" aria-label="মেনু খুলুন" aria-expanded="false">
                            <i class="fa-solid fa-bars"></i>
                        </a>
                    </div>
                    <div class="menu-logo">
                        <a href="{{route('home')}}"><img src="{{asset($generalsetting->white_logo)}}" alt="{{ $generalsetting->name ?? 'Mondolshopbd' }}" /></a>
                    </div>
                    <div class="menu-bag">
                        <p class="margin-shopping cart-icon" role="button" tabindex="0" aria-label="শপিং কার্ট খুলুন">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="mobilecart-qty">{{Cart::instance('shopping')->count()}}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mobile-search">
                <form action="{{route('search')}}">
                    <input type="text" placeholder="Search Product ... " value="" class="msearch_keyword msearch_click" name="keyword" aria-label="Search Product" />
                    <button type="submit" aria-label="Search"><i data-feather="search"></i></button>
                </form>
                <div class="search_result"></div>
            </div>

            

            <div class="main-header">
                <!-- header to end -->
                <div class="logo-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="logo-header">
                                    <div class="main-logo">
                                        <a href="{{route('home')}}"><img src="{{asset($generalsetting->white_logo)}}" alt="{{ $generalsetting->name ?? 'Mondolshopbd' }}" /></a>
                                    </div>
                                    <div class="main-search">
                                        <form action="{{route('search')}}">
                                            <input type="text" placeholder="Search Product..." class="search_keyword search_click" name="keyword" aria-label="Search Product" />
                                            <button type="submit" aria-label="Search">
                                                <i data-feather="search"></i>
                                            </button>
                                        </form>
                                        <div class="search_result"></div>
                                    </div>
                                    <div class="header-list-items">
                                        <ul>
                                            <li class="track_btn">
                                                <a href="{{route('customer.order_track')}}"> <i class="fa fa-truck"></i>Track Order</a>
                                            </li>
                                            @if(Auth::guard('customer')->user())
                                             <li class="for_order">
                                                <p>
                                                    <a href="{{route('customer.account')}}">
                                                        <i class="fa-regular fa-user"></i>

                                                        {{Str::limit(Auth::guard('customer')->user()->name,14)}}
                                                    </a>
                                                </p>
                                            </li>
                                            @else
                                            <li class="for_order">
                                                <p>
                                                    <a href="{{route('customer.login')}}">
                                                        <i class="fa-regular fa-user"></i>
                                                        Login / Sign Up
                                                    </a>
                                                </p>
                                            </li>
                                            @endif

                                            <li class="cart-dialog" >
                                                <a href="#" id="cart-icon" role="button" aria-label="শপিং কার্ট খুলুন">
                                                    <p class="margin-shopping cart-icon" role="button" tabindex="0" aria-label="শপিং কার্ট খুলুন">
                                                        <i class="fa-solid fa-cart-shopping"></i>
                                                        <span class="cart-qty-text">{{Cart::instance('shopping')->count()}}</span>
                                                    </p>
                                                </a>
                                                {{--<div class="cshort-summary">
                                                    <ul>
                                                        @foreach(Cart::instance('shopping')->content() as $key=>$value)
                                                        <li>
                                                            <a href=""><img src="{{asset($value->options->image)}}" alt="" /></a>
                                                        </li>
                                                        <li><a href="">{{Str::limit($value->name, 30)}}</a></li>
                                                        <li>Qty: {{$value->qty}}</li>
                                                        <li>
                                                            <p>৳{{$value->price}}</p>
                                                            <button class="remove-cart cart_remove" data-id="{{$value->rowId}}"><i data-feather="x"></i></button>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                    <p><strong>সর্বমোট : ৳{{$subtotal}}</strong></p>
                                                    <a href="{{route('customer.checkout')}}" class="go_cart"> অর্ডার করুন </a>
                                                </div> --}}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="menu-area">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="catagory_menu">
                                    <ul>
                                        @foreach ($menucategories as $scategory)
                                        <li class="cat_bar ">
                                            <a href="{{ url('category/' . $scategory->slug) }}"> 
                                                <span class="cat_head">{{ $scategory->name }}</span>
                                                @if ($scategory->subcategories->count() > 0)
                                                <i class="fa-solid fa-angle-down cat_down"></i>
                                                @endif
                                            </a>
                                            @if($scategory->subcategories->count() > 0)
                                            <ul class="Cat_menu">
                                                @foreach ($scategory->subcategories as $subcat)
                                                <li class="Cat_list cat_list_hover">
                                                    <a href="{{ url('subcategory/' . $subcat->slug) }}">
                                                        <span>{{ Str::limit($subcat->subcategoryName, 25) }}</span>
                                                        @if($subcat->childcategories->count() > 0)<i class="fa-solid fa-chevron-right cat_down"></i>@endif
                                                    </a>
                                                    @if($subcat->childcategories->count() > 0)
                                                    <ul class="child_menu">
                                                        @foreach($subcat->childcategories as $childcat)
                                                        <li class="child_main">
                                                            <a href="{{ url('products/'.$childcat->slug) }}">{{ $childcat->childcategoryName }}</a>
                                                            
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                    @endif
                                                </li>
                                                @endforeach
                                            </ul>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- main-header end -->
        </header>
        <main id="content" role="main">
            @yield('content')
        </main>
            <!-- content end -->
        <!-- Storefront Modern Footer Start -->
        <footer class="site-modern-footer">
            <div class="footer-top-section">
                <div class="container">
                    <div class="row gy-4">
                        <!-- Column 1: Brand Info & Contacts -->
                        <div class="col-lg-4 col-md-6">
                            <div class="footer-brand-box">
                                <a href="{{ route('home') }}" class="footer-logo d-inline-block mb-3">
                                    <img src="{{ asset($generalsetting->white_logo ?? $generalsetting->dark_logo ?? 'frontEnd/images/logo.png') }}" alt="{{ $generalsetting->name ?? 'MondolShop BD' }}" class="img-fluid" />
                                </a>
                                <p class="footer-about-text">
                                    {{ $generalsetting->footer_about ?: 'আপনার প্রয়োজনীয় পণ্য এখন আরও সহজে এবং নিরাপদে। মানসম্মত পণ্য, সেরা দাম এবং দ্রুত ডেলিভারির জন্য MondolShop BD আপনার বিশ্বস্ত সঙ্গী।' }}
                                </p>
                                
                                <div class="footer-contact-items">
                                    @php
                                        $footerPhone = $generalsetting->footer_phone ?: ($contact->hotline ?? $contact->phone ?? '01877786651');
                                        $footerEmail = $generalsetting->footer_email ?: ($contact->email ?? $contact->hotmail ?? 'support@mondolshopbd.com');
                                        $footerAddress = $generalsetting->footer_address ?: ($contact->address ?? 'ঢাকা, বাংলাদেশ');
                                    @endphp

                                    <div class="footer-contact-item">
                                        <span class="contact-icon"><i class="fa-solid fa-phone"></i></span>
                                        <a href="tel:{{ $footerPhone }}" class="contact-link">{{ $footerPhone }}</a>
                                    </div>
                                    <div class="footer-contact-item">
                                        <span class="contact-icon"><i class="fa-solid fa-envelope"></i></span>
                                        <a href="mailto:{{ $footerEmail }}" class="contact-link">{{ $footerEmail }}</a>
                                    </div>
                                    <div class="footer-contact-item">
                                        <span class="contact-icon"><i class="fa-solid fa-location-dot"></i></span>
                                        <span class="contact-text">{{ $footerAddress }}</span>
                                    </div>
                                </div>

                                <div class="footer-social-row mt-3">
                                    @if(isset($socialicons) && count($socialicons) > 0)
                                        @foreach($socialicons as $soc)
                                            <a href="{{ $soc->link ?: '#' }}" class="social-circle-btn" target="_blank" rel="noopener noreferrer" aria-label="{{ $soc->title ?? 'Social Link' }}">
                                                <i class="{{ $soc->icon }}"></i>
                                            </a>
                                        @endforeach
                                    @else
                                        <a href="#" class="social-circle-btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                                        <a href="#" class="social-circle-btn" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                                        <a href="#" class="social-circle-btn" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                                        <a href="#" class="social-circle-btn" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                                        <a href="#" class="social-circle-btn" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Column 2: Useful Links -->
                        <div class="col-lg-2 col-md-6 col-6">
                            <div class="footer-links-box">
                                <h4 class="footer-heading">
                                    <i class="fa-solid fa-link heading-icon"></i> {{ $generalsetting->footer_useful_links_title ?: 'Useful Links' }}
                                </h4>
                                <ul class="footer-links-list">
                                    <li>
                                        <a href="{{ route('contact') }}"><i class="fa-solid fa-angle-right"></i> Contact Us</a>
                                    </li>
                                    @if(isset($pages) && count($pages) > 0)
                                        @foreach($pages as $page)
                                            <li>
                                                <a href="{{ route('page', ['slug' => $page->slug]) }}"><i class="fa-solid fa-angle-right"></i> {{ $page->name }}</a>
                                            </li>
                                        @endforeach
                                    @else
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Order Procedure</a></li>
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Delivery Rules</a></li>
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Return Policy</a></li>
                                    @endif
                                    <li>
                                        <a href="{{ route('customer.order_track') }}"><i class="fa-solid fa-angle-right"></i> Track Your Order</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Column 3: Information -->
                        <div class="col-lg-2 col-md-6 col-6">
                            <div class="footer-links-box">
                                <h4 class="footer-heading">
                                    <i class="fa-solid fa-circle-info heading-icon"></i> {{ $generalsetting->footer_info_links_title ?: 'Information' }}
                                </h4>
                                <ul class="footer-links-list">
                                    @if(isset($pagesright) && count($pagesright) > 0)
                                        @foreach($pagesright as $page)
                                            <li>
                                                <a href="{{ route('page', ['slug' => $page->slug]) }}"><i class="fa-solid fa-angle-right"></i> {{ $page->name }}</a>
                                            </li>
                                        @endforeach
                                    @else
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Terms &amp; Conditions</a></li>
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Privacy Policy</a></li>
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> FAQ</a></li>
                                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> About Us</a></li>
                                    @endif
                                    <li>
                                        <a href="{{ route('sitemap') }}"><i class="fa-solid fa-angle-right"></i> Sitemap</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Column 4: Stay Connected & Download App -->
                        <div class="col-lg-4 col-md-6">
                            <div class="footer-connect-box">
                                @if(($generalsetting->newsletter_status ?? 1) == 1)
                                    <h4 class="footer-heading">
                                        <i class="fa-solid fa-envelope heading-icon"></i> {{ $generalsetting->newsletter_title ?: 'Stay Connected' }}
                                    </h4>
                                    <p class="newsletter-desc">
                                        {{ $generalsetting->newsletter_text ?: 'Subscribe to our newsletter for exclusive offers, new arrivals & the latest updates.' }}
                                    </p>
                                    <form id="footerNewsletterForm" action="{{ route('newsletter.subscribe') }}" method="POST" class="footer-newsletter-box">
                                        @csrf
                                        <div class="newsletter-field-wrapper">
                                            <i class="fa-solid fa-envelope input-prefix-icon"></i>
                                            <input type="email" name="email" id="newsletterEmailInput" placeholder="Enter your email address" required aria-label="Enter your email address" />
                                            <button type="submit" class="btn-newsletter-subscribe" id="newsletterSubmitBtn">
                                                <i class="fa-solid fa-paper-plane me-1"></i> Subscribe
                                            </button>
                                        </div>
                                        <div id="newsletterMessageAlert" class="newsletter-feedback mt-2" style="display: none;"></div>
                                    </form>
                                @endif

                                @if(($generalsetting->app_download_status ?? 1) == 1)
                                    <div class="app-download-section mt-4">
                                        <div class="app-divider-title">
                                            <span>{{ $generalsetting->app_download_title ?: 'Download Our App' }}</span>
                                        </div>
                                        <div class="app-badges-grid d-flex flex-wrap gap-2 mt-3">
                                            <a href="{{ $generalsetting->play_store_url ?: '#' }}" class="app-badge-btn" target="_blank" rel="noopener noreferrer">
                                                <i class="fa-brands fa-google-play badge-icon"></i>
                                                <div class="badge-text-wrap">
                                                    <span class="sub-label">GET IT ON</span>
                                                    <span class="main-label">Google Play</span>
                                                </div>
                                            </a>
                                            <a href="{{ $generalsetting->app_store_url ?: '#' }}" class="app-badge-btn" target="_blank" rel="noopener noreferrer">
                                                <i class="fa-brands fa-apple badge-icon"></i>
                                                <div class="badge-text-wrap">
                                                    <span class="sub-label">Download on the</span>
                                                    <span class="main-label">App Store</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Middle Trust Features Bar -->
            @if(($generalsetting->features_status ?? 1) == 1)
                <div class="footer-features-bar">
                    <div class="container">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-0 align-items-center">
                            <div class="col feature-item-col">
                                <div class="feature-single-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div class="feature-content-box">
                                        <h5 class="feature-title">{{ $generalsetting->feature1_title ?: '100% Secure Payment' }}</h5>
                                        <p class="feature-subtitle">{{ $generalsetting->feature1_subtitle ?: 'Safe & Secure Transactions' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col feature-item-col">
                                <div class="feature-single-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-truck-fast"></i>
                                    </div>
                                    <div class="feature-content-box">
                                        <h5 class="feature-title">{{ $generalsetting->feature2_title ?: 'Fast Delivery' }}</h5>
                                        <p class="feature-subtitle">{{ $generalsetting->feature2_subtitle ?: 'All Over Bangladesh' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col feature-item-col">
                                <div class="feature-single-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                    </div>
                                    <div class="feature-content-box">
                                        <h5 class="feature-title">{{ $generalsetting->feature3_title ?: 'Easy Return' }}</h5>
                                        <p class="feature-subtitle">{{ $generalsetting->feature3_subtitle ?: 'Within 7 Days' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col feature-item-col">
                                <div class="feature-single-item">
                                    <div class="feature-icon-box">
                                        <i class="fa-solid fa-headset"></i>
                                    </div>
                                    <div class="feature-content-box">
                                        <h5 class="feature-title">{{ $generalsetting->feature4_title ?: '24/7 Support' }}</h5>
                                        <p class="feature-subtitle">{{ $generalsetting->feature4_subtitle ?: "We're Here to Help" }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Bottom Orange Copyright Bar -->
            <div class="footer-copyright-bar">
                <div class="container">
                    <div class="row align-items-center gy-2">
                        <div class="col-lg-7 col-md-12">
                            <div class="copyright-text">
                                <p class="mb-0">
                                    @if(!empty($generalsetting->copyright))
                                        {!! $generalsetting->copyright !!}
                                    @else
                                        © {{ date('Y') }} {{ $generalsetting->name ?? 'MondolShop BD' }}. All rights reserved. | Online Shopping In Bangladesh With Home Delivery | Designed &amp; Developed by <a href="https://hostiqs.com/" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-link"></i> Hostiqs</a>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-12">
                            <div class="payment-methods-wrapper text-lg-end text-center">
                                @if(!empty($generalsetting->payment_methods_image) && file_exists(public_path($generalsetting->payment_methods_image)))
                                    <img src="{{ asset($generalsetting->payment_methods_image) }}" alt="Payment Methods" class="img-fluid payment-banner-img" />
                                @elseif(($generalsetting->show_payment_methods ?? 1) == 1)
                                    <div class="payment-badges-list d-inline-flex flex-wrap align-items-center justify-content-lg-end justify-content-center gap-1">
                                        <span class="payment-badge-pill" title="bKash">
                                            <span class="badge-bkash">bKash</span>
                                        </span>
                                        <span class="payment-badge-pill" title="Nagad">
                                            <span class="badge-nagad"><i class="fa-solid fa-fire text-danger me-1"></i>Nagad</span>
                                        </span>
                                        <span class="payment-badge-pill" title="Rocket">
                                            <span class="badge-rocket">Rocket</span>
                                        </span>
                                        <span class="payment-badge-pill" title="VISA">
                                            <span class="badge-visa">VISA</span>
                                        </span>
                                        <span class="payment-badge-pill" title="Mastercard">
                                            <span class="badge-mastercard"><span class="mc-circle mc-red"></span><span class="mc-circle mc-orange"></span></span>
                                        </span>
                                        <span class="payment-badge-pill" title="Cash on Delivery">
                                            <span class="badge-cod"><i class="fa-solid fa-truck me-1"></i>Cash on Delivery</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Storefront Modern Footer End -->

        <nav class="footer_nav" aria-label="Mobile Bottom Navigation">
            <ul>
                <li>
                    <a class="toggle" role="button" tabindex="0" aria-label="ক্যাটাগরি মেনু খুলুন">
                        <span>
                            <i class="fa-solid fa-bars"></i>
                        </span>
                        <span>Category</span>
                    </a>
                </li>

                <li>
                    @php
                        $mobileWaSetting = $generalsetting ?? \App\Models\GeneralSetting::where('status', 1)->first();
                        $mobileWaContact = $contact ?? \App\Models\Contact::where('status', 1)->first();
                        $mobileWaRaw = !empty($mobileWaSetting->whatsapp_number) ? $mobileWaSetting->whatsapp_number : ($mobileWaContact->phone ?? '');
                        $mobileWaClean = preg_replace('/[^0-9]/', '', (string)$mobileWaRaw);
                        if (!empty($mobileWaClean)) {
                            if (str_starts_with($mobileWaClean, '01') && strlen($mobileWaClean) === 11) {
                                $mobileWaClean = '88' . $mobileWaClean;
                            } elseif (str_starts_with($mobileWaClean, '1') && strlen($mobileWaClean) === 10) {
                                $mobileWaClean = '880' . $mobileWaClean;
                            }
                        }
                        $mobileWaMsg = !empty($mobileWaSetting->whatsapp_message) ? $mobileWaSetting->whatsapp_message : 'Hello, I want to know more about your products.';
                        $mobileWaUrl = !empty($mobileWaClean) ? 'https://api.whatsapp.com/send?phone=' . $mobileWaClean . '&text=' . rawurlencode($mobileWaMsg) : 'https://wa.link/qb68wg';
                    @endphp
                    <a href="{{ $mobileWaUrl }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp এ মেসেজ করুন">
                        <span>
                            <i class="fa-solid fa-message"></i>
                        </span>
                        <span>Message</span>
                    </a>
                </li>

                <li class="mobile_home">
                    <a href="{{route('home')}}" aria-label="হোমপেজ">
                        <span><i class="fa-solid fa-home"></i></span> <span>Home</span>
                    </a>
                </li>

                <li>
                    <a href="#" class="cart-icon" role="button" aria-label="শপিং কার্ট খুলুন">
                        <span>
                            <i class="fa-solid fa-cart-shopping"></i>
                        </span>
                        <span>Cart (<b class="mobilecart-qty">{{Cart::instance('shopping')->count()}}</b>)</span>
                    </a>
                </li>
                @if(Auth::guard('customer')->user())
                <li>
                    <a href="{{route('customer.account')}}" aria-label="আমার একাউন্ট">
                        <span>
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <span>Account</span>
                    </a>
                </li>
                @else
                <li>
                    <a href="{{route('customer.login')}}" aria-label="লগইন বা সাইন আপ">
                        <span>
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <span>Login</span>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
        

        <div class="scrolltop" style="">
            <div class="scroll">
                <i class="fa fa-angle-up"></i>
            </div>
        </div>

        @include('frontEnd.layouts.partials.whatsapp_button')
        
        @include('frontEnd.layouts.partials.side_cart')
        
        

        <!-- /. fixed sidebar -->

        <div id="custom-modal"></div>
        <div id="page-overlay"></div>
        <div id="loading"><div class="custom-loader"></div></div>

        <script src="{{asset('frontEnd/js/jquery-3.6.3.min.js')}}"></script>
        <script src="{{asset('frontEnd/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('frontEnd/js/owl.carousel.min.js')}}"></script>
        <script src="{{asset('frontEnd/js/wow.min.js')}}"></script>
        <script>
            new WOW().init();
        </script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <!-- feather icon -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
        <script>
            feather.replace();
        </script>
        <script src="{{asset('backEnd/')}}/assets/js/toastr.min.js"></script>
        {!! Toastr::message() !!} @stack('script')
        
        

        
        
        
        <script>
        
        
        
        
 
        
            $(document).on("click", ".btn_cart_action, .quick_view, .quick_view_btn", function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                $("#loading").show();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('quickview')}}",
                        success: function (data) {
                            $("#loading").hide();
                            if (data) {
                                $("#custom-modal").html(data);
                                $("#custom-modal").show();
                                $("#page-overlay").show();
                            }
                        },
                        error: function () {
                            $("#loading").hide();
                            if (typeof toastr !== 'undefined') {
                                toastr.error('পণ্যটির বিবরণ লোড করা যায়নি');
                            }
                        }
                    });
                } else {
                    $("#loading").hide();
                }
            });

            $(document).on("click", "#page-overlay", function () {
                $("#custom-modal").hide();
                $("#page-overlay").hide();
            });

            $(document).on("keyup", function (e) {
                if (e.key === "Escape") {
                    $("#custom-modal").hide();
                    $("#page-overlay").hide();
                }
            });
        </script>
        <!-- quick view end -->
        <!-- cart js start -->
        <script>
            function openSideCart() {
                $('#side-cart-overlay').addClass('active');
                $('#side-cart').addClass('active');
                $('body').css('overflow', 'hidden');
            }

            function notifyOrOpenSideCart() {
                var isMobile = (window.innerWidth || $(window).width()) <= 767;
                if (!isMobile) {
                    openSideCart();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.success('পণ্যটি সফলভাবে কার্টে যোগ হয়েছে');
                    }
                }
            }

            function closeSideCart() {
                $('#side-cart-overlay').removeClass('active');
                $('#side-cart').removeClass('active');
                $('body').css('overflow', '');
            }

            function refreshSideCart(callback) {
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart.side_cart_content') }}",
                    dataType: "json",
                    headers: { 'X-Side-Cart': '1' },
                    success: function (res) {
                        if (res && res.status === 'success') {
                            $('#side-cart-body').html(res.side_cart_html);
                            updateAllCartCounters(res.cart_count);
                            $('#side-cart-subtotal').text('৳' + res.subtotal);
                            if (parseInt(res.cart_count) > 0) {
                                $('#side-cart-footer').show();
                            } else {
                                $('#side-cart-footer').hide();
                            }
                        }
                        if (typeof callback === 'function') {
                            callback(res);
                        }
                    }
                });
            }

            // Global trigger for cart icons in header & mobile bar
            $(document).on('click', '.cart-icon, #cart-icon', function (e) {
                e.preventDefault();
                refreshSideCart(function() {
                    openSideCart();
                });
            });

            // Close Side Cart triggers
            $(document).on('click', '#close-side-cart, #side-cart-overlay, .side-cart-close-trigger', function (e) {
                e.preventDefault();
                closeSideCart();
            });

            // Close on Escape key
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $('#side-cart').hasClass('active')) {
                    closeSideCart();
                }
            });

            // Side Cart item increment
            $(document).on('click', '.side-cart-increment', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart.increment') }}",
                    data: { id: id, source: 'side_cart' },
                    headers: { 'X-Side-Cart': '1' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            $('#side-cart-body').html(res.side_cart_html);
                            updateAllCartCounters(res.cart_count);
                            $('#side-cart-subtotal').text('৳' + res.subtotal);
                            if (parseInt(res.cart_count) > 0) {
                                $('#side-cart-footer').show();
                            }
                        }
                    }
                });
            });

            // Side Cart item decrement
            $(document).on('click', '.side-cart-decrement', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart.decrement') }}",
                    data: { id: id, source: 'side_cart' },
                    headers: { 'X-Side-Cart': '1' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            $('#side-cart-body').html(res.side_cart_html);
                            updateAllCartCounters(res.cart_count);
                            $('#side-cart-subtotal').text('৳' + res.subtotal);
                            if (parseInt(res.cart_count) > 0) {
                                $('#side-cart-footer').show();
                            } else {
                                $('#side-cart-footer').hide();
                            }
                        }
                    }
                });
            });

            // Side Cart item remove
            $(document).on('click', '.side-cart-remove', function (e) {
                e.preventDefault();
                var id = $(this).data('id');
                if (!id) return;
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart.remove') }}",
                    data: { id: id, source: 'side_cart' },
                    headers: { 'X-Side-Cart': '1' },
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.status === 'success') {
                            $('#side-cart-body').html(res.side_cart_html);
                            updateAllCartCounters(res.cart_count);
                            $('#side-cart-subtotal').text('৳' + res.subtotal);
                            if (parseInt(res.cart_count) === 0) {
                                $('#side-cart-footer').hide();
                            }
                        }
                    }
                });
            });

            // Add to cart from Category / Quickview buttons
            $(".addcartbutton").on("click", function () {
                var id = $(this).data("id");
                var size = $('input[name="product_size' + id + '"]:checked').val();
                var color = $('input[name="product_color' + id + '"]:checked').val();
                
                if (!color) { color = ''; }
                if (!size) { size = ''; }
                var qty = 1;
                if (id) {
                    $.ajax({
                        cache: false,
                        type: "GET",
                        url: "{{url('add-to-cart')}}/" + id + "/" + qty + "?color=" + color + "&size=" + size,
                        dataType: "json",
                        success: function (data) {
                            if (data) {
                                $('#Modal' + id).modal('hide');
                                if (data.side_cart_html) {
                                    $('#side-cart-body').html(data.side_cart_html);
                                    $('#side-cart-badge').text(data.cart_count);
                                    $('.mobilecart-qty').text(data.cart_count);
                                    $('.cart-qty-text').text(data.cart_count);
                                    $('#side-cart-subtotal').text('৳' + data.subtotal);
                                    if (parseInt(data.cart_count) > 0) {
                                        $('#side-cart-footer').show();
                                    }
                                    notifyOrOpenSideCart();
                                }
                                updateAllCartCounters(data.cart_count);
                            }
                        },
                    });
                }
            });

            $(".cart_store").on("click", function () {
                var id = $(this).data("id");
                var qty = $(this).parent().find("input").val();
                if (id) {
                    $.ajax({
                        type: "POST",
                        data: { id: id, qty: qty ? qty : 1, _token: '{{ csrf_token() }}' },
                        url: "{{route('cart.store')}}",
                        dataType: 'json',
                        success: function (data) {
                            if (data) {
                                window.dataLayer = window.dataLayer || [];
                                window.dataLayer.push({ ecommerce: null });
                                window.dataLayer.push({
                                    event: 'add_to_cart',
                                    event_id: data.event_id || undefined,
                                    ecommerce: {
                                        currency: 'BDT',
                                        value: data.item ? data.item.value : 0,
                                        items: [{
                                            item_id: data.item ? String(data.item.id) : String(id),
                                            item_name: data.item ? data.item.name : 'Product',
                                            price: data.item ? data.item.price : 0,
                                            quantity: data.item ? data.item.qty : (qty ? parseInt(qty) : 1)
                                        }]
                                    }
                                });
                                if (data.side_cart_html) {
                                    $('#side-cart-body').html(data.side_cart_html);
                                    $('#side-cart-badge').text(data.cart_count);
                                    $('.mobilecart-qty').text(data.cart_count);
                                    $('.cart-qty-text').text(data.cart_count);
                                    $('#side-cart-subtotal').text('৳' + data.subtotal);
                                    if (parseInt(data.cart_count) > 0) {
                                        $('#side-cart-footer').show();
                                    }
                                    notifyOrOpenSideCart();
                                }
                                updateAllCartCounters(data.cart_count);
                            }
                        },
                    });
                }
            });

            $(".cart_remove").on("click", function () {
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.remove')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                updateAllCartCounters();
                                return cart_summary();
                            }
                        },
                    });
                }
            });

            $(".cart_increment").on("click", function () {
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.increment')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                updateAllCartCounters();
                            }
                        },
                    });
                }
            });

            $(".cart_decrement").on("click", function () {
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.decrement')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                updateAllCartCounters();
                            }
                        },
                    });
                }
            });

            function updateAllCartCounters(count) {
                if (typeof count !== 'undefined' && count !== null) {
                    $('.mobilecart-qty, .cart-qty-text, #side-cart-badge').text(count);
                    return;
                }
                $.ajax({
                    type: "GET",
                    url: "{{route('mobile.cart.count')}}",
                    success: function (data) {
                        var qty = data ? data.toString().trim() : '0';
                        $('.mobilecart-qty, .cart-qty-text, #side-cart-badge').text(qty);
                    },
                });
            }

            function cart_count(count) {
                updateAllCartCounters(count);
            }

            function mobile_cart(count) {
                updateAllCartCounters(count);
            }

            function cart_summary() {
                $.ajax({
                    type: "GET",
                    url: "{{route('shipping.charge')}}",
                    dataType: "html",
                    success: function (response) {
                        $(".cart-summary").html(response);
                    },
                });
            }
        </script>
        <!-- cart js end -->
        <!-- search js start -->
        <script>
            var liveSearchTimer = null;
            var liveSearchXhr = null;

            function executeLiveSearch($input) {
                var keyword = ($input.val() || '').trim();
                var $container = $input.closest('.mobile-search, .main-search');
                var $targetResult = $container.length ? $container.find('.search_result') : $input.siblings('.search_result');
                if (!$targetResult.length) {
                    $targetResult = $('.search_result');
                }

                if (liveSearchXhr && liveSearchXhr.readyState !== 4) {
                    liveSearchXhr.abort();
                }

                if (keyword.length < 1) {
                    $targetResult.empty();
                    return;
                }

                liveSearchXhr = $.ajax({
                    type: "GET",
                    data: { keyword: keyword },
                    url: "{{route('livesearch')}}",
                    success: function (products) {
                        if (products && products.trim().length > 0) {
                            $targetResult.html(products);
                        } else {
                            $targetResult.empty();
                        }
                    },
                    error: function (xhr, status) {
                        if (status !== 'abort') {
                            $targetResult.empty();
                        }
                    }
                });
            }

            $(document).on("keyup input", ".search_click, .msearch_click", function () {
                var $this = $(this);
                clearTimeout(liveSearchTimer);
                liveSearchTimer = setTimeout(function () {
                    executeLiveSearch($this);
                }, 300);
            });

            $(document).on("click", function (e) {
                if (!$(e.target).closest('.main-search, .mobile-search, .search_result, .search_click, .msearch_click').length) {
                    $(".search_result").empty();
                }
            });

            $(document).on("keydown", function (e) {
                if (e.key === "Escape") {
                    $(".search_result").empty();
                }
            });
        </script>
        <!-- search js end -->
        <script>
            $(".district").on("change", function () {
                var id = $(this).val();
                $.ajax({
                    type: "GET",
                    data: { id: id },
                    url: "{{route('districts')}}",
                    success: function (res) {
                        if (res) {
                            $(".area").empty();
                            $(".area").append('<option value="">Select..</option>');
                            $.each(res, function (key, value) {
                                $(".area").append('<option value="' + key + '" >' + value + "</option>");
                            });
                        } else {
                            $(".area").empty();
                        }
                    },
                });
            });
        </script>
        <script>
            $(".toggle").on("click", function () {
                $("#page-overlay").show();
                $(".mobile-menu").addClass("active");
            });

            $("#page-overlay").on("click", function () {
                $("#page-overlay").hide();
                $(".mobile-menu").removeClass("active");
                $(".feature-products").removeClass("active");
            });

            $(".mobile-menu-close").on("click", function () {
                $("#page-overlay").hide();
                $(".mobile-menu").removeClass("active");
            });

            $(".mobile-filter-toggle").on("click", function () {
                $("#page-overlay").show();
                $(".feature-products").addClass("active");
            });
        </script>
        <script>
            $(document).ready(function () {
                $(".parent-category").each(function () {
                    const menuCatToggle = $(this).find(".menu-category-toggle");
                    const secondNav = $(this).find(".second-nav");

                    menuCatToggle.on("click", function () {
                        menuCatToggle.toggleClass("active");
                        secondNav.slideToggle("fast");
                        $(this).closest(".parent-category").toggleClass("active");
                    });
                });
                $(".parent-subcategory").each(function () {
                    const menuSubcatToggle = $(this).find(".menu-subcategory-toggle");
                    const thirdNav = $(this).find(".third-nav");

                    menuSubcatToggle.on("click", function () {
                        menuSubcatToggle.toggleClass("active");
                        thirdNav.slideToggle("fast");
                        $(this).closest(".parent-subcategory").toggleClass("active");
                    });
                });
            });
        </script>



        <script>
            // document.addEventListener("DOMContentLoaded", function () {
            //     window.addEventListener("scroll", function () {
            //         if (window.scrollY > 200) {
            //             document.getElementById("navbar_top").classList.add("fixed-top");
            //         } else {
            //             document.getElementById("navbar_top").classList.remove("fixed-top");
            //             document.body.style.paddingTop = "0";
            //         }
            //     });
            // });
            /*=== Main Menu Fixed === */
            // document.addEventListener("DOMContentLoaded", function () {
            //     window.addEventListener("scroll", function () {
            //         if (window.scrollY > 0) {
            //             document.getElementById("m_navbar_top").classList.add("fixed-top");
            //             // add padding top to show content behind navbar
            //             navbar_height = document.querySelector(".navbar").offsetHeight;
            //             document.body.style.paddingTop = navbar_height + "px";
            //         } else {
            //             document.getElementById("m_navbar_top").classList.remove("fixed-top");
            //             // remove padding top from body
            //             document.body.style.paddingTop = "0";
            //         }
            //     });
            // });
            /*=== Main Menu Fixed === */

            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) {
                    $(".scrolltop:hidden").stop(true, true).fadeIn();
                } else {
                    $(".scrolltop").stop(true, true).fadeOut();
                }
            });
            $(function () {
                $(".scroll").click(function () {
                    $("html,body").animate({ scrollTop: $(".gotop").offset().top }, "1000");
                    return false;
                });
            });
        </script>
        <script>
            $(document).on("submit", "#footerNewsletterForm", function(e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $("#newsletterSubmitBtn");
                var $input = $("#newsletterEmailInput");
                var $msg = $("#newsletterMessageAlert");
                var emailVal = $.trim($input.val());

                if (!emailVal) {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('দয়া করে আপনার ইমেইল ঠিকানা দিন।');
                    }
                    return;
                }

                $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Subscribing...');
                $msg.hide().removeClass('text-success text-danger text-warning');

                $.ajax({
                    type: "POST",
                    url: $form.attr("action"),
                    data: $form.serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $btn.prop("disabled", false).html('<i class="fa-solid fa-paper-plane me-1"></i> Subscribe');
                        if (response.success) {
                            $input.val('');
                            $msg.addClass('text-success').text(response.message).fadeIn();
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message);
                            }
                        } else {
                            $msg.addClass('text-warning').text(response.message).fadeIn();
                            if (typeof toastr !== 'undefined') {
                                toastr.warning(response.message);
                            }
                        }
                    },
                    error: function(xhr) {
                        $btn.prop("disabled", false).html('<i class="fa-solid fa-paper-plane me-1"></i> Subscribe');
                        var errMsg = 'কিছু সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        $msg.addClass('text-danger').text(errMsg).fadeIn();
                        if (typeof toastr !== 'undefined') {
                            toastr.error(errMsg);
                        }
                    }
                });
            });
        </script>
        <script>
            $(".filter_btn").click(function(){
               $(".filter_sidebar").addClass('active');
               $("body").css("overflow-y", "hidden");
            })
            $(".filter_close").click(function(){
               $(".filter_sidebar").removeClass('active');
               $("body").css("overflow-y", "auto");
            })
        </script>
        @if(isset($gtm_code) && $gtm_code->isNotEmpty())
        <!-- Google Tag Manager (noscript) -->
        @foreach($gtm_code as $gtm)
        @php
            $gtmContainerId = Str::startsWith(strtoupper(trim($gtm->code)), 'GTM-') ? strtoupper(trim($gtm->code)) : 'GTM-' . strtoupper(trim($gtm->code));
            $gtmBaseUrl = method_exists($gtm, 'getEffectiveScriptBaseUrl') ? $gtm->getEffectiveScriptBaseUrl() : 'https://www.googletagmanager.com';
        @endphp
        <noscript><iframe src="{{ $gtmBaseUrl }}/ns.html?id={{ $gtmContainerId }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        @endforeach
        <!-- End Google Tag Manager (noscript) -->
        @endif

        {{-- Injected Dynamic Footer Snippets (WPCode Engine) --}}
        @if(!empty($injectedFooterSnippets))
            @foreach($injectedFooterSnippets as $footerSnippet)
                {!! $footerSnippet !!}
            @endforeach
        @endif
    </body>
</html>
