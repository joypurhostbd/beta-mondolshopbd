@extends('backEnd.layouts.master')
@section('title', 'Roles Edit')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .permission-module-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fff;
            transition: all 0.2s ease;
        }
        .permission-module-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .permission-module-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px 16px;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .permission-module-body {
            padding: 14px 16px;
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
                    <a href="{{ route('roles.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Roles
                    </a>
                </div>
                <h4 class="page-title">Edit Role & Permissions (<span>{{ $edit_data->name }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <form action="{{ route('roles.update') }}" method="POST" data-parsley-validate="">
                        @csrf
                        <input type="hidden" name="hidden_id" value="{{ $edit_data->id }}">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Role Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $edit_data->name) }}" id="name" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="form-check p-2 bg-light rounded border w-100 d-flex align-items-center">
                                    <input type="checkbox" class="form-check-input ms-1 me-2" id="checkall_global">
                                    <label class="form-check-label fw-bold text-primary cursor-pointer mb-0" for="checkall_global">
                                        <i class="fe-check-square me-1"></i> Select / Unselect All System Permissions
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">
                        <h5 class="mb-3 text-dark fw-semibold"><i class="fe-shield me-1 text-primary"></i> Module Permissions Matrix</h5>

                        <div class="row">
                            @foreach($groupedPermissions as $moduleName => $permissions)
                            <div class="col-lg-6 col-xl-4">
                                <div class="permission-module-card">
                                    <div class="permission-module-header">
                                        <span class="fw-bold text-dark font-14">{{ $moduleName }}</span>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input module-check-all" id="module_check_{{ Str::slug($moduleName) }}" data-target="module_group_{{ Str::slug($moduleName) }}">
                                            <label class="form-check-label font-11 text-muted cursor-pointer" for="module_check_{{ Str::slug($moduleName) }}">
                                                Select All
                                            </label>
                                        </div>
                                    </div>
                                    <div class="permission-module-body module_group_{{ Str::slug($moduleName) }}">
                                        <div class="row">
                                            @foreach($permissions as $perm)
                                            <div class="col-12 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input permission-checkbox" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" name="permission[]" {{ $edit_data->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label font-13" for="perm_{{ $perm->id }}">
                                                        {{ $perm->name }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                <i class="fe-check-circle me-1"></i> Update Role
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary waves-effect ms-1">Cancel</a>
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
    <script>
        $(document).ready(function() {
            // Global check all
            $('#checkall_global').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.permission-checkbox').prop('checked', isChecked);
                $('.module-check-all').prop('checked', isChecked);
            });

            // Module check all
            $('.module-check-all').on('change', function() {
                var targetClass = $(this).data('target');
                var isChecked = $(this).is(':checked');
                $('.' + targetClass + ' .permission-checkbox').prop('checked', isChecked);
            });
        });
    </script>
@endsection