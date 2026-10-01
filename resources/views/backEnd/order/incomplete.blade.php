@extends('backEnd.layouts.master')
@section('title','Abandoned Cart Leads')
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
                    <a href="{{route('admin.orders', 'pending')}}" class="btn btn-primary rounded-pill"><i class="fe-clock"></i> View Pending Orders</a>
                </div>
                <h4 class="page-title">🛒 Abandoned Cart Leads & Recovery ({{ $datas->count() }})</h4>
            </div>
        </div>
    </div>     
    <!-- end page title --> 

    <!-- Navigation Tabs -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card mb-2">
                <div class="card-body p-2">
                    <ul class="nav nav-pills flex-wrap gap-1">
                        <li class="nav-item">
                            <a href="{{ route('admin.orders', 'all') }}" class="nav-link py-1 px-3 text-dark bg-light">
                                <i class="fe-grid me-1"></i> All Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.orders', 'pending') }}" class="nav-link py-1 px-3 text-dark bg-light">
                                <i class="fe-clock me-1"></i> Pending Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('incomplete.index') }}" class="nav-link py-1 px-3 active bg-danger text-white">
                                <i class="fe-user-x me-1"></i> Abandoned Cart Leads
                                <span class="badge bg-white text-danger ms-1">{{ $datas->count() }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="avatar-lg rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-shopping-bag font-22 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $total_leads ?? $datas->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Total Leads</p>
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
                                <h3 class="text-dark mt-1">৳<span data-plugin="counterup">{{ number_format($total_recoverable_amount ?? 0) }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Recoverable Value</p>
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
                                <i class="fe-phone-call font-22 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $leads_with_phone ?? $datas->where('phone', '!=', '')->count() }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Contactable Leads</p>
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
                                <i class="fe-clock font-22 avatar-title text-warning"></i>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <h3 class="text-dark mt-1"><span data-plugin="counterup">{{ $today_leads ?? 0 }}</span></h3>
                                <p class="text-muted mb-1 text-truncate">Today's Leads</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="row order_page">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="bulkDeleteForm" action="{{ route('incomplete.bulk_delete') }}" method="POST">
                        @csrf
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <button type="submit" id="bulkDeleteBtn" class="btn btn-sm btn-outline-danger" disabled onclick="return confirm('Are you sure you want to delete the selected leads?')">
                                    <i class="fe-trash-2 me-1"></i> Delete Selected (<span id="selectedCount">0</span>)
                                </button>
                                <span class="text-muted font-12">Select leads to delete in bulk.</span>
                            </div>
                            <div class="btn-group btn-group-sm" role="group" id="leadFilterGroup">
                                <button type="button" class="btn btn-outline-secondary active" data-filter="all">All ({{ $datas->count() }})</button>
                                <button type="button" class="btn btn-outline-success" data-filter="phone">With Phone ({{ $leads_with_phone }})</button>
                                <button type="button" class="btn btn-outline-warning" data-filter="no-phone">No Phone ({{ $datas->count() - $leads_with_phone }})</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="datatable-buttons" class="table table-striped table-hover dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th style="width: 25px;" class="text-center">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Address</th>
                                        <th>Cart Items</th>
                                        <th>Total Value</th>
                                        <th style="width: 200px;">Recovery Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $value)
                                    @php
                                        $items = is_string($value->data) ? json_decode($value->data) : (is_array($value->data) || is_object($value->data) ? $value->data : []);
                                        $totalQty = 0;
                                        $totalAmount = 0;
                                        if ($items) {
                                            foreach ($items as $item) {
                                                $itemObj = (object)$item;
                                                $qty = (int)($itemObj->qty ?? 1);
                                                $price = (float)($itemObj->price ?? 0);
                                                $totalQty += $qty;
                                                $totalAmount += ($price * $qty);
                                            }
                                        }
                                        $hasPhone = !empty($value->phone);
                                        $waNumber = \App\ValueObjects\Phone::toWhatsApp($value->phone ?? '');
                                    @endphp
                                    <tr data-has-phone="{{ $hasPhone ? '1' : '0' }}">
                                        <td class="text-center align-middle">
                                            <input type="checkbox" name="ids[]" value="{{ $value->id }}" class="form-check-input lead-checkbox">
                                        </td>
                                        <td class="align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            <strong>{{ $value->created_at ? $value->created_at->format('d M Y') : 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $value->created_at ? $value->created_at->format('h:i A') : '' }}</small>
                                        </td>
                                        <td class="align-middle"><strong>{{ $value->name ?? 'Guest Buyer' }}</strong></td>
                                        <td class="align-middle">
                                            @if($hasPhone)
                                            <div class="d-flex flex-column gap-1">
                                                <a href="tel:{{ $value->phone }}" class="text-primary fw-bold font-13">
                                                    <i class="fe-phone"></i> {{ $value->phone }}
                                                </a>
                                                @if(!empty($waNumber))
                                                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hello ' . ($value->name ?? 'Customer') . ', greetings from BrandCityBD! We noticed you left items in your cart. Can we assist you in completing your order?') }}" target="_blank" class="btn btn-xs btn-success rounded-pill px-2 py-0" style="width: fit-content;">
                                                    <i class="fe-message-circle me-1"></i> WhatsApp
                                                </a>
                                                @endif
                                            </div>
                                            @else
                                            <span class="badge bg-soft-warning text-warning font-11">No Phone</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ Str::limit($value->address ?? 'N/A', 30) }}</td>
                                        <td class="align-middle">
                                            <button type="button" class="btn btn-xs btn-outline-primary view-cart-items-btn" 
                                                data-name="{{ $value->name ?? 'Guest Buyer' }}" 
                                                data-phone="{{ $value->phone ?? 'No phone' }}" 
                                                data-address="{{ $value->address ?? 'N/A' }}" 
                                                data-convert-url="{{ route('incomplete.convert', $value->id) }}" 
                                                data-has-phone="{{ $hasPhone ? '1' : '0' }}"
                                                data-items='@json($items)'>
                                                <i class="fe-eye me-1"></i> {{ $totalQty }} {{ Str::plural('item', $totalQty) }}
                                            </button>
                                        </td>
                                        <td class="align-middle"><strong class="text-success font-14">৳{{ number_format($totalAmount, 2) }}</strong></td>
                                        <td class="align-middle">
                                            <div class="button-list">
                                                @if($hasPhone)
                                                <a class="btn btn-xs btn-success waves-effect waves-light" href="{{ route('incomplete.convert', $value->id) }}" onclick="return confirm('Convert this abandoned lead into an active confirmed Order?')">
                                                    <i class="fe-check-circle"></i> Convert
                                                </a>
                                                <a class="btn btn-xs btn-info waves-effect waves-light" href="{{ route('incomplete.sms', $value->id) }}" onclick="return confirm('Send recovery reminder SMS to {{ $value->phone }}?')">
                                                    <i class="fe-message-square"></i> SMS
                                                </a>
                                                @else
                                                <button type="button" class="btn btn-xs btn-secondary" disabled title="Cannot convert without customer phone number">
                                                    <i class="fe-slash"></i> No Phone
                                                </button>
                                                @endif
                                                <a class="btn btn-xs btn-danger waves-effect waves-light" href="{{ route('incomplete.delete', $value->id) }}" onclick="return confirm('Are you sure you want to delete this lead?')">
                                                    <i class="fe-trash-2"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Reusable Cart Items Modal (Replaces hundreds of duplicate modals) -->
