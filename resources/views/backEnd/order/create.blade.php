@extends('backEnd.layouts.master') @section('title','Order Create') @section('css')
<style>
 .increment_btn,
 .remove_btn {
  margin-top: -17px;
  margin-bottom: 10px;
 }
</style>
<link href="{{asset('backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<link href="{{asset('backEnd')}}/assets/libs/summernote/summernote-lite.min.css" rel="stylesheet" type="text/css" />
@endsection @section('content')

<div class="container-fluid">
 <!-- start page title -->
 <div class="row">
  <div class="col-12">
   <div class="page-title-box">
    <div class="page-title-right">
     <form method="post" action="{{route('admin.order.cart_clear')}}" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-danger rounded-pill delete-confirm" title="Delete"><i class="fas fa-trash-alt"></i> Cart Clear</button>
     </form>
    </div>
    <h4 class="page-title">Order Create</h4>
   </div>
  </div>
 </div>
 <!-- end page title -->
 <div class="row justify-content-center">
  <div class="col-lg-12">
   <div class="card">
    <div class="card-body">
     <form action="{{route('admin.order.store')}}" method="POST" class="row pos_form" data-parsley-validate="" enctype="multipart/form-data">
      @csrf
      <div class="col-sm-6">
       <div class="form-group mb-3">
        <label for="barcode_search" class="form-label font-weight-bold"><i class="fa fa-barcode"></i> Barcode / SKU Instant Scan</label>
        <div class="input-group">
         <span class="input-group-text"><i class="fa fa-barcode"></i></span>
         <input type="text" id="barcode_search" class="form-control" placeholder="Scan Barcode or Type Code & Press Enter..." autofocus autocomplete="off" />
        </div>
        <small id="barcode_feedback" class="text-muted"></small>
       </div>
      </div>
      <div class="col-sm-6">
       <div class="form-group mb-3">
        <label for="product_id" class="form-label font-weight-bold">Products List *</label>
        <select id="cart_add" class="form-control select2 @error('product_id') is-invalid @enderror" value="{{ old('product_id') }}">
         <option value="">Select Product...</option>
         @foreach($products as $value)
         <option value="{{$value->id}}">{{$value->name}} ({{$value->product_code ?? 'ID: '.$value->id}})</option>
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
      <div class="col-sm-12">
       <table class="table table-bordered table-responsive-sm">
        <thead>
         <tr>
          <th style="width: 8%;">Image</th>
          <th style="width: 24%;">Name</th>
          <th style="width: 18%;">Attributes</th>
          <th style="width: 14%;">Quantity</th>
          <th style="width: 12%;">Sell Price</th>
          <th style="width: 10%;">Discount</th>
          <th style="width: 10%;">Sub Total</th>
          <th style="width: 4%;">Action</th>
         </tr>
        </thead>
        <tbody id="cartTable">
         @include('backEnd.order.cart_content', ['cartinfo' => $cartinfo, 'cartProducts' => $cartProducts ?? collect()])
        </tbody>
       </table>
      </div>
      <!-- custome address -->
      <div class="col-sm-6">
       <div class="row">
        <div class="col-sm-12">
         <div class="form-group mb-2">
          <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="Customer Name" name="name" value="" required />
          @error('name')
          <span class="invalid-feedback" role="alert">
           <strong>{{ $message }}</strong>
          </span>
          @enderror
         </div>
        </div>
        <!-- col-end -->
        <div class="col-sm-12">
         <div class="form-group mb-2">
          <input type="number" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Customer Number" name="phone" value="" required />
          @error('phone')
          <span class="invalid-feedback" role="alert">
           <strong>{{ $message }}</strong>
          </span>
          @enderror
         </div>
        </div>
        <!-- col-end -->
        <div class="col-sm-12">
         <div class="form-group mb-3">
          <input type="address" placeholder="Address" id="address" class="form-control @error('address') is-invalid @enderror" name="address" value="" required />
          @error('email')
          <span class="invalid-feedback" role="alert">
           <strong>{{ $message }}</strong>
          </span>
          @enderror
         </div>
        </div>
        <div class="col-sm-12">
         <div class="form-group mb-3">
          <select type="area" id="area" class="form-control @error('area') is-invalid @enderror" name="area" required>
           <option value="">Select....</option>
            <option value="1">ঢাকা সিটির ভিতরে হোম ডেলিভারি</option>

            <option value="2">ঢাকা সিটির বাহিরে হোম ডেলিভারি</option>

            <option value="3">কুরিয়ার অফিস থেকে ডেলিভারি</option>

          </select>
          @error('email')
          <span class="invalid-feedback" role="alert">
           <strong>{{ $message }}</strong>
          </span>
          @enderror
         </div>
        </div>
        <!-- col-end -->
       </div>
      </div>
      <!-- cart total -->
      <div class="col-sm-6">
       <table class="table table-bordered">
        <tbody id="cart_details">
         @php $subtotal = Cart::instance('pos_shopping')->subtotal(); $subtotal = str_replace(',','',$subtotal); $subtotal = str_replace('.00', '',$subtotal); $shipping = Session::get('pos_shipping'); $total_discount =
         Session::get('pos_discount')+Session::get('product_discount'); @endphp
         <tr>
          <td>Sub Total</td>
          <td>{{$subtotal}}</td>
         </tr>
         <tr>
          <td>Shipping Fee</td>
          <td>{{$shipping}}</td>
         </tr>
         <tr>
          <td>Discount</td>
          <td>{{$total_discount}}</td>
         </tr>
         <tr>
          <td>Total</td>
          <td>{{($subtotal + $shipping)- $total_discount}}</td>
         </tr>
        </tbody>
       </table>
      </div>
      <div>
       <input type="submit" class="btn btn-success" value="Order Submit" />
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
@include('backEnd.order.attribute_modal')
@endsection @section('script')
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
</script>

