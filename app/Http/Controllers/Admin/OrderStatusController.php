<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\OrderStatusStoreRequest;
use App\Http\Requests\Admin\OrderStatusUpdateRequest;
use App\Models\OrderStatus;
use App\Models\Order;
use Toastr;
use Str;

class OrderStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:setting-list|setting-create|setting-edit|setting-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:setting-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setting-edit', ['only' => ['edit', 'update', 'inactive', 'active']]);
        $this->middleware('permission:setting-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = OrderStatus::withCount('orders')->orderBy('id', 'DESC')->get();

        $total_statuses = OrderStatus::count();
        $active_statuses = OrderStatus::where('status', 1)->count();
        $inactive_statuses = OrderStatus::where('status', 0)->count();
        $orders_count = Order::count();

        return view('backEnd.orderstatus.index', compact(
            'data',
            'total_statuses',
            'active_statuses',
            'inactive_statuses',
            'orders_count'
        ));
    }

    public function create()
    {
        return view('backEnd.orderstatus.create');
    }

    public function store(OrderStatusStoreRequest $request)
    {
        $input = $request->validated();
        $slug = !empty($request->slug) ? Str::slug($request->slug) : (Str::slug($request->name) ?: preg_replace('/\s+/u', '-', mb_strtolower(trim($request->name))));
        $input['slug'] = $slug;
        $input['status'] = $request->has('status') ? 1 : 0;

        OrderStatus::create($input);

        Toastr::success('Success', 'Order status created successfully');
        return redirect()->route('orderstatus.index');
    }

    public function show($id)
    {
        $orderStatus = OrderStatus::findOrFail($id);
        return redirect()->route('orderstatus.edit', $orderStatus->id);
    }

    public function edit($id)
    {
        $edit_data = OrderStatus::findOrFail($id);
        return view('backEnd.orderstatus.edit', compact('edit_data'));
    }

    public function update(OrderStatusUpdateRequest $request)
    {
        $statusId = $request->hidden_id ?? $request->id;
        $update_data = OrderStatus::findOrFail($statusId);

        $input = $request->validated();
        $slug = !empty($request->slug) ? Str::slug($request->slug) : (Str::slug($request->name) ?: preg_replace('/\s+/u', '-', mb_strtolower(trim($request->name))));
        $input['slug'] = $slug;
        $input['status'] = $request->has('status') ? 1 : 0;

        $update_data->update($input);

        Toastr::success('Success', 'Order status updated successfully');
        return redirect()->route('orderstatus.index');
    }

    public function inactive(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:order_statuses,id',
        ]);

        $inactive = OrderStatus::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Order status inactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:order_statuses,id',
        ]);

        $active = OrderStatus::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Order status activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:order_statuses,id',
        ]);

        $delete_data = OrderStatus::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Order status deleted successfully');
        return redirect()->back();
    }
}
