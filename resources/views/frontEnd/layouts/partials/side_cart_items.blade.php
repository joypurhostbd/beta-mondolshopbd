@php
    $items = $items ?? Cart::instance('shopping')->content();
    $subtotal = $subtotal ?? Cart::instance('shopping')->subtotal();
    $subtotal = str_replace(',', '', $subtotal);
    $subtotal = str_replace('.00', '', $subtotal);
    $itemCount = Cart::instance('shopping')->count();
@endphp

@if($items->count() > 0)
    <div class="side-cart-items-wrapper">
        <ul class="side-cart-items-list list-unstyled mb-0">
            @foreach($items as $item)
                <li class="side-cart-item" id="side-cart-item-{{ $item->rowId }}">
                    <div class="d-flex align-items-start gap-2 gap-sm-3">
                        <!-- Product Thumbnail -->
                        <a href="{{ route('product', $item->options->slug ?? '#') }}" class="side-cart-item-thumb flex-shrink-0">
                            <img src="{{ asset($item->options->image ?? 'frontEnd/images/no-image.png') }}" 
                                 alt="{{ $item->name }}" 
                                 onerror="this.src='{{ asset('frontEnd/images/no-image.png') }}';" />
                        </a>
                        
                        <!-- Item Details Column -->
                        <div class="side-cart-item-details flex-grow-1" style="min-width: 0;">
                            <!-- Title & Delete Button Row -->
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <a href="{{ route('product', $item->options->slug ?? '#') }}" class="side-cart-item-name font-weight-bold text-dark text-decoration-none flex-grow-1" style="min-width: 0;">
                                    {{ $item->name }}
                                </a>
                                <button type="button" class="side-cart-remove-btn side-cart-remove flex-shrink-0" data-id="{{ $item->rowId }}" title="কার্ট থেকে মুছুন" aria-label="Remove item">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Variant Tags -->
                            @if(!empty($item->options->product_size) || !empty($item->options->product_color) || !empty($item->options->pro_unit))
                                <div class="side-cart-item-variants d-flex flex-wrap gap-1 mt-1">
                                    @if(!empty($item->options->product_size))
                                        <span class="badge bg-light text-secondary border font-11">সাইজ: {{ $item->options->product_size }}</span>
                                    @endif
                                    @if(!empty($item->options->product_color))
                                        <span class="badge bg-light text-secondary border font-11">কালার: {{ $item->options->product_color }}</span>
                                    @endif
                                    @if(!empty($item->options->pro_unit))
                                        <span class="badge bg-light text-secondary border font-11">{{ $item->options->pro_unit }}</span>
                                    @endif
                                </div>
                            @endif

                            <!-- Stepper & Price Row -->
                            <div class="side-cart-item-bottom d-flex align-items-center justify-content-between mt-2 pt-1">
                                <div class="side-cart-stepper d-inline-flex align-items-center border rounded">
                                    <button type="button" class="btn btn-sm side-cart-stepper-btn side-cart-decrement" data-id="{{ $item->rowId }}" aria-label="Decrease quantity">−</button>
                                    <span class="side-cart-qty-value px-2 font-weight-bold font-13">{{ $item->qty }}</span>
                                    <button type="button" class="btn btn-sm side-cart-stepper-btn side-cart-increment" data-id="{{ $item->rowId }}" aria-label="Increase quantity">+</button>
                                </div>

                                <div class="side-cart-item-price-wrap text-end flex-shrink-0">
                                    <span class="side-cart-item-price font-weight-bold">৳{{ number_format($item->price * $item->qty, 0) }}</span>
                                    @if($item->qty > 1)
                                        <small class="text-muted d-block font-11" style="line-height: 1;">(৳{{ number_format($item->price, 0) }} × {{ $item->qty }})</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@else
    <div class="side-cart-empty text-center py-5 px-3">
        <div class="side-cart-empty-icon mb-3">
            <svg width="68" height="68" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </div>
        <h5 class="side-cart-empty-title font-weight-bold text-dark mb-1">আপনার কার্ট বর্তমানে খালি</h5>
        <p class="side-cart-empty-desc text-muted font-13 mb-4">পছন্দের পণ্য কার্টে যোগ করে কেনাকাটা শুরু করুন।</p>
        <button type="button" class="btn btn-outline-primary side-cart-close-trigger px-4 py-2 rounded-pill font-weight-bold font-13">
            কেনাকাটা শুরু করুন
        </button>
    </div>
@endif