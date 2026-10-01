@extends('backEnd.layouts.master')
@section('title','Order Process')
@section('css')
<style>
    .increment_btn,.remove_btn {
    margin-top: -17px;
    margin-bottom: 10px;
}
</style>
<link href="{{asset('backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd')}}/assets/libs/summernote/summernote-lite.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Order Process [Invoice : #{{$data->invoice_id}}]</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Image</th>
                            <th>Product</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->orderdetails as $key=>$product)
                        <tr>
                            <td>{{$key+1}}</td>
                            <td><img src="{{asset(($product->featuredImageRelation ?? $product->image)?->image ?? '')}}" height="50" width="50" alt=""></td>
                            <td>{{$product->product_name}}</td>
                        </tr>
                        @endforeach
                        
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
               <form action="{{route('admin.order_change')}}" method="POST" class=row data-parsley-validate="" name="editForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$data->id}}">
                    
                            <div class="col-sm-6">
                              <div class="form-group mb-3">
                                   <label for="name" class="form-label">Customer name </label>
                                   <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{$data->shipping?$data->shipping->name:''}}" placeholder="Name">
                                     @error('name')
                                      <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                      </span>
                                     @enderror
                              </div>
                            </div>
                            
                             <div class="col-sm-6">
                              <div class="form-group mb-3">
                                   <label for="phone" class="form-label">Customer Phone </label>
                                   <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" id="phone" value="{{$data->shipping?$data->shipping->phone:''}}" placeholder="Phone Number">
                                     @error('phone')
                                      <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                      </span>
                                     @enderror
                              </div>
                            </div>
                             <div class="col-sm-12">
                              <div class="form-group mb-3">
                                   <label for="address" class="form-label">Customer Address </label>
                                   <textarea name="address" class="form-control @error('address') is-invalid @enderror">{{$data->shipping?$data->shipping->address:''}}</textarea>
                                    @error('address')
                                      <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                      </span>
                                     @enderror
                              </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <label for="area">Delivery Area *</label>
                                    <select type="area" id="area" class="form-control @error('area') is-invalid @enderror" name="area"   required>
                                        @foreach($shippingcharge as $key=>$value)
                                        <option @if($data->shipping?$data->shipping->area:'' == $value->name) selected @endif value="{{$value->id}}">{{$value->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('area')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                <label for="category_id" class="form-label">Order Status</label>
                                 <select class="form-control select2-multiple @error('status') is-invalid @enderror" value="{{ old('status') }}" name="status" data-toggle="select2"  data-placeholder="Choose ..." required>
                                    <optgroup >
                                        <option value="">Select..</option>
                                        @foreach($orderstatus as $value)
                                        <option value="{{$value->id}}"  @if($data->order_status==$value->id) selected @endif>{{$value->name}}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="admin_note" class="form-label">Admin Note / Advance Payment TxID</label>
                                <textarea name="admin_note" id="admin_note" rows="2" class="form-control" placeholder="Advance delivery fee details, TxID (bKash/Nagad), hold reasons or internal notes...">{{ old('admin_note', $data->admin_note ?? '') }}</textarea>
                            </div>
                        </div>
                        <!-- col end -->

                        <div class="col-sm-12">
                            <div class="form-group mb-3">
                                <label for="note" class="form-label">Customer Note</label>
                                <input type="text" name="note" id="note" class="form-control" value="{{ old('note', $data->note ?? '') }}" placeholder="Customer instructions...">
                            </div>
                        </div>
                        <!-- col end -->

                        <!-- SteadFast Courier Action Section -->
                        <div class="col-sm-12">
                            <div class="card border border-info mb-3 bg-soft-light">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="font-14 mb-1 text-dark"><i class="fe-truck text-info me-1"></i> SteadFast Courier Management</h5>
                                            @if(!empty($data->consignment_id) || !empty($data->tracking_code))
                                                <p class="font-13 text-muted mb-0">
                                                    Tracking Code: <strong class="text-primary">{{ $data->tracking_code ?? $data->consignment_id }}</strong>
                                                    @if(!empty($data->consignment_id) && $data->consignment_id !== $data->tracking_code)
                                                        <span class="ms-1 font-12 text-muted">(CID: {{ $data->consignment_id }})</span>
                                                    @endif
                                                </p>
                                            @else
                                                <p class="font-12 text-muted mb-0">Order is not yet dispatched to SteadFast courier.</p>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            @if(empty($data->consignment_id) && empty($data->tracking_code))
                                                <button type="button" id="btn-send-single-steadfast" class="btn btn-sm btn-outline-warning rounded-pill">
                                                    <i class="fe-send me-1"></i> Send to SteadFast
                                                </button>
                                            @else
                                                <button type="button" id="btn-check-courier-status" class="btn btn-sm btn-outline-info rounded-pill">
                                                    <i class="fe-refresh-cw me-1"></i> Check Live Status
                                                </button>
                                                <a href="https://steadfast.com.bd/t/{{ urlencode($data->tracking_code ?? $data->consignment_id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                                    <i class="fe-external-link me-1"></i> SteadFast Portal
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div id="courier-live-status-result" class="mt-2 font-13" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- col end -->
                  
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
<script src="{{asset('backEnd/')}}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{asset('backEnd/')}}/assets/js/pages/form-validation.init.js"></script>
<script src="{{asset('backEnd/')}}/assets/libs/select2/js/select2.min.js"></script>
<script src="{{asset('backEnd/')}}/assets/js/pages/form-advanced.init.js"></script>
<!-- Plugins js -->
<script src="{{asset('backEnd/')}}/assets/libs//summernote/summernote-lite.min.js"></script>
<script>
  $(".summernote").summernote({
    placeholder: "Enter Your Text Here",
  });

  $('#btn-send-single-steadfast').on('click', function() {
    var $btn = $(this);
    if (!confirm('Are you sure you want to dispatch this order to SteadFast Courier?')) {
      return;
    }
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Sending...');
    $.ajax({
      type: 'POST',
      url: "{{ route('admin.order.send_steadfast', $data->id) }}",
      data: {
        _token: "{{ csrf_token() }}"
      },
      success: function(res) {
        $btn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to SteadFast');
        if (res.status === 'success') {
          if (typeof toastr !== 'undefined') toastr.success(res.message);
          setTimeout(function() { window.location.reload(); }, 1200);
        } else {
          if (typeof toastr !== 'undefined') toastr.error(res.message || 'Failed to dispatch order');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="fe-send me-1"></i> Send to SteadFast');
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error sending to SteadFast';
        if (typeof toastr !== 'undefined') toastr.error(msg);
      }
    });
  });

  $('#btn-check-courier-status').on('click', function() {
    var $btn = $(this);
    var $result = $('#courier-live-status-result');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Checking...');
    $result.show().html('<span class="text-muted"><i class="fa fa-spinner fa-spin me-1"></i> Fetching live status from SteadFast...</span>');

    $.ajax({
      type: 'GET',
      url: "{{ route('admin.order.courier_status', $data->id) }}",
      success: function(res) {
        $btn.prop('disabled', false).html('<i class="fe-refresh-cw me-1"></i> Check Live Status');
        if (res.status === 'success') {
          var badgeClass = 'bg-info';
          var stat = (res.delivery_status || '').toLowerCase();
          if (stat === 'delivered' || stat === 'completed') badgeClass = 'bg-success';
          else if (stat === 'cancelled' || stat === 'returned') badgeClass = 'bg-danger';
          else if (stat === 'in_transit' || stat === 'shipped') badgeClass = 'bg-primary';

          var changeNotice = res.status_changed ? ' <small class="text-success ms-1">(Order status updated!)</small>' : '';
          $result.html('<strong>Current SteadFast Status:</strong> <span class="badge ' + badgeClass + ' text-uppercase font-12 ms-1">' + res.delivery_status + '</span>' + changeNotice);
          if (typeof toastr !== 'undefined') toastr.info('SteadFast Status: ' + res.delivery_status);
        } else {
          $result.html('<span class="text-danger">' + (res.message || 'Failed to fetch status') + '</span>');
        }
      },
      error: function(xhr) {
        $btn.prop('disabled', false).html('<i class="fe-refresh-cw me-1"></i> Check Live Status');
        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error checking status';
        $result.html('<span class="text-danger">' + msg + '</span>');
      }
    });
  });
</script>
@endsection