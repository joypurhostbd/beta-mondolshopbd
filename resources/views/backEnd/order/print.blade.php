<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Print</title>
    <link rel="stylesheet" href="{{asset('frontEnd/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('frontEnd/css/all.min.css')}}" />
</head>
<body>
    <style>
        .customer-invoice {
            page-break-before: always;
        }
        body{
            background:#F1F2F5
        }
    .customer-invoice {
        margin: 25px 0;
    }
    .invoice_btn{
        margin-bottom: 15px;
    }
    p{
        margin:0;
    }
    td{
        font-size: 16px;
    }
   @page { 
    margin:0px;
    }
   @media print {
   
    .invoice_btn{
        margin-bottom: 0 !important;
    }
    td{
        font-size: 18px;
    }
    p{
        margin:0;
    }
    header,footer,.no-print,.left-side-menu,.navbar-custom {
      display: none !important;
    }
  }
</style>
<div class="container">
    <div class="row">
        <div class="col-sm-12 mt-3 text-center">
            <button onclick="printFunction()"class="no-print btn btn-xs btn-success waves-effect waves-light"><i class="fa fa-print"></i></button>
        </div>
    </div>
</div>
@foreach($orders as $order)
<section class="customer-invoice ">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 mt-3">
                <div class="invoice-innter" style="max-width:500px;margin: 0 auto;background: #fff;">
                   <table style="width:100%" >
                        <tr>
                            <td style="text-align:center">
                              
                                <div class="invoice_form">
                                    <p style="font-size:30px;line-height:1.8;color:#222">{{$generalsetting->name}}</p>
                                    <p style="font-size:16px;line-height:1.8;color:#222">{{$contact->address}} -{{$contact->phone}}</p>
                                    <p style="font-size: 15px; color: #222;font-weight:bold;">Invoice ID : <strong>#{{$order->invoice_id}}</strong></p>
                                    <p style="font-size: 15px; color: #222;font-weight:bold;">Invoice Date: <strong>{{$order->created_at->format('d-m-y')}}</strong></span></p>
                                    @if(!empty($order->courier_tracking_id))
                                    <div style="margin-top: 6px; padding: 4px 10px; background: #e0f2fe; border: 1px dashed #0284c7; border-radius: 4px; display: inline-block;">
                                        <p style="font-size: 13px; color: #0369a1; font-weight: bold; margin: 0;">
                                            <i class="fe-truck"></i> {{ $order->courier_display_name }} Tracking ID: <strong>{{ $order->courier_tracking_id }}</strong>
                                        </p>
                                        @if(!empty($order->courier_status))
                                        <p style="font-size: 11px; color: #555; margin: 2px 0 0 0;">
                                            Status: <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $order->courier_status) }}</strong>
                                        </p>
                                        @endif
                                    </div>
                                    @endif
                                
                                </div>
                            </td>
                           
                        </tr>
                        <tr>
                             <td>
                                <div class="invoice_to" style="padding-top: 20px;">
                                    <p style="font-size:16px;line-height:1.8;color:#222;"><strong>Contact Info:</strong></p>
                                    <p style="font-size:16px;line-height:1.8;color:#222;">{{$order->shipping?$order->shipping->name:''}}</p>
                                    <p style="font-size:16px;line-height:1.8;color:#222;">{{$order->shipping?$order->shipping->phone:''}}</p>
                                    <p style="font-size:16px;line-height:1.8;color:#222;">{{$order->shipping?$order->shipping->address:''}}</p>
                                    <p style="font-size:16px;line-height:1.8;color:#222;">{{$order->shipping?$order->shipping->area:''}}</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                       <table class="table" style="margin-top: 30px;margin-bottom: 0;">
                        <thead style="background: #4DBC60; color: #fff;">
                            <tr>
                                <th>SL</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderdetails as $key=>$value)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$value->product_name}} <br> @if($value->product_size) <small>Size: {{$value->product_size}}</small> @endif   @if($value->product_color) <small>Color: {{$value->product_color}}</small> @endif </td>
                                <td>৳{{$value->sale_price}}</td>
                                <td>{{$value->qty}}</td>
                                <td>৳{{$value->sale_price*$value->qty}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align:right ;border:0px;    padding: 5px;">SubTota</td>
                                <td style="text-align:center ;border:0px;    padding: 5px;">৳{{$order->orderdetails->sum('sale_price')}}</td>
                            </tr>    
                             <tr>
                                <td colspan="4" style="text-align:right ;border:0px;    padding: 5px;">Shipping(+)</td>
                                <td style="text-align:center ;border:0px;    padding: 5px;">৳{{ $order->shipping_charge }}</td>
                            </tr>    
                            <tr >
                                <td colspan="4" style="text-align:right ;border:0px;    padding: 5px;">Discount(-)</td>
                                <td style="text-align:center ;border:0px;    padding: 5px;">৳{{ $order->discount }}</td>
                            </tr> 
                             <tr>
                                <td colspan="4" style="text-align:right ;border:0px;    padding: 5px;">Final Total(-)</td>
                                <td style="text-align:center ;border:0px;    padding: 5px;">৳{{$order->amount }}</td>
                            </tr> 
                           
                        </tfoot>
                    </table>
                    <!--<div class="invoice-bottom">-->
                        
                       
                    <!--    <div class="terms-condition" style="overflow: hidden; width: 100%; text-align: center; padding: 20px 0; border-top: 1px solid #ddd;">-->
                    <!--        <h5 style="font-style: italic;"><a href="{{route('page',['slug'=>'terms-condition'])}}">Terms & Conditions</a></h5>-->
                    <!--        <p style="text-align: center; font-style: italic; font-size: 15px; margin-top: 10px;">* This is a computer generated invoice, does not require any signature.</p>-->
                    <!--    </div>-->
                    <!--</div>-->
                </div>
            </div>
        </div>
    </div>
</section>
@endforeach
<script>
    function printFunction() {
        window.print();
    }
</script>
</body>
</html>
