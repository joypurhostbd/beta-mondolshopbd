@extends('backEnd.layouts.master') 
@section('title', 'Create Shipping Charge') 

@section('css')
<link href="{{ asset('backEnd/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{ route('shippingcharges.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe-list me-1"></i> Manage Rates
                    </a>
                </div>
                <h4 class="page-title">Add Shipping Charge / Area</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('shippingcharges.store') }}" method="POST" class="row" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf                        
                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Shipping Area Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" id="name" placeholder="e.g. Inside Dhaka, Outside Dhaka, Sub-Urban" required="" />
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-6">
                            <div class="form-group mb-3">
                                <label for="amount" class="form-label">Delivery Charge Amount (BDT) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" name="amount" value="{{ old('amount') }}" id="amount" placeholder="e.g. 60, 120" required="" />
                                </div>
                                @error('amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col-end -->

                        <div class="col-sm-6 mb-3">
                            <div class="form-group">
                                <label for="status" class="d-block form-label">Status</label>
                                <input type="checkbox" value="1" name="status" id="status" class="js-switch" data-color="#28a745" checked />
                                <small class="text-muted d-block mt-1">Active rates are automatically offered to customers at checkout.</small>
                                @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-success waves-effect waves-light me-1">
                                <i class="fe-check-circle me-1"></i> Save Shipping Charge
                            </button>
                            <a href="{{ route('shippingcharges.index') }}" class="btn btn-secondary waves-effect">
                                <i class="fe-x me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
                <!-- end card-body-->
            </div>
            <!-- end card-->
        </div>
        <!-- end col-->
    </div>
</div>
@endsection 

@section('script')
<script src="{{ asset('backEnd/assets/libs/parsleyjs/parsley.min.js') }}"></script>
<script src="{{ asset('backEnd/assets/libs/switchery/switchery.min.js') }}"></script>
<script>
    $(document).ready(function() {
        if ($('.js-switch').length > 0) {
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function(html) {
                new Switchery(html, { size: 'small', color: '#28a745' });
            });
        }
    });
</script>
@endsection
