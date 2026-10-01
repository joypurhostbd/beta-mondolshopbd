<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\AdminCustomerUpdateRequest;
use Illuminate\Support\Arr;
use App\Models\Customer;
use App\Models\Order;
use App\Models\IpBlock;
use Toastr;
use Image;
use File;
use Auth;
use Hash;

class CustomerManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:customer-list|customer-edit', ['only' => ['index', 'profile', 'ip_block']]);
        $this->middleware('permission:customer-edit', ['only' => ['edit', 'update', 'active', 'inactive', 'adminlog', 'ipblock_store', 'ipblock_update', 'ipblock_destroy']]);
    }

    public function index(Request $request)
    {
        $query = Customer::query()->withCount('orders');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('phone', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active' || $request->status == '1') {
                $query->where(function ($q) {
                    $q->where('status', 'active')->orWhere('status', 1);
                });
            } elseif ($request->status === 'inactive' || $request->status == '0') {
                $query->where(function ($q) {
                    $q->where('status', 'inactive')->orWhere('status', 0);
                });
            }
        }

        $show_data = $query->orderBy('id', 'DESC')->paginate(20)->withQueryString();

        $kpis = [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::where(function ($q) {
                $q->where('status', 'active')->orWhere('status', 1);
            })->count(),
            'inactive_customers' => Customer::where(function ($q) {
                $q->where('status', 'inactive')->orWhere('status', 0);
            })->count(),
            'total_orders' => Order::whereNotNull('customer_id')->count(),
        ];

        return view('backEnd.customer.index', compact('show_data', 'kpis'));
    }

    public function edit($id)
    {
        $edit_data = Customer::findOrFail($id);
        return view('backEnd.customer.edit', compact('edit_data'));
    }

    public function update(AdminCustomerUpdateRequest $request)
    {
        $input = $request->validated();
        $update_data = Customer::findOrFail($request->hidden_id);

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);
        }

        $image = $request->file('image');
        if ($image) {
            $uploadDir = public_path('uploads/customer');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0777, true, true);
            }

            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destination = 'uploads/customer/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->encode('webp', 90);
                $width = 200;
                $height = 200;
                $img->height() > $img->width() ? $width = null : $height = null;
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save(public_path($destination));
                $imageUrl = $destination;
            } catch (\Throwable $e) {
                $image->move($uploadDir, $name);
                $imageUrl = 'uploads/customer/' . $name;
            }

            if ($update_data->image && File::exists(public_path($update_data->image)) && !str_contains($update_data->image, 'default')) {
                File::delete(public_path($update_data->image));
            }
            $input['image'] = $imageUrl;
        } else {
            $input['image'] = $update_data->image;
        }

        $input['status'] = $request->has('status') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Customer data updated successfully');
        return redirect()->route('customers.index');
    }

    public function inactive(Request $request)
    {
        $inactive = Customer::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'Customer status set to inactive successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = Customer::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'Customer status set to active successfully');
        return redirect()->back();
    }

    public function profile(Request $request)
    {
        $profile = Customer::with(['orders.status', 'orders.shipping', 'orders.orderdetails'])->findOrFail($request->id);

        $customer_stats = [
            'total_orders' => $profile->orders->count(),
            'total_spent' => $profile->orders->where('order_status', 6)->sum('amount') ?: $profile->orders->sum('amount'),
            'total_all_orders_amount' => $profile->orders->sum('amount'),
            'last_order_date' => $profile->orders->max('created_at'),
        ];

        return view('backEnd.customer.profile', compact('profile', 'customer_stats'));
    }

    public function adminlog(Request $request)
    {
        $customer = Customer::findOrFail($request->hidden_id);
        Auth::guard('customer')->loginUsingId($customer->id);
        return redirect()->route('customer.account');
    }

    public function ip_block(Request $request)
    {
        $data = IpBlock::orderBy('id', 'DESC')->get();

        $kpis = [
            'total_blocked_ips' => $data->count(),
            'recent_blocked_ips' => IpBlock::where('created_at', '>=', now()->subDays(7))->count(),
            'total_customers' => Customer::count(),
            'total_orders' => Order::whereNotNull('customer_id')->count(),
        ];

        return view('backEnd.reports.ipblock', compact('data', 'kpis'));
    }

    public function ipblock_store(Request $request)
    {
        $this->validate($request, [
            'ip_no' => 'required|string|max:100',
            'reason' => 'required|string|max:500',
        ]);

        $store_data = new IpBlock();
        $store_data->ip_no = $request->ip_no;
        $store_data->reason = $request->reason;
        $store_data->save();
        Toastr::success('Success', 'IP address added successfully');
        return redirect()->back();
    }

    public function ipblock_update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer|exists:ip_blocks,id',
            'ip_no' => 'required|string|max:100',
            'reason' => 'required|string|max:500',
        ]);

        $update_data = IpBlock::findOrFail($request->id);
        $update_data->ip_no = $request->ip_no;
        $update_data->reason = $request->reason;
        $update_data->save();
        Toastr::success('Success', 'IP address updated successfully');
        return redirect()->back();
    }

    public function ipblock_destroy(Request $request)
    {
        $delete_data = IpBlock::findOrFail($request->id);
        $delete_data->delete();
        Toastr::success('Success', 'IP address deleted successfully');
        return redirect()->back();
    }
}
