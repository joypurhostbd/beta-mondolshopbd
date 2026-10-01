@extends('backEnd.layouts.master')
@section('title', 'Header, Footer & Code Snippets Manager')

@section('css')
<link href="{{asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('snippets.create')}}" class="btn btn-primary rounded-pill waves-effect waves-light">
                        <i class="fe-plus me-1"></i> Add New Snippet
                    </a>
                </div>
                <h4 class="page-title">Code Snippets & Custom Scripts</h4>
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
                                <i class="fe-code font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span>{{ $kpis['total'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Snippets</p>
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
                                <h3 class="text-dark mt-1"><span>{{ $kpis['active'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Active</p>
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
                                <i class="fe-arrow-up-circle font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span>{{ $kpis['head'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Header Scripts</p>
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
                                <i class="fe-arrow-down-circle font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span>{{ $kpis['footer'] ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Footer Scripts</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Snippets list -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted font-14 mb-3">
                        Manage all external tracking pixels (Google Analytics, TikTok, Clarity), custom CSS, and JavaScript without editing theme files. Cached for zero performance overhead.
                    </p>
                    <div class="table-responsive">
                        <table id="datatable-buttons" class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Target Pages</th>
                                    <th>Device</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($snippets as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $item->title }}</strong>
                                        @if(!empty($item->description))
                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->type->value === 'javascript')
                                            <span class="badge bg-soft-warning text-warning">JavaScript</span>
                                        @elseif($item->type->value === 'css')
                                            <span class="badge bg-soft-info text-info">CSS</span>
                                        @elseif($item->type->value === 'html')
                                            <span class="badge bg-soft-primary text-primary">HTML / Script</span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary">Text</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->location->value === 'head')
                                            <span class="badge bg-soft-info text-info"><i class="fe-arrow-up me-1"></i> Header (&lt;head&gt;)</span>
                                        @elseif($item->location->value === 'body_open')
                                            <span class="badge bg-soft-primary text-primary"><i class="fe-maximize me-1"></i> Body Open (&lt;body&gt;)</span>
                                        @else
                                            <span class="badge bg-soft-warning text-warning"><i class="fe-arrow-down me-1"></i> Footer (&lt;/body&gt;)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->targetPages === 'all')
                                            <span class="badge bg-soft-secondary text-secondary">All Pages</span>
                                        @elseif($item->targetPages === 'homepage')
                                            <span class="badge bg-soft-success text-success">Homepage Only</span>
                                        @elseif($item->targetPages === 'checkout')
                                            <span class="badge bg-soft-purple text-purple">Checkout Only</span>
                                        @elseif($item->targetPages === 'thank_you')
                                            <span class="badge bg-soft-pink text-pink">Thank You Page</span>
                                        @else
                                            <span class="badge bg-soft-dark text-dark">Custom URLs</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted text-capitalize">{{ $item->deviceTarget->value }}</small>
                                    </td>
                                    <td>{{ $item->priority }}</td>
                                    <td>
                                        @if($item->status)
                                            <span class="badge bg-soft-success text-success font-12"><i class="fe-check-circle me-1"></i> Active</span>
                                        @else
                                            <span class="badge bg-soft-danger text-danger font-12"><i class="fe-x-circle me-1"></i> Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="button-list">
                                            @if($item->status)
                                                <form method="post" action="{{route('snippets.inactive')}}" class="d-inline"> 
                                                    @csrf
                                                    <input type="hidden" value="{{$item->id}}" name="hidden_id">       
                                                    <button type="submit" class="btn btn-xs btn-secondary waves-effect waves-light" title="Deactivate"><i class="fe-power"></i></button>
                                                </form>
                                            @else
                                                <form method="post" action="{{route('snippets.active')}}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" value="{{$item->id}}" name="hidden_id">        
                                                    <button type="submit" class="btn btn-xs btn-success waves-effect waves-light" title="Activate"><i class="fe-check"></i></button>
                                                </form>
                                            @endif

                                            <a href="{{route('snippets.edit', $item->id)}}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Snippet"><i class="fe-edit-1"></i></a>

                                            <form method="post" action="{{route('snippets.destroy')}}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this snippet?');">        
                                                @csrf
                                                <input type="hidden" value="{{$item->id}}" name="hidden_id">
                                                <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light" title="Delete"><i class="fe-trash-2"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fe-code font-24 mb-2 d-block"></i>
                                        No code snippets added yet. Click <strong>Add New Snippet</strong> to insert your first tracking code or custom script.
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
<script src="{{asset('backEnd/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#datatable-buttons').DataTable({
            responsive: true,
            order: [[6, 'asc']]
        });
    });
</script>
@endsection
