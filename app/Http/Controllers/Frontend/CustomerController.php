<?php

namespace App\Http\Controllers\Frontend;
use App\Services\Payment\ShurjoPayService;
use App\Enums\PaymentStatusEnum;
use App\Enums\StatusEnum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Frontend\CustomerRegisterRequest;
use App\Http\Requests\Frontend\CustomerSigninRequest;
use App\Http\Requests\Frontend\CustomerForgotPasswordRequest;
use App\Http\Requests\Frontend\CustomerPasswordResetRequest;
use App\Http\Requests\Frontend\CustomerOrderSaveRequest;
use App\Http\Requests\Frontend\CustomerPasswordUpdateRequest;
use Brian2694\Toastr\Facades\Toastr;
use Intervention\Image\Facades\Image;
use App\Models\Customer;
use App\Models\District;
use App\Models\Order;
use App\Models\ShippingCharge;
use App\Models\OrderDetails;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\Review;
use App\Models\PaymentGateway;
use App\Models\SmsGateway;
use App\Models\GeneralSetting;
use App\Models\IncompleteOrder;
use Session;
use Hash;
use Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Mail;
use Str;
use DB;
use App\Jobs\SendMetaCapiEventJob;
use Illuminate\Support\Facades\Log;
class CustomerController extends Controller
{
    function __construct()
    {
        $this->middleware('customer', ['except' => ['register','store','verify','resendotp','account_verify','login','signin','logout','checkout','forgot_password','forgot_verify','forgot_reset','forgot_store','forgot_resend','order_save','order_success','order_track','order_track_result','invoice']]);
    }

    public function review(Request $request){
        $this->validate($request,[
            'ratting'=>'required_without:rating',
            'rating'=>'required_without:ratting',
            'review'=>'required',
        ]);

        // data save
        $review              =   new Review();
        $review->name        =   Auth::guard('customer')->user()->name ? Auth::guard('customer')->user()->name : 'N / A';
        $review->email       =   Auth::guard('customer')->user()->email ? Auth::guard('customer')->user()->email : 'N / A';
        $review->product_id  =   $request->product_id;
        $review->review      =   $request->review;
        $review->rating      =   $request->rating ?? $request->ratting;
        $review->customer_id =   Auth::guard('customer')->user()->id;
        $review->status      =   'pending';
        $review->save();

        Toastr::success('Thanks, Your review send successfully', 'Success!');
        return redirect()->back();
    }

    public function login(){
        return view('frontEnd.layouts.customer.login');
    }
    
    public function signin(CustomerSigninRequest $request){
        $auth_check = Customer::where('phone',$request->phone)->first();
        if($auth_check){
            if (Auth::guard('customer')->attempt(['phone' => $request->phone, 'password' => $request->password])) {
                $request->session()->regenerate();
                Toastr::success('You are login successfully', 'success!');
                if(Cart::instance('shopping')->count() > 0){
                    return redirect()->route('customer.checkout');
                }
                return redirect()->intended('customer/account');
            }
            Toastr::error('message', 'Opps! your phone or password wrong');
            return redirect()->back();
        }else{
            Toastr::error('message', 'Sorry! You have no account');
            return redirect()->back();
        }
    }
    
    public function register(){
        return view('frontEnd.layouts.customer.register');
    }
    
    public function store(CustomerRegisterRequest $request){
        $last_id = Customer::orderBy('id', 'desc')->first();
        $last_id = $last_id?$last_id->id+1:1;
        $store              = new Customer();
        $store->name        = $request->name;
        $store->slug        = strtolower(Str::slug($request->name.'-'.$last_id));
        $store->phone       = $request->phone;
        $store->email       = $request->email;
        $store->password    = bcrypt($request->password);
        $store->verify      = 1;
        $store->status      = 1;
        $store->save();
        
        Toastr::success('Success','Account Create Successfully');
        return redirect()->route('customer.login');
    }
    public function verify(){
        return view('frontEnd.layouts.customer.verify');
    }
    public function resendotp(Request $request){
        $customer_info = Customer::where('phone',session::get('verify_phone'))->first();
        if ($customer_info) {
            $customer_info->verify = rand(1111,9999);
            $customer_info->save();
            SmsGateway::sendAccountVerificationOtpSms($customer_info, $customer_info->verify);
        }
        Toastr::success('Success','Resend code send successfully');
        return redirect()->back();
    }
    public function account_verify(Request $request){
        $this->validate($request,[
            'otp' => 'required',
        ]);
        $customer_info = Customer::where('phone',session::get('verify_phone'))->first();
        if($customer_info->verify != $request->otp){
            Toastr::error('Success','Your OTP not match');
            return redirect()->back();
        }

        $customer_info->verify = 1;
        $customer_info->status = 1;
        $customer_info->save();
        Auth::guard('customer')->loginUsingId($customer_info->id);
        $request->session()->regenerate();
        return redirect()->route('customer.account');
    }
    public function forgot_password(){
        return view('frontEnd.layouts.customer.forgot_password');
    }
    