<script type="text/javascript">
 $(document).ready(function () {
  $(".select2").select2();
 });
</script>
<script>
 function cart_content() {
  $.ajax({
   type: "GET",
   url: "{{route('admin.order.cart_content')}}",
   dataType: "html",
   success: function (cartinfo) {
    $("#cartTable").html(cartinfo);
   },
  });
 }
 function cart_details() {
  $.ajax({
   type: "GET",
   url: "{{route('admin.order.cart_details')}}",
   dataType: "html",
   success: function (cartinfo) {
    $("#cart_details").html(cartinfo);
   },
  });
 }

 function addProductToCart(productId, size, color, qty) {
  if (!productId) return;
  $.ajax({
   cache: false,
   type: "GET",
   data: {
    id: productId,
    product_size: size || '',
    product_color: color || '',
    qty: qty || 1
   },
   url: "{{route('admin.order.cart_add')}}",
   dataType: "json",
   success: function (cartinfo) {
    cart_content();
    cart_details();
   },
  });
 }

 function showAttributeModal(product) {
  $('#attr_modal_product_id').val(product.id);
  $('#attr_modal_name').text(product.name);
  $('#attr_modal_price').text('৳' + product.new_price);
  $('#attr_modal_stock').text('Stock: ' + product.stock);
  if (product.image) {
   $('#attr_modal_img').attr('src', product.image).show();
  } else {
   $('#attr_modal_img').hide();
  }
  $('#attr_modal_qty').val(1);

  // Render sizes
  if (product.sizes && product.sizes.length > 0) {
   var sizeHtml = '';
   $.each(product.sizes, function(idx, s) {
    var checked = (idx === 0) ? 'checked' : '';
    sizeHtml += '<input type="radio" class="btn-check" name="attr_modal_size" id="attr_size_' + s.id + '" value="' + s.name + '" ' + checked + ' autocomplete="off">';
    sizeHtml += '<label class="btn btn-outline-primary btn-sm px-2 py-1 font-12" for="attr_size_' + s.id + '">' + s.name + '</label>';
   });
   $('#attr_modal_sizes').html(sizeHtml);
   $('#attr_modal_size_group').show();
  } else {
   $('#attr_modal_sizes').empty();
   $('#attr_modal_size_group').hide();
  }

  // Render colors
  if (product.colors && product.colors.length > 0) {
   var colorHtml = '';
   $.each(product.colors, function(idx, c) {
    var checked = (idx === 0) ? 'checked' : '';
    colorHtml += '<input type="radio" class="btn-check" name="attr_modal_color" id="attr_color_' + c.id + '" value="' + c.name + '" ' + checked + ' autocomplete="off">';
    colorHtml += '<label class="btn btn-outline-secondary btn-sm px-2 py-1 font-12" for="attr_color_' + c.id + '">' + (c.color ? '<span style="display:inline-block;width:10px;height:10px;background:' + c.color + ';border-radius:50%;margin-right:4px;"></span>' : '') + c.name + '</label>';
   });
   $('#attr_modal_colors').html(colorHtml);
   $('#attr_modal_color_group').show();
  } else {
   $('#attr_modal_colors').empty();
   $('#attr_modal_color_group').hide();
  }

  var modalEl = document.getElementById('productAttributeModal');
  var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
 }

 function checkAndAddProduct(id) {
  if (!id) return;
  $.ajax({
   type: "GET",
   url: "{{route('admin.order.product_attributes')}}",
   data: { id: id },
   dataType: "json",
   success: function(res) {
    if (res.has_attributes) {
     showAttributeModal(res);
    } else {
     addProductToCart(id, '', '', 1);
    }
   },
   error: function() {
    addProductToCart(id, '', '', 1);
   }
  });
 }

 $(document).on("change", "#cart_add", function (e) {
  var id = $(this).val();
  if (id) {
   checkAndAddProduct(id);
   $(this).val('').trigger('change.select2');
  }
 });

 $(document).on('click', '#attr_modal_submit_btn', function(e) {
  e.preventDefault();
  var id = $('#attr_modal_product_id').val();
  var size = $('input[name="attr_modal_size"]:checked').val() || '';
  var color = $('input[name="attr_modal_color"]:checked').val() || '';
  var qty = parseInt($('#attr_modal_qty').val()) || 1;

  addProductToCart(id, size, color, qty);

  var modalEl = document.getElementById('productAttributeModal');
  var modal = bootstrap.Modal.getInstance(modalEl);
  if (modal) {
   modal.hide();
  }
 });

 // Barcode scanner and SKU enter listener
 $(document).on("keydown", "#barcode_search", function (e) {
  if (e.key === "Enter" || e.keyCode === 13) {
   e.preventDefault();
   var query = $(this).val().trim();
   if (!query) return;

   $("#barcode_feedback").html('<span class="text-primary"><i class="fa fa-spinner fa-spin"></i> Searching...</span>');

   $.ajax({
    type: "GET",
    url: "{{route('admin.order.product_search')}}",
    data: { q: query },
    dataType: "json",
    success: function (res) {
     if (res.exact_match && res.matched_product) {
      checkAndAddProduct(res.matched_product.id);
      $("#barcode_feedback").html('<span class="text-success"><i class="fa fa-check"></i> Added: ' + res.matched_product.name + '</span>');
      $("#barcode_search").val('').focus();
     } else if (res.products && res.products.length === 1) {
      checkAndAddProduct(res.products[0].id);
      $("#barcode_feedback").html('<span class="text-success"><i class="fa fa-check"></i> Added: ' + res.products[0].name + '</span>');
      $("#barcode_search").val('').focus();
     } else if (res.products && res.products.length > 1) {
      $("#barcode_feedback").html('<span class="text-warning">Multiple matches (' + res.products.length + '). Please select from dropdown below.</span>');
     } else {
      $("#barcode_feedback").html('<span class="text-danger"><i class="fa fa-times"></i> No product found for "' + query + '"</span>');
     }
    },
    error: function () {
     $("#barcode_feedback").html('<span class="text-danger">Error searching product.</span>');
    }
   });
  }
 });

 $(document).on("click", ".cart_increment", function (e) {
  e.preventDefault();
  var id = $(this).data("id");
  var qty = $(this).val();
  if (id) {
   $.ajax({
    cache: false,
    data: { id: id, qty: qty },
    type: "GET",
    url: "{{route('admin.order.cart_increment')}}",
    dataType: "json",
    success: function () {
     cart_content();
     cart_details();
    },
   });
  }
 });

 $(document).on("click", ".cart_decrement", function (e) {
  e.preventDefault();
  var id = $(this).data("id");
  var qty = $(this).val();
  if (id) {
   $.ajax({
    cache: false,
    type: "GET",
    data: { id: id, qty: qty },
    url: "{{route('admin.order.cart_decrement')}}",
    dataType: "json",
    success: function () {
     cart_content();
     cart_details();
    },
   });
  }
 });

 $(document).on("click", ".cart_remove", function (e) {
  e.preventDefault();
  var id = $(this).data("id");
  if (id) {
   $.ajax({
    cache: false,
    type: "GET",
    data: { id: id },
    url: "{{route('admin.order.cart_remove')}}",
    dataType: "json",
    success: function () {
     cart_content();
     cart_details();
    },
   });
  }
 });

 $(document).on("change", ".product_discount", function () {
  var id = $(this).data("id");
  var discount = $(this).val();
  $.ajax({
   cache: false,
   type: "GET",
   data: { id: id, discount: discount },
   url: "{{route('admin.order.product_discount')}}",
   dataType: "json",
   success: function () {
    cart_content();
    cart_details();
   },
  });
 });

 $(document).on("click", ".cartclear", function (e) {
  $.ajax({
   cache: false,
   type: "GET",
   url: "{{route('admin.order.cart_clear')}}",
   dataType: "json",
   success: function () {
    cart_content();
    cart_details();
   },
  });
 });

  $(document).on("change", ".cart_attribute_change", function (e) {
   var rowId = $(this).data('id');
   var type = $(this).data('type');
   var val = $(this).val();
   var data = { rowId: rowId };
   if (type === 'size') {
       data.product_size = val;
   } else if (type === 'color') {
       data.product_color = val;
   }
   $.ajax({
       cache: false,
       type: "GET",
       data: data,
       url: "{{route('admin.order.cart_update_attribute')}}",
       dataType: "json",
       success: function(res) {
           cart_content();
           cart_details();
       }
   });
  });

 $(document).on("change", "#area", function () {
  var id = $(this).val();
  $.ajax({
   type: "GET",
   data: { id: id },
   url: "{{route('admin.order.cart_shipping')}}",
   dataType: "html",
   success: function () {
    cart_content();
    cart_details();
   },
  });
 });
</script>
@endsection
