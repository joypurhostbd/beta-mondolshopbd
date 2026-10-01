@extends('backEnd.layouts.master')
@section('title', 'Customer Manage')

@section('css')
<link href="{{asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('customers.ip_block')}}" class="btn btn-warning rounded-pill waves-effect waves-light"><i class="fe-shield me-1"></i> IP Block Manage</a>
                </div>
                <h4 class="page-title">Customer Manage</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI summary cards -->
    <div class="row mb-2">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-users font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['total_customers'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-success border-success border">
                                <i class="fe-user-check font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['active_customers'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-user-x font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['inactive_customers'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Inactive Customers</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                <i class="fe-shopping-bag font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['total_orders'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Orders</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end KPI summary cards -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Search and Filter Bar -->
                    <form method="GET" action="{{ route('customers.index') }}" class="row g-2 mb-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fe-search"></i></span>
                                <input type="text" name="keyword" value="{{ request()->get('keyword') }}" class="form-control" placeholder="Search by name, phone, email, address...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request()->get('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ request()->get('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary waves-effect waves-light"><i class="fe-filter me-1"></i> Filter</button>
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary waves-effect waves-light"><i class="fe-rotate-ccw me-1"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Orders</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($show_data as $key => $value)
                                <tr>
                                    <td>{{ $loop->iteration + ($show_data->currentPage() - 1) * $show_data->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $avatarPath = (!empty($value->image) && file_exists(public_path($value->image))) ? asset($value->image) : asset('uploads/default/user.png');
                                            @endphp
                                            <img src="{{ $avatarPath }}" alt="{{ $value->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #e2e8f0;">
                                            <div>
                                                <h5 class="m-0 font-14"><a href="{{ route('customers.profile', ['id' => $value->id]) }}" class="text-dark">{{ $value->name }}</a></h5>
                                                <small class="text-muted">Joined: {{ $value->created_at ? $value->created_at->format('d M Y') : 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="fe-phone font-12 text-muted me-1"></i> {{ $value->phone }}</div>
                                        @if(!empty($value->email))
                                            <div><i class="fe-mail font-12 text-muted me-1"></i> <small>{{ $value->email }}</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-info text-info font-13 px-2 py-1">
                                            <i class="fe-shopping-cart me-1"></i> {{ $value->orders_count ?? 0 }} Orders
                                        </span>
                                    </td>
                                    <td>
                                        @if($value->status == 'active' || $value->status == '1' || $value->status === true)
                                            <span class="badge bg-soft-success text-success font-12">Active</span>
                                        @else
                                            <span class="badge bg-soft-danger text-danger font-12">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button-list">
                                            @if($value->status == 'active' || $value->status == '1' || $value->status === true)
                                                <form method="post" action="{{ route('customers.inactive') }}" class="d-inline"> 
                                                    @csrf
                                                    <input type="hidden" value="{{ $value->id }}" name="hidden_id">       
                                                    <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate Customer"><i class="fe-thumbs-down"></i></button>
                                                </form>
                                            @else
                                                <form method="post" action="{{ route('customers.active') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" value="{{ $value->id }}" name="hidden_id">        
                                                    <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate Customer"><i class="fe-thumbs-up"></i></button>
                                                </form>
                                            @endif

                                            <a href="{{ route('customers.profile', ['id' => $value->id]) }}" class="btn btn-xs btn-blue waves-effect waves-light" title="View Profile & Orders"><i class="fe-eye"></i></a>

                                            <a href="{{ route('customers.edit', $value->id) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Customer"><i class="fe-edit-1"></i></a>

                                            <form method="post" action="{{ route('customers.adminlog') }}" class="d-inline" target="_blank">
                                                @csrf
                                                <input type="hidden" value="{{ $value->id }}" name="hidden_id">  
                                                <button type="button" class="btn btn-xs btn-pink waves-effect waves-light change-confirm" title="Login as Customer"><i class="fe-log-in"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fe-users font-24 mb-2 d-block"></i>
                                        No customer records found matching your criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="custom-paginate mt-3">
                        {{ $show_data->links('pagination::bootstrap-4') }}
                    </div>
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>
@endsection

@section('script')
<!-- third party js -->
<script src="{{asset('backEnd/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>
<!-- third party js ends -->
@endsection