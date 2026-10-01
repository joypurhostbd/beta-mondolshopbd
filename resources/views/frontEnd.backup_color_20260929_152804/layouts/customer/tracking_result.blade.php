@extends('frontEnd.layouts.master')
@section('title','Order Track Result')
@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-8">
                @foreach($order as $key=>$value)
                <div class="form-content p-4 shadow-sm rounded bg-white mb-4">
                    <p class="auth-title text-center mb-4">📦 Order Tracking Details</p>

                    <!-- Order Summary Info -->
                    <div class="track_info bg-light p-3 rounded mb-4">
                        <div class="row">
                            <div class="col-sm-4 mb-2"><strong>Invoice:</strong> #{{$value->invoice_id}}</div>
                            <div class="col-sm-4 mb-2"><strong>Date:</strong> {{$value->created_at ? $value->created_at->format('d M Y, h:i A') : ''}}</div>
                            <div class="col-sm-4 mb-2">
                                <strong>Status:</strong> 
                                <span class="badge bg-primary">{{$value->status->name ?? 'Processing'}}</span>
                            </div>
                            @if($value->isCourierDispatched())
                            <div class="col-sm-4 mb-2">
                                <strong>Courier:</strong> 
                                <span class="badge bg-info text-white"><i class="fa fa-truck me-1"></i>{{ $value->courier_display_name }}</span>
                            </div>
                            <div class="col-sm-4 mb-2">
                                <strong>Courier Status:</strong> 
                                <span class="badge bg-secondary">{{ ucwords(str_replace('_', ' ', $value->courier_status ?? ($value->status->name ?? 'Pending'))) }}</span>
                            </div>
                            @endif
                            @if($value->consignment_id || $value->tracking_code)
                            <div class="col-sm-12 mt-2">
                                <strong>Courier Tracking:</strong> 
                                <span class="text-success font-monospace">{{ $value->courier_tracking_id }}</span>
                                @if($value->courier_tracking_url)
                                <a href="{{ $value->courier_tracking_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2 py-0 px-2" style="font-size: 12px;">
                                    <i class="fa fa-external-link-alt"></i> Track on {{ $value->courier_display_name }}
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Stepper -->
                    @php
                        $statusCode = (int)$value->order_status;
                        // 1 = Pending, 2 = Confirmed, 3 = Processing, 4 = Shipped, 5 = Delivered, 6 = Cancelled
                    @endphp
                    @if($statusCode === 6)
                        <div class="alert alert-danger text-center">❌ This order has been cancelled.</div>
                    @else
                        <div class="order-progress-container mb-4">
                            <div class="d-flex justify-content-between text-center position-relative">
                                <div class="step {{ $statusCode >= 1 ? 'text-primary font-weight-bold' : 'text-muted' }}">
                                    <i class="fa fa-clipboard-check fa-2x d-block mb-1"></i>
                                    <small>1. Placed</small>
                                </div>
                                <div class="step {{ $statusCode >= 2 ? 'text-primary font-weight-bold' : 'text-muted' }}">
                                    <i class="fa fa-cogs fa-2x d-block mb-1"></i>
                                    <small>2. Processing</small>
                                </div>
                                <div class="step {{ $statusCode >= 4 ? 'text-primary font-weight-bold' : 'text-muted' }}">
                                    <i class="fa fa-shipping-fast fa-2x d-block mb-1"></i>
                                    <small>3. Shipped</small>
                                </div>
                                <div class="step {{ $statusCode >= 5 ? 'text-success font-weight-bold' : 'text-muted' }}">
                                    <i class="fa fa-box-open fa-2x d-block mb-1"></i>
                                    <small>4. Delivered</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Table -->
                    <table class="table table-bordered tracktable mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Product Name</th>
                                <th style="width: 15%; text-align: center;">Qty</th>
                                <th style="width: 25%; text-align: right;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($value->orderdetails as $product)
                            <tr>
                                <td>{{$product->product_name}}</td>
                                <td style="text-align: center;">{{$product->qty}}</td>
                                <td style="text-align: right;">{{number_format($product->sale_price * $product->qty, 2)}} TK</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Delivery Charge:</strong></td>
                                <td style="text-align: right;">{{number_format($value->shipping_charge, 2)}} TK</td>
                            </tr>
                            @if($value->discount > 0)
                            <tr>
                                <td colspan="2" class="text-end"><strong>Discount:</strong></td>
                                <td style="text-align: right; color: green;">-{{number_format($value->discount, 2)}} TK</td>
                            </tr>
                            @endif
                            <tr class="table-active">
                                <td colspan="2" class="text-end"><strong>Total Amount:</strong></td>
                                <td style="text-align: right; font-weight: bold;">{{number_format($value->amount, 2)}} TK</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
@push('script')
<script src="{{asset('frontEnd/')}}/js/parsley.min.js"></script>
<script src="{{asset('frontEnd/')}}/js/form-validation.init.js"></script>
@endpush