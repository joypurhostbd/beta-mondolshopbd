@extends('backEnd.layouts.master')
@section('title', 'Users Create')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/css/switchery.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('users.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Users
                    </a>
                </div>
                <h4 class="page-title">Create System User</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" id="name" placeholder="Enter full name" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" id="email" placeholder="user@domain.com" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="Minimum 6 characters" required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control @error('confirm-password') is-invalid @enderror" name="confirm-password" id="confirm-password" placeholder="Re-type password" required>
                                @error('confirm-password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="roles" class="form-label">Assign Role(s) *</label>
                                <select class="form-control select2-multiple" name="roles[]" data-toggle="select2" multiple="multiple" data-placeholder="Select roles..." required>
                                    <optgroup label="System Roles">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>{{ $role->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('roles')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="image" class="form-label">Avatar / Profile Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" name="image" id="image" accept="image/*">
                                <small class="text-muted">Recommended format: Square WebP/JPG/PNG (e.g. 200x200px)</small>
                                @error('image')
                                    <span class="invalid-feedback d-block" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block form-label">Account Status</label>
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

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                <i class="fe-check-circle me-1"></i> Save User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary waves-effect ms-1">Cancel</a>
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
    <script src="{{ asset('backEnd/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/switchery.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            var elem = document.querySelector('.js-switch');
            if (elem) {
                var init = new Switchery(elem);
            }
        });
    </script>
@endsection