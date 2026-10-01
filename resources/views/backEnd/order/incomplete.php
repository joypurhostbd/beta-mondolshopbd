@extends('backEnd.layouts.master')
@section('title',' Order')
@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('admin.order.create')}}" class="btn btn-danger rounded-pill"><i class="fe-shopping-cart"></i> Add New</a>
                </div>
                <h4 class="page-title">{{$order_status->name}} Order ({{$order_status->orders_count}})</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row order_page">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
               
                <div class="table-responsive ">
                <table id="datatable-buttons" class="table table-striped   w-100">
                    <thead>
                        <tr>
                            
                            <th>Date</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Items show</th>
                            
                            
                        </tr>
                    </thead>
                
                
                    <tbody>
                        @foreach(datas as $key=>$value)
                        <tr>
                            
                            <td>{{$value->created_at}}</td>
                            <td>{{$value->name}}</td>
                            <td>{{$value->phone}}</td>
                            <td>{{$value->address}}</td>
                            <td>
                                
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">show products</button>
                            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                          <div class="modal-dialog" role="document">
                            <div class="modal-content">
                              
                              <div class="modal-body">
                               <table class="cart_table table table-bordered table-striped text-center mb-0">
                                    <thead>
                                     <tr>
                                      <th style="width: 20%;">ডিলিট</th>
                                      <th style="width: 40%;">প্রোডাক্ট</th>
                                      <th style="width: 20%;">পরিমাণ</th>
                                      <th style="width: 20%;">মূল্য</th>
                                     </tr>
                                    </thead>
                            
                                    <tbody>
                                     @foreach( json_decode($value->data) as $data)
                                     <tr>
                                      <td>
                                       <a class="cart_remove" data-id="{{$data->rowId}}"><i class="fas fa-trash text-danger"></i></a>
                                      </td>
                                      <td class="text-left">
                                       <a href="{{route('product',$data->options->slug)}}"> <img src="{{asset($data->options->image)}}" style="height:30px;width:30px" /> {{Str::limit($value->name,20)}}</a>
                                       @if($data->options->product_size)
                                        <p>Size: {{$data->options->product_size}}</p>
                                       @endif
                                       @if($data->options->product_color)
                                       <p>Color: {{ $data->options->product_color }}</p>
                                       @endif
                                      </td>
                                      <td class="cart_qty">
                                       <div class="qty-cart vcart-qty">
                                        <div class="quantity">
                                         <button class="minus cart_decrement" data-id="{{$data->rowId}}">-</button>
                                         <input type="text" value="{{$data->qty}}" readonly />
                                         <button class="plus cart_increment" data-id="{{$data->rowId}}">+</button>
                                        </div>
                                       </div>
                                      </td>
                                      <td><span class="alinur">৳ </span><strong>{{$data->price}}</strong></td>
                                     </tr>
                                     @endforeach
                                    </tbody>
                                    <tfoot>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">মোট</th>
                                      <td>
                                       <span id="net_total"><span class="alinur">৳ </span><strong>{{$data->subtotal}}</strong></span>
                                      </td>
                                     </tr>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">ডেলিভারি চার্জ</th>
                                      <td>
                                       <span id="cart_shipping_cost"><span class="alinur">৳ </span><strong>{{$data->shipping}}</strong></span>
                                      </td>
                                     </tr>
                                     <tr>
                                      <th colspan="3" class="text-end px-4">সর্বমোট</th>
                                      <td>
                                       <span id="grand_total"><span class="alinur">৳ </span><strong>{{$data->subtotal+$$data->shipping}}</strong></span>
                                      </td>
                                     </tr>
                                    </tfoot>
                                   </table>
                              </div>
                              <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                          
                              </div>
                            </div>
                          </div>
                        </div>
                            </td>
                          
                            
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="custom-paginate">
                    {{$show_data->links('pagination::bootstrap-4')}}
                </div>
            </div> <!-- end card body-->
           
        </div> <!-- end card -->
    </div><!-- end col-->
   </div>
</div>
<!-- Assign User End -->
<div class="modal fade" id="asignUser" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('admin.order.assign')}}" id="order_assign">
      <div class="modal-body">
        <div class="form-group">
            <select name="user_id" id="user_id" class="form-control">
                <option value="">Select..</option>
                @foreach($users as $key=>$value)
                <option value="{{$value->id}}">{{$value->name}}</option>
                @endforeach
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>
<!-- Assign User End-->

