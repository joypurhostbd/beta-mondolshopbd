@extends('backEnd.layouts.master')
@section('title', 'Stock Report')

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
                        <li class="breadcrumb-item active">Stock Report</li>
                    </ol>
                </div>
                <h4 class="page-title">Inventory Stock Report</h4>
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
                                <i class="fe-package font-22 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_products ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total SKUs</p>
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
                                <i class="fe-box font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ number_format($total_stock ?? 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">In-Stock Units</p>
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
                                <i class="fe-dollar-sign font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1">৳<span data-plugin="counterup">{{ number_format($total_purchase ?? 0, 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Cost Value</p>
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
                                <i class="fe-trending-up font-22 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1">৳<span data-plugin="counterup">{{ number_format($total_price ?? 0, 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Retail Value</p>
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
                    <form method="GET" action="{{ route('admin.stock_report') }}" class="no-print mb-3">
                        <div class="row align-items-end">   
                            <div class="col-md-3 mb-2">
                                <label for="keyword" class="form-label font-weight-bold">Keyword / SKU</label>
                                <input type="text" value="{{ request()->get('keyword') }}" class="form-control" name="keyword" id="keyword" placeholder="Search by name or code...">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label for="category_id" class="form-label font-weight-bold">Category</label>
                                <select class="form-control select2" name="category_id" id="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request()->get('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="start_date" class="form-label font-weight-bold">Start Date</label>
                                <input type="text" value="{{ request()->get('start_date') }}" class="form-control flatdate" name="start_date" id="start_date" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label for="end_date" class="form-label font-weight-bold">End Date</label>
                                <input type="text" value="{{ request()->get('end_date') }}" class="form-control flatdate" name="end_date" id="end_date" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="col-md-2 mb-2">
                                <div class="d-flex gap-1">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light w-100">
                                        <i class="fe-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('admin.stock_report') }}" class="btn btn-secondary waves-effect" title="Reset Filters">
                                        <i class="fe-rotate-ccw"></i>
                                    </a>
                                </div>
                            </div>
                        </div>  
                    </form>

                    <div class="row mb-3 align-items-center no-print">
                        <div class="col-sm-6">
                            <div class="pagination-wrapper">
                                {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end mt-2 mt-sm-0">
                            <button onclick="window.print()" class="btn btn-success btn-sm waves-effect waves-light me-1">
                                <i class="fe-printer me-1"></i> Print Report
                            </button>
                            <button id="export-csv-btn" class="btn btn-info btn-sm waves-effect waves-light">
                                <i class="fe-download me-1"></i> Export CSV
                            </button>
                        </div>
                    </div>

                    <!-- Print View Header -->
                    <div class="print-header">
                        <h3>Stock & Inventory Valuation Report</h3>
                        <p class="text-muted">Generated on: {{ date('F d, Y h:i A') }}</p>
                    </div>

                    <div id="content-to-export" class="table-responsive">
                        <table class="table table-bordered table-striped nowrap w-100" id="stock-report-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">SL</th>
                                    <th style="width: 30%">Product Name</th>
                                    <th style="width: 15%">Category</th>
                                    <th style="width: 10%">SKU / Code</th>
                                    <th style="width: 10%" class="text-end">Cost Price</th>
                                    <th style="width: 10%" class="text-end">Retail Price</th>
                                    <th style="width: 10%" class="text-center">Stock</th>
                                    <th style="width: 10%" class="text-end">Total Valuation</th>
                                </tr>
                            </thead>               
                        
                            <tbody>
                                @php
                                    $page_stock = 0;
                                    $page_total = 0;
                                @endphp
                                @forelse($products as $key => $value)
                                <tr>
                                    <td>{{ $products->firstItem() + $key }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $value->name }}</span>
                                    </td>
                                    <td>
                                        @if($value->category)
                                            <span class="badge bg-soft-primary text-primary">{{ $value->category->name }}</span>
                                        @else
                                            <span class="text-muted font-12">N/A</span>
                                        @endif
                                    </td>
                                    <td><code>{{ $value->product_code ?? 'N/A' }}</code></td>
                                    <td class="text-end">৳{{ number_format($value->purchase_price, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($value->new_price, 2) }}</td>
                                    <td class="text-center">
                                        @if($value->stock <= 0)
                                            <span class="badge bg-danger">0 Pcs (Out of Stock)</span>
                                        @elseif($value->stock < 5)
                                            <span class="badge bg-warning">{{ $value->stock }} Pcs (Low Stock)</span>
                                        @else
                                            <span class="badge bg-soft-success text-success">{{ $value->stock }} Pcs</span>
                                        @endif
                                    </td>
                                    <td class="text-end font-weight-bold">৳{{ number_format($value->stock * $value->new_price, 2) }}</td>
                                </tr>
                                @php
                                    $page_stock += $value->stock;
                                    $page_total += $value->stock * $value->new_price;
                                @endphp
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fe-alert-circle font-22 d-block mb-1"></i>
                                        No inventory products found matching the criteria.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end font-weight-bold">Page Subtotal:</td>
                                    <td class="text-center font-weight-bold">{{ number_format($page_stock) }} Pcs</td>
                                    <td class="text-end font-weight-bold">৳{{ number_format($page_total, 2) }}</td>
                                </tr>
                                <tr class="bg-soft-primary">
                                    <td colspan="8" class="text-center py-2">
                                        <div class="row text-center font-14">
                                            <div class="col-md-4">
                                                <strong>Total Inventory Stock:</strong> <span class="badge bg-primary font-13">{{ number_format($total_stock) }} Pcs</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Total Cost Value:</strong> <span class="badge bg-warning font-13">৳{{ number_format($total_purchase, 2) }}</span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Total Retail Value:</strong> <span class="badge bg-success font-13">৳{{ number_format($total_price, 2) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-3 no-print">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
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
            var table = document.getElementById('stock-report-table');
            var rows = table.querySelectorAll('tr');
            var csv = [];
            
            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll('td, th');
                for (var j = 0; j < cols.length; j++) {
                    var data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s+)/gm, ' ');
                    data = data.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(','));
            }
            
            var csvString = csv.join('\n');
            var filename = 'stock_report_' + new Date().toISOString().slice(0, 10) + '.csv';
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
