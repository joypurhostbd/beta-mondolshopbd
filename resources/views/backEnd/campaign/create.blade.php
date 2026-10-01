@extends('backEnd.layouts.master')
@section('title', 'Create Landing Page Campaign')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .image-preview-box {
            border: 2px dashed #dbe0e6;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #fafbfe;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: border-color 0.2s, background-color 0.2s;
            position: relative;
            overflow: hidden;
        }
        .image-preview-box:hover {
            border-color: #4a81d4;
            background: #f1f5fa;
        }
        .image-preview-box img {
            max-height: 120px;
            max-width: 100%;
            object-fit: contain;
            border-radius: 6px;
        }
        .product-info-card {
            border-left: 4px solid #4a81d4;
            background: #f8fafd;
            border-radius: 6px;
            padding: 12px 16px;
        }
        .slug-preview {
            background: #f1f3f7;
            padding: 4px 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 13px;
            color: #2c3e50;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('campaign.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Landing Pages
                    </a>
                </div>
                <h4 class="page-title">Create Landing Page Campaign</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row justify-content-center">
        <div class="col-lg-11">
            <form action="{{ route('campaign.store') }}" method="POST" data-parsley-validate="" enctype="multipart/form-data" id="campaignForm">
                @csrf

                <!-- Section 1: Basic Campaign Information -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-info me-1"></i> 1. Basic Campaign Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="form-group">
                                    <label for="name" class="form-label fw-bold">Landing Page / Campaign Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Premium Honey Nuts Mega Offer" required>
                                    @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <div class="mt-2 text-muted font-12">
                                        <strong>Live URL Preview:</strong>
                                        <span class="slug-preview">{{ url('/campaign') }}/<span id="slug-text">your-campaign-slug</span></span>
                                    </div>
                                    <input type="hidden" name="slug" id="slug" value="{{ old('slug') }}">
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label for="product_id" class="form-label fw-bold">Associated Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('product_id') is-invalid @enderror" name="product_id" id="product_id" data-toggle="select2" data-placeholder="Choose Product..." required>
                                        <option value="">Select Product...</option>
                                        @foreach($products as $value)
                                            <option value="{{ $value->id }}" 
                                                data-price="{{ $value->new_price }}" 
                                                data-oldprice="{{ $value->old_price }}" 
                                                data-sku="{{ $value->product_code ?? 'N/A' }}" 
                                                data-stock="{{ $value->stock ?? 0 }}"
                                                {{ old('product_id') == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }} @if(!empty($value->product_code)) (SKU: {{ $value->product_code }}) @endif - ৳{{ $value->new_price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Live Selected Product Info Card -->
                            <div class="col-12 mb-3" id="productInfoWrapper" style="display: none;">
                                <div class="product-info-card d-flex flex-wrap align-items-center justify-content-between">
                                    <div>
                                        <span class="text-muted font-12 d-block">Selected Product:</span>
                                        <strong class="font-14 text-dark" id="prodName">--</strong>
                                        <span class="ms-2 badge bg-soft-info text-info font-11" id="prodSku">SKU: --</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 mt-2 mt-sm-0">
                                        <div>
                                            <span class="text-muted font-12">Regular Price:</span>
                                            <del class="text-muted ms-1" id="prodOldPrice">৳0</del>
                                            <strong class="text-success ms-1 font-14" id="prodNewPrice">৳0</strong>
                                        </div>
                                        <div id="prodStockBadge">
                                            <!-- Dynamically filled with Stock Badge -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="offer_title" class="form-label fw-bold">Catchy Offer Headline / Subtitle</label>
                                    <input type="text" class="form-control @error('offer_title') is-invalid @enderror" name="offer_title" id="offer_title" value="{{ old('offer_title', 'ড্রাই ফ্রুটের অনন্য স্বাদ আর ন্যাচারাল হানির পুষ্টিকর গুণ এখন এক জায়গায়') }}" placeholder="e.g. ৫০% পর্যন্ত মেগা ছাড় + সারা দেশে ফ্রি ডেলিভারি!">
                                    <small class="text-muted">Displayed prominently right below the hero banner.</small>
                                    @error('offer_title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="special_price" class="form-label fw-bold">Special Campaign Price (৳)</label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('special_price') is-invalid @enderror" name="special_price" id="special_price" value="{{ old('special_price') }}" placeholder="Leave empty for regular product price">
                                    <small class="text-muted">Overrides regular price during campaign.</small>
                                    @error('special_price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="free_shipping" class="d-block fw-bold mb-1 font-13">Free Delivery</label>
                                            <label class="switch">
                                                <input type="checkbox" value="1" name="free_shipping" id="free_shipping" {{ old('free_shipping') ? 'checked' : '' }}>
                                                <span class="slider round"></span>
                                            </label>
                                            <small class="text-muted d-block">Free Shipping</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label for="status" class="d-block fw-bold mb-1 font-13">Status (Live)</label>
                                            <label class="switch">
                                                <input type="checkbox" value="1" name="status" id="status" checked>
                                                <span class="slider round"></span>
                                            </label>
                                            <small class="text-muted d-block">Active</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Section 2: Visual Media & Banners (With Instant Preview) -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-image me-1"></i> 2. Visual Media & Showcase Banners
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Hero / Main Banner Image <span class="text-danger">*</span></label>
                                    <div class="image-preview-box" id="heroPreviewBox" onclick="document.getElementById('image_one').click();">
                                        <img id="heroPreviewImg" src="" style="display: none;" alt="Hero Preview">
                                        <div id="heroPlaceholder">
                                            <i class="fe-upload-cloud font-24 text-primary d-block mb-1"></i>
                                            <span class="font-13 fw-semibold text-dark">Click to Upload Hero Banner</span>
                                            <small class="text-muted d-block">Recommended: 1200x500px (Max 4MB)</small>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none @error('image_one') is-invalid @enderror" name="image_one" id="image_one" accept="image/*" required onchange="previewSingleImage(this, 'heroPreviewImg', 'heroPlaceholder')">
                                    @error('image_one')
                                        <span class="text-danger font-12 d-block mt-1"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Feature Showcase Image 2 <span class="text-muted">(Optional)</span></label>
                                    <div class="image-preview-box" id="feat1PreviewBox" onclick="document.getElementById('image_two').click();">
                                        <img id="feat1PreviewImg" src="" style="display: none;" alt="Feature 1 Preview">
                                        <div id="feat1Placeholder">
                                            <i class="fe-image font-24 text-muted d-block mb-1"></i>
                                            <span class="font-13 fw-semibold text-dark">Upload Feature Image 2</span>
                                            <small class="text-muted d-block">Secondary banner/highlight</small>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none @error('image_two') is-invalid @enderror" name="image_two" id="image_two" accept="image/*" onchange="previewSingleImage(this, 'feat1PreviewImg', 'feat1Placeholder')">
                                    @error('image_two')
                                        <span class="text-danger font-12 d-block mt-1"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-group">
                                    <label class="form-label fw-bold">Feature Showcase Image 3 <span class="text-muted">(Optional)</span></label>
                                    <div class="image-preview-box" id="feat2PreviewBox" onclick="document.getElementById('image_three').click();">
                                        <img id="feat2PreviewImg" src="" style="display: none;" alt="Feature 2 Preview">
                                        <div id="feat2Placeholder">
                                            <i class="fe-image font-24 text-muted d-block mb-1"></i>
                                            <span class="font-13 fw-semibold text-dark">Upload Feature Image 3</span>
                                            <small class="text-muted d-block">Third banner/highlight</small>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control d-none @error('image_three') is-invalid @enderror" name="image_three" id="image_three" accept="image/*" onchange="previewSingleImage(this, 'feat2PreviewImg', 'feat2Placeholder')">
                                    @error('image_three')
                                        <span class="text-danger font-12 d-block mt-1"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Video Embed & Urgent Countdown Scheduling -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-video me-1"></i> 3. Video Showcase & Campaign Countdown Schedule
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="video_url" class="form-label fw-bold">YouTube Product Video URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-danger"><i class="fe-youtube font-16"></i></span>
                                        <input type="text" class="form-control @error('video_url') is-invalid @enderror" name="video_url" id="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=... or https://youtu.be/...">
                                    </div>
                                    <small class="text-muted d-block mt-1">Paste standard YouTube link. System automatically embeds it in the video section.</small>
                                    @error('video_url')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="start_date" class="form-label fw-bold">Campaign Start Date</label>
                                    <input type="text" class="form-control flatpickr-datetime @error('start_date') is-invalid @enderror" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d H:i')) }}" placeholder="Select start time...">
                                    <small class="text-muted">Campaign launch time.</small>
                                    @error('start_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group">
                                    <label for="end_date" class="form-label fw-bold">Offer End Date (Countdown Timer)</label>
                                    <input type="text" class="form-control flatpickr-datetime @error('end_date') is-invalid @enderror" name="end_date" id="end_date" value="{{ old('end_date') }}" placeholder="Select end time...">
                                    <small class="text-muted">Enables live countdown timer on page.</small>
                                    @error('end_date')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Customer Reviews & Social Proof -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-star me-1"></i> 4. Customer Reviews & Social Proof Screenshots
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="review" class="form-label fw-bold">Review Section Title</label>
                                    <input type="text" class="form-control @error('review') is-invalid @enderror" name="review" id="review" value="{{ old('review', 'সম্মানিত কাস্টমারদের রিভিউ') }}" placeholder="e.g. কাস্টমারদের সন্তুষ্টির প্রমাণ">
                                    @error('review')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Upload Review Screenshots <span class="text-muted">(Multiple)</span></label>
                                
                                <div class="clone hide" style="display: none;">
                                    <div class="control-group input-group mb-2">
                                        <input type="file" name="image[]" class="form-control" accept="image/*" />
                                        <button class="btn btn-outline-danger" type="button"><i class="fe-trash-2"></i></button>
                                    </div>
                                </div>

                                <div class="input-group control-group increment mb-2">
                                    <input type="file" name="image[]" class="form-control @error('image') is-invalid @enderror" accept="image/*" />
                                    <button class="btn btn-success btn-increment" type="button" title="Add Another Review Image"><i class="fe-plus"></i></button>
                                    @error('image')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <small class="text-muted">Click the <strong>+</strong> button to upload customer feedback & inbox screenshots.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Content Copy & Highlights -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-file-text me-1"></i> 5. Landing Page Copy & Content Highlights
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="short_description" class="form-label fw-bold">Short Highlights / Key Bullet Points</label>
                                    <textarea name="short_description" id="short_description" rows="4" class="summernote form-control @error('short_description') is-invalid @enderror">{{ old('short_description') }}</textarea>
                                    @error('short_description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mb-3">
                                <div class="form-group">
                                    <label for="description" class="form-label fw-bold">Detailed Product Benefits & Why Choose Us</label>
                                    <textarea name="description" id="description" rows="6" class="summernote form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 6: SEO & Digital Marketing Metadata -->
                <div class="card shadow-sm border mb-3">
                    <div class="card-header bg-light py-2">
                        <h5 class="card-title mb-0 font-15 text-primary">
                            <i class="fe-share-2 me-1"></i> 6. SEO & Social Media Ad Tracking (Facebook / Google)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="meta_title" class="form-label fw-bold">Meta Title</label>
                                    <input type="text" class="form-control @error('meta_title') is-invalid @enderror" name="meta_title" id="meta_title" value="{{ old('meta_title') }}" placeholder="SEO Title for Google & Social Share">
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label for="meta_keywords" class="form-label fw-bold">Meta Keywords</label>
                                    <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" name="meta_keywords" id="meta_keywords" value="{{ old('meta_keywords') }}" placeholder="e.g. honey nuts, organic dry fruits, offer">
                                </div>
                            </div>

                            <div class="col-12 mb-2">
                                <div class="form-group">
                                    <label for="meta_description" class="form-label fw-bold">Meta Description</label>
                                    <textarea name="meta_description" id="meta_description" rows="2" class="form-control @error('meta_description') is-invalid @enderror" placeholder="Short description for Facebook preview cards and Google Search results...">{{ old('meta_description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions Sticky Bottom -->
                <div class="card shadow-sm border mb-4 sticky-bottom bg-white">
                    <div class="card-body py-2 d-flex justify-content-between align-items-center">
                        <a href="{{ route('campaign.index') }}" class="btn btn-light rounded-pill px-4">
                            <i class="fe-x me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success rounded-pill px-4 font-15 shadow-sm" id="submitBtn">
                            <i class="fe-check-circle me-1"></i> Publish Landing Page
                        </button>
                    </div>
                </div>

            </form>
        </div> <!-- end col-->
    </div>
</div>
@endsection

@section('script')
    <script src="{{ asset('backEnd/assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.js') }}"></script>

    <script type="text/javascript">
        // Helper function for instant image previews
        function previewSingleImage(input, previewImgId, placeholderId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById(previewImgId);
                    img.src = e.target.result;
                    img.style.display = 'block';
                    var placeholder = document.getElementById(placeholderId);
                    if (placeholder) placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).ready(function () {
            // Select2 Init
            $('.select2').select2({
                width: '100%'
            });

            // Flatpickr DateTime Init
            $('.flatpickr-datetime').flatpickr({
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                time_24hr: true
            });

            // Summernote Init
            $(".summernote").summernote({
                placeholder: "Write content here...",
                height: 160,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });

            // Live Slug Generation
            $('#name').on('input', function() {
                var title = $(this).val();
                var slug = title.toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                $('#slug').val(slug);
                $('#slug-text').text(slug || 'your-campaign-slug');
            });

            // Product Selection Info Preview
            $('#product_id').on('change', function() {
                var selected = $(this).find(':selected');
                if (selected.val()) {
                    var name = selected.text().split('- ৳')[0].trim();
                    var price = selected.data('price') || 0;
                    var oldprice = selected.data('oldprice') || 0;
                    var sku = selected.data('sku') || 'N/A';
                    var stock = parseInt(selected.data('stock')) || 0;

                    $('#prodName').text(name);
                    $('#prodSku').text('SKU: ' + sku);
                    $('#prodNewPrice').text('৳' + price);
                    if (oldprice > 0) {
                        $('#prodOldPrice').text('৳' + oldprice).show();
                    } else {
                        $('#prodOldPrice').hide();
                    }

                    if (stock > 0) {
                        $('#prodStockBadge').html('<span class="badge bg-soft-success text-success font-12"><i class="fe-check-circle me-1"></i>In Stock (' + stock + ')</span>');
                    } else {
                        $('#prodStockBadge').html('<span class="badge bg-soft-danger text-danger font-12"><i class="fe-alert-triangle me-1"></i>Out of Stock</span>');
                    }
                    $('#productInfoWrapper').slideDown(200);
                } else {
                    $('#productInfoWrapper').slideUp(200);
                }
            });

            // Trigger product info if already selected (e.g. on validation fail / old input)
            if ($('#product_id').val()) {
                $('#product_id').trigger('change');
            }

            // Customer Review Proof Multi-upload incremental
            $(".btn-increment").click(function () {
                var html = $(".clone").html();
                $(".increment").after(html);
            });

            $("body").on("click", ".btn-outline-danger", function () {
                $(this).closest(".control-group").remove();
            });

            // Form Submit button spinner
            $('#campaignForm').on('submit', function() {
                $('#submitBtn').prop('disabled', true).html('<i class="fe-rotate-cw fe-spin me-1"></i> Publishing...');
            });
        });
    </script>
@endsection