@extends('backEnd.layouts.master')
@section('title', 'Landing Page Manage')

@section('css')
    <link href="{{ asset('backEnd/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .copy-url-btn {
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-url-btn:hover {
            color: #1abc9c !important;
        }
        .banner-thumb {
            width: 60px;
            height: 38px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.2s;
        }
        .banner-thumb:hover {
            transform: scale(1.1);
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('campaign.create') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-plus me-1"></i> Create Landing Page
                    </a>
                </div>
                <h4 class="page-title">Landing Pages (<span>{{ $metrics['total'] ?? count($show_data) }}</span>)</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI Summary Metrics -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-layout font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['total'] ?? count($show_data) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Landing Pages</p>
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
                                <p class="text-muted mb-0 font-12 text-truncate">Active Running Pages</p>
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
                                <i class="fe-pause-circle font-20 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['inactive'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Draft / Inactive Pages</p>
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
                                <i class="fe-camera font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['reviews_count'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Customer Proof Images</p>
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
                                <th style="width: 35px;">SL</th>
                                <th style="width: 70px;">Banner</th>
                                <th>Landing Page & Public Link</th>
                                <th>Linked Product & Stock</th>
                                <th>Highlights</th>
                                <th>Status</th>
                                <th style="width: 140px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                    
                        <tbody>
                            @foreach($show_data as $key => $value)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if(!empty($value->image_one))
                                        <img src="{{ asset($value->image_one) }}" class="banner-thumb border shadow-sm" alt="banner">
                                    @else
                                        <div class="avatar-xs rounded bg-light border d-flex align-items-center justify-content-center text-muted">
                                            <i class="fe-image font-14"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark font-14 mb-1">{{ $value->name }}</div>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('campaign', $value->slug) }}" target="_blank" class="text-primary font-11 text-decoration-none" title="Open Public Page">
                                            <i class="fe-external-link me-1"></i>/campaign/{{ $value->slug }}
                                        </a>
                                        <button type="button" class="btn btn-link p-0 text-muted copy-url-btn font-12" data-url="{{ route('campaign', $value->slug) }}" title="Click to Copy Full URL">
                                            <i class="fe-copy"></i> Copy
                                        </button>
                                    </div>
                                    @if(!empty($value->offer_title))
                                        <small class="text-muted d-block mt-1 font-11 text-truncate" style="max-width: 280px;">{{ $value->offer_title }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($value->product)
                                        <div class="text-dark fw-semibold font-13">{{ $value->product->name }}</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="font-12 fw-bold text-success">৳{{ $value->special_price ? $value->special_price : $value->product->new_price }}</span>
                                            @if($value->special_price && $value->special_price < $value->product->new_price)
                                                <del class="text-muted font-11">৳{{ $value->product->new_price }}</del>
                                            @endif
                                            
                                            <!-- Stock Alert -->
                                            @if(($value->product->stock ?? 0) > 0)
                                                <span class="badge bg-soft-success text-success font-10">In Stock ({{ $value->product->stock }})</span>
                                            @else
                                                <span class="badge bg-danger text-white font-10"><i class="fe-alert-circle me-1"></i>Out of Stock</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="badge bg-soft-secondary text-secondary">No Product Linked</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-soft-info text-info font-11" title="Customer Proof Images">
                                            <i class="fe-camera me-1"></i>{{ $value->images_count ?? $value->images->count() }} Images
                                        </span>
                                        @if(!empty($value->video_url))
                                            <span class="badge bg-soft-danger text-danger font-11" title="Has YouTube Showcase Video">
                                                <i class="fe-video me-1"></i>Video
                                            </span>
                                        @endif
                                        @if(!empty($value->free_shipping))
                                            <span class="badge bg-soft-primary text-primary font-11" title="Free Delivery Campaign">
                                                <i class="fe-truck me-1"></i>Free Del.
                                            </span>
                                        @endif
                                        @if(!empty($value->end_date))
                                            @if($value->is_expired)
                                                <span class="badge bg-soft-danger text-danger font-11" title="Campaign Expired">
                                                    <i class="fe-clock me-1"></i>Expired
                                                </span>
                                            @else
                                                <span class="badge bg-soft-warning text-warning font-11" title="Countdown Timer Running">
                                                    <i class="fe-clock me-1"></i>{{ date('d M, Y', strtotime($value->end_date)) }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <!-- Instant AJAX Status Switch -->
                                    <div class="form-check form-switch d-flex align-items-center gap-2">
                                        <input class="form-check-input status-switch" type="checkbox" role="switch" 
                                               id="status_{{ $value->id }}" 
                                               data-id="{{ $value->id }}" 
                                               {{ $value->status == 1 ? 'checked' : '' }} 
                                               style="cursor: pointer; width: 34px; height: 18px;">
                                        <span class="status-label font-12 fw-semibold {{ $value->status == 1 ? 'text-success' : 'text-muted' }}" id="status_label_{{ $value->id }}">
                                            {{ $value->status == 1 ? 'Active' : 'Draft' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="button-list">
                                        <!-- View Live Page -->
                                        <a href="{{ route('campaign', $value->slug) }}" class="btn btn-xs btn-info waves-effect waves-light" target="_blank" title="View Live Landing Page">
                                            <i class="fe-external-link"></i>
                                        </a>

                                        <!-- Duplicate Campaign -->
                                        <form method="POST" action="{{ route('campaign.clone', $value->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-secondary waves-effect waves-light" title="Duplicate / Clone Campaign for A/B Testing">
                                                <i class="fe-copy"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Campaign -->
                                        <a href="{{ route('campaign.edit', $value->id) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="Edit Landing Page">
                                            <i class="fe-edit-1"></i>
                                        </a>

                                        <!-- Delete Campaign -->
                                        <form method="POST" action="{{ route('campaign.destroy') }}" class="d-inline">        
                                            @csrf
                                            <input type="hidden" value="{{ $value->id }}" name="hidden_id">
                                            <button type="submit" class="btn btn-xs btn-danger waves-effect waves-light delete-confirm" title="Delete Landing Page">
                                                <i class="fe-trash-2"></i>
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

    <script type="text/javascript">
        $(document).ready(function() {
            // 1-Click Copy Campaign URL with Clipboard API
            $('.copy-url-btn').on('click', function(e) {
                e.preventDefault();
                var url = $(this).data('url');
                var $btn = $(this);

                navigator.clipboard.writeText(url).then(function() {
                    var originalHtml = $btn.html();
                    $btn.html('<i class="fe-check text-success"></i> Copied!');
                    toastr.success('Campaign URL copied to clipboard: ' + url);
                    setTimeout(function() {
                        $btn.html(originalHtml);
                    }, 2000);
                }).catch(function(err) {
                    toastr.error('Failed to copy URL');
                });
            });

            // Instant AJAX Status Switch Toggle
            $('.status-switch').on('change', function() {
                var campaignId = $(this).data('id');
                var isChecked = $(this).is(':checked');
                var $switch = $(this);
                var $label = $('#status_label_' + campaignId);

                $.ajax({
                    url: "{{ route('campaign.toggle-status') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: campaignId
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.status == 1) {
                                $label.text('Active').removeClass('text-muted').addClass('text-success');
                            } else {
                                $label.text('Draft').removeClass('text-success').addClass('text-muted');
                            }
                            toastr.success(response.message || 'Status updated successfully');
                        } else {
                            $switch.prop('checked', !isChecked);
                            toastr.error(response.message || 'Failed to update status');
                        }
                    },
                    error: function(xhr) {
                        $switch.prop('checked', !isChecked);
                        toastr.error('Error updating campaign status');
                    }
                });
            });
        });
    </script>
@endsection