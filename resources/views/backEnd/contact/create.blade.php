@extends('backEnd.layouts.master')
@section('title', 'Create Contact Info')
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
                    <a href="{{route('contact.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage Contacts</a>
                </div>
                <h4 class="page-title">Create Contact Info</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <form action="{{route('contact.store')}}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                    @csrf
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label font-weight-bold">Primary Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" id="phone" placeholder="e.g. +880 1700-000000" required="">
                            @error('phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="hotline" class="form-label font-weight-bold">Hotline / Support Number</label>
                            <input type="text" class="form-control @error('hotline') is-invalid @enderror" name="hotline" value="{{ old('hotline') }}" id="hotline" placeholder="e.g. 09600-000000">
                            @error('hotline')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="email" class="form-label font-weight-bold">Primary Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" id="email" placeholder="e.g. info@mondolshopbd.com" required="">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="hotmail" class="form-label font-weight-bold">Secondary / Support Email</label>
                            <input type="email" class="form-control @error('hotmail') is-invalid @enderror" name="hotmail" value="{{ old('hotmail') }}" id="hotmail" placeholder="e.g. support@mondolshopbd.com">
                            @error('hotmail')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="address" class="form-label font-weight-bold">Physical Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="address" rows="2" placeholder="e.g. House #1, Road #2, Dhanmondi, Dhaka, Bangladesh" required="">{{ old('address') }}</textarea>
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="maplink" class="form-label font-weight-bold">Google Map Embed / Link</label>
                            <input type="text" class="form-control @error('maplink') is-invalid @enderror" name="maplink" value="{{ old('maplink') }}" id="maplink" placeholder="Google Maps URL or embed code">
                            @error('maplink')
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
                              <input type="checkbox" value="1" name="status" checked>
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
                        <button type="submit" class="btn btn-success waves-effect waves-light"><i class="fe-check-circle me-1"></i> Save Contact Info</button>
                        <a href="{{route('contact.index')}}" class="btn btn-secondary waves-effect ms-1">Cancel</a>
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