    public function forgot_verify(CustomerForgotPasswordRequest $request){
        $customer_info = Customer::where('phone',$request->phone)->first();
        if(!$customer_info){
            Toastr::error('Your phone number not found');
            return back();
        }
        $customer_info->forgot = rand(1111,9999);
        $customer_info->save();

        SmsGateway::sendPasswordResetOtpSms($customer_info, $customer_info->forgot);
        
        session::put('verify_phone',$request->phone);
        Toastr::success('Your account register successfully');
        return redirect()->route('customer.forgot.reset');
    }
    
    public function forgot_resend(Request $request){
        $customer_info = Customer::where('phone',session::get('verify_phone'))->first();
        if ($customer_info) {
            $customer_info->forgot = rand(1111,9999);
            $customer_info->save();
            SmsGateway::sendPasswordResetOtpSms($customer_info, $customer_info->forgot);
        }

        Toastr::success('Success','Resend code send successfully');
        return redirect()->back();
    }
    public function forgot_reset(){
        if(!Session::get('verify_phone')){
          Toastr::error('Something wrong please try again');
          return redirect()->route('customer.forgot.password'); 
        };
        return view('frontEnd.layouts.customer.forgot_reset');
    }
    public function forgot_store(CustomerPasswordResetRequest $request){

        $customer_info = Customer::where('phone',session::get('verify_phone'))->first();

        if($customer_info->forgot != $request->otp){
            Toastr::error('Success','Your OTP not match');
            return redirect()->back();
        }

        $customer_info->forgot = 1;
        $customer_info->password = bcrypt($request->password);
        $customer_info->save();
        if(Auth::guard('customer')->attempt(['phone' => $customer_info->phone, 'password' => $request->password])) {
            Session::forget('verify_phone');
            $request->session()->regenerate();
            Toastr::success('You are login successfully', 'success!');
                return redirect()->intended('customer/account');
        }
    }
    public function account(){
        return view('frontEnd.layouts.customer.account');
    }
    public function logout(Request $request){
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Toastr::success('You are logout successfully', 'success!');
        return redirect()->route('customer.login');
    }
    public function checkout(){
        $shippingcharge = ShippingCharge::where('status',1)->get();
        $select_charge = ShippingCharge::where('status',1)->first();
        $bkash_gateway = PaymentGateway::where(['status'=> 1, 'type'=>'bkash'])->first();
        $shurjopay_gateway = PaymentGateway::where(['status'=> 1, 'type'=>'shurjopay'])->first();
        if ($select_charge) {
            Session::put('shipping', $select_charge->amount);
        }

        $cartContent = Cart::instance('shopping')->content();
        $cartCount = (int) Cart::instance('shopping')->count();
        $subtotal = (float) str_replace(',', '', Cart::instance('shopping')->subtotal());
        $eventId = 'ic_' . substr(md5(session()->getId() . '_' . $subtotal . '_' . $cartCount), 0, 16);

        if ($cartCount > 0) {
            try {
                $fbp = request()->cookie('_fbp');
                $fbc = request()->cookie('_fbc') ?: (request()->has('fbclid') ? 'fb.1.' . time() . '.' . request()->get('fbclid') : null);
                $userData = [
                    'fbp' => $fbp,
                    'fbc' => $fbc,
                    'client_ip_address' => request()->ip(),
                    'client_user_agent' => request()->userAgent(),
                ];

                $contents = [];
                $contentIds = [];
                foreach ($cartContent as $item) {
                    $id = (string) $item->id;
                    $qty = (int) ($item->qty ?? 1);
                    $price = (float) ($item->price ?? 0);
                    $contents[] = [
                        'id' => $id,
                        'quantity' => $qty,
                        'item_price' => $price,
                    ];
                    $contentIds[] = $id;
                }

                $customData = [
                    'currency' => 'BDT',
                    'value' => $subtotal,
                    'content_type' => 'product',
                    'contents' => $contents,
                    'content_ids' => $contentIds,
                    'num_items' => $cartCount,
                ];

                SendMetaCapiEventJob::dispatch(
                    'InitiateCheckout',
                    $userData,
                    $customData,
                    $eventId,
                    request()->fullUrl()
                );
            } catch (\Throwable $e) {
                Log::warning('Meta CAPI InitiateCheckout dispatch error: ' . $e->getMessage());
            }
        }

        return view('frontEnd.layouts.customer.checkout', compact('shippingcharge', 'bkash_gateway', 'shurjopay_gateway', 'eventId'));
    }
    public function order_save(CustomerOrderSaveRequest $request){
            $ip = '';
            // Check for shared internet/ISP IP
            if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } 
            // Check for IP addresses passed from proxies
            elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // Sometimes HTTP_X_FORWARDED_FOR can contain multiple IP addresses separated by commas
                $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($ip_list as $ip_addr) {
                    $ip_addr = trim($ip_addr); // remove spaces
                    if (filter_var($ip_addr, FILTER_VALIDATE_IP)) {
                        $ip = $ip_addr;
                        break;
                    }
                }
            } 
            // Default IP
            elseif (!empty($_SERVER['REMOTE_ADDR']) && filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            // Convert IPv6 localhost to IPv4 localhost for consistency
            if ($ip == '::1') {
                $ip = '127.0.0.1';
            }

        if(Cart::instance('shopping')->count() <= 0) {
            Toastr::error('Your shopping empty', 'Failed!');
            return redirect()->back();
        }

        $subtotal = Cart::instance('shopping')->subtotal();
        $subtotal = str_replace(',','',$subtotal);
        $subtotal = str_replace('.00', '',$subtotal);
        $discount = Session::get('discount');

        $shipping_area  = ShippingCharge::where('id', $request->area)->first();
        $shippingfee  = Session::get('shipping') ?? ($shipping_area->amount ?? 70);
        $order = DB::transaction(function () use ($request, $ip, $subtotal, $shippingfee, $discount, $shipping_area) {
            if(Auth::guard('customer')->user()){
                $customer_id = Auth::guard('customer')->user()->id;
            }else{
                $exits_customer = Customer::where('phone',$request->phone)->select('phone','id')->first();
                if($exits_customer){
                    $customer_id = $exits_customer->id;
                }else{
                    $autoPassword = (string) rand(111111,999999);
                    $store              = new Customer();
                    $store->name        = $request->name;
                    $store->slug        = $request->name;
                    $store->phone       = $request->phone;
                    $store->password    = bcrypt($autoPassword);
                    $store->verify      = 1;
                    $store->status      = 1;
                    $store->save();
                    $customer_id = $store->id;

                    SmsGateway::sendAutoGeneratedPasswordSms($store, $autoPassword);
                }
            }

            // order data save
            $order                   = new Order();
            $order->invoice_id       = rand(11111,99999);
            $order->ip_address       = $ip;
            $order->amount           = ($subtotal + $shippingfee) - $discount;
            $order->discount         = $discount ? $discount : 0;
            $order->shipping_charge  = $shippingfee;
            $order->customer_id      = $customer_id;
            $order->order_status     = \App\Enums\OrderStatusEnum::Pending->value;
            $response = $order->fraud_check($request->phone);
            if (($response['status'] ?? '') === 'success' && isset($response['total_stats'])) {
                $order->f_check = $response['total_stats']['delivery_rate'] ?? 0;
                $order->fraud_report = [
                    'total_parcel' => (int) ($response['total_stats']['total_parcel'] ?? 0),
                    'total_delivered' => (int) ($response['total_stats']['total_delivered'] ?? 0),
                    'total_cancel' => (int) ($response['total_stats']['total_cancel'] ?? 0),
                    'delivery_rate' => (float) ($response['total_stats']['delivery_rate'] ?? 0),
                    'courier_breakdown' => $response['courier_breakdown'] ?? [],
                ];
            } else {
                $order->f_check = 0;
            }
            $order->save();

            // shipping data save
            $shipping              = new Shipping();
            $shipping->order_id    = $order->id;
            $shipping->customer_id = $customer_id;
            $shipping->name        = $request->name;
            $shipping->phone       = $request->phone;
            $shipping->address     = $request->address;
            $shipping->area        = $shipping_area->name ?? '';
            $shipping->save();

            // payment data save
            $payment                 = new Payment();
            $payment->order_id       = $order->id;
            $payment->customer_id    = $customer_id;
            $payment->payment_method = $request->payment_method;
            $payment->amount         = $order->amount;
            $payment->payment_status = \App\Enums\PaymentStatusEnum::Pending->value;
            $payment->save();

            // order details data save
            foreach(Cart::instance('shopping')->content() as $cart){
                $order_details                  = new OrderDetails();
                $order_details->order_id        = $order->id;
                $order_details->product_id      = $cart->id;
                $order_details->product_name    = $cart->name;
                $order_details->purchase_price  = $cart->options->purchase_price ?? 0;
                $order_details->product_color   = $cart->options->product_color ?? null;
                $order_details->product_size    = $cart->options->product_size ?? null;
                $order_details->sale_price      = $cart->price;
                $order_details->qty             = $cart->qty;
                $order_details->save();
            }

            $cartId = Session::get('incomplete-order');
            $incomplete = IncompleteOrder::where('cart_id',$cartId)->first();
            if($incomplete){
                $incomplete->delete();
            }

            // Record outbox event for reliable domain event publishing (Rule 11)
            app(\App\Services\OutboxService::class)->record('order.created', [
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'customer_id' => $order->customer_id,
                'amount' => $order->amount,
            ]);

            return $order;
        });

        // Server-side Purchase Tracking
        try {
            $purchaseEventId = 'order_' . ($order->invoice_id ?? $order->id);

            SendMetaCapiEventJob::dispatch(
                'Purchase',
                [],
                [],
                $purchaseEventId,
                request()->fullUrl(),
                $order->id
            );

            SendServerGtmEventJob::dispatch(
                'purchase',
                [],
                [],
                $purchaseEventId,
                $order->id
            );

            Log::info('Purchase tracking jobs dispatched', [
                'order_id' => $order->id,
                'invoice_id' => $order->invoice_id,
                'event_id' => $purchaseEventId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Purchase tracking dispatch failed: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
            ]);
        }

        event(new \App\Events\OrderPlaced($order));

        Cart::instance('shopping')->destroy();
        
        Toastr::success('Thanks, Your order place successfully', 'Success!');
        
        if ($request->payment_method == 'bkash') {
            $redirectUrl = '/bkash/checkout-url/create?order_id=' . $order->id;
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'redirect',
                    'redirect_url' => url($redirectUrl),
                ]);
            }
            return redirect($redirectUrl);
        } elseif ($request->payment_method == 'shurjopay') {
            $info = array( 
                'currency' => "BDT",
                'amount' => $order->amount, 
                'order_id' => uniqid(), 
                'discsount_amount' =>0 , 
                'disc_percent' =>0 , 
                'client_ip' => $request->ip(), 
                'customer_name' =>  $request->name, 
                'customer_phone' => $request->phone, 
                'email' => "customer@gmail.com", 
                'customer_address' => $request->address, 
                'customer_city' => $request->area, 
                'customer_state' => $request->area, 
                'customer_postcode' => "1212", 
                'customer_country' => "BD",
                'value1' => $order->id
            );
            $shurjopay_service = new ShurjoPayService();
            return $shurjopay_service->checkout($info);
        } else {
            Session::put('last_order_id', $order->id);
            session()->save();

            if ($request->ajax() || $request->wantsJson()) {
                $order->loadMissing(['orderdetails', 'shipping', 'payment']);

                $contents = [];
                $contentIds = [];
                foreach ($order->orderdetails as $d) {
                    $contents[] = [
                        'id' => (string) ($d->product_id ?? $d->id),
                        'quantity' => (int) ($d->qty ?? 1),
                        'item_price' => (float) ($d->sale_price ?? 0),
                    ];
                    $contentIds[] = (string) ($d->product_id ?? $d->id);
                }

                return response()->json([
                    'status' => 'success',
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoice_id,
                    'amount' => (float) $order->amount,
                    'event_id' => 'order_' . ($order->invoice_id ?? $order->id),
                    'contents' => $contents,
                    'content_ids' => $contentIds,
                    'num_items' => (int) $order->orderdetails->sum('qty'),
                    'redirect_url' => url('customer/order-success/' . $order->id),
                ]);
            }

            return redirect('customer/order-success/' . $order->id);
        }
        
    }
    
    public function orders($slug = 'all')
    {
        $customerId = Auth::guard('customer')->user()->id;
        $orders = Order::where('customer_id', $customerId)
            ->filterByStatusSlug($slug)
            ->with(['status', 'shipping'])
            ->latest()
            ->get();

        $courierCounts = [
            'all' => Order::where('customer_id', $customerId)->count(),
            'courier-pending' => Order::where('customer_id', $customerId)->filterByStatusSlug('courier-pending')->count(),
            'courier-partial' => Order::where('customer_id', $customerId)->filterByStatusSlug('courier-partial')->count(),
            'courier-cancel' => Order::where('customer_id', $customerId)->filterByStatusSlug('courier-cancel')->count(),
            'courier-delivered' => Order::where('customer_id', $customerId)->filterByStatusSlug('courier-delivered')->count(),
        ];

        return view('frontEnd.layouts.customer.orders', compact('orders', 'slug', 'courierCounts'));
    }
    public function order_success($id) {
        $order = Order::with(['orderdetails.image', 'orderdetails.product.category', 'shipping', 'payment'])
            ->where('id', $id)
            ->firstOrFail();
        $isOwner = false;
        if (Auth::guard('customer')->check() && (int) Auth::guard('customer')->user()->id === (int) $order->customer_id) {
            $isOwner = true;
        } elseif (Session::get('last_order_id') == $id) {
            $isOwner = true;
        }

        if (!$isOwner) {
            Toastr::error('Unauthorized access to order details.', 'Access Denied');
            return redirect()->route('home');
        }

        return view('frontEnd.layouts.customer.order_success',compact('order'));
    }
    public function invoice(Request $request)
    {
        $customerId = Auth::guard('customer')->check() ? Auth::guard('customer')->user()->id : null;
        $orderQuery = Order::where('id', $request->id);
        if ($customerId) {
            $orderQuery->where('customer_id', $customerId);
        } elseif (Session::get('last_order_id') == $request->id) {
            // Guest access to their just-placed order
        } else {
            Toastr::error('Unauthorized access to order details.', 'Access Denied');
            return redirect()->route('home');
        }

        $order = $orderQuery->with('orderdetails', 'payment', 'shipping', 'customer')->firstOrFail();
        return view('frontEnd.layouts.customer.invoice', compact('order'));
    } 
    public function order_note(Request $request)
    {
        $order = Order::where(['id'=>$request->id,'customer_id'=>Auth::guard('customer')->user()->id])->firstOrFail();
        return view('frontEnd.layouts.customer.order_note',compact('order'));
    }
    public function profile_edit(Request $request)
    {
        $profile_edit = Customer::where(['id'=>Auth::guard('customer')->user()->id])->firstOrFail();
        $districts = District::distinct()->select('district')->get();
        $areas = District::where(['district'=>$profile_edit->district])->select('area_name','id')->get();
        return view('frontEnd.layouts.customer.profile_edit',compact('profile_edit','districts','areas'));
    }
    public function profile_update(Request $request)
    {
        $update_data = Customer::where(['id'=>Auth::guard('customer')->user()->id])->firstOrFail();

        $image = $request->file('image');
        if($image){
            // image with intervention 
            $name = time() . '-' . Str::random(10) . '.webp';
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name);
            $name = strtolower(Str::slug($name));
            $uploadpath = 'uploads/customer/';
            $imageUrl = $uploadpath.$name; 
            $img = Image::make($image->getRealPath());
            $img->encode('webp', 90);
            $width = 120;
            $height = 120;
            $img->resize($width, $height);
            $img->save($imageUrl);
        }else{
            $imageUrl = $update_data->image;
        }

        $update_data->name        =   $request->name;
        $update_data->phone       =   $request->phone;
        $update_data->email       =   $request->email;
        $update_data->address     =   $request->address;
        $update_data->district    =   $request->district;
        $update_data->area        =   $request->area;
        $update_data->image       =   $imageUrl;
        $update_data->save();

        Toastr::success('Your profile update successfully', 'Success!');
       return redirect()->route('customer.account');
    }

    public function order_track(){
        return view('frontEnd.layouts.customer.order_track');
    }

     public function order_track_result(Request $request)
     {
        $phone = $request->phone;
        $invoice_id = $request->invoice_id;

        if (!$phone || !$invoice_id) {
            Toastr::error('Please provide both Invoice ID and Phone number to track your order.', 'Invalid Request');
            return redirect()->back();
        }

        $order = Order::with(['orderdetails', 'shipping', 'status', 'payment'])
            ->where('invoice_id', $invoice_id)
            ->whereHas('shipping', function ($q) use ($phone) {
                $q->where('phone', $phone);
            })
            ->get();

        if ($order->count() == 0) {
            Toastr::error('No order found matching the provided Invoice ID and Phone number.', 'Not Found');
            return redirect()->back();
        }

        return view('frontEnd.layouts.customer.tracking_result', compact('order'));
     }


    public function change_pass(){
        return view('frontEnd.layouts.customer.change_password');
    }

     public function password_update(CustomerPasswordUpdateRequest $request)
    {
        $customer = Customer::find(Auth::guard('customer')->user()->id);
        $hashPass = $customer->password;

        if (Hash::check($request->old_password, $hashPass)) {

            $customer->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            Toastr::success('Success', 'Password changed successfully!');
            return redirect()->route('customer.account');
        }else{
            Toastr::error('Failed', 'Old password not match!');
            return redirect()->back();
        }
    }
}
