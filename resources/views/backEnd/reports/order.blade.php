@extends('backEnd.layouts.master')
@section('title', 'Order Report')

@section('css')
<link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backEnd/assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    p {
        margin: 0;
    }
    @media print {
        body {
            background-color: #fff !important;
            font-size: 13px !important;
            color: #000 !important;
        }
        .container-fluid {
            width: 100% !important;
            padding: 0 !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .card-body {
            padding: 0 !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        .table th, .table td {
            border: 1px solid #ddd !important;
            padding: 6px 8px !important;
        }
        .no-print, .left-side-menu, .navbar-custom, .footer, .page-title-box {
            display: none !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 20px;
            text-align: center;
        }
    }
    .print-header {
        display: none;
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
                    <ol class="breadcrumb m-0 me-2 d-none d-md-inline-flex">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reports</a></li>
                        <li class="breadcrumb-item active">Order Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Sales & Order Report</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- KPI Metric Cards -->
    <div class="row no-print">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-shopping-bag font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ number_format($total_orders_count ?? 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Orders</p>
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
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ number_format($total_item ?? 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Items Sold</p>
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
                                <i class="fe-dollar-sign font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1">৳ <span data-plugin="counterup">{{ number_format($total_sales ?? 0, 2) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Sales Value</p>
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
                                <i class="fe-trending-up font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1">৳ <span data-plugin="counterup">{{ number_format($total_profit ?? 0, 2) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Estimated Profit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end KPI cards -->

    <!-- Print Header -->
    <div class="print-header">
        <h2>{{ $generalsetting->name ?? config('app.name', 'Mondol Shop BD') }}</h2>
        <h4>Sales & Order Report</h4>
        <p>Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        @if(request()->filled('start_date') || request()->filled('end_date'))
            <p>Period: {{ request()->get('start_date', 'Start') }} to {{ request()->get('end_date', 'Today') }}</p>
        @endif
        <hr/>
    </div>

    <!-- Filter Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.order_report') }}" class="no-print mb-4">
                        <div class="row g-2">   
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="keyword" class="form-label">Search Keyword</label>
                                    <input type="text" id="keyword" value="{{ request()->get('keyword') }}" class="form-control" name="keyword" placeholder="Product, invoice, customer, phone...">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="order_status" class="form-label">Order Status</label>
                                    <select class="form-control select2" id="order_status" name="order_status">
                                        <option value="">All Statuses</option>
                                        @foreach($order_statuses as $status)
                                            <option value="{{ $status->id }}" @if(request()->get('order_status') == $status->id) selected @endif>{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="user_id" class="form-label">Assigned User</label>
                                    <select class="form-control select2" id="user_id" name="user_id">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @if(request()->get('user_id') == $user->id) selected @endif>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="text" id="start_date" value="{{ request()->get('start_date') }}" class="form-control flatdate" name="start_date" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <div class="col-md-2 col-sm-6">
                                <div class="form-group">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="text" id="end_date" value="{{ request()->get('end_date') }}" class="form-control flatdate" name="end_date" placeholder="YYYY-MM-DD">
                                </div>
                            </div>

                            <div class="col-md-1 col-sm-12 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary" title="Apply Filter"><i class="fe-filter"></i></button>
                                    <a href="{{ route('admin.order_report') }}" class="btn btn-outline-secondary" title="Reset"><i class="fe-rotate-ccw"></i></a>
                                </div>
                            </div>
                        </div>  
                    </form>

                    <!-- Export & Print Actions -->
                    <div class="row mb-3 align-items-center">
                        <div class="col-sm-6 no-print">
                            <p class="text-muted mb-0">Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} records</p>
                        </div>
                        <div class="col-sm-6">
                            <div class="export-print text-end">
                                <button type="button" onclick="window.print()" class="no-print btn btn-success btn-sm me-1"><i class="fe-printer me-1"></i> Print</button>
                                <button type="button" id="export-csv-btn" class="no-print btn btn-info btn-sm"><i class="fe-download me-1"></i> Export CSV</button>
                            </div>
                        </div>
                    </div>

                    <!-- Report Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-centered table-nowrap mb-0 align-middle" id="order-report-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Invoice</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Product</th>
                                    <th class="text-end">Purchase (৳)</th>
                                    <th class="text-end">Sale (৳)</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total Sale (৳)</th>
                                    <th class="text-center">Status</th>
                                    <th>Assignee</th>
                                </tr>
                            </thead>               
                            <tbody>
                                @php
                                    $page_purchase = 0;
                                    $page_qty = 0;
                                    $page_sale = 0;
                                @endphp
                                @forelse($orders as $key => $item)
                                    @php
                                        $item_purchase_total = ($item->purchase_price ?? 0) * ($item->qty ?? 0);
                                        $item_sale_total = ($item->sale_price ?? 0) * ($item->qty ?? 0);
                                        $page_purchase += $item_purchase_total;
                                        $page_qty += ($item->qty ?? 0);
                                        $page_sale += $item_sale_total;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                        <td>
                                            @if($item->order && $item->order->invoice_id)
                                                <a href="{{ route('admin.orders', ['slug' => 'all']) }}?keyword={{ $item->order->invoice_id }}" class="fw-semibold text-primary" target="_blank">
                                                    #{{ $item->order->invoice_id }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->created_at ? $item->created_at->format('d-m-Y') : 'N/A' }}</td>
                                        <td>{{ $item->shipping->name ?? 'N/A' }}</td>
                                        <td>{{ $item->shipping->phone ?? 'N/A' }}</td>
                                        <td>
                                            <div class="fw-medium text-truncate" style="max-width: 200px;" title="{{ $item->product_name }}">
                                                {{ $item->product_name }}
                                            </div>
                                            @if($item->product_color || $item->product_size)
                                                <small class="text-muted">
                                                    @if($item->product_color) Color: {{ $item->product_color }} @endif
                                                    @if($item->product_size) Size: {{ $item->product_size }} @endif
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-end">{{ number_format($item->purchase_price ?? 0, 2) }}</td>
                                        <td class="text-end">{{ number_format($item->sale_price ?? 0, 2) }}</td>
                                        <td class="text-center"><span class="badge bg-soft-info text-info">{{ $item->qty }}</span></td>
                                        <td class="text-end fw-semibold">{{ number_format($item_sale_total, 2) }}</td>
                                        <td class="text-center">
                                            @if($item->order && $item->order->status)
                                                <span class="badge bg-soft-primary text-primary">{{ $item->order->status->name }}</span>
                                            @else
                                                <span class="badge bg-soft-secondary text-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->order && $item->order->user ? $item->order->user->name : 'Unassigned' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            <i class="fe-info font-20 d-block mb-1"></i>
                                            No order records found matching the specified criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">Page Subtotal:</th>
                                    <th class="text-end">{{ number_format($page_purchase, 2) }}</th>
                                    <th></th>
                                    <th class="text-center">{{ number_format($page_qty) }}</th>
                                    <th class="text-end">{{ number_format($page_sale, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr class="bg-light fw-bold">
                                    <th colspan="6" class="text-end">Overall Filtered Totals:</th>
                                    <th class="text-end text-danger">৳ {{ number_format($total_purchase, 2) }}</th>
                                    <th></th>
                                    <th class="text-center text-primary">{{ number_format($total_item) }}</th>
                                    <th class="text-end text-success">৳ {{ number_format($total_sales, 2) }}</th>
                                    <th colspan="2" class="text-center text-info">Profit: ৳ {{ number_format($total_profit, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3 no-print">
                        {{ $orders->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('backEnd/assets/libs/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
<script>
    $(document).ready(function () {
        if ($('.select2').length > 0) {
            $('.select2').select2();
        }
        if ($('.flatdate').length > 0) {
            flatpickr(".flatdate", {
                dateFormat: "Y-m-d"
            });
        }

        $('#export-csv-btn').on('click', function () {
            var table = document.getElementById('order-report-table');
            var rows = table.querySelectorAll('tr');
            var csv = [];
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('td, th');
                for (var j = 0; j < cols.length; j++) {
                    var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ').trim();
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(','));
            }
            
            var csvString = csv.join('\n');
            var filename = 'order_report_' + new Date().toISOString().slice(0, 10) + '.csv';
            var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            
            if (navigator.msSaveBlob) {
                navigator.msSaveBlob(blob, filename);
            } else {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });
</script>
@endsection