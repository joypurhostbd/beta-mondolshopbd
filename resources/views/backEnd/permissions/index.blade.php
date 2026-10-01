@extends('backEnd.layouts.master')
@section('title', 'Permissions Manage')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('permissions.create') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-plus me-1"></i> Create Permission
                    </a>
                </div>
                <h4 class="page-title">Permissions Manage (<span>{{ $kpis['total_permissions'] ?? count($show_data) }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI Summary Metrics -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-key font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['total_permissions'] ?? count($show_data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Permissions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-info border-info border">
                                <i class="fe-folder font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['total_modules'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">System Modules</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-success border-success border">
                                <i class="fe-shield font-20 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['assigned_roles'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Roles with Perms</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-warning border-warning border">
                                <i class="fe-layers font-20 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['total_roles'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Roles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Permission Name</th>
                                <th>Module Group</th>
                                <th>Guard</th>
                                <th>Assigned Roles</th>
                                <th style="width: 130px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($show_data as $key => $value)
                            @php
                                $parts = explode('-', $value->name);
                                if (count($parts) > 1) {
                                    array_pop($parts);
                                    $mod = implode(' ', array_map('ucfirst', $parts));
                                } else {
                                    $mod = 'General';
                                }
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold text-dark font-14">
                                        <code>{{ $value->name }}</code>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-info text-info font-12 px-2 py-1">
                                        <i class="fe-folder me-1"></i>{{ $mod }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border font-12">
                                        {{ $value->guard_name }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary px-2 py-1 font-12">
                                        <i class="fe-shield me-1"></i>{{ $value->roles_count }} Role(s)
                                    </span>
                                </td>
                                <td>
                                    <div class="button-list d-flex align-items-center gap-1">
                                        <a href="{{ route('permissions.show', $value->id) }}" class="btn btn-xs btn-info waves-effect waves-light" title="View Permission">
                                            <i class="fe-eye"></i>
                                        </a>

                                        <a href="{{ route('permissions.edit', $value->id) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Permission">
                                            <i class="fe-edit-1"></i>
                                        </a>

                                        <form method="post" action="{{ route('permissions.destroy') }}" class="d-inline">        
                                            @csrf
                                            <input type="hidden" value="{{ $value->id }}" name="hidden_id">
                                            <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Permission">
                                                <i class="mdi mdi-close"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>
@endsection

@section('script')
    <!-- third party js -->
    <script src="{{ asset('backEnd/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/pages/datatables.init.js') }}"></script>
    <!-- third party js ends -->
@endsection