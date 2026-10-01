@extends('backEnd.layouts.master')
@section('title','Brand Manage')
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
                    <a href="{{route('brands.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Add Brand</a>
                </div>
                <h4 class="page-title">Brand Manage (<span>{{ $metrics['total'] ?? count($data) }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- Navigation Pills -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{route('categories.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-grid me-1"></i> Categories</a>
                <a href="{{route('brands.index')}}" class="btn btn-primary btn-sm rounded-pill"><i class="fe-tag me-1"></i> Brands ({{ $metrics['total'] ?? count($data) }})</a>
                <a href="{{route('colors.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-droplet me-1"></i> Colors</a>
                <a href="{{route('sizes.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-maximize-2 me-1"></i> Sizes</a>
            </div>
        </div>
    </div>

    <!-- Brand Overview Metrics -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-tag font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['total'] ?? count($data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Brands</p>
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
                                <h4 class="text-dark my-0">{{ $metrics['active'] ?? $data->where('status', 1)->count() }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Active Brands</p>
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
                                <h4 class="text-dark my-0">{{ $metrics['inactive'] ?? $data->where('status', 0)->count() }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Inactive Brands</p>
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
                                <p class="text-muted mb-0 font-12 text-truncate">Brands With Products</p>
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
                            <th>Brand Info</th>
                            <th>Products</th>
                            <th>Logo</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach($data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-dark">{{$value->name}}</span>
                                    <small class="text-muted">Slug: {{$value->slug}}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-soft-secondary text-secondary" title="Products Count">
                                    <i class="fe-package me-1"></i>{{ $value->products_count ?? 0 }} Products
                                </span>
                            </td>
                            <td>
                                @if($value->image)
                                <img src="{{asset($value->image)}}" class="backend-image rounded border" alt="{{$value->name}}" onerror="this.onerror=null;this.src='{{asset('public/uploads/default/brand.png')}}';">
                                @else
                                <span class="badge bg-soft-light text-muted border">No Logo</span>
                                @endif
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
                                    <form method="post" action="{{route('brands.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Set Inactive"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('brands.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Set Active"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('brands.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Brand"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('brands.destroy')}}" class="d-inline">        
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                        <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Brand"><i class="mdi mdi-close"></i></button>
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