@extends('backEnd.layouts.master')
@section('title','Color Manage')

@section('css')
<link href="{{asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start Color title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('colors.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Add Color</a>
                </div>
                <h4 class="page-title">Color Manage (<span>{{ $metrics['total'] ?? count($show_data) }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end Color title --> 

    <!-- Navigation Pills -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{route('categories.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-grid me-1"></i> Categories</a>
                <a href="{{route('brands.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-tag me-1"></i> Brands</a>
                <a href="{{route('colors.index')}}" class="btn btn-primary btn-sm rounded-pill"><i class="fe-droplet me-1"></i> Colors ({{ $metrics['total'] ?? count($show_data) }})</a>
                <a href="{{route('sizes.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-maximize-2 me-1"></i> Sizes</a>
            </div>
        </div>
    </div>

    <!-- Color Overview Metrics -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-droplet font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['total'] ?? count($show_data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Colors</p>
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
                                <h4 class="text-dark my-0">{{ $metrics['active'] ?? $show_data->where('status', 1)->count() }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Active Colors</p>
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
                                <i class="fe-x-circle font-20 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['inactive'] ?? $show_data->where('status', 0)->count() }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Inactive Colors</p>
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
                                <i class="fe-package font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['with_products'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Colors With Products</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">SL</th>
                            <th>Color Swatch & Name</th>
                            <th>Hex Code</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>               
                
                    <tbody>
                        @foreach($show_data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded border shadow-sm" style="background-color: {{ $value->color }}; width: 26px; height: 26px;"></span>
                                    <span class="fw-semibold text-dark">{{$value->colorName}}</span>
                                </div>
                            </td>
                            <td>
                                <code>{{ $value->color }}</code>
                            </td>
                            <td>
                                <span class="badge bg-soft-secondary text-secondary" title="Products Count">
                                    <i class="fe-package me-1"></i>{{ $value->products_count ?? 0 }} Products
                                </span>
                            </td>
                            
                            <td>
                                @if($value->status==1)
                                <span class="badge bg-soft-success text-success">Active</span> 
                                @else 
                                <span class="badge bg-soft-danger text-danger">Inactive</span> 
                                @endif
                            </td>
                            <td>
                                <div class="button-list">
                                    @if($value->status == 1)
                                    <form method="post" action="{{route('colors.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Set Inactive"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('colors.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Set Active"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('colors.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Color"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('colors.destroy')}}" class="d-inline">        
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                        <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Color"><i class="mdi mdi-close"></i></button>
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