<div class="modal fade" id="cartItemsModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title font-14" id="cartModalLabel">
                    <i class="fe-shopping-bag text-primary me-1"></i> Cart Items for <span id="modalCustomerName"></span> (<span id="modalCustomerPhone"></span>)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start" style="width: 45%;">Product</th>
                                <th style="width: 15%;">Unit Price</th>
                                <th style="width: 15%;">Qty</th>
                                <th style="width: 25%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="modalItemsTbody">
                            <!-- Injected via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 p-2 bg-light rounded font-12">
                    <strong>Delivery Address:</strong> <span id="modalCustomerAddress"></span>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a class="btn btn-sm btn-success" id="modalConvertBtn" href="#" onclick="return confirm('Convert this abandoned lead into an active confirmed Order?')">
                    <i class="fe-check-circle me-1"></i> Convert to Order
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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

<script>
$(document).ready(function() {
    // Single Dynamic Modal Handler
    $(document).on('click', '.view-cart-items-btn', function() {
        var name = $(this).data('name');
        var phone = $(this).data('phone');
        var address = $(this).data('address');
        var convertUrl = $(this).data('convert-url');
        var hasPhone = $(this).data('has-phone') == '1';
        var items = $(this).data('items');

        $('#modalCustomerName').text(name);
        $('#modalCustomerPhone').text(phone);
        $('#modalCustomerAddress').text(address);
        
        if (hasPhone) {
            $('#modalConvertBtn').attr('href', convertUrl).show();
        } else {
            $('#modalConvertBtn').hide();
        }

        var tbodyHtml = '';
        var totalAmount = 0;

        // Ensure items is parsed as an array/object
        if (typeof items === 'string') {
            try { items = JSON.parse(items); } catch(e) { items = []; }
        }

        // If items is an object (associative), convert to array values
        var itemsArray = Array.isArray(items) ? items : (items ? Object.values(items) : []);

        if (itemsArray.length > 0) {
            $.each(itemsArray, function(idx, item) {
                var qty = parseInt(item.qty || 1);
                var price = parseFloat(item.price || 0);
                var lineTotal = qty * price;
                totalAmount += lineTotal;

                var attributes = '';
                if (item.options && item.options.product_size) {
                    attributes += '<span class="badge bg-soft-secondary text-secondary me-1">Size: ' + item.options.product_size + '</span>';
                }
                if (item.options && item.options.product_color) {
                    attributes += '<span class="badge bg-soft-info text-info">Color: ' + item.options.product_color + '</span>';
                }

                tbodyHtml += '<tr>' +
                    '<td class="text-start">' +
                        '<div class="fw-bold">' + (item.name || 'Product') + '</div>' +
                        attributes +
                    '</td>' +
                    '<td>৳' + price.toFixed(2) + '</td>' +
                    '<td>' + qty + '</td>' +
                    '<td><strong>৳' + lineTotal.toFixed(2) + '</strong></td>' +
                '</tr>';
            });

            tbodyHtml += '<tr class="table-light fw-bold">' +
                '<td colspan="3" class="text-end">Total Cart Value:</td>' +
                '<td><strong class="text-success">৳' + totalAmount.toFixed(2) + '</strong></td>' +
            '</tr>';
        } else {
            tbodyHtml = '<tr><td colspan="4" class="text-center text-muted py-3">No cart items recorded for this lead.</td></tr>';
        }

        $('#modalItemsTbody').html(tbodyHtml);
        $('#cartItemsModal').modal('show');
    });

    // Select All Checkbox Handler
    $('#selectAll').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.lead-checkbox:visible').prop('checked', isChecked);
        updateBulkDeleteState();
    });

    $(document).on('change', '.lead-checkbox', function() {
        updateBulkDeleteState();
    });

    function updateBulkDeleteState() {
        var checkedCount = $('.lead-checkbox:checked').length;
        $('#selectedCount').text(checkedCount);
        $('#bulkDeleteBtn').prop('disabled', checkedCount === 0);
    }

    // Lead Filter Handler
    $('#leadFilterGroup button').on('click', function() {
        $('#leadFilterGroup button').removeClass('active');
        $(this).addClass('active');

        var filter = $(this).data('filter');
        if (filter === 'phone') {
            $('tbody tr[data-has-phone="1"]').show();
            $('tbody tr[data-has-phone="0"]').hide();
        } else if (filter === 'no-phone') {
            $('tbody tr[data-has-phone="1"]').hide();
            $('tbody tr[data-has-phone="0"]').show();
        } else {
            $('tbody tr').show();
        }
        $('#selectAll').prop('checked', false);
        updateBulkDeleteState();
    });
});
</script>
@endsection