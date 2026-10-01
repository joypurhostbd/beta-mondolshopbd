@extends('backEnd.layouts.master')
@section('title', 'Customer Edit')
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
                    <a href="{{route('customers.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Customer List</a>
                </div>
                <h4 class="page-title">Customer Edit</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form action="{{route('customers.update')}}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" value="{{$edit_data->id}}" name="hidden_id">
                    
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
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
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $edit_data->phone) }}" id="phone" required="">
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
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $edit_data->email) }}" id="email">
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
                            <label for="password" class="form-label">Password <small class="text-muted">(Leave empty to keep unchanged)</small></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="" id="password" placeholder="Enter new password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->

                    <div class="col-sm-6 mb-3">
                        <div class="form-group">
                            <label for="image" class="form-label">Customer Avatar</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" id="image" accept="image/*">
                            @php
                                $avatarPath = (!empty($edit_data->image) && file_exists(public_path($edit_data->image))) ? asset($edit_data->image) : asset('uploads/default/user.png');
                            @endphp
                            <div class="mt-2">
                                <img src="{{ $avatarPath }}" alt="{{ $edit_data->name }}" class="rounded-circle img-thumbnail" style="width: 70px; height: 70px; object-fit: cover;">
                            </div>
                            @error('image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col end -->

                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="address" class="form-label">Delivery Address</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $edit_data->address) }}" id="address">
                            @error('address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->

                    <div class="col-sm-6 mb-3">
                        <div class="form-group">
                            <label for="status" class="d-block mb-1">Account Status</label>
                            <label class="switch">
                                <input type="checkbox" value="1" name="status" @if($edit_data->status == 'active' || $edit_data->status == '1' || $edit_data->status === true) checked @endif>
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

                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-success waves-effect waves-light"><i class="fe-check-circle me-1"></i> Update Customer</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary waves-effect waves-light ms-1">Cancel</a>
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
@endsection