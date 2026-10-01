@extends('backEnd.layouts.master')
@section('title', 'General Setting Manage')

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
                    <a href="{{route('settings.create')}}" class="btn btn-primary rounded-pill waves-effect waves-light"><i class="fe-plus me-1"></i> Create New Setting</a>
                </div>
                <h4 class="page-title">General Setting Manage</h4>
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
                                <i class="fe-sliders font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['total_settings'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Profiles</p>
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
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['active_settings'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active Profile</p>
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
                                <i class="fe-power font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $kpis['inactive_settings'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Inactive Profiles</p>
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
                                <i class="fe-globe font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h4 class="text-dark mt-1 text-truncate font-16">{{ $kpis['active_brand_name'] ?? 'Not Configured' }}</h4>
                                <p class="text-muted mb-1 text-truncate">Active Brand</p>
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
                    <div class="table-responsive">
                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Company Name</th>
                                    <th>White Logo</th>
                                    <th>Dark Logo</th>
                                    <th>Favicon</th>
                                    <th>Copyright</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($show_data as $key => $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $value->name }}</strong>
                                        <div class="mt-1 d-flex align-items-center gap-1" title="বাটন ও ব্যাজ কালার থিম">
                                            <span class="badge" style="background-color: {{ $value->order_btn_bg_color ?: '#fe5200' }}; color: {{ $value->order_btn_text_color ?: '#ffffff' }}; font-size: 10px; padding: 2px 5px;" title="অর্ডার বাটন">
                                                {{ $value->order_btn_text ?: 'অর্ডার করুন' }}
                                            </span>
                                            <span class="badge" style="background-color: {{ $value->cart_btn_bg_color ?: '#2f3543' }}; color: {{ $value->cart_btn_text_color ?: '#ffffff' }}; font-size: 10px; padding: 2px 5px;" title="কার্ট বাটন">
                                                {{ $value->cart_btn_text ?: 'কার্টে যোগ' }}
                                            </span>
                                            <span class="badge" style="background-color: {{ $value->discount_badge_bg_color ?: '#ffffff' }}; color: {{ $value->discount_badge_text_color ?: '#fe5200' }}; border: 1px solid {{ $value->discount_badge_border_color ?: '#fe5200' }}; font-size: 10px; padding: 1px 4px;" title="ছাড় ব্যাজ">
                                                % {{ $value->discount_badge_text ?: 'ছাড়' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if(!empty($value->white_logo) && file_exists(public_path($value->white_logo)))
                                            <div class="p-1 bg-dark rounded d-inline-block">
                                                <img src="{{ asset($value->white_logo) }}" style="max-height: 35px; max-width: 100px; object-fit: contain;" alt="White Logo">
                                            </div>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary">No Logo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($value->dark_logo) && file_exists(public_path($value->dark_logo)))
                                            <div class="p-1 bg-light rounded d-inline-block border">
                                                <img src="{{ asset($value->dark_logo) }}" style="max-height: 35px; max-width: 100px; object-fit: contain;" alt="Dark Logo">
                                            </div>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary">No Logo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($value->favicon) && file_exists(public_path($value->favicon)))
                                            <img src="{{ asset($value->favicon) }}" style="width: 28px; height: 28px; object-fit: contain;" class="rounded border p-1" alt="Favicon">
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary">No Favicon</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $value->copyright ?: 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if($value->status == 1)
                                            <span class="badge bg-soft-success text-success font-12"><i class="fe-check-circle me-1"></i> Active</span>
                                        @else
                                            <span class="badge bg-soft-danger text-danger font-12"><i class="fe-x-circle me-1"></i> Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button-list">
                                            @if($value->status == 1)
                                                <form method="post" action="{{route('settings.inactive')}}" class="d-inline"> 
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                                    <button type="button" class="btn btn-xs btn-secondary waves-effect waves-light change-confirm" title="Deactivate"><i class="fe-thumbs-down"></i></button>
                                                </form>
                                            @else
                                                <form method="post" action="{{route('settings.active')}}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                                    <button type="button" class="btn btn-xs btn-success waves-effect waves-light change-confirm" title="Activate"><i class="fe-thumbs-up"></i></button>
                                                </form>
                                            @endif

                                            <a href="{{route('settings.edit', $value->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Setting"><i class="fe-edit-1"></i></a>

                                            <form method="post" action="{{route('settings.destroy')}}" class="d-inline">        
                                                @csrf
                                                <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                                <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete"><i class="fe-trash-2"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fe-sliders font-24 mb-2 d-block"></i>
                                        No general settings configured yet.
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