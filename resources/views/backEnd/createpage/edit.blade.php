@extends('backEnd.layouts.master')
@section('title', 'Page Edit')
@section('css')
<link href="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backEnd/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('pages.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Pages
                    </a>
                </div>
                <h4 class="page-title">Edit Page: {{ $edit_data->name }}</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('pages.update') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{ $edit_data->id }}" name="id">
                        <input type="hidden" value="{{ $edit_data->id }}" name="hidden_id">
                        
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Page Name <span class="text-danger">*</span></label>
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
                                <label for="title" class="form-label">Page Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $edit_data->title) }}" id="title" required="">
                                @error('title')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Page Content / Description <span class="text-danger">*</span></label>
                                <textarea class="summernote form-control @error('description') is-invalid @enderror" name="description" id="description" rows="10" required="">{{ old('description', $edit_data->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block form-label">Publication Status</label>
                                <input type="checkbox" value="1" name="status" id="status" class="js-switch" data-color="#28a745" {{ $edit_data->status == 1 ? 'checked' : '' }} />
                                <small class="text-muted d-block mt-1">Active pages are visible to website visitors.</small>
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
                                <i class="fe-check-circle me-1"></i> Update Page
                            </button>
                            <a href="{{ route('pages.index') }}" class="btn btn-secondary waves-effect">
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
<script src="{{ asset('backEnd/assets/libs/switchery/switchery.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.js') }}"></script>
<script>
    $(document).ready(function() {
        if ($('.js-switch').length > 0) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                new Switchery(html, { size: 'small', color: '#28a745' });
            });
        }

        $(".summernote").summernote({
            placeholder: "Enter complete page details, terms, or policy information here...",
            height: 250,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endsection