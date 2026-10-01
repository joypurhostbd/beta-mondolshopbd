@extends('backEnd.layouts.master')
@section('title', 'Pixels & CAPI Manage')
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
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                        <li class="breadcrumb-item active">Pixels &amp; CAPI</li>
                    </ol>
                    <a href="{{route('pixels.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Create Pixel</a>
                </div>
                <h4 class="page-title">Facebook Pixel &amp; Conversions API (CAPI) Configuration</h4>
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
                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-activity font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_pixels ?? $data->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Pixels</p>
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
                                <i class="fe-check-circle font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $active_pixels ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Pixels</p>
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
                                <i class="fe-server font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1">
                                    <span data-plugin="counterup">{{ $data->where('capi_status', 1)->whereNotNull('access_token')->count() }}</span>
                                </h3>
                                <p class="text-muted mb-1 text-truncate">CAPI Enabled</p>
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
                                <i class="mdi mdi-facebook font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h4 class="mt-1 text-truncate" title="{{ $latest_pixel ? $latest_pixel->code : 'None' }}">
                                    <span class="badge bg-soft-primary text-primary font-13">{{ $latest_pixel ? $latest_pixel->code : 'None' }}</span>
                                </h4>
                                <p class="text-muted mb-1 text-truncate">Active Pixel ID</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End KPI Metric Cards -->

   <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">SL</th>
                            <th>Facebook Pixel ID</th>
                            <th>Conversions API (CAPI)</th>
                            <th>Test Code</th>
                            <th>Browser Pixel</th>
                            <th>Created At</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach($data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <span class="fw-bold text-dark"><i class="mdi mdi-facebook text-primary me-1"></i>{{$value->code}}</span>
                            </td>
                            <td>
                                @if(!empty($value->access_token) && ($value->capi_status ?? 1) == 1)
                                    <span class="badge bg-soft-success text-success"><i class="fe-check-circle me-1"></i>Server-Side Active</span>
                                @elseif(!empty($value->access_token))
                                    <span class="badge bg-soft-warning text-warning"><i class="fe-pause-circle me-1"></i>CAPI Paused</span>
                                @else
                                    <span class="badge bg-soft-secondary text-secondary"><i class="fe-x-circle me-1"></i>Token Not Set</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($value->test_event_code))
                                    <span class="badge bg-soft-info text-info"><i class="fe-code me-1"></i>{{ $value->test_event_code }}</span>
                                @else
                                    <span class="text-muted font-12">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($value->status == 1)
                                    <span class="badge bg-soft-success text-success"><i class="fe-check-circle me-1"></i>Active</span>
                                @else 
                                    <span class="badge bg-soft-danger text-danger"><i class="fe-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td>{{ $value->created_at ? $value->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            <td>
                                <div class="button-list">
                                    @if($value->status == 1)
                                    <form method="post" action="{{route('pixels.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('pixels.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('pixels.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('pixels.destroy')}}" class="d-inline">        
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                        <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete"><i class="mdi mdi-close"></i></button>
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
<!-- third party js ends -->
@endsection