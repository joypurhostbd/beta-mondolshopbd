@extends('backEnd.layouts.master')
@section('title', 'Product Manage')

@section('css')
<style>
    /* Enterprise ERP Product Management Styles */
    .erp-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease-in-out;
    }
    .erp-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }
    .kpi-icon-wrap {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 22px;
    }
    .kpi-label {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        color: #64748b;
    }
    .kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.2;
    }
    .erp-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .erp-table thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 14px;
        vertical-align: middle;
    }
    .erp-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.86rem;
        color: #334155;
    }
    .erp-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .product-thumb-wrap {
        position: relative;
        width: 46px;
        height: 46px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .product-thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.2s ease;
    }
    .product-thumb-wrap:hover img {
        transform: scale(1.1);
    }
    .product-code-pill {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.74rem;
        background: #f1f5f9;
        color: #0f52cd;
        padding: 2px 7px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        display: inline-block;
    }
    .action-btn-cluster {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .action-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px !important;
        font-size: 13px;
        transition: all 0.15s ease;
    }
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.08);
    }
    .bulk-bar {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
    }
    .filter-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 16px;
    }
    .stock-badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-soft-success {
        background-color: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .badge-soft-warning {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .badge-soft-danger {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .badge-soft-info {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }
    .badge-soft-secondary {
        background-color: #f8fafc;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Page Header & Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box py-3">
                <div class="page-title-right">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{route('products.price_edit')}}" class="btn btn-outline-primary btn-sm rounded-2 fw-medium shadow-sm">
                            <i class="fe-dollar-sign me-1"></i> Bulk Price & Stock
                        </a>
                        <a href="{{route('products.create')}}" class="btn btn-danger btn-sm rounded-2 fw-medium shadow-sm">
                            <i class="fe-plus me-1"></i> Add Product
                        </a>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="page-title mb-0">Product Manage</h4>
                    <span class="badge bg-soft-primary text-primary rounded-pill font-13 fw-semibold px-2">
                        {{ $total_products ?? $data->total() }} Total
                    </span>
                </div>
            </div>
        </div>
    </div> 

    <!-- Enterprise KPI Metric Cards -->
    <div class="row mb-3 g-2 g-xl-3">
        <!-- Total Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="erp-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Total Products</div>
                        <div class="kpi-value mt-1">{{ number_format((float)($total_products ?? 0)) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-soft-primary text-primary">
                        <i class="fe-package"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Products -->
        <div class="col-sm-6 col-xl-3">
            <div class="erp-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Active Products</div>
                        <div class="kpi-value mt-1 text-success">{{ number_format((float)($total_active ?? 0)) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-soft-success text-success">
                        <i class="fe-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-sm-6 col-xl-3">
            <div class="erp-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Low Stock (≤ 5)</div>
                        <div class="kpi-value mt-1 text-danger">{{ number_format((float)($low_stock_count ?? 0)) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-soft-danger text-danger">
                        <i class="fe-alert-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deals & Features -->
        <div class="col-sm-6 col-xl-3">
            <div class="erp-card p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="kpi-label">Deals & Features</div>
                        <div class="kpi-value mt-1 text-warning">{{ number_format((float)($deals_count ?? 0)) }}</div>
                    </div>
                    <div class="kpi-icon-wrap bg-soft-warning text-warning">
                        <i class="fe-zap"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="row">
        <div class="col-12">
            <div class="erp-card">
                <div class="card-body p-3 p-md-4">
                    
                    <!-- Filters & Search Form -->
                    <div class="filter-panel">
                        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
                            <div class="col-12 col-md-4 col-lg-3">
                                <label class="form-label font-12 fw-semibold text-muted mb-1">Search Products</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="fe-search text-muted"></i></span>
                                    <input type="text" name="keyword" class="form-control border-start-0" placeholder="Name, SKU, Barcode, ID..." value="{{ request('keyword') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-3 col-lg-3">
                                <label class="form-label font-12 fw-semibold text-muted mb-1">Category</label>
                                <select name="category_id" class="form-select form-select-sm">
                                    <option value="">All Categories</option>
                                    @if(isset($categories))
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="form-label font-12 fw-semibold text-muted mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-lg-4 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary fw-medium px-3">
                                    <i class="fe-filter me-1"></i> Filter
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                                    <i class="fe-rotate-ccw me-1"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Bulk Actions Toolbar -->
                    <div class="bulk-bar mb-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary font-11 px-2 py-1"><i class="fe-check-square me-1"></i> Bulk Actions</span>
                                <span class="text-muted font-12 d-none d-sm-inline">Apply to selected products:</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <a href="{{route('products.update_deals',['status'=>1])}}" class="btn btn-xs btn-outline-success rounded-2 bulk_product_action" title="Mark selected as Hot Deal">
                                    <i class="fe-thumbs-up me-1"></i> Deal (Yes)
                                </a>
                                <a href="{{route('products.update_deals',['status'=>0])}}" class="btn btn-xs btn-outline-danger rounded-2 bulk_product_action" title="Remove Hot Deal from selected">
                                    <i class="fe-thumbs-down me-1"></i> Deal (No)
                                </a>
                                <a href="{{route('products.update_feature',['status'=>1])}}" class="btn btn-xs btn-outline-info rounded-2 bulk_product_action" title="Mark selected as Featured">
                                    <i class="fe-star me-1"></i> Feature (Yes)
                                </a>
                                <a href="{{route('products.update_feature',['status'=>0])}}" class="btn btn-xs btn-outline-secondary rounded-2 bulk_product_action" title="Remove Featured from selected">
                                    <i class="fe-x-circle me-1"></i> Feature (No)
                                </a>
                                <a href="{{route('products.update_status',['status'=>1])}}" class="btn btn-xs btn-primary rounded-2 bulk_product_action" title="Activate selected products">
                                    <i class="fe-check me-1"></i> Active
                                </a>
                                <a href="{{route('products.update_status',['status'=>0])}}" class="btn btn-xs btn-warning rounded-2 bulk_product_action" title="Deactivate selected products">
                                    <i class="fe-slash me-1"></i> Inactive
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Enterprise Products Table -->
                    <div class="table-responsive">
                        <table class="table erp-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 32px;" class="text-center">
                                        <div class="form-check m-0 d-flex justify-content-center">
                                            <input type="checkbox" class="form-check-input checkall" id="checkall" title="Select all products">
                                            <label class="form-check-label" for="checkall"></label>
                                        </div>
                                    </th>
                                    <th style="width: 45px;" class="text-center">SL</th>
                                    <th style="width: 140px;">Action</th>
                                    <th style="min-width: 220px;">Product Name & SKU</th>
                                    <th style="width: 120px;">Category</th>
                                    <th style="width: 70px;" class="text-center">Image</th>
                                    <th style="width: 110px;">Price</th>
                                    <th style="width: 100px;">Stock</th>
                                    <th style="width: 130px;">Deal & Feature</th>
                                    <th style="width: 85px;" class="text-center">Status</th>
                                </tr>
                            </thead>               
                        
                            <tbody>
                                @forelse($data as $key=>$value)
                                <tr>
                                    <!-- Checkbox -->
                                    <td class="text-center">
                                        <div class="form-check m-0 d-flex justify-content-center">
                                            <input type="checkbox" class="checkbox form-check-input" value="{{$value->id}}">
                                        </div>
                                    </td>

                                    <!-- Serial -->
                                    <td class="text-center text-muted font-12 fw-medium">
                                        {{$loop->iteration}}
                                    </td>

                                    <!-- Action Column (Live Preview + Edit + Toggle Status + Delete) -->
                                    <td>
                                        <div class="action-btn-cluster">
                                            <!-- Live Preview in New Tab -->
                                            <a href="{{ route('product', $value->slug ?? $value->id) }}" 
                                               target="_blank" 
                                               rel="noopener noreferrer" 
                                               class="btn btn-sm btn-light text-info action-btn" 
                                               title="Preview Product in New Tab">
                                                <i class="fe-eye"></i>
                                            </a>

                                            <!-- Edit Product -->
                                            <a href="{{route('products.edit', $value->id)}}" 
                                               class="btn btn-sm btn-light text-primary action-btn" 
                                               title="Edit Product">
                                                <i class="fe-edit"></i>
                                            </a>

                                            <!-- Toggle Status (Active / Inactive) -->
                                            @if($value->status == 1)
                                            <form method="post" action="{{route('products.inactive')}}" class="d-inline"> 
                                                @csrf
                                                <input type="hidden" value="{{$value->id}}" name="hidden_id">       
                                                <button type="submit" class="btn btn-sm btn-light text-warning action-btn" title="Set Inactive">
                                                    <i class="fe-slash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <form method="post" action="{{route('products.active')}}" class="d-inline">
                                                @csrf
                                                <input type="hidden" value="{{$value->id}}" name="hidden_id">        
                                                <button type="submit" class="btn btn-sm btn-light text-success action-btn" title="Set Active">
                                                    <i class="fe-check"></i>
                                                </button>
                                            </form>
                                            @endif

                                            <!-- Delete Product -->
                                            <form method="post" action="{{route('products.destroy')}}" class="d-inline">        
                                                @csrf
                                                <input type="hidden" value="{{$value->id}}" name="hidden_id">
                                                <button type="submit" class="btn btn-sm btn-light text-danger action-btn delete-confirm" title="Delete Product">
                                                    <i class="fe-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    <!-- Product Name & SKU -->
                                    <td>
                                        <div class="fw-semibold text-dark font-14">{{$value->name}}</div>
                                        <div class="mt-1 d-flex flex-wrap align-items-center gap-1">
                                            @if(!empty($value->product_code))
                                            <span class="product-code-pill" title="SKU / Product Code">
                                                <i class="fe-tag font-10 me-1"></i>{{$value->product_code}}
                                            </span>
                                            @endif
                                            @if(!empty($value->pro_barcode))
                                            <span class="product-code-pill bg-light text-secondary" title="Barcode">
                                                <i class="fe-maximize font-10 me-1"></i>{{$value->pro_barcode}}
                                            </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Category -->
                                    <td>
                                        @if($value->category)
                                        <span class="badge bg-light text-dark border font-12 fw-medium">
                                            {{$value->category->name}}
                                        </span>
                                        @else
                                        <span class="text-muted font-12">-</span>
                                        @endif
                                    </td>

                                    <!-- Product Thumbnail -->
                                    <td class="text-center">
                                        <div class="product-thumb-wrap mx-auto position-relative">
                                            <img src="{{asset(($value->featuredImage ?? $value->image)?->image ?? 'uploads/default/product.png')}}"
                                                 alt="{{$value->name}}"
                                                 onerror="this.src='{{asset('uploads/default/product.png')}}';">
                                            @if($value->featuredImage)
                                            <span class="badge bg-success position-absolute top-0 start-0" style="font-size:9px;">★</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td>
                                        <div class="fw-bold text-dark font-14">৳{{ number_format((float)$value->new_price, 2) }}</div>
                                        @if($value->old_price > $value->new_price)
                                        <del class="text-muted font-11">৳{{ number_format((float)$value->old_price, 2) }}</del>
                                        @endif
                                    </td>

                                    <!-- Stock Level -->
                                    <td>
                                        @if($value->stock <= 0)
                                        <span class="stock-badge badge-soft-danger">
                                            <i class="fe-alert-circle font-11"></i> 0 Out of Stock
                                        </span>
                                        @elseif($value->stock <= 5)
                                        <span class="stock-badge badge-soft-warning">
                                            <i class="fe-alert-triangle font-11"></i> {{$value->stock}} Low Stock
                                        </span>
                                        @else
                                        <span class="stock-badge badge-soft-success">
                                            <i class="fe-check-circle font-11"></i> {{$value->stock}} in stock
                                        </span>
                                        @endif
                                    </td>

                                    <!-- Deals & Features -->
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($value->topsale == 1)
                                            <span class="badge badge-soft-danger font-11">
                                                <i class="fe-zap me-1"></i>Hot Deal
                                            </span>
                                            @endif
                                            @if($value->feature_product == 1)
                                            <span class="badge badge-soft-info font-11">
                                                <i class="fe-star me-1"></i>Featured
                                            </span>
                                            @endif
                                            @if($value->topsale != 1 && $value->feature_product != 1)
                                            <span class="text-muted font-12">-</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="text-center">
                                        @if($value->status == 1)
                                        <span class="badge badge-soft-success font-11">
                                            <i class="fe-check me-1"></i>Active
                                        </span>
                                        @else
                                        <span class="badge badge-soft-danger font-11">
                                            <i class="fe-x me-1"></i>Inactive
                                        </span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                        <i class="fe-inbox font-28 d-block mb-2 text-secondary"></i>
                                        <div class="fw-semibold font-15 text-dark">No products found</div>
                                        <p class="font-13 text-muted mb-0">Try changing your search terms or filter criteria.</p>
                                    </td>
                                </tr>
                                @endforelse
                             </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="custom-paginate mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="font-12 text-muted">
                            Showing {{ $data->firstItem() ?? 0 }} to {{ $data->lastItem() ?? 0 }} of {{ $data->total() }} entries
                        </div>
                        <div>
                            {{$data->links('pagination::bootstrap-4')}}
                        </div>
                    </div>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function(){
    $(".checkall").on('change', function(){
        $(".checkbox").prop('checked', $(this).is(":checked"));
    });
    
    $(document).on('click', '.bulk_product_action', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var product = $('input.checkbox:checked').map(function(){
            return $(this).val();
        });
        var product_ids = product.get();
        if(product_ids.length === 0){
            toastr.error('Please select at least one product.');
            return;
        }
        $.ajax({
            type: 'GET',
            url: url,
            data: { product_ids: product_ids },
            success: function(res){
                if(res.status === 'success'){
                    toastr.success(res.message);
                    window.location.reload();
                } else {
                    toastr.error(res.message || 'Action failed.');
                }
            },
            error: function(){
                toastr.error('An error occurred during bulk operation.');
            }
        });
    });
});
</script>
@endsection