@extends('backEnd.layouts.master')
@section('title', 'Users Edit')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/css/switchery.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .current-avatar-preview {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
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
                    <a href="{{ route('users.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Users
                    </a>
                </div>
                <h4 class="page-title">Edit User (<span>{{ $edit_data->name }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <form action="{{ route('users.update') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" value="{{ $edit_data->id }}" name="hidden_id">
                        
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $edit_data->name) }}" id="name" required>
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
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $edit_data->email) }}" id="email" required>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="Leave blank to keep unchanged">
                                <small class="text-muted">Leave blank if you do not want to change password.</small>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control @error('confirm-password') is-invalid @enderror" name="confirm-password" id="confirm-password" placeholder="Re-type password">
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
                                <select class="form-control select2-multiple" name="roles[]" data-toggle="select2" multiple="multiple" data-placeholder="Choose roles..." required>
                                    <optgroup label="System Roles">
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ $edit_data->hasRole($role->name) ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
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
                                @if(!empty($edit_data->image) && file_exists(public_path($edit_data->image)))
                                    <div class="mt-2 d-flex align-items-center">
                                        <img src="{{ asset($edit_data->image) }}" alt="{{ $edit_data->name }}" class="current-avatar-preview me-2">
                                        <span class="text-muted font-12">Current avatar</span>
                                    </div>
                                @endif
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
                                    <input type="checkbox" value="1" name="status" {{ $edit_data->status == 1 ? 'checked' : '' }}>
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
                                <i class="fe-check-circle me-1"></i> Update User
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