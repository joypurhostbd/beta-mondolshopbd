@extends('frontEnd.layouts.master')
@section('title','Customer Account')
@section('content')
<section class="customer-section">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <div class="customer-sidebar">
                    @include('frontEnd.layouts.customer.sidebar')
                </div>
            </div>
            <div class="col-sm-9">
                <div class="customer-content">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="account-title mb-0">My Orders</h5>
                    </div>

                    <!-- Courier Status Filter Tabs -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="{{ route('customer.orders', 'all') }}" class="btn btn-sm {{ ($slug ?? 'all') == 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All Orders <span class="badge bg-light text-dark ms-1">{{ $courierCounts['all'] ?? count($orders) }}</span>
                        </a>
                        <a href="{{ route('customer.orders', 'courier-pending') }}" class="btn btn-sm {{ ($slug ?? '') == 'courier-pending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fa fa-truck me-1"></i> Courier Pending <span class="badge bg-light text-dark ms-1">{{ $courierCounts['courier-pending'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('customer.orders', 'courier-partial') }}" class="btn btn-sm {{ ($slug ?? '') == 'courier-partial' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            <i class="fa fa-clock me-1"></i> Courier Partial <span class="badge bg-light text-dark ms-1">{{ $courierCounts['courier-partial'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('customer.orders', 'courier-cancel') }}" class="btn btn-sm {{ ($slug ?? '') == 'courier-cancel' ? 'btn-danger' : 'btn-outline-secondary' }}">
                            <i class="fa fa-times-circle me-1"></i> Courier Cancel <span class="badge bg-light text-dark ms-1">{{ $courierCounts['courier-cancel'] ?? 0 }}</span>
                        </a>
                        <a href="{{ route('customer.orders', 'courier-delivered') }}" class="btn btn-sm {{ ($slug ?? '') == 'courier-delivered' ? 'btn-success' : 'btn-outline-secondary' }}">
                            <i class="fa fa-check-circle me-1"></i> Courier Delivered <span class="badge bg-light text-dark ms-1">{{ $courierCounts['courier-delivered'] ?? 0 }}</span>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sl</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $key=>$value)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$value->created_at->format('d-m-y')}}</td>
                                    <td>৳{{$value->amount}}</td>
                                    <td>৳{{$value->discount}}</td>
                                    <td>
                                        @if($value->isCourierDispatched())
                                            <div>
                                                <span class="badge bg-info text-white" title="Courier Partner"><i class="fa fa-truck me-1"></i>{{ $value->courier_display_name }}</span>
                                            </div>
                                            <div class="mt-1">
                                                <span class="badge bg-secondary">
                                                    {{ ucwords(str_replace('_', ' ', $value->courier_status ?: ($value->status->name ?? 'In Courier'))) }}
                                                </span>
                                            </div>
                                            @if($value->courier_tracking_url)
                                                <div class="mt-1">
                                                    <a href="{{ $value->courier_tracking_url }}" target="_blank" class="badge bg-light text-primary text-decoration-none border" title="Track Live">
                                                        <small><i class="fa fa-external-link-alt"></i> {{ $value->courier_tracking_id }}</small>
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">{{$value->status ? $value->status->name : 'Processing'}}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{route('customer.invoice',['id'=>$value->id])}}" class="invoice_btn"><i class="fa-solid fa-eye"></i></a>
                                        @if($value->admin_note)
                                        <a href="{{route('customer.order_note',['id'=>$value->id])}}" class="invoice_btn bg-primary"><i class="fa-solid fa-pencil"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa fa-box-open fa-2x mb-2 d-block"></i>
                                        No orders found for this status.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection