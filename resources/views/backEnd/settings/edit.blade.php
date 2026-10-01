@extends('backEnd.layouts.master')
@section('title', 'General Setting Update')

@section('css')
<link href="{{asset('backEnd/assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('settings.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage Settings</a>
                </div>
                <h4 class="page-title">General Setting Update</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{route('settings.update')}}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="hidden_id" value="{{$edit_data->id}}">
                        <input type="hidden" name="id" value="{{$edit_data->id}}">

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Company / Website Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $edit_data->name) }}" id="name" required="">
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="copyright" class="form-label">Copyright Notice Text</label>
                                <input type="text" class="form-control @error('copyright') is-invalid @enderror" name="copyright" value="{{ old('copyright', $edit_data->copyright) }}" id="copyright" placeholder="e.g. © 2026 Mondol Shop BD. All Rights Reserved.">
                                @error('copyright')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <label for="white_logo" class="form-label">White / Light Logo <small class="text-muted">(For Dark Headers)</small></label>
                                <input type="file" class="form-control @error('white_logo') is-invalid @enderror" name="white_logo" id="white_logo" accept="image/*">
                                @if(!empty($edit_data->white_logo) && file_exists(public_path($edit_data->white_logo)))
                                    <div class="mt-2 p-2 bg-dark rounded d-inline-block">
                                        <img src="{{asset($edit_data->white_logo)}}" style="max-height: 45px; max-width: 130px; object-fit: contain;" alt="White Logo">
                                    </div>
                                @endif
                                @error('white_logo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <label for="dark_logo" class="form-label">Dark Logo <small class="text-muted">(For Light Headers)</small></label>
                                <input type="file" class="form-control @error('dark_logo') is-invalid @enderror" name="dark_logo" id="dark_logo" accept="image/*">
                                @if(!empty($edit_data->dark_logo) && file_exists(public_path($edit_data->dark_logo)))
                                    <div class="mt-2 p-2 bg-light rounded d-inline-block border">
                                        <img src="{{asset($edit_data->dark_logo)}}" style="max-height: 45px; max-width: 130px; object-fit: contain;" alt="Dark Logo">
                                    </div>
                                @endif
                                @error('dark_logo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-4 mb-3">
                            <div class="form-group">
                                <label for="favicon" class="form-label">Favicon Icon <small class="text-muted">(32x32 px)</small></label>
                                <input type="file" class="form-control @error('favicon') is-invalid @enderror" name="favicon" id="favicon" accept="image/*">
                                @if(!empty($edit_data->favicon) && file_exists(public_path($edit_data->favicon)))
                                    <div class="mt-2">
                                        <img src="{{asset($edit_data->favicon)}}" style="width: 32px; height: 32px; object-fit: contain;" class="rounded border p-1" alt="Favicon">
                                    </div>
                                @endif
                                @error('favicon')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block mb-1">Status</label>
                                <label class="switch">
                                    <input type="checkbox" value="1" name="status" @if($edit_data->status == 1) checked @endif>
                                    <span class="slider round"></span>
                                </label>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <!-- WhatsApp Floating Button Settings Section -->
                        <div class="col-12 mt-2 mb-3">
                            <div class="card border border-success bg-soft-success">
                                <div class="card-body p-3">
                                    <h5 class="card-title text-success mb-2">
                                        <i class="mdi mdi-whatsapp me-1"></i> WhatsApp Floating Button Settings
                                    </h5>
                                    <p class="text-muted font-12 mb-3">
                                        Configure the responsive floating WhatsApp chat button displayed on the left side of your storefront.
                                    </p>
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <label for="whatsapp_status" class="d-block mb-1 font-weight-bold">WhatsApp Button</label>
                                            <label class="switch">
                                                <input type="checkbox" value="1" name="whatsapp_status" id="whatsapp_status" @if(($edit_data->whatsapp_status ?? 1) == 1) checked @endif>
                                                <span class="slider round"></span>
                                            </label>
                                            <small class="text-muted d-block font-11">Show/Hide floating button on website.</small>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-group">
                                                <label for="whatsapp_number" class="form-label font-weight-bold">WhatsApp Number</label>
                                                <input type="text" class="form-control" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number', $edit_data->whatsapp_number) }}" placeholder="e.g. 017XXXXXXXX">
                                                <small class="text-muted font-11">Receiver's phone number.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-group">
                                                <label for="whatsapp_title" class="form-label font-weight-bold">Button Label / Hover Title</label>
                                                <input type="text" class="form-control" name="whatsapp_title" id="whatsapp_title" value="{{ old('whatsapp_title', $edit_data->whatsapp_title ?? 'WhatsApp Support') }}" placeholder="e.g. Chat with us">
                                                <small class="text-muted font-11">Hover tooltip text.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mb-2">
                                            <div class="form-group">
                                                <label for="whatsapp_message" class="form-label font-weight-bold">Default Welcome Message</label>
                                                <input type="text" class="form-control" name="whatsapp_message" id="whatsapp_message" value="{{ old('whatsapp_message', $edit_data->whatsapp_message ?? 'Hello! I want to know more about your products.') }}" placeholder="e.g. Pre-filled chat message">
                                                <small class="text-muted font-11">Default message for general pages.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6 col-sm-6 mb-2">
                                            <div class="border rounded p-2 bg-white">
                                                <label for="whatsapp_dynamic_context" class="d-block mb-1 font-weight-bold text-dark">Smart Contextual Messages</label>
                                                <label class="switch">
                                                    <input type="checkbox" value="1" name="whatsapp_dynamic_context" id="whatsapp_dynamic_context" @if(($edit_data->whatsapp_dynamic_context ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted d-block font-11">Auto-include product details (name, price, SKU, size, color, URL) on product pages, and cart summary on cart/checkout pages.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 mb-2">
                                            <div class="border rounded p-2 bg-white">
                                                <label for="whatsapp_product_button" class="d-block mb-1 font-weight-bold text-dark">Product Page 'WhatsApp Order' Button</label>
                                                <label class="switch">
                                                    <input type="checkbox" value="1" name="whatsapp_product_button" id="whatsapp_product_button" @if(($edit_data->whatsapp_product_button ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted d-block font-11">Display a dedicated "WhatsApp এ অর্ডার করুন" button directly on single product details page.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- WhatsApp end -->

                        <!-- Product Card & Action Button Styling Section -->
                        <div class="col-12 mt-2 mb-3">
                            <div class="card border border-warning shadow-sm">
                                <div class="card-header bg-soft-warning py-2 d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-warning mb-0 font-16">
                                        <i class="fe-sliders me-1"></i> বাটন ও ব্যাজ স্টাইলিং ও কালার কাস্টমাইজেশন (Button & Badge Customization)
                                    </h5>
                                    <span class="badge bg-warning text-dark">Storefront UI Theme</span>
                                </div>
                                <div class="card-body p-3">
                                    <p class="text-muted font-12 mb-3">
                                        প্রোডাক্ট কার্ড, কুইক ভিউ এবং ডিটেইলস পেজের <strong>"অর্ডার করুন"</strong>, <strong>"কার্টে যোগ"</strong> বাটন এবং <strong>"ছাড়" ডিসকাউন্ট ব্যাজ</strong> এর ব্যাকগ্রাউন্ড কালার, টেক্সট কালার, হোভার কালার ও টেক্সট লেবেল সরাসরি এখান থেকে পরিবর্তন করতে পারবেন।
                                    </p>

                                    <div class="row">
                                        <!-- Configuration Columns -->
                                        <div class="col-lg-8 col-md-7">
                                            
                                            <!-- 1. অর্ডার করুন বাটন সেটিংস -->
                                            <div class="border rounded p-3 mb-3 bg-light">
                                                <h6 class="text-dark font-weight-bold mb-3">
                                                    <i class="fe-shopping-bag me-1 text-warning"></i> ১. "অর্ডার করুন" বাটন সেটিংস (Order Now Button)
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="order_btn_text" class="form-label font-weight-bold font-12">বাটন টেক্সট / লেবেল</label>
                                                            <input type="text" class="form-control form-control-sm" name="order_btn_text" id="order_btn_text" value="{{ old('order_btn_text', $edit_data->order_btn_text ?? 'অর্ডার করুন') }}" placeholder="অর্ডার করুন">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="order_btn_bg_color" class="form-label font-weight-bold font-12">ব্যাকগ্রাউন্ড কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="order_btn_bg_color_picker" value="{{ old('order_btn_bg_color', $edit_data->order_btn_bg_color ?? '#fe5200') }}">
                                                                <input type="text" class="form-control" name="order_btn_bg_color" id="order_btn_bg_color" value="{{ old('order_btn_bg_color', $edit_data->order_btn_bg_color ?? '#fe5200') }}" placeholder="#fe5200">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="order_btn_text_color" class="form-label font-weight-bold font-12">টেক্সট / ফন্ট কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="order_btn_text_color_picker" value="{{ old('order_btn_text_color', $edit_data->order_btn_text_color ?? '#ffffff') }}">
                                                                <input type="text" class="form-control" name="order_btn_text_color" id="order_btn_text_color" value="{{ old('order_btn_text_color', $edit_data->order_btn_text_color ?? '#ffffff') }}" placeholder="#ffffff">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="order_btn_hover_bg_color" class="form-label font-weight-bold font-12">হোভার ব্যাকগ্রাউন্ড কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="order_btn_hover_bg_color_picker" value="{{ old('order_btn_hover_bg_color', $edit_data->order_btn_hover_bg_color ?? '#e04800') }}">
                                                                <input type="text" class="form-control" name="order_btn_hover_bg_color" id="order_btn_hover_bg_color" value="{{ old('order_btn_hover_bg_color', $edit_data->order_btn_hover_bg_color ?? '#e04800') }}" placeholder="#e04800">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ২. কার্টে যোগ বাটন সেটিংস -->
                                            <div class="border rounded p-3 mb-3 bg-light">
                                                <h6 class="text-dark font-weight-bold mb-3">
                                                    <i class="fe-shopping-cart me-1 text-dark"></i> ২. "কার্টে যোগ" বাটন সেটিংস (Add to Cart Button)
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="cart_btn_text" class="form-label font-weight-bold font-12">বাটন টেক্সট / লেবেল</label>
                                                            <input type="text" class="form-control form-control-sm" name="cart_btn_text" id="cart_btn_text" value="{{ old('cart_btn_text', $edit_data->cart_btn_text ?? 'কার্টে যোগ') }}" placeholder="কার্টে যোগ">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="cart_btn_bg_color" class="form-label font-weight-bold font-12">ব্যাকগ্রাউন্ড কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="cart_btn_bg_color_picker" value="{{ old('cart_btn_bg_color', $edit_data->cart_btn_bg_color ?? '#2f3543') }}">
                                                                <input type="text" class="form-control" name="cart_btn_bg_color" id="cart_btn_bg_color" value="{{ old('cart_btn_bg_color', $edit_data->cart_btn_bg_color ?? '#2f3543') }}" placeholder="#2f3543">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="cart_btn_text_color" class="form-label font-weight-bold font-12">টেক্সট / ফন্ট কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="cart_btn_text_color_picker" value="{{ old('cart_btn_text_color', $edit_data->cart_btn_text_color ?? '#ffffff') }}">
                                                                <input type="text" class="form-control" name="cart_btn_text_color" id="cart_btn_text_color" value="{{ old('cart_btn_text_color', $edit_data->cart_btn_text_color ?? '#ffffff') }}" placeholder="#ffffff">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="cart_btn_hover_bg_color" class="form-label font-weight-bold font-12">হোভার ব্যাকগ্রাউন্ড কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="cart_btn_hover_bg_color_picker" value="{{ old('cart_btn_hover_bg_color', $edit_data->cart_btn_hover_bg_color ?? '#1e222b') }}">
                                                                <input type="text" class="form-control" name="cart_btn_hover_bg_color" id="cart_btn_hover_bg_color" value="{{ old('cart_btn_hover_bg_color', $edit_data->cart_btn_hover_bg_color ?? '#1e222b') }}" placeholder="#1e222b">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ৩. ডিসকাউন্ট ব্যাজ (ছাড়) সেটিংস -->
                                            <div class="border rounded p-3 mb-2 bg-light">
                                                <h6 class="text-dark font-weight-bold mb-3">
                                                    <i class="fe-tag me-1 text-danger"></i> ৩. ডিসকাউন্ট ব্যাজ (ছাড়) সেটিংস (Sale / Discount Badge)
                                                </h6>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="discount_badge_text" class="form-label font-weight-bold font-12">ব্যাজ টেক্সট / সাফিক্স</label>
                                                            <input type="text" class="form-control form-control-sm" name="discount_badge_text" id="discount_badge_text" value="{{ old('discount_badge_text', (!empty($edit_data->discount_badge_text) ? $edit_data->discount_badge_text : 'ছাড়')) }}" placeholder="ছাড়">
                                                            <small class="text-muted font-11">যেমন: 30% এর পর প্রদর্শিত টেক্সট (ডিফল্ট: 'ছাড়')</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="discount_badge_bg_color" class="form-label font-weight-bold font-12">ব্যাজ ব্যাকগ্রাউন্ড কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="discount_badge_bg_color_picker" value="{{ old('discount_badge_bg_color', (!empty($edit_data->discount_badge_bg_color) ? $edit_data->discount_badge_bg_color : '#ffffff')) }}">
                                                                <input type="text" class="form-control" name="discount_badge_bg_color" id="discount_badge_bg_color" value="{{ old('discount_badge_bg_color', (!empty($edit_data->discount_badge_bg_color) ? $edit_data->discount_badge_bg_color : '#ffffff')) }}" placeholder="#ffffff">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="discount_badge_text_color" class="form-label font-weight-bold font-12">ব্যাজ টেক্সট কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="discount_badge_text_color_picker" value="{{ old('discount_badge_text_color', (!empty($edit_data->discount_badge_text_color) ? $edit_data->discount_badge_text_color : '#fe5200')) }}">
                                                                <input type="text" class="form-control" name="discount_badge_text_color" id="discount_badge_text_color" value="{{ old('discount_badge_text_color', (!empty($edit_data->discount_badge_text_color) ? $edit_data->discount_badge_text_color : '#fe5200')) }}" placeholder="#fe5200">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-group">
                                                            <label for="discount_badge_border_color" class="form-label font-weight-bold font-12">ব্যাজ বর্ডার কালার</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="color" class="form-control form-control-color p-1" style="max-width: 42px; height: 31px;" id="discount_badge_border_color_picker" value="{{ old('discount_badge_border_color', (!empty($edit_data->discount_badge_border_color) ? $edit_data->discount_badge_border_color : '#fe5200')) }}">
                                                                <input type="text" class="form-control" name="discount_badge_border_color" id="discount_badge_border_color" value="{{ old('discount_badge_border_color', (!empty($edit_data->discount_badge_border_color) ? $edit_data->discount_badge_border_color : '#fe5200')) }}" placeholder="#fe5200">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <!-- Live Interactive Preview Column -->
                                        <div class="col-lg-4 col-md-5">
                                            <div class="card border border-secondary shadow-none bg-white sticky-top" style="top: 20px;">
                                                <div class="card-header bg-light py-2 text-center border-bottom">
                                                    <span class="font-weight-bold text-dark font-13">
                                                        <i class="fe-eye me-1 text-primary"></i> লাইভ ইন্টারেক্টিভ প্রিভিউ (Live Preview)
                                                    </span>
                                                </div>
                                                <div class="card-body p-3 d-flex flex-column align-items-center">
                                                    <small class="text-muted text-center mb-2 font-11">কালার বা টেক্সট পরিবর্তনের সাথে সাথে প্রিভিউ পরিবর্তিত হবে:</small>
                                                    <div style="width: 220px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; position: relative; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 12px;">
                                                        <!-- Preview Badge -->
                                                        <div style="position: absolute; top: 12px; right: 12px; z-index: 2;">
                                                            <div id="preview_discount_badge" style="background: {{ (!empty($edit_data->discount_badge_bg_color) ? $edit_data->discount_badge_bg_color : '#ffffff') }}; border: 1.5px solid {{ (!empty($edit_data->discount_badge_border_color) ? $edit_data->discount_badge_border_color : '#fe5200') }}; border-radius: 5px; padding: 2px 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); transition: all 0.2s;">
                                                                <span id="preview_discount_text" style="color: {{ (!empty($edit_data->discount_badge_text_color) ? $edit_data->discount_badge_text_color : '#fe5200') }}; font-weight: 700; font-size: 11px; display: inline-flex; gap: 2px;">
                                                                    <span>30%</span> <span id="preview_discount_suffix">{{ (!empty($edit_data->discount_badge_text) ? $edit_data->discount_badge_text : 'ছাড়') }}</span>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <!-- Preview Mock Image -->
                                                        <div style="width: 100%; height: 130px; background: #f8fafc; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; border: 1px dashed #cbd5e1;">
                                                            <i class="fe-image font-24 text-muted"></i>
                                                        </div>
                                                        <div style="font-size: 12.5px; font-weight: 600; color: #1e293b; line-height: 1.3; margin-bottom: 4px;">Premium Quality Trouser</div>
                                                        <div style="font-size: 13px; font-weight: 700; color: #fe5200; margin-bottom: 10px;">৳ 1290.00 <del style="color: #94a3b8; font-size: 11px; margin-left: 4px;">৳ 1843.00</del></div>
                                                        <!-- Preview Action Buttons -->
                                                        <div style="display: flex; flex-direction: column; gap: 6px;">
                                                            <button type="button" id="preview_order_btn" style="width: 100%; background-color: {{ $edit_data->order_btn_bg_color ?? '#fe5200' }}; color: {{ $edit_data->order_btn_text_color ?? '#ffffff' }}; border: 1px solid {{ $edit_data->order_btn_bg_color ?? '#fe5200' }}; border-radius: 5px; font-size: 12.5px; font-weight: 700; padding: 7px 0; text-align: center; cursor: pointer; transition: all 0.2s;">
                                                                <span id="preview_order_text">{{ $edit_data->order_btn_text ?? 'অর্ডার করুন' }}</span>
                                                            </button>
                                                            <button type="button" id="preview_cart_btn" style="width: 100%; background-color: {{ $edit_data->cart_btn_bg_color ?? '#2f3543' }}; color: {{ $edit_data->cart_btn_text_color ?? '#ffffff' }}; border: 1px solid {{ $edit_data->cart_btn_bg_color ?? '#2f3543' }}; border-radius: 5px; font-size: 12px; font-weight: 700; padding: 7px 0; text-align: center; cursor: pointer; transition: all 0.2s;">
                                                                <span id="preview_cart_text">{{ $edit_data->cart_btn_text ?? 'কার্টে যোগ' }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 text-center">
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" id="btn-reset-preview-defaults">
                                                            <i class="fe-rotate-ccw me-1"></i> ডিফল্ট কালারে রিসেট করুন
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- Product Card & Action Button Styling Section End -->

                        <!-- Footer Customization & Dynamic Control Section -->
                        <div class="col-12 mt-2 mb-3">
                            <div class="card border border-primary shadow-sm">
                                <div class="card-header bg-soft-primary py-2 d-flex align-items-center justify-content-between">
                                    <h5 class="card-title text-primary mb-0 font-16">
                                        <i class="fe-layout me-1"></i> Footer Customization & Dynamic Settings
                                    </h5>
                                    <span class="badge bg-primary">Storefront Footer</span>
                                </div>
                                <div class="card-body p-3">
                                    <p class="text-muted font-12 mb-3">
                                        Configure all sections of your storefront footer dynamically, including brand information, contact details, column titles, newsletter, mobile app download buttons, middle trust badges, and payment gateway methods.
                                    </p>

                                    <!-- Sub-section 1: Brand & Contact Info -->
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="text-dark font-weight-bold mb-3"><i class="fe-info me-1 text-primary"></i> 1. Brand Tagline & Contact Overrides (Column 1)</h6>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <div class="form-group">
                                                    <label for="footer_about" class="form-label font-weight-bold">Footer About / Tagline Text</label>
                                                    <textarea class="form-control" name="footer_about" id="footer_about" rows="2" placeholder="e.g. আপনার প্রয়োজনীয় পণ্য এখন আরও সহজে এবং নিরাপদে...">{{ old('footer_about', $edit_data->footer_about) }}</textarea>
                                                    <small class="text-muted font-11">Short description displayed below the white logo in column 1.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="footer_phone" class="form-label font-weight-bold">Footer Phone / Hotline</label>
                                                    <input type="text" class="form-control" name="footer_phone" id="footer_phone" value="{{ old('footer_phone', $edit_data->footer_phone) }}" placeholder="e.g. 01877786651">
                                                    <small class="text-muted font-11">Leave blank to use primary contact hotline.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="footer_email" class="form-label font-weight-bold">Footer Email</label>
                                                    <input type="email" class="form-control" name="footer_email" id="footer_email" value="{{ old('footer_email', $edit_data->footer_email) }}" placeholder="e.g. support@mondolshopbd.com">
                                                    <small class="text-muted font-11">Leave blank to use primary contact email.</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="footer_address" class="form-label font-weight-bold">Footer Address / Location</label>
                                                    <input type="text" class="form-control" name="footer_address" id="footer_address" value="{{ old('footer_address', $edit_data->footer_address) }}" placeholder="e.g. ঢাকা, বাংলাদেশ">
                                                    <small class="text-muted font-11">Leave blank to use primary contact address.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-section 2: Column Titles -->
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="text-dark font-weight-bold mb-3"><i class="fe-list me-1 text-primary"></i> 2. Footer Menu Column Titles (Columns 2 & 3)</h6>
                                        <div class="row">
                                            <div class="col-md-6 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="footer_useful_links_title" class="form-label font-weight-bold">Column 2 Title</label>
                                                    <input type="text" class="form-control" name="footer_useful_links_title" id="footer_useful_links_title" value="{{ old('footer_useful_links_title', $edit_data->footer_useful_links_title ?? 'Useful Links') }}" placeholder="e.g. Useful Links">
                                                    <small class="text-muted font-11">Header for column 2 (Useful Links).</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="footer_info_links_title" class="form-label font-weight-bold">Column 3 Title</label>
                                                    <input type="text" class="form-control" name="footer_info_links_title" id="footer_info_links_title" value="{{ old('footer_info_links_title', $edit_data->footer_info_links_title ?? 'Information') }}" placeholder="e.g. Information">
                                                    <small class="text-muted font-11">Header for column 3 (Information).</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-section 3: Newsletter & App Download (Column 4) -->
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="text-dark font-weight-bold mb-3"><i class="fe-mail me-1 text-primary"></i> 3. Newsletter & Mobile App Download (Column 4)</h6>
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <label for="newsletter_status" class="d-block mb-1 font-weight-bold">Newsletter Subscription</label>
                                                <label class="switch">
                                                    <input type="checkbox" value="1" name="newsletter_status" id="newsletter_status" @if(($edit_data->newsletter_status ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted d-block font-11">Show/Hide newsletter subscription box.</small>
                                            </div>
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="newsletter_title" class="form-label font-weight-bold">Newsletter Header Title</label>
                                                    <input type="text" class="form-control" name="newsletter_title" id="newsletter_title" value="{{ old('newsletter_title', $edit_data->newsletter_title ?? 'Stay Connected') }}" placeholder="e.g. Stay Connected">
                                                </div>
                                            </div>
                                            <div class="col-md-5 col-sm-12 mb-2">
                                                <div class="form-group">
                                                    <label for="newsletter_text" class="form-label font-weight-bold">Newsletter Subtitle Text</label>
                                                    <input type="text" class="form-control" name="newsletter_text" id="newsletter_text" value="{{ old('newsletter_text', $edit_data->newsletter_text ?? 'Subscribe to our newsletter for exclusive offers, new arrivals & the latest updates.') }}" placeholder="e.g. Subscribe to our newsletter...">
                                                </div>
                                            </div>

                                            <div class="col-12 mt-2 pt-2 border-top"></div>

                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <label for="app_download_status" class="d-block mb-1 font-weight-bold">App Download Section</label>
                                                <label class="switch">
                                                    <input type="checkbox" value="1" name="app_download_status" id="app_download_status" @if(($edit_data->app_download_status ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted d-block font-11">Show/Hide App Store & Play Store badges.</small>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="app_download_title" class="form-label font-weight-bold">App Section Title</label>
                                                    <input type="text" class="form-control" name="app_download_title" id="app_download_title" value="{{ old('app_download_title', $edit_data->app_download_title ?? 'Download Our App') }}" placeholder="e.g. Download Our App">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="play_store_url" class="form-label font-weight-bold">Google Play Store URL</label>
                                                    <input type="url" class="form-control" name="play_store_url" id="play_store_url" value="{{ old('play_store_url', $edit_data->play_store_url) }}" placeholder="https://play.google.com/store/apps/details?id=...">
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="app_store_url" class="form-label font-weight-bold">Apple App Store URL</label>
                                                    <input type="url" class="form-control" name="app_store_url" id="app_store_url" value="{{ old('app_store_url', $edit_data->app_store_url) }}" placeholder="https://apps.apple.com/app/...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-section 4: Middle Trust Features Bar -->
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <div class="d-flex align-items-center justify-content-between mb-3">
                                            <h6 class="text-dark font-weight-bold mb-0"><i class="fe-shield me-1 text-primary"></i> 4. Trust Badges / Middle Features Bar (4 Features)</h6>
                                            <div>
                                                <label class="switch mb-0">
                                                    <input type="checkbox" value="1" name="features_status" id="features_status" @if(($edit_data->features_status ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted ms-1 font-11">Show/Hide Features Bar</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="p-2 border rounded bg-white">
                                                    <span class="badge bg-soft-info text-info mb-1"><i class="fe-lock me-1"></i> Feature 1 (Payment)</span>
                                                    <div class="form-group mb-1">
                                                        <label class="font-11 font-weight-bold mb-0">Title</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature1_title" value="{{ old('feature1_title', $edit_data->feature1_title ?? '100% Secure Payment') }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-11 font-weight-bold mb-0">Subtitle</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature1_subtitle" value="{{ old('feature1_subtitle', $edit_data->feature1_subtitle ?? 'Safe & Secure Transactions') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="p-2 border rounded bg-white">
                                                    <span class="badge bg-soft-success text-success mb-1"><i class="fe-truck me-1"></i> Feature 2 (Delivery)</span>
                                                    <div class="form-group mb-1">
                                                        <label class="font-11 font-weight-bold mb-0">Title</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature2_title" value="{{ old('feature2_title', $edit_data->feature2_title ?? 'Fast Delivery') }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-11 font-weight-bold mb-0">Subtitle</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature2_subtitle" value="{{ old('feature2_subtitle', $edit_data->feature2_subtitle ?? 'All Over Bangladesh') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="p-2 border rounded bg-white">
                                                    <span class="badge bg-soft-warning text-warning mb-1"><i class="fe-refresh-cw me-1"></i> Feature 3 (Return)</span>
                                                    <div class="form-group mb-1">
                                                        <label class="font-11 font-weight-bold mb-0">Title</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature3_title" value="{{ old('feature3_title', $edit_data->feature3_title ?? 'Easy Return') }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-11 font-weight-bold mb-0">Subtitle</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature3_subtitle" value="{{ old('feature3_subtitle', $edit_data->feature3_subtitle ?? 'Within 7 Days') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <div class="p-2 border rounded bg-white">
                                                    <span class="badge bg-soft-danger text-danger mb-1"><i class="fe-headphones me-1"></i> Feature 4 (Support)</span>
                                                    <div class="form-group mb-1">
                                                        <label class="font-11 font-weight-bold mb-0">Title</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature4_title" value="{{ old('feature4_title', $edit_data->feature4_title ?? '24/7 Support') }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-11 font-weight-bold mb-0">Subtitle</label>
                                                        <input type="text" class="form-control form-control-sm" name="feature4_subtitle" value="{{ old('feature4_subtitle', $edit_data->feature4_subtitle ?? "We're Here to Help") }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sub-section 5: Payment Gateways Display -->
                                    <div class="border rounded p-3 bg-light">
                                        <h6 class="text-dark font-weight-bold mb-3"><i class="fe-credit-card me-1 text-primary"></i> 5. Payment Gateways Badges (Bottom Orange Bar)</h6>
                                        <div class="row align-items-center">
                                            <div class="col-md-4 col-sm-6 mb-2">
                                                <label for="show_payment_methods" class="d-block mb-1 font-weight-bold">Payment Methods Badges</label>
                                                <label class="switch">
                                                    <input type="checkbox" value="1" name="show_payment_methods" id="show_payment_methods" @if(($edit_data->show_payment_methods ?? 1) == 1) checked @endif>
                                                    <span class="slider round"></span>
                                                </label>
                                                <small class="text-muted d-block font-11">Show/Hide payment method icons (bKash, Nagad, Rocket, Visa, Mastercard, COD).</small>
                                            </div>
                                            <div class="col-md-8 col-sm-6 mb-2">
                                                <div class="form-group">
                                                    <label for="payment_methods_image" class="form-label font-weight-bold">Custom Payment Banner Image <small class="text-muted">(Optional)</small></label>
                                                    <input type="file" class="form-control" name="payment_methods_image" id="payment_methods_image" accept="image/*">
                                                    <small class="text-muted font-11">If uploaded, this custom image will be used instead of individual badge pills.</small>
                                                    @if(!empty($edit_data->payment_methods_image) && file_exists(public_path($edit_data->payment_methods_image)))
                                                        <div class="mt-2 p-1 bg-white border rounded d-inline-block">
                                                            <img src="{{ asset($edit_data->payment_methods_image) }}" style="max-height: 35px; max-width: 250px; object-fit: contain;" alt="Payment Banner">
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- Footer Customization End -->

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-success waves-effect waves-light"><i class="fe-check-circle me-1"></i> Update Setting</button>
                            <a href="{{ route('settings.index') }}" class="btn btn-secondary waves-effect waves-light ms-1">Cancel</a>
                        </div>

                    </form>

                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('backEnd/assets/libs/parsleyjs/parsley.min.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/form-validation.init.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/select2/js/select2.min.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/form-advanced.init.js')}}"></script>
<script>
    (function() {
        const pairs = [
            { textId: 'order_btn_bg_color', pickerId: 'order_btn_bg_color_picker' },
            { textId: 'order_btn_text_color', pickerId: 'order_btn_text_color_picker' },
            { textId: 'order_btn_hover_bg_color', pickerId: 'order_btn_hover_bg_color_picker' },
            { textId: 'cart_btn_bg_color', pickerId: 'cart_btn_bg_color_picker' },
            { textId: 'cart_btn_text_color', pickerId: 'cart_btn_text_color_picker' },
            { textId: 'cart_btn_hover_bg_color', pickerId: 'cart_btn_hover_bg_color_picker' },
            { textId: 'discount_badge_bg_color', pickerId: 'discount_badge_bg_color_picker' },
            { textId: 'discount_badge_text_color', pickerId: 'discount_badge_text_color_picker' },
            { textId: 'discount_badge_border_color', pickerId: 'discount_badge_border_color_picker' },
        ];

        function syncLivePreview() {
            const orderBg = document.getElementById('order_btn_bg_color')?.value || '#fe5200';
            const orderText = document.getElementById('order_btn_text_color')?.value || '#ffffff';
            const orderHoverBg = document.getElementById('order_btn_hover_bg_color')?.value || '#e04800';
            const orderLabel = document.getElementById('order_btn_text')?.value || 'অর্ডার করুন';

            const cartBg = document.getElementById('cart_btn_bg_color')?.value || '#2f3543';
            const cartText = document.getElementById('cart_btn_text_color')?.value || '#ffffff';
            const cartHoverBg = document.getElementById('cart_btn_hover_bg_color')?.value || '#1e222b';
            const cartLabel = document.getElementById('cart_btn_text')?.value || 'কার্টে যোগ';

            const badgeBg = document.getElementById('discount_badge_bg_color')?.value || '#ffffff';
            const badgeText = document.getElementById('discount_badge_text_color')?.value || '#fe5200';
            const badgeBorder = document.getElementById('discount_badge_border_color')?.value || '#fe5200';
            const badgeSuffix = document.getElementById('discount_badge_text')?.value || 'ছাড়';

            // Preview Order Button
            const pOrderBtn = document.getElementById('preview_order_btn');
            const pOrderText = document.getElementById('preview_order_text');
            if (pOrderBtn && pOrderText) {
                pOrderBtn.style.backgroundColor = orderBg;
                pOrderBtn.style.borderColor = orderBg;
                pOrderBtn.style.color = orderText;
                pOrderText.innerText = orderLabel;
                pOrderBtn.onmouseenter = function() { this.style.backgroundColor = orderHoverBg; this.style.borderColor = orderHoverBg; };
                pOrderBtn.onmouseleave = function() { this.style.backgroundColor = orderBg; this.style.borderColor = orderBg; };
            }

            // Preview Cart Button
            const pCartBtn = document.getElementById('preview_cart_btn');
            const pCartText = document.getElementById('preview_cart_text');
            if (pCartBtn && pCartText) {
                pCartBtn.style.backgroundColor = cartBg;
                pCartBtn.style.borderColor = cartBg;
                pCartBtn.style.color = cartText;
                pCartText.innerText = cartLabel;
                pCartBtn.onmouseenter = function() { this.style.backgroundColor = cartHoverBg; this.style.borderColor = cartHoverBg; };
                pCartBtn.onmouseleave = function() { this.style.backgroundColor = cartBg; this.style.borderColor = cartBg; };
            }

            // Preview Discount Badge
            const pBadge = document.getElementById('preview_discount_badge');
            const pBadgeText = document.getElementById('preview_discount_text');
            const pBadgeSuffix = document.getElementById('preview_discount_suffix');
            if (pBadge && pBadgeText && pBadgeSuffix) {
                pBadge.style.backgroundColor = badgeBg;
                pBadge.style.borderColor = badgeBorder;
                pBadgeText.style.color = badgeText;
                pBadgeSuffix.innerText = badgeSuffix;
            }
        }

        pairs.forEach(function(pair) {
            const textInput = document.getElementById(pair.textId);
            const pickerInput = document.getElementById(pair.pickerId);

            if (textInput && pickerInput) {
                pickerInput.addEventListener('input', function() {
                    textInput.value = this.value;
                    syncLivePreview();
                });

                textInput.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
                        pickerInput.value = val;
                    }
                    syncLivePreview();
                });
            }
        });

        ['order_btn_text', 'cart_btn_text', 'discount_badge_text'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', syncLivePreview);
            }
        });

        const resetBtn = document.getElementById('btn-reset-preview-defaults');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                const defaults = {
                    'order_btn_text': 'অর্ডার করুন',
                    'order_btn_bg_color': '#fe5200',
                    'order_btn_text_color': '#ffffff',
                    'order_btn_hover_bg_color': '#e04800',
                    'cart_btn_text': 'কার্টে যোগ',
                    'cart_btn_bg_color': '#2f3543',
                    'cart_btn_text_color': '#ffffff',
                    'cart_btn_hover_bg_color': '#1e222b',
                    'discount_badge_text': 'ছাড়',
                    'discount_badge_bg_color': '#ffffff',
                    'discount_badge_text_color': '#fe5200',
                    'discount_badge_border_color': '#fe5200'
                };

                for (let key in defaults) {
                    const t = document.getElementById(key);
                    if (t) t.value = defaults[key];
                    const p = document.getElementById(key + '_picker');
                    if (p) p.value = defaults[key];
                }
                syncLivePreview();
            });
        }

        syncLivePreview();
    })();
</script>
@endsection