@extends('backEnd.layouts.master')
@section('title', 'Product Price & Stock Manage')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('products.index')}}" class="btn btn-outline-primary rounded-pill me-1"><i class="fe-list me-1"></i> All Products</a>
                    <a href="{{route('products.create')}}" class="btn btn-primary rounded-pill"><i class="fe-plus me-1"></i> Add Product</a>
                </div>
                <h4 class="page-title">Bulk Price & Stock Manage</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 

    <!-- Navigation Pills -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{route('products.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-package me-1"></i> Product List</a>
                <a href="{{route('products.price_edit')}}" class="btn btn-primary btn-sm rounded-pill"><i class="fe-dollar-sign me-1"></i> Quick Price & Stock Edit</a>
                <a href="{{route('categories.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-grid me-1"></i> Categories</a>
                <a href="{{route('brands.index')}}" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="fe-tag me-1"></i> Brands</a>
            </div>
        </div>
    </div>

    <!-- KPI Metric Summary Cards -->
    <div class="row mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="widget-rounded-circle card mb-2 mb-xl-0 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-sm rounded-circle bg-soft-primary border-primary border">
                                <i class="fe-package font-20 avatar-title text-primary"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['total_active'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Active Products</p>
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
                                <i class="fe-layers font-20 avatar-title text-success"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ number_format($metrics['total_stock'] ?? 0) }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Total Units in Stock</p>
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
                                <i class="fe-percent font-20 avatar-title text-info"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['discounted'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Discounted Products</p>
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
                            <div class="avatar-sm rounded-circle bg-soft-danger border-danger border">
                                <i class="fe-alert-triangle font-20 avatar-title text-danger"></i>
                            </div>
                        </div>
                        <div class="col">
                            <div class="text-end">
                                <h4 class="text-dark my-0">{{ $metrics['low_stock'] ?? 0 }}</h4>
                                <p class="text-muted mb-0 font-12 text-truncate">Low Stock (≤ 5)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border mb-0">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('products.price_edit') }}" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fe-search"></i></span>
                                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Search by name, SKU, barcode..." value="{{ request('keyword') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category_id" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fe-filter me-1"></i> Filter</button>
                            <a href="{{ route('products.price_edit') }}" class="btn btn-light btn-sm"><i class="fe-rotate-ccw me-1"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Price & Stock Edit Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border">
                <form action="{{route('products.price_update')}}" method="POST">
                    @csrf
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                        <span class="text-muted font-13">Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} Products</span>
                        <button type="submit" class="btn btn-success btn-sm"><i class="fe-check-circle me-1"></i> Save Changes</button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">SL</th>
                                        <th style="width: 70px;">Image</th>
                                        <th>Product Information</th>
                                        <th style="width: 180px;">Old Price (৳)</th>
                                        <th style="width: 180px;">New Price (৳)</th>
                                        <th style="width: 150px;">Stock</th>
                                    </tr>
                                </thead>               
                            
                                <tbody>
                                    @forelse($products as $key => $value)
                                    <tr>
                                        <td>{{ $products->firstItem() + $loop->index }}</td>
                                        <td>
                                            @if($value->image)
                                                <img src="{{ asset($value->image->image) }}" class="rounded border shadow-sm" width="46" height="46" style="object-fit: cover;" alt="product">
                                            @else
                                                <div class="avatar-sm rounded bg-light border d-flex align-items-center justify-content-center text-muted">
                                                    <i class="fe-image font-16"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="hidden" value="{{$value->id}}" name="ids[]">
                                            <div class="fw-semibold text-dark">{{$value->name}}</div>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @if($value->product_code)
                                                    <span class="badge bg-soft-primary text-primary font-11">SKU: {{$value->product_code}}</span>
                                                @endif
                                                @if($value->category)
                                                    <span class="badge bg-soft-secondary text-secondary font-11"><i class="fe-grid me-1"></i>{{$value->category->name}}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="any" min="0" class="form-control form-control-sm text-end" value="{{$value->old_price}}" name="old_price[]" placeholder="0.00">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" step="any" min="0" class="form-control form-control-sm text-end fw-semibold text-success" value="{{$value->new_price}}" name="new_price[]" placeholder="0.00">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" min="0" class="form-control form-control-sm text-end @if($value->stock <= 5) border-danger text-danger fw-bold @endif" value="{{$value->stock}}" name="stock[]" placeholder="0">
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fe-info font-20 d-block mb-1"></i>
                                            No active products found matching criteria.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- end card body-->

                    <div class="card-footer bg-white py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                        <div>
                            {{ $products->links('pagination::bootstrap-5') }}
                        </div>
                        <div>
                            <button type="submit" class="btn btn-success"><i class="fe-check-circle me-1"></i> Update Price & Stock</button>
                        </div>
                    </div>
                </form>
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>
@endsection