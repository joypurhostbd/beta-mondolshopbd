@extends('backEnd.layouts.master')
@section('title', 'Banner Create')
@section('css')
<link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backEnd/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0 me-2 d-none d-md-inline-flex">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('banners.index') }}">Banners</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                    <a href="{{ route('banners.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Banners
                    </a>
                </div>
                <h4 class="page-title">Create Banner</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-uppercase font-14 mb-3"><i class="fe-image me-1 text-primary"></i> New Banner Configuration</h5>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fe-alert-circle me-1"></i> <strong>Please fix the errors below:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('banners.store') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="category_id" class="form-label font-weight-bold">Banner Category <span class="text-danger">*</span></label>
                                <select class="form-control select2 @error('category_id') is-invalid @enderror" name="category_id" id="category_id" data-toggle="select2" required>
                                    <option value="">Select Category...</option>
                                    @foreach($categories as $value)
                                        <option value="{{ $value->id }}" {{ old('category_id') == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="link" class="form-label font-weight-bold">Target Link <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('link') is-invalid @enderror" name="link" value="{{ old('link', '#') }}" id="link" placeholder="e.g. https://domain.com/category/slug or #" required="">
                                @error('link')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-12 mb-3">
                            <div class="form-group">
                                <label for="image" class="form-label font-weight-bold">Banner Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" id="image" accept="image/*" required="">
                                <small class="text-muted d-block mt-1">Recommended formats: WEBP, JPG, PNG. Max size: 5MB.</small>
                                <div id="banner-image-preview-wrapper" class="mt-2 d-none">
                                    <p class="font-12 text-muted mb-1">Image Preview:</p>
                                    <img id="banner-image-preview" src="" alt="Preview" class="rounded border shadow-sm" style="max-height: 120px; max-width: 280px; object-fit: contain;">
                                </div>
                                @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-12 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block form-label font-weight-bold">Status</label>
                                <input type="checkbox" value="1" name="status" id="status" class="js-switch" data-color="#28a745" checked />
                                <small class="text-muted d-block mt-1">Toggle to activate or deactivate this banner across the storefront.</small>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-success waves-effect waves-light me-1">
                                <i class="fe-check-circle me-1"></i> Save Banner
                            </button>
                            <a href="{{ route('banners.index') }}" class="btn btn-secondary waves-effect">
                                <i class="fe-x me-1"></i> Cancel
                            </a>
                        </div>

                    </form>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col-->
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('backEnd/assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/switchery/switchery.min.js') }}"></script>
<script>
    $(document).ready(function(){
        if ($('.select2').length > 0) {
            $('.select2').select2();
            $('.select2').on('change', function() {
                if (typeof $(this).parsley === 'function') {
                    $(this).parsley().validate();
                }
            });
        }
        if ($('.js-switch').length > 0) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                new Switchery(html, { size: 'small', color: '#28a745' });
            });
        }
        $('#image').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    $('#banner-image-preview').attr('src', evt.target.result);
                    $('#banner-image-preview-wrapper').removeClass('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                $('#banner-image-preview-wrapper').addClass('d-none');
            }
        });
    });
</script>
@endsection