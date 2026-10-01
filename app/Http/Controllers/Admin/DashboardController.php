<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use App\Services\DashboardAnalyticsService;
use App\Services\GeoOrderAnalyticsService;
use Session;
use Toastr;
use Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except(['locked','unlocked']);
    }

    public function dashboard(
        Request $request,
        GeoOrderAnalyticsService $geoService,
        DashboardAnalyticsService $analyticsService
    ) {
        $summary = $analyticsService->getDashboardSummary();

        $latest_order = Order::latest()->limit(5)->with(['customer', 'shipping', 'orderdetails', 'status'])->get();
        $latest_customer = Customer::latest()->limit(5)->get();

        $low_stock_products = $analyticsService->getLowStockProducts(5, 10);
        $top_selling_products = $analyticsService->getTopSellingProducts(5);
        $monthly_sale = $analyticsService->getMonthlyDeliveredSales(30);

        $geoAnalytics = $geoService->getGeographicOverview(['period' => 'all_time']);

        return view('backEnd.admin.dashboard', array_merge($summary, compact(
            'latest_order',
            'latest_customer',
            'low_stock_products',
            'top_selling_products',
            'monthly_sale',
            'geoAnalytics'
        )));
    }

    public function geoAnalytics(Request $request, GeoOrderAnalyticsService $geoService)
    {
        $filters = $request->only(['period', 'start_date', 'end_date', 'status', 'district']);
        $analytics = $geoService->getGeographicOverview($filters);

        return response()->json($analytics);
    }
    public function changepassword(){
        return view('backEnd.admin.changepassword');
    }
     public function newpassword(Request $request)
    {
        $this->validate($request, [
            'old_password'=>'required',
            'new_password'=>'required',
            'confirm_password' => 'required_with:new_password|same:new_password|'
        ]);

        $user = User::find(Auth::id());
        $hashPass = $user->password;

        if (Hash::check($request->old_password, $hashPass)) {

            $user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            Toastr::success('Success', 'Password changed successfully!');
            return redirect()->route('dashboard');
        }else{
            Toastr::error('Failed', 'Old password not match!');
            return back();
        }
    }
    public function locked(){
        // only if user is logged in
        
            Session::put('locked', true);
            return view('backEnd.auth.locked');
        

        return redirect()->route('login');
    }

    public function unlocked(Request $request)
    {
        if(!Auth::check())
            return redirect()->route('login');
        $password = $request->password;
        if(Hash::check($password,Auth::user()->password)){
            Session::forget('locked');
            Toastr::success('Success', 'You are logged in successfully!');
            return redirect()->route('dashboard');
        }
        Toastr::error('Failed', 'Your password not match!');
        return back();
    }
}