<!-- Assign User End -->
<div class="modal fade" id="changeStatus" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Assign User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('admin.order.status')}}" id="order_status_form">
      <div class="modal-body">
        <div class="form-group">
            <select name="order_status" id="order_status" class="form-control">
                <option value="">Select..</option>
                @foreach($orderstatus as $key=>$value)
                <option value="{{$value->id}}">{{$value->name}}</option>
                @endforeach
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>
<!-- Assign User End-->
<!-- pathao coureir start -->
<div class="modal fade" id="pathao" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pathao Courier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{route('admin.order.pathao')}}" id="order_sendto_pathao">

      <div class="modal-body">
        <div class="form-group">
            <label for="pathaostore" class="form-label">Store</label>
           <select name="pathaostore" id="pathaostore" class="pathaostore form-control" >
             <option value="">Select Store...</option>
             @if(isset($pathaostore['data']['data']))
             @foreach($pathaostore['data']['data'] as $key=>$store)
             <option value="{{$store['store_id']}}">{{$store['store_name']}}</option>
             @endforeach
             @else
             @endif
           </select>
            @if ($errors->has('pathaostore'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('pathaostore') }}</strong>
              </span>
              @endif
        </div>
        <!-- form group end -->
        <div class="form-group mt-3">
          <label for="pathaocity" class="form-label">City</label>
           <select name="pathaocity" id="pathaocity" class="chosen-select pathaocity form-control" style="width:100%" >
             <option value="">Select City...</option>
             @if(isset($pathaocities['data']['data']))
             @foreach($pathaocities['data']['data'] as $key=>$city)
             <option value="{{$city['city_id']}}">{{$city['city_name']}}</option>
             @endforeach
             @else
             @endif
           </select>
            @if ($errors->has('pathaocity'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('pathaocity') }}</strong>
              </span>
              @endif
        </div>
        <!-- form group end -->
        <div class="form-group mt-3">
          <label for="" class="form-label">Zone</label>
             <select name="pathaozone" id="pathaozone" class="pathaozone chosen-select form-control  {{ $errors->has('pathaozone') ? ' is-invalid' : '' }}" value="{{ old('pathaozone') }}"  style="width:100%">
            </select>
             @if ($errors->has('pathaozone'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('pathaozone') }}</strong>
              </span>
              @endif
        </div>
        <!-- form group end -->
        <div class="form-group mt-3">
          <label for="" class="form-label">Area</label>
             <select name="pathaoarea" id="pathaoarea" class="pathaoarea chosen-select form-control  {{ $errors->has('pathaoarea') ? ' is-invalid' : '' }}" value="{{ old('pathaoarea') }}"  style="width:100%">
            </select>
             @if ($errors->has('pathaoarea'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('pathaoarea') }}</strong>
              </span>
              @endif
        </div>
        <!-- form group end -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-success">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>
<!-- pathao courier  End-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $(".checkall").on('change',function(){
      $(".checkbox").prop('checked',$(this).is(":checked"));
    });

    // order assign
    $(document).on('submit', 'form#order_assign', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        let user_id=$(document).find('select#user_id').val();
    
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{user_id,order_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
                
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    
    });

    // order status change 
    $(document).on('submit', 'form#order_status_form', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        let order_status=$(document).find('select#order_status').val();
    
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{order_status,order_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
                
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    
    });
    // order delete
    $(document).on('click', '.order_delete', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{order_ids},
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
                
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    
    });
    
    // multiple print
    $(document).on('click', '.multi_order_print', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select Atleast One Order!');
            return ;
        }
        $.ajax({
           type:'GET',
           url,
           data:{order_ids},
           success:function(res){
               if(res.status=='success'){
                   console.log(res.items, res.info);                          
                   var myWindow = window.open("", "_blank");                   
                   myWindow.document.write(res.view);
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    });
    // multiple courier
    $(document).on('click', '.multi_order_courier', function(e){
        e.preventDefault();
        var url = $(this).attr('href');
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data:{order_ids},
           success:function(res){
            console.log(res);
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
                
            }else{
                console.log(res.status);
                toastr.error('Failed something wrong');
            }
           }
        });
    
    });

    // Pathao Courier 
    $(document).on('submit', 'form#order_sendto_pathao', function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        var method = $(this).attr('method');
        //let order_status=$(document).find('select#order_status').val();
    
        var order = $('input.checkbox:checked').map(function(){
          return $(this).val();
        });
        var order_ids=order.get();
        
        if(order_ids.length ==0){
            toastr.error('Please Select An Order First !');
            return ;
        }

        var store_id = $('#pathaostore').val();
        var city_id = $('#pathaocity').val();
        var zone_id = $('#pathaozone').val();
        var area_id = $('#pathaoarea').val();

        if (!store_id || !city_id || !zone_id || !area_id) {
            toastr.error('Please fill out all required fields!');
            return;
        }
        
        $.ajax({
           type:'GET',
           url:url,
           data: {
                order_ids: order_ids,
                store_id: store_id,
                city_id: city_id,
                zone_id: zone_id,
                area_id: area_id,
            },
           success:function(res){
               if(res.status=='success'){
                toastr.success(res.message);
                window.location.reload();
                
            }else{
                toastr.error('Failed something wrong');
            }
           }
        });
    
    });
})
</script>
@endsection