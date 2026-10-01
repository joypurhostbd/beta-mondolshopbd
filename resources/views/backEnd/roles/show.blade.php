@extends('backEnd.layouts.master')
@section('title', 'Role Details - ' . $role->name)

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary rounded-pill me-1">
                        <i class="fe-edit-1 me-1"></i> Edit Role
                    </a>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary rounded-pill">
                        <i class="fe-arrow-left me-1"></i> Back to Roles
                    </a>
                </div>
                <h4 class="page-title">Role Details & Permissions</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <div class="row">
        <!-- Role Info Overview -->
        <div class="col-lg-4">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar-lg mx-auto mb-2 rounded-circle bg-soft-primary border-primary border d-flex align-items-center justify-content-center">
                            <i class="fe-shield font-28 text-primary"></i>
                        </div>
                        <h4 class="mb-1 text-dark">{{ $role->name }}</h4>
                        <p class="text-muted font-13 mb-2">Guard: <code>{{ $role->guard_name }}</code></p>

                        @if(strtolower($role->name) === 'admin')
                            <span class="badge bg-soft-danger text-danger px-2 py-1 font-12">
                                <i class="fe-lock me-1"></i>System Core Role
                            </span>
                        @else
                            <span class="badge bg-soft-success text-success px-2 py-1 font-12">
                                <i class="fe-check-circle me-1"></i>Custom Role
                            </span>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Permissions:</span>
                        <span class="fw-bold text-dark">{{ $role->permissions_count ?? $role->permissions->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Assigned Users:</span>
                        <span class="fw-bold text-dark">{{ $role->users_count ?? $role->users->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Created Date:</span>
                        <span class="fw-bold text-dark">{{ $role->created_at ? $role->created_at->format('d M, Y') : 'N/A' }}</span>
                    </div>

                    @if($role->users && $role->users->count() > 0)
                        <hr class="my-3">
                        <h5 class="font-14 text-dark fw-semibold mb-2">Assigned Users ({{ $role->users->count() }})</h5>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($role->users as $u)
                                <span class="badge bg-light text-dark border px-2 py-1 mb-1">
                                    <i class="fe-user font-11 text-muted me-1"></i>{{ $u->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Role Permissions Matrix -->
        <div class="col-lg-8">
            <div class="card shadow-sm border">
                <div class="card-header bg-light border-bottom py-2">
                    <h5 class="card-title my-0 text-dark font-15">
                        <i class="fe-key me-1 text-primary"></i> Assigned Permissions Matrix ({{ $role->permissions->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($groupedPermissions && $groupedPermissions->count() > 0)
                        <div class="row">
                            @foreach($groupedPermissions as $moduleName => $permissions)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-2 h-100 bg-light-subtle">
                                    <h6 class="text-primary fw-bold mb-2 pb-1 border-bottom">
                                        <i class="fe-folder font-12 me-1"></i>{{ $moduleName }} ({{ $permissions->count() }})
                                    </h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($permissions as $p)
                                            <span class="badge bg-soft-primary text-primary font-12 px-2 py-1 mb-1">
                                                <i class="fe-check font-10 me-1"></i>{{ $p->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="fe-alert-triangle me-1"></i> No permissions currently assigned to this role.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

