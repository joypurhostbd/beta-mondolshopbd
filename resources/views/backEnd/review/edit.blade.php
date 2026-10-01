@extends('backEnd.layouts.master')
@section('title', 'Review Edit')
@section('css')
    <link href="{{ asset('backEnd/assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <a href="{{ route('reviews.index') }}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage Reviews</a>
                    </div>
                    <h4 class="page-title">Edit Review #{{ $edit_data->id }}</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('reviews.update') }}" method="POST" class="row" data-parsley-validate=""
                            enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" value="{{ $edit_data->id }}" name="hidden_id">
                            <input type="hidden" value="{{ $edit_data->id }}" name="id">

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="product_id" class="form-label fw-bold">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('product_id') is-invalid @enderror"
                                        name="product_id" id="product_id" data-toggle="select2"
                                        data-placeholder="Select Product..." required>
                                        <option value="">Select Product...</option>
                                        @foreach ($products as $value)
                                            <option value="{{ $value->id }}" {{ old('product_id', $edit_data->product_id) == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col end -->

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="customer_id" class="form-label fw-bold">Customer <span class="text-muted">(Optional)</span></label>
                                    <select class="form-control select2 @error('customer_id') is-invalid @enderror"
                                        name="customer_id" id="customer_id" data-toggle="select2"
                                        data-placeholder="Select Customer...">
                                        <option value="">Guest / Manual Reviewer</option>
                                        @foreach ($customers as $value)
                                            <option value="{{ $value->id }}" {{ old('customer_id', $edit_data->customer_id) == $value->id ? 'selected' : '' }}>
                                                {{ $value->name }} ({{ $value->email ?: 'No Email' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col-end -->

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label fw-bold">Reviewer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" id="name" value="{{ old('name', $edit_data->name) }}" required>
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
                                    <label for="email" class="form-label fw-bold">Reviewer Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" id="email" value="{{ old('email', $edit_data->email) }}">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col-end -->

                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="ratting" class="form-label fw-bold">Rating <span class="text-danger">*</span></label>
                                    <select class="form-control @error('ratting') is-invalid @enderror"
                                        name="ratting" id="ratting" required>
                                        <option value="">Select Rating...</option>
                                        <option value="5" {{ old('ratting', $edit_data->ratting) == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 Stars (Excellent)</option>
                                        <option value="4" {{ old('ratting', $edit_data->ratting) == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 Stars (Good)</option>
                                        <option value="3" {{ old('ratting', $edit_data->ratting) == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 Stars (Average)</option>
                                        <option value="2" {{ old('ratting', $edit_data->ratting) == 2 ? 'selected' : '' }}>⭐⭐ 2 Stars (Poor)</option>
                                        <option value="1" {{ old('ratting', $edit_data->ratting) == 1 ? 'selected' : '' }}>⭐ 1 Star (Terrible)</option>
                                    </select>
                                    @error('ratting')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col-end -->

                            <div class="col-sm-6 mb-3">
                                <div class="form-group">
                                    <label for="status" class="d-block fw-bold mb-1">Status (Approved / Active)</label>
                                    <label class="switch">
                                        <input type="checkbox" value="1" name="status"
                                            {{ ($edit_data->status === 'active' || $edit_data->status == 1) ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    @error('status')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col end -->

                            <div class="col-sm-12 mb-3">
                                <div class="form-group">
                                    <label for="review" class="form-label fw-bold">Review Content <span class="text-danger">*</span></label>
                                    <textarea name="review" id="review" rows="5" class="form-control @error('review') is-invalid @enderror" required>{{ old('review', $edit_data->review) }}</textarea>
                                    @error('review')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <!-- col end -->

                            <div class="col-12">
                                <button type="submit" class="btn btn-success"><i class="fe-check-circle me-1"></i> Update Review</button>
                            </div>

                        </form>

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col-->
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('backEnd/assets/libs/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/pages/form-validation.init.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('backEnd/assets/js/pages/form-advanced.init.js') }}"></script>
    <script src="{{ asset('backEnd/assets/libs/summernote/summernote-lite.min.js') }}"></script>
@endsection