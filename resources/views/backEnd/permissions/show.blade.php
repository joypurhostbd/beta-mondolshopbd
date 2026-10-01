@extends('backEnd.layouts.master')
@section('title', 'Permission Details - ' . $permission->name)

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('permissions.edit', $permission->id) }}" class="btn btn-primary rounded-pill me-1">
                        <i class="fe-edit-1 me-1"></i> Edit Permission
                    </a>
                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary rounded-pill">
                        <i class="fe-arrow-left me-1"></i> Back to Permissions
                    </a>
                </div>
                <h4 class="page-title">Permission Details</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <!-- Overview Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar-lg mx-auto mb-2 rounded-circle bg-soft-info border-info border d-flex align-items-center justify-content-center">
                            <i class="fe-key font-28 text-info"></i>
                        </div>
                        <h4 class="mb-1 text-dark"><code>{{ $permission->name }}</code></h4>
                        <p class="text-muted font-13 mb-2">Module: <strong class="text-primary">{{ $moduleName }}</strong></p>
                        <span class="badge bg-light text-muted border font-12">
                            Guard: {{ $permission->guard_name }}
                        </span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Assigned Roles:</span>
                        <span class="fw-bold text-dark">{{ $permission->roles_count ?? $permission->roles->count() }} Role(s)</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Created Date:</span>
                        <span class="fw-bold text-dark">{{ $permission->created_at ? $permission->created_at->format('d M, Y') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Roles Matrix -->
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header bg-light border-bottom py-2">
                    <h5 class="card-title my-0 text-dark font-15">
                        <i class="fe-shield me-1 text-primary"></i> Roles Holding This Permission ({{ $permission->roles->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($permission->roles && $permission->roles->count() > 0)
                        <div class="row">
                            @foreach($permission->roles as $role)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3 d-flex align-items-center justify-content-between bg-light-subtle">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle bg-soft-primary border-primary border d-flex align-items-center justify-content-center me-2">
                                            <i class="fe-shield text-primary font-16"></i>
                                        </div>
                                        <div>
                                            <h5 class="font-14 my-0 fw-semibold text-dark">{{ $role->name }}</h5>
                                            <span class="text-muted font-11">Guard: {{ $role->guard_name }}</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('roles.show', $role->id) }}" class="btn btn-xs btn-outline-primary waves-effect">
                                        View Role
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fe-alert-triangle me-1"></i> No roles currently possess this permission.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

