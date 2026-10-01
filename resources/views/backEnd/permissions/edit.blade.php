@extends('backEnd.layouts.master')
@section('title', 'Permissions Edit')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('permissions.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Permissions
                    </a>
                </div>
                <h4 class="page-title">Edit Permission (<span>{{ $edit_data->name }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <form action="{{ route('permissions.update') }}" method="POST" data-parsley-validate="">
                        @csrf
                        <input type="hidden" name="hidden_id" value="{{ $edit_data->id }}">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Permission Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $edit_data->name) }}" id="name" required>
                            <small class="text-muted d-block mt-1">
                                <i class="fe-info me-1"></i> Standard format: <code>module-action</code> (e.g. <code>product-list</code>, <code>order-create</code>).
                            </small>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="guard_name" class="form-label">Guard Name</label>
                            <input type="text" class="form-control" name="guard_name" value="{{ old('guard_name', $edit_data->guard_name ?? 'web') }}" id="guard_name" readonly>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                <i class="fe-check-circle me-1"></i> Update Permission
                            </button>
                            <a href="{{ route('permissions.index') }}" class="btn btn-secondary waves-effect ms-1">Cancel</a>
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
@endsection