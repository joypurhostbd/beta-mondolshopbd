@extends('backEnd.layouts.master')
@section('title', 'Users Manage')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .user-avatar-thumb {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e2e8f0;
        }
        .user-avatar-placeholder {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 15px;
            text-transform: uppercase;
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
                    <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-user-plus me-1"></i> Create User
                    </a>
                </div>
                <h4 class="page-title">Users Manage (<span>{{ $kpis['total_users'] ?? count($data) }}</span>)</h4>
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
                                <i class="fe-users font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['total_users'] ?? count($data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total System Users</p>
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
                                <i class="fe-check-circle font-20 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['active_users'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Active Users</p>
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
                            <div class="avatar-sm rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-user-x font-20 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['inactive_users'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Inactive Users</p>
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
                                <i class="fe-shield font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $kpis['total_roles'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Available Roles</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role(s)</th>
                                <th>Status</th>
                                <th style="width: 140px;">Action</th>
                            </tr>
                        </thead>
                    
                        <tbody>
                            @foreach($data as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2 flex-shrink-0">
                                            @if(!empty($value->image) && file_exists(public_path($value->image)))
                                                <img src="{{ asset($value->image) }}" alt="{{ $value->name }}" class="user-avatar-thumb">
                                            @else
                                                <div class="user-avatar-placeholder">
                                                    {{ strtoupper(substr($value->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="font-14 my-0 fw-semibold">{{ $value->name }}</h5>
                                            <span class="text-muted font-11">
                                                Joined: {{ $value->created_at ? $value->created_at->format('d M, Y') : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $value->email }}" class="text-body">
                                        <i class="fe-mail font-12 text-muted me-1"></i>{{ $value->email }}
                                    </a>
                                </td>
                                <td>
                                    @forelse($value->roles as $role)
                                        <span class="badge bg-soft-primary text-primary px-2 py-1 me-1 mb-1">
                                            <i class="fe-shield font-11 me-1"></i>{{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="badge bg-soft-secondary text-secondary px-2 py-1">No Role</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if($value->status == 1)
                                        <span class="badge bg-soft-success text-success px-2 py-1 font-12">
                                            <i class="fe-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger px-2 py-1 font-12">
                                            <i class="fe-x-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="button-list d-flex align-items-center gap-1">
                                        @if(auth()->check() && (int)$value->id === (int)auth()->id())
                                            <button type="button" class="btn btn-xs btn-outline-secondary" disabled title="Self-account active">
                                                <i class="fe-user-check"></i>
                                            </button>
                                        @else
                                            @if($value->status == 1)
                                                <form method="post" action="{{ route('users.inactive') }}" class="d-inline"> 
                                                    @csrf
                                                    <input type="hidden" value="{{ $value->id }}" name="hidden_id">       
                                                    <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate User">
                                                        <i class="fe-thumbs-down"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="post" action="{{ route('users.active') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" value="{{ $value->id }}" name="hidden_id">        
                                                    <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate User">
                                                        <i class="fe-thumbs-up"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <a href="{{ route('users.edit', $value->id) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit User">
                                            <i class="fe-edit-1"></i>
                                        </a>

                                        @if(auth()->check() && (int)$value->id === (int)auth()->id())
                                            <button type="button" class="btn btn-xs btn-outline-danger" disabled title="Cannot delete logged-in account">
                                                <i class="mdi mdi-lock"></i>
                                            </button>
                                        @else
                                            <form method="post" action="{{ route('users.destroy') }}" class="d-inline">        
                                                @csrf
                                                <input type="hidden" value="{{ $value->id }}" name="hidden_id">
                                                <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete User">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </form>
                                        @endif
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