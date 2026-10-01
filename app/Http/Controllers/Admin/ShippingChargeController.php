<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ShippingChargeStoreRequest;
use App\Http\Requests\Admin\ShippingChargeUpdateRequest;
use App\Models\ShippingCharge;
use Toastr;

class ShippingChargeController extends Controller
{    
    public function __construct()
    {
        $this->middleware('permission:shipping-list|shipping-create|shipping-edit|shipping-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:shipping-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:shipping-edit', ['only' => ['edit', 'update', 'inactive', 'active']]);
        $this->middleware('permission:shipping-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = ShippingCharge::orderBy('id', 'ASC')->get();

        $total_rates = ShippingCharge::count();
        $active_rates = ShippingCharge::where('status', 1)->count();
        $inactive_rates = ShippingCharge::where('status', 0)->count();
        $avg_rate = (float) (ShippingCharge::where('status', 1)->avg('amount') ?? 0);

        return view('backEnd.shippingcharge.index', compact(
            'show_data',
            'total_rates',
            'active_rates',
            'inactive_rates',
            'avg_rate'
        ));
    }

    public function create()
    {
        return view('backEnd.shippingcharge.create');
    }

    public function store(ShippingChargeStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        
        ShippingCharge::create($input);

        Toastr::success('Success', 'Shipping charge created successfully');
        return redirect()->route('shippingcharges.index');
    }

    public function show($id)
    {
        $shipping = ShippingCharge::findOrFail($id);
        return redirect()->route('shippingcharges.edit', $shipping->id);
    }

    public function edit($id)
    {
        $edit_data = ShippingCharge::findOrFail($id);
        return view('backEnd.shippingcharge.edit', compact('edit_data'));
    }

    public function update(ShippingChargeUpdateRequest $request)
    {
        $chargeId = $request->hidden_id ?? $request->id;
        $update_data = ShippingCharge::findOrFail($chargeId);

        $input = $request->validated();       
        $input['status'] = $request->has('status') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Shipping charge updated successfully');
        return redirect()->route('shippingcharges.index');
    }

    public function inactive(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:shipping_charges,id',
        ]);

        $inactive = ShippingCharge::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Shipping charge inactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:shipping_charges,id',
        ]);

        $active = ShippingCharge::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Shipping charge activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:shipping_charges,id',
        ]);

        $delete_data = ShippingCharge::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Shipping charge deleted successfully');
        return redirect()->back();
    }
}
