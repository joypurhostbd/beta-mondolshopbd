@extends('backEnd.layouts.master') 
@section('title', 'Shipping Charge Manage') 

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
                    <a href="{{ route('shippingcharges.create') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-plus-circle me-1"></i> Add Shipping Charge
                    </a>
                </div>
                <h4 class="page-title">Shipping Charge Management</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- KPI Summary Cards -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card widget-rounded-circle">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-truck font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-8 text-end">
                            <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_rates ?? 0 }}</span></h3>
                            <p class="text-muted mb-1 text-truncate">Total Zones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card widget-rounded-circle">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-soft-success border-success border">
                                <i class="fe-check-circle font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col-8 text-end">
                            <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $active_rates ?? 0 }}</span></h3>
                            <p class="text-muted mb-1 text-truncate">Active Rates</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card widget-rounded-circle">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-eye-off font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col-8 text-end">
                            <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $inactive_rates ?? 0 }}</span></h3>
                            <p class="text-muted mb-1 text-truncate">Inactive Rates</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card widget-rounded-circle">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                <i class="fe-dollar-sign font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-8 text-end">
                            <h3 class="text-dark mt-1">৳{{ number_format($avg_rate ?? 0, 0) }}</h3>
                            <p class="text-muted mb-1 text-truncate">Avg Active Charge</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End KPI Summary Cards -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Shipping Area Name</th>
                                <th>Delivery Charge (Amount)</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($show_data as $key => $value)
                            <tr style="vertical-align: middle;">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <strong class="text-dark">{{ $value->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-soft-primary text-primary fs-6 px-2 py-1">
                                        ৳ {{ number_format($value->amount, 2) }}
                                    </span>
                                </td>
                                <td>
                                    @if($value->status == 1)
                                        <span class="badge bg-soft-success text-success px-2 py-1">Active</span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger px-2 py-1">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="button-list">
                                        @if($value->status == 1)
                                        <form method="post" action="{{ route('shippingcharges.inactive') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" value="{{ $value->id }}" name="hidden_id" />
                                            <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                        </form>
                                        @else
                                        <form method="post" action="{{ route('shippingcharges.active') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" value="{{ $value->id }}" name="hidden_id" />
                                            <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                        </form>
                                        @endif

                                        <a href="{{ route('shippingcharges.edit', $value->id) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Rate"><i class="fe-edit-1"></i></a>

                                        <form method="post" action="{{ route('shippingcharges.destroy') }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" value="{{ $value->id }}" name="hidden_id" />
                                            <button type="button" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Rate"><i class="mdi mdi-close"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- end card body-->
            </div>
            <!-- end card -->
        </div>
        <!-- end col-->
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
