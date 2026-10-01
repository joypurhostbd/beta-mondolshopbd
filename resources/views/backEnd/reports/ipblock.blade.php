@extends('backEnd.layouts.master')
@section('title', 'IP Block Manage')

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
                    <ol class="breadcrumb m-0 me-2 d-none d-md-inline-flex">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">IP Block</li>
                    </ol>
                    <a href="{{ route('customers.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-users me-1"></i> Customer Manage
                    </a>
                </div>
                <h4 class="page-title">Security & IP Access Control</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI Metric Cards -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-shield font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['total_blocked_ips'] ?? $data->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Blocked IPs</p>
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
                            <div class="avatar-lg rounded-circle bg-soft-warning border-warning border">
                                <i class="fe-clock font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['recent_blocked_ips'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Recent Blocks (7d)</p>
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
                                <i class="fe-shopping-bag font-22 avatar-title text-success"></i>
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
    <!-- end KPI Metric Cards -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fe-lock me-1 text-danger"></i> Block New IP Address</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.ipblock.store') }}" method="POST" class="row mb-3" data-parsley-validate="">
                        @csrf
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label for="ip_no" class="form-label font-weight-bold">IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('ip_no') is-invalid @enderror" name="ip_no" value="{{ old('ip_no') }}" id="ip_no" placeholder="e.g. 192.168.1.1 or 103.230.104.5" required="">
                                @error('ip_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->
                        <div class="col-md-5">
                            <div class="form-group mb-2">
                                <label for="reason" class="form-label font-weight-bold">Block Reason <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('reason') is-invalid @enderror" name="reason" value="{{ old('reason') }}" id="reason" placeholder="Reason for blocking IP (e.g. Fraud orders, brute force, spamming)" required="">
                                @error('reason')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->
                        <div class="col-md-2 d-flex align-items-end mb-2">
                            <button type="submit" class="btn btn-danger w-100 waves-effect waves-light">
                                <i class="fe-shield me-1"></i> Block IP
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive mt-3">
                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th style="width: 5%">SL</th>
                                    <th style="width: 20%">IP Address</th>
                                    <th style="width: 45%">Reason</th>
                                    <th style="width: 15%">Blocked Date</th>
                                    <th style="width: 15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $key => $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="badge bg-soft-danger text-danger font-13 px-2 py-1">
                                            <i class="fe-globe me-1"></i>{{ $value->ip_no }}
                                        </span>
                                    </td>
                                    <td>{{ $value->reason }}</td>
                                    <td><small class="text-muted">{{ $value->created_at ? $value->created_at->format('d M Y, h:i A') : 'N/A' }}</small></td>
                                    <td>
                                        <div class="button-list">
                                            <a class="btn btn-xs btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#ipEdit{{$value->id}}" title="Edit">
                                                <i class="fe-edit-1"></i>
                                            </a>

                                            <form method="post" action="{{ route('customers.ipblock.destroy') }}" class="d-inline">        
                                                @csrf
                                                <input type="hidden" value="{{ $value->id }}" name="id">
                                                <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fe-shield font-24 mb-2 d-block text-success"></i>
                                        No IP addresses are currently blocked.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>

@foreach($data as $key => $value)
<!-- Modal -->
<div class="modal fade" id="ipEdit{{$value->id}}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fe-edit me-1 text-primary"></i> Edit Blocked IP Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ route('customers.ipblock.update') }}" method="POST" class="row" data-parsley-validate="">
            @csrf
            <input type="hidden" name="id" value="{{ $value->id }}">
            <div class="col-sm-12">
                <div class="form-group mb-3">
                    <label for="ip_no_{{ $value->id }}" class="form-label font-weight-bold">IP Address <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="ip_no" value="{{ $value->ip_no }}" id="ip_no_{{ $value->id }}" required="">
                </div>
            </div>
            <!-- col-end -->
            <div class="col-sm-12">
                <div class="form-group mb-3">
                    <label for="reason_{{ $value->id }}" class="form-label font-weight-bold">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="reason" id="reason_{{ $value->id }}" required="" rows="3">{{ $value->reason }}</textarea>
                </div>
            </div>
            <!-- col-end -->
            <div class="col-12 text-end">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success waves-effect waves-light ms-1">Save Changes</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endforeach
@endsection

@section('script')
<!-- third party js -->
<script src="{{asset('backEnd/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-select/js/dataTables.select.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/pdfmake/build/pdfmake.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/pdfmake/build/vfs_fonts.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/datatables.init.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/parsleyjs/parsley.min.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/form-validation.init.js')}}"></script>
<!-- third party js ends -->
@endsection