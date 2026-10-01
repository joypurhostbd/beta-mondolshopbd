@extends('backEnd.layouts.master')
@section('title', 'Social Media Manage')

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
                    <a href="{{route('socialmedias.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Create Social Link</a>
                </div>
                <h4 class="page-title">Social Media Manage</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI Metric Cards -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary rounded">
                            <i class="fe-share-2 avatar-title font-22 text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="my-0 font-weight-bold">{{ $total_social ?? $show_data->count() }}</h3>
                            <p class="text-muted mb-0">Total Channels</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success rounded">
                            <i class="fe-check-circle avatar-title font-22 text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="my-0 font-weight-bold text-success">{{ $active_social ?? 0 }}</h3>
                            <p class="text-muted mb-0">Active Channels</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning rounded">
                            <i class="fe-pause-circle avatar-title font-22 text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h3 class="my-0 font-weight-bold text-warning">{{ $inactive_social ?? 0 }}</h3>
                            <p class="text-muted mb-0">Inactive Channels</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-info rounded">
                            <i class="fe-globe avatar-title font-22 text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h4 class="my-0 font-weight-bold text-truncate" style="max-width: 150px;">{{ $latest_social ? $latest_social->title : 'None' }}</h4>
                            <p class="text-muted mb-0">Latest Active</p>
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
                            <th style="width: 60px;">SL</th>
                            <th>Platform Name</th>
                            <th>Icon Preview</th>
                            <th>Target URL</th>
                            <th>Status</th>
                            <th style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach($show_data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <span class="fw-bold text-dark">{{$value->title}}</span>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white shadow-sm" style="width: 36px; height: 36px; background-color: {{ $value->color ?: '#4b5563' }}; font-size: 16px;">
                                    <i class="{{$value->icon}}"></i>
                                </span>
                                <small class="text-muted ms-1 font-monospace">{{$value->icon}}</small>
                            </td>
                            <td>
                                @if($value->link)
                                    <a href="{{$value->link}}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none">
                                        <i class="fe-external-link me-1"></i>{{ Str::limit($value->link, 40) }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($value->status == 1)
                                    <span class="badge bg-soft-success text-success"><i class="fe-check-circle me-1"></i>Active</span>
                                @else 
                                    <span class="badge bg-soft-danger text-danger"><i class="fe-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="button-list">
                                    @if($value->status == 1)
                                    <form method="post" action="{{route('socialmedias.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('socialmedias.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('socialmedias.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('socialmedias.destroy')}}" class="d-inline">        
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