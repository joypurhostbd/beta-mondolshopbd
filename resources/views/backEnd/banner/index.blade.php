@extends('backEnd.layouts.master')
@section('title', 'Banner Manage')
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
                        <li class="breadcrumb-item"><a href="{{ route('banner_category.index') }}">Banners</a></li>
                        <li class="breadcrumb-item active">Manage</li>
                    </ol>
                    <a href="{{route('banners.create')}}" class="btn btn-primary rounded-pill">
                        <i class="fe-plus me-1"></i> Add Banner
                    </a>
                </div>
                <h4 class="page-title">Banner Configuration</h4>
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
                                <i class="fe-image font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_banners ?? $data->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Banners</p>
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
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $active_banners ?? $data->where('status', 1)->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Banners</p>
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
                                <i class="fe-x-circle font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $inactive_banners ?? $data->where('status', 0)->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Inactive Banners</p>
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
                                <i class="fe-layers font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_categories ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Categories</p>
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
            <div class="card-body">
                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Category</th>
                            <th>Image Preview</th>
                            <th>Target Link</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach($data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                @if($value->category)
                                    <span class="badge bg-soft-primary text-primary px-2 py-1">
                                        <i class="fe-tag me-1"></i>{{ $value->category->name }}
                                    </span>
                                @else
                                    <span class="badge bg-soft-secondary text-secondary">Uncategorized</span>
                                @endif
                            </td>
                            <td>
                                @if($value->image)
                                    <img src="{{asset($value->image)}}" class="backend-image rounded border" style="max-height: 50px; max-width: 120px; object-fit: contain;" alt="Banner Image">
                                @else
                                    <span class="text-muted font-12"><i class="fe-image me-1"></i>No Image</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $value->link }}" target="_blank" class="text-primary font-12 text-truncate d-inline-block" style="max-width: 200px;" title="{{ $value->link }}">
                                    <i class="fe-external-link me-1"></i>{{ $value->link }}
                                </a>
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
                                    <form method="post" action="{{route('banners.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('banners.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('banners.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('banners.destroy')}}" class="d-inline">        
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