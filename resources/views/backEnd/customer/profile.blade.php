@extends('backEnd.layouts.master')
@section('title', 'Customer Profile')
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
                    <a href="{{route('customers.index')}}" class="btn btn-primary rounded-pill"><i class="fe-arrow-left me-1"></i> Customer List</a>
                    <a href="{{route('customers.edit', $profile->id)}}" class="btn btn-info rounded-pill ms-1"><i class="fe-edit me-1"></i> Edit Profile</a>
                    <form method="post" action="{{route('customers.adminlog')}}" class="d-inline ms-1" target="_blank">
                        @csrf
                        <input type="hidden" value="{{$profile->id}}" name="hidden_id">        
                        <button type="button" class="btn btn-pink rounded-pill change-confirm" title="Login as customer"><i class="fe-log-in me-1"></i> Login as Customer</button>
                    </form>
                </div>
                <h4 class="page-title">Customer Profile: {{ $profile->name }}</h4>
            </div>
        </div>
    </div>  
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-4 col-xl-4">
            <div class="card text-center">
                <div class="card-body">
                    @php
                        $avatarPath = (!empty($profile->image) && file_exists(public_path($profile->image))) ? asset($profile->image) : asset('uploads/default/user.png');
                    @endphp
                    <img src="{{ $avatarPath }}" class="rounded-circle avatar-xl img-thumbnail" alt="{{ $profile->name }}" style="object-fit: cover;">

                    <h4 class="mb-1 mt-2">{{ $profile->name }}</h4>
                    <p class="text-muted mb-2">
                        @if($profile->status == 'active' || $profile->status == '1' || $profile->status === true)
                            <span class="badge bg-soft-success text-success font-12">Active Account</span>
                        @else
                            <span class="badge bg-soft-danger text-danger font-12">Inactive Account</span>
                        @endif
                    </p>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <a href="tel:{{$profile->phone}}" class="btn btn-success btn-sm waves-effect waves-light"><i class="fe-phone me-1"></i> Call</a>
                        @if(!empty($profile->email))
                            <a href="mailto:{{$profile->email}}" class="btn btn-danger btn-sm waves-effect waves-light"><i class="fe-mail me-1"></i> Email</a>
                        @endif
                    </div>

                    <div class="text-start mt-3">
                        <h4 class="font-13 text-uppercase border-bottom pb-2">Customer Details :</h4>
                        <table class="table table-borderless table-sm">
                            <tbody>
                            <tr>
                                <th scope="row" class="text-muted" style="width: 100px;">Full Name :</th>
                                <td class="text-dark fw-medium">{{ $profile->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted">Mobile :</th>
                                <td class="text-dark">{{ $profile->phone }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted">Email :</th>
                                <td class="text-dark">{{ $profile->email ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted">Address :</th>
                                <td class="text-dark">{{ $profile->address ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row" class="text-muted">Joined :</th>
                                <td class="text-dark">{{ $profile->created_at ? $profile->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end card -->
        </div> <!-- end col-->

        <div class="col-lg-8 col-xl-8">
            <!-- Customer Stats Row -->
            <div class="row mb-2">
                <div class="col-md-4">
                    <div class="widget-rounded-circle card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <div class="avatar-md rounded-circle bg-soft-primary border-primary border">
                                        <i class="fe-shopping-bag font-20 avatar-title text-primary"></i>
                                    </div>
                                </div>
                                <div class="col-8 text-end">
                                    <h4 class="text-dark mt-0 mb-0">{{ $customer_stats['total_orders'] ?? 0 }}</h4>
                                    <p class="text-muted mb-0 font-12">Total Orders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="widget-rounded-circle card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <div class="avatar-md rounded-circle bg-soft-success border-success border">
                                        <i class="fe-dollar-sign font-20 avatar-title text-success"></i>
                                    </div>
                                </div>
                                <div class="col-8 text-end">
                                    <h4 class="text-dark mt-0 mb-0">৳{{ number_format($customer_stats['total_all_orders_amount'] ?? 0, 0) }}</h4>
                                    <p class="text-muted mb-0 font-12">Total Spent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="widget-rounded-circle card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <div class="avatar-md rounded-circle bg-soft-info border-info border">
                                        <i class="fe-calendar font-20 avatar-title text-info"></i>
                                    </div>
                                </div>
                                <div class="col-8 text-end">
                                    <h5 class="text-dark mt-0 mb-0 font-13">{{ $customer_stats['last_order_date'] ? date('d M Y', strtotime($customer_stats['last_order_date'])) : 'No Orders' }}</h5>
                                    <p class="text-muted mb-0 font-12">Last Order</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fe-shopping-cart me-1"></i> Order History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Invoice ID</th>
                                    <th>Recipient</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($profile->orders as $key => $value)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <a href="{{ route('admin.order.invoice', ['id' => $value->id]) }}" class="fw-bold text-primary">#{{ $value->invoice_id }}</a>
                                    </td>
                                    <td>{{ $value->shipping ? $value->shipping->name : ($value->customer ? $value->customer->name : 'N/A') }}</td>
                                    <td>
                                        <small>{{ date('d M Y', strtotime($value->created_at)) }}</small>
                                        <br>
                                        <small class="text-muted">{{ date('h:i A', strtotime($value->created_at)) }}</small>
                                    </td>
                                    <td class="fw-bold">৳{{ number_format($value->amount, 0) }}</td>
                                    <td>
                                        @if($value->status)
                                            <span class="badge bg-soft-primary text-primary font-12">{{ $value->status->name }}</span>
                                        @else
                                            <span class="badge bg-soft-secondary text-secondary font-12">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.order.invoice', ['id' => $value->id]) }}" class="btn btn-xs btn-primary waves-effect waves-light" title="View Invoice"><i class="fe-file-text"></i> Invoice</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fe-package font-24 mb-2 d-block"></i>
                                        No order history found for this customer.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('backEnd/assets/libs/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('backEnd/assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js')}}"></script>
@endsection