@extends('backEnd.layouts.master')
@section('title', 'Tag Manager Manage')
@section('css')
<link href="{{asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .badge-event { font-size: 10px; padding: 2px 5px; margin: 1px; }
</style>
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
                        <li class="breadcrumb-item"><a href="{{ route('tagmanagers.index') }}">Settings</a></li>
                        <li class="breadcrumb-item active">Tag Manager</li>
                    </ol>
                    <a href="{{route('tagmanagers.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Add Tag Manager</a>
                </div>
                <h4 class="page-title">Google Tag Manager Configuration</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    @if(!empty($has_multiple_active))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fe-info me-2 font-16"></i>
                <strong>Notice:</strong> You have <strong>{{ $active_tags }}</strong> active Google Tag Manager containers. All active containers will inject simultaneously into your storefront.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="row">
        <div class="col-sm-6 col-md-4 col-xl">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-tag font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_tags ?? $data->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Tags</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-xl">
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
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $active_tags ?? $data->where('status', 1)->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Tags</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-xl">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-warning border-warning border">
                                <i class="fe-pause-circle font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $inactive_tags ?? $data->where('status', 0)->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Inactive Tags</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-xl">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-purple border border-purple">
                                <i class="fe-server font-22 avatar-title text-purple"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $server_side_count ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Server-Side sGTM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-4 col-xl">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-info border-info border">
                                <i class="fe-code font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h4 class="mt-1 text-truncate" title="{{ $latest_tag ?? 'None' }}">
                                    <span class="badge bg-soft-info text-info font-13">{{ $latest_tag ?? 'None' }}</span>
                                </h4>
                                <p class="text-muted mb-1 text-truncate">Active GTM ID</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end KPI Summary Cards -->

   <div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Tag Manager Container</th>
                            <th>Tracking Mode</th>
                            <th>Active Events Matrix</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                
                    <tbody>
                        @foreach($data as $key=>$value)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>
                                <div>
                                    @if(!empty($value->title))
                                    <div class="fw-semibold text-dark">{{ $value->title }}</div>
                                    @endif
                                    <div class="d-inline-flex align-items-center mt-1">
                                        <code>{{$value->code}}</code>
                                        <button type="button" class="btn btn-xs btn-outline-secondary ms-2 copy-btn p-0 px-1" data-clipboard-text="{{$value->code}}" title="Copy Container ID">
                                            <i class="fe-copy font-11"></i>
                                        </button>
                                    </div>
                                    @if($value->hasServerSide())
                                    <div class="text-muted font-11 mt-1">
                                        <i class="fe-link me-1"></i>{{ Str::limit($value->server_container_url, 35) }}
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($value->hasServerSide())
                                    <span class="badge bg-primary"><i class="fe-server me-1"></i>Dual-Track (Web + sGTM)</span>
                                    @if($value->custom_loader_domain)
                                    <span class="badge bg-soft-success text-success d-block mt-1 font-10">First-Party Domain</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary"><i class="fe-globe me-1"></i>Web Client-Side</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 280px;">
                                    @if($value->eventConfigs && $value->eventConfigs->isNotEmpty())
                                        @foreach($value->eventConfigs as $ev)
                                            @if($ev->is_web_enabled || $ev->is_server_enabled)
                                            <span class="badge bg-light text-dark border badge-event" title="Web: {{ $ev->is_web_enabled ? 'ON' : 'OFF' }} | Server: {{ $ev->is_server_enabled ? 'ON' : 'OFF' }}">
                                                {{ ucfirst(str_replace('_', ' ', $ev->event_key)) }}
                                                @if($ev->is_server_enabled)<i class="fe-server text-primary ms-1 font-9"></i>@endif
                                            </span>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted font-11">All Standard Events Active</span>
                                    @endif
                                </div>
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
                                    <form method="post" action="{{route('tagmanagers.inactive')}}" class="d-inline"> 
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                        <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                    </form>
                                    @else
                                    <form method="post" action="{{route('tagmanagers.active')}}" class="d-inline">
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                        <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                    </form>
                                    @endif

                                    <a href="{{route('tagmanagers.edit',$value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit"><i class="fe-edit-1"></i></a>

                                    <form method="post" action="{{route('tagmanagers.destroy')}}" class="d-inline">        
                                        @csrf
                                        <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                        <button type="button" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete"><i class="mdi mdi-close"></i></button>
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

<script type="text/javascript">
    $(document).ready(function() {
        $(document).on('click', '.delete-confirm', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            if (typeof swal !== 'undefined') {
                swal({
                    title: "Are you sure you want to delete this record?",
                    text: "If you delete this, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then(function (willDelete) {
                    if (willDelete) {
                        form.submit();
                    }
                });
            } else if (confirm("Are you sure you want to delete this record?")) {
                form.submit();
            }
        });

        $(document).on('click', '.change-confirm', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            if (typeof swal !== 'undefined') {
                swal({
                    title: "Are you sure you want to change this record?",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then(function (willChange) {
                    if (willChange) {
                        form.submit();
                    }
                });
            } else if (confirm("Are you sure you want to change this record?")) {
                form.submit();
            }
        });

        $(document).on('click', '.copy-btn', function () {
            var text = $(this).attr('data-clipboard-text');
            var btn = $(this);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    var originalHtml = btn.html();
                    btn.html('<i class="fe-check text-success font-11"></i>');
                    setTimeout(function () { btn.html(originalHtml); }, 1500);
                });
            } else {
                var tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(text).select();
                document.execCommand('copy');
                tempInput.remove();
                var originalHtml = btn.html();
                btn.html('<i class="fe-check text-success font-11"></i>');
                setTimeout(function () { btn.html(originalHtml); }, 1500);
            }
        });
    });
</script>
@endsection