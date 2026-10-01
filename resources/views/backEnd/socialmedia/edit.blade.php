@extends('backEnd.layouts.master')
@section('title', 'Edit Social Media Link')
@section('css')
<link href="{{asset('backEnd/assets/libs/select2/css/select2.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/css/switchery.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('socialmedias.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage Social Links</a>
                </div>
                <h4 class="page-title">Edit Social Media Link</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <form action="{{route('socialmedias.update')}}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" value="{{$edit_data->id}}" name="id">
                    <input type="hidden" value="{{$edit_data->id}}" name="hidden_id">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label font-weight-bold">Platform Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $edit_data->title) }}" id="title" placeholder="e.g. Facebook, Instagram, YouTube" required="">
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="icon" class="form-label font-weight-bold">Icon Class <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('icon') is-invalid @enderror" name="icon" value="{{ old('icon', $edit_data->icon) }}" id="icon" placeholder="e.g. fe-facebook, fe-instagram, fe-youtube, fab fa-tiktok" required="">
                            <small class="text-muted">Use Feather icons (`fe-facebook`, `fe-instagram`, `fe-youtube`, `fe-twitter`) or FontAwesome classes.</small>
                            @error('icon')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="link" class="form-label font-weight-bold">Target Link / URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('link') is-invalid @enderror" name="link" value="{{ old('link', $edit_data->link) }}" id="link" placeholder="https://facebook.com/yourbrand" required="">
                            @error('link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="color" class="form-label font-weight-bold">Brand Color</label>
                            <div class="d-flex align-items-center">
                                <input type="color" class="form-control form-control-color me-2 @error('color') is-invalid @enderror" name="color" value="{{ old('color', $edit_data->color ?: '#3b5998') }}" id="color">
                                <span class="text-muted font-monospace small">Theme color for icon pill</span>
                            </div>
                            @error('color')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6 mb-3">
                        <div class="form-group">
                            <label for="status" class="d-block font-weight-bold">Status</label>
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
                    <div class="col-12">
                        <button type="submit" class="btn btn-success waves-effect waves-light"><i class="fe-check-circle me-1"></i> Update Social Link</button>
                        <a href="{{route('socialmedias.index')}}" class="btn btn-secondary waves-effect ms-1">Cancel</a>
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
<script src="{{asset('backEnd/assets/js/switchery.min.js')}}"></script>
@endsection