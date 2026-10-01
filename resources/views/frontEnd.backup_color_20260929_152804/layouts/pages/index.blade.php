@extends('frontEnd.layouts.master')
@section('title', 'Home')
@push('seo')
<meta name="app-url" content="{{ route('home') }}" />
<meta name="robots" content="index, follow" />
<meta name="description" content="{{ $generalsetting->name }} - আপনার বিশ্বস্ত অনলাইন শপিং প্ল্যাটফর্ম। সেরা মানের পোশাক ও পণ্য সুলভ মূল্যে কিনুন সারাদেশে দ্রুত হোম ডেলিভারিতে।" />
<meta name="keywords" content="{{ $generalsetting->name }}, online shopping, ecommerce bangladesh, drop shoulder, t-shirt, sweatshirt, winter collection" />

<!-- Open Graph data -->
<meta property="og:title" content="{{ $generalsetting->name }} | সেরা অনলাইন শপিং" />
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ route('home') }}" />
<meta property="og:image" content="{{ asset($generalsetting->white_logo) }}" />
<meta property="og:description" content="{{ $generalsetting->name }} - আপনার বিশ্বস্ত অনলাইন শপিং প্ল্যাটফর্ম। সেরা মানের পোশাক ও পণ্য সুলভ মূল্যে কিনুন সারাদেশে ক্যাশ অন ডেলিভারিতে।" />

<!-- Twitter Card data -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $generalsetting->name }} | সেরা অনলাইন শপিং" />
<meta name="twitter:description" content="{{ $generalsetting->name }} - আপনার বিশ্বস্ত অনলাইন শপিং প্ল্যাটফর্ম।" />
<meta name="twitter:image" content="{{ asset($generalsetting->white_logo) }}" />

<!-- Schema.org JSON-LD Structured Data for Google Rich Snippets -->
<script type="application/ld+json">
{!! json_encode([
  chr(64) . 'context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'WebSite',
      '@id' => url('/') . '/#website',
      'url' => url('/'),
      'name' => $generalsetting->name,
      'description' => $generalsetting->name . ' - আপনার বিশ্বস্ত অনলাইন শপিং প্ল্যাটফর্ম',
      'potentialAction' => [
        [
          '@type' => 'SearchAction',
          'target' => [
            '@type' => 'EntryPoint',
            'urlTemplate' => route('search') . '?keyword={search_term_string}',
          ],
          'query-input' => 'required name=search_term_string',
        ],
      ],
      'inLanguage' => 'bn-BD',
    ],
    array_filter([
      '@type' => 'Organization',
      '@id' => url('/') . '/#organization',
      'name' => $generalsetting->name,
      'url' => url('/'),
      'logo' => [
        '@type' => 'ImageObject',
        'url' => asset($generalsetting->white_logo),
      ],
      'contactPoint' => (!empty($contact->phone)) ? [
        [
          '@type' => 'ContactPoint',
          'telephone' => $contact->phone,
          'contactType' => 'customer service',
          'areaServed' => 'BD',
          'availableLanguage' => ['Bengali', 'English'],
        ],
      ] : null,
      'sameAs' => (!empty($socialicons) && $socialicons->isNotEmpty())
        ? $socialicons->pluck('link')->filter()->values()->toArray()
        : null,
    ]),
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush
@section('content')
<h1 class="visually-hidden">{{ $generalsetting->name }} - আপনার বিশ্বস্ত অনলাইন ফ্যাশন ও শপিং প্ল্যাটফর্ম</h1>
<section class="slider-section">
    <div class="container">
        <div class="row">
            {{-- 
            <div class="col-sm-3 hidetosm">
                <div class="sidebar-menu">
                    <ul class="hideshow">
                        @foreach ($menucategories as $key => $category)
                            <li>
                                <a href="{{ route('category', $category->slug) }}">
                                    <img src="{{ asset($category->image) }}" alt="" />
                                    {{ $category->name }}
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                                <ul class="sidebar-submenu">
                                    @foreach ($category->subcategories as $key => $subcategory)
                                        <li>
                                            <a href="{{ route('subcategory', $subcategory->slug) }}">
                                                {{ $subcategory->subcategoryName }} <i
                                                    class="fa-solid fa-chevron-right"></i> </a>
                                            <ul class="sidebar-childmenu">
                                                @foreach ($subcategory->childcategories as $key => $childcat)
                                                    <li>
                                                        <a href="{{ route('products', $childcat->slug) }}">
                                                            {{ $childcat->childcategoryName }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            --}}
            <div class="col-sm-12">
                <div class="home-slider-container">
                    <div class="main_slider owl-carousel">
                        @foreach ($sliders as $key => $value)
                            <div class="slider-item">
                                @if(!empty($value->link))
                                    <a href="{{ $value->link }}">
                                        <img src="{{ asset($value->image) }}" alt="Banner {{ $key + 1 }}" />
                                    </a>
                                @else
                                    <img src="{{ asset($value->image) }}" alt="Banner {{ $key + 1 }}" />
                                @endif
                            </div>
                            <!-- slider item -->
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- slider end -->

<!-- Trust & Value Proposition Badges (CRO) -->
<section class="trust-features-section">
    <div class="container">
        <div class="trust-features-grid">
            <div class="trust-feature-item">
                <div class="trust-feature-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div class="trust-feature-content">
                    <h3 class="trust-feature-title">ক্যাশ অন ডেলিভারি</h3>
                    <p class="trust-feature-desc">পণ্য হাতে পেয়ে মূল্য পরিশোধ</p>
                </div>
            </div>
            <div class="trust-feature-item">
                <div class="trust-feature-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div class="trust-feature-content">
                    <h3 class="trust-feature-title">দ্রুততম ডেলিভারি</h3>
                    <p class="trust-feature-desc">সারাদেশে নিরাপদ হোম ডেলিভারি</p>
                </div>
            </div>
            <div class="trust-feature-item">
                <div class="trust-feature-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="trust-feature-content">
                    <h3 class="trust-feature-title">সহজ রিটার্ন সুবিধা</h3>
                    <p class="trust-feature-desc">পছন্দ না হলে দ্রুত এক্সচেঞ্জ</p>
                </div>
            </div>
            <div class="trust-feature-item">
                <div class="trust-feature-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div class="trust-feature-content">
                    <h3 class="trust-feature-title">গ্রাহক সেবা</h3>
                    <p class="trust-feature-desc">যেকোনো সহযোগিতায় পাশে আছি</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($sliderbottomads) && $sliderbottomads->isNotEmpty())
<section class="slider-bottom-ads-section py-2 py-md-3">
    <div class="container">
        <div class="row g-2 g-md-3">
            @foreach ($sliderbottomads as $key => $ad)
                <div class="col-12 col-md-{{ (int)(12 / min(count($sliderbottomads), 3)) }}">
                    <div class="banner-ad-card overflow-hidden rounded shadow-sm">
                        @if(!empty($ad->link) && $ad->link !== '#')
                            <a href="{{ $ad->link }}">
                                <img src="{{ asset($ad->image) }}" class="img-fluid w-100 rounded" style="object-fit: cover; max-height: 220px;" alt="Banner Ad {{ $key + 1 }}" loading="lazy" />
                            </a>
                        @else
                            <img src="{{ asset($ad->image) }}" class="img-fluid w-100 rounded" style="object-fit: cover; max-height: 220px;" alt="Banner Ad {{ $key + 1 }}" loading="lazy" />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="homeproduct">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="sec_title">
                    <div class="section-title-header">
                        <div class="timer_inner">
                            <div>
                                <h2 class="section-title-name"> Top Categories </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12">
                <div class="topcategory">
                    @foreach ($menucategories as $key => $value)
                        <div class="cat_item">
                            <div class="cat_img">
                                <a href="{{ route('category', $value->slug) }}">
                                    <img src="{{ asset($value->image) }}" alt="{{ $value->name }}" loading="lazy" />
                                </a>
                            </div>
                            <div class="cat_name">
                                <a href="{{ route('category', $value->slug) }}">
                                    {{ $value->name }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>

<section class="homeproduct">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="sec_title">
                    <div class="section-title-header">
                        <div class="timer_inner">
                            <div>
                                <h2 class="section-title-name"> Hot Deal </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="product_slider owl-carousel">
                    @foreach ($hotdeal_top as $key => $value)
                        @include('frontEnd.layouts.partials.product_card', ['value' => $value])
                    @endforeach
                </div>
            </div>
            <div class="col-sm-12">
               <div class="show_more_btn" style="text-align: center; margin-top: 15px;">
                   <a href="{{ route('hotdeals') }}" class="view_more_btn">View More</a>
               </div> 
            </div>
        </div>
    </div>
</section>

@foreach ($homeproducts as $homecat)
    <section class="homeproduct">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="sec_title">
                        <div class="section-title-header">
                            <h2 class="section-title-name">{{ $homecat->name }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="product_sliders">
                        @foreach ($homecat->products as $key => $value)
                            @include('frontEnd.layouts.partials.product_card', ['value' => $value])
                        @endforeach
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="show_more_btn">
                        <a href="{{ route('category', $homecat->slug) }}" class="view_more_btn">View More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endforeach

@if(isset($footertopads) && $footertopads->isNotEmpty())
<section class="footer-top-ads-section py-2 py-md-3">
    <div class="container">
        <div class="row g-2 g-md-3">
            @foreach ($footertopads as $key => $ad)
                <div class="col-12 col-md-{{ (int)(12 / min(count($footertopads), 2)) }}">
                    <div class="banner-ad-card overflow-hidden rounded shadow-sm">
                        @if(!empty($ad->link) && $ad->link !== '#')
                            <a href="{{ $ad->link }}">
                                <img src="{{ asset($ad->image) }}" class="img-fluid w-100 rounded" style="object-fit: cover; max-height: 240px;" alt="Footer Ad {{ $key + 1 }}" loading="lazy" />
                            </a>
                        @else
                            <img src="{{ asset($ad->image) }}" class="img-fluid w-100 rounded" style="object-fit: cover; max-height: 240px;" alt="Footer Ad {{ $key + 1 }}" loading="lazy" />
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@endsection

@push('script')
<script>
    $(document).ready(function() {
        $(".main_slider").owlCarousel({
            items: 1,
            loop: true,
            dots: true,
            autoplay: true,
            nav: true,
            autoplayHoverPause: true,
            margin: 0,
            mouseDrag: true,
            smartSpeed: 800,
            autoplayTimeout: 5000,
            navText: [
                "<i class='fa-solid fa-angle-left'></i>",
                "<i class='fa-solid fa-angle-right'></i>"
            ],
        });

        $(".product_slider").owlCarousel({
            margin: 15,
            items: 6,
            loop: true,
            dots: false,
            autoplay: true,
            autoplayTimeout: 6000,
            autoplayHoverPause: true,
            responsiveClass: true,
            responsive: {
                0: {
                    items: 2,
                    nav: false,
                },
                600: {
                    items: 4,
                    nav: false,
                },
                1000: {
                    items: 6,
                    nav: false,
                },
            },
        });
    });
</script>
@endpush
