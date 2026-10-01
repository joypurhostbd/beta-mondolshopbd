@extends('backEnd.layouts.master')
@section('title', 'Pending Reviews')

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
                    <a href="{{route('reviews.index')}}" class="btn btn-outline-primary rounded-pill me-1"><i class="fe-list me-1"></i> All Reviews</a>
                    <a href="{{route('reviews.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Add Review</a>
                </div>
                <h4 class="page-title">Pending Reviews (<span>{{ $metrics['pending'] ?? count($data) }}</span>)</h4>
            </div>
        </div>
    </div>        
    <!-- end page title --> 

    <!-- Navigation Tabs / Pills -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{route('reviews.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="fe-message-square me-1"></i> All Reviews ({{ $metrics['total'] ?? 0 }})
                </a>
                <a href="{{route('reviews.pending')}}" class="btn btn-warning btn-sm rounded-pill text-dark fw-semibold">
                    <i class="fe-clock me-1"></i> Pending Approval ({{ $metrics['pending'] ?? count($data) }})
                </a>
            </div>
        </div>
    </div>

    <!-- Review Overview Metrics -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-message-square font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['total'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Reviews</p>
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
                                <i class="fe-clock font-20 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['pending'] ?? count($data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Pending Approval</p>
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
                                <h4 class="text-dark my-0">{{ $metrics['active'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Active Reviews</p>
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
                                <i class="fe-star font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['avg_rating'] ?? '0.0' }} / 5.0</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Average Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <div class="card-body">
                    <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100 align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">SL</th>
                                <th>Product</th>
                                <th>Reviewer</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Status</th>
                                <th style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                    
                        <tbody>
                            @foreach($data as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($value->product && $value->product->image)
                                            <img src="{{ asset($value->product->image->image) }}" class="rounded border shadow-sm" width="40" height="40" style="object-fit: cover;" alt="product">
                                        @else
                                            <div class="avatar-xs rounded bg-light border d-flex align-items-center justify-content-center text-muted">
                                                <i class="fe-image font-14"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-semibold text-dark">{{ $value->product->name ?? 'Product #'.$value->product_id }}</span>
                                            @if($value->product && $value->product->product_code)
                                                <div class="text-muted font-11">SKU: {{ $value->product->product_code }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $value->name ?? 'Anonymous' }}</div>
                                    <div class="text-muted font-11">{{ $value->email ?? 'No email' }}</div>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= (int)$value->ratting)
                                                <i class="mdi mdi-star"></i>
                                            @else
                                                <i class="mdi mdi-star-outline text-muted"></i>
                                            @endif
                                        @endfor
                                        <span class="text-dark font-12 fw-semibold ms-1">({{ $value->ratting }})</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-wrap d-inline-block" style="max-width: 280px;" title="{{ $value->review }}">
                                        {{ Str::limit($value->review, 80) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-soft-warning text-warning fw-semibold px-2 py-1">
                                        <i class="fe-clock me-1"></i>Pending
                                    </span>
                                </td>
                                <td>
                                    <div class="button-list">
                                        <form method="post" action="{{route('reviews.active')}}" class="d-inline">
                                            @csrf
                                            <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                            <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Approve Review">
                                                <i class="fe-check"></i> Approve
                                            </button>
                                        </form>

                                        <a href="{{route('reviews.edit', $value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Review">
                                            <i class="fe-edit-1"></i>
                                        </a>

                                        <form method="post" action="{{route('reviews.destroy')}}" class="d-inline">        
                                            @csrf
                                            <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                            <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Review">
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