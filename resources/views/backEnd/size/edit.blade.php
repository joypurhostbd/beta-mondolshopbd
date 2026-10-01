@extends('backEnd.layouts.master')
@section('title','Size Edit')
@section('content')
<div class="container-fluid">
    
    <!-- start Size title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('sizes.index')}}" class="btn btn-primary rounded-pill"><i class="fe-list me-1"></i> Manage</a>
                </div>
                <h4 class="page-title">Size Edit</h4>
            </div>
        </div>
    </div>       
    <!-- end Size title --> 
   <div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form action="{{route('sizes.update')}}" method="POST" class="row" data-parsley-validate="">
                    @csrf
                    <input type="hidden" value="{{$edit_data->id}}" name="id">
                    <input type="hidden" value="{{$edit_data->id}}" name="hidden_id">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="sizeName" class="form-label">Size Name *</label>
                            <input type="text" class="form-control @error('sizeName') is-invalid @enderror" name="sizeName" value="{{ $edit_data->sizeName}}" id="sizeName" required="">
                            @error('sizeName')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <!-- col-end -->                    
                    <div class="col-sm-6 mb-3">
                        <div class="form-group">
                            <label for="status" class="d-block">Status</label>
                            <label class="switch">
                              <input type="checkbox" value="1" name="status" @if($edit_data->status==1)checked @endif>
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
                    <div>
                        <input type="submit" class="btn btn-success" value="Submit">
                    </div>

                </form>

            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col-->
   </div>
</div>
@endsection

@section('script')
<script src="{{asset('backEnd/assets/libs/parsleyjs/parsley.min.js')}}"></script>
<script src="{{asset('backEnd/assets/js/pages/form-validation.init.js')}}"></script>
@endsection