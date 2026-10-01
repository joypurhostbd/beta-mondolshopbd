<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ReviewStoreRequest;
use App\Http\Requests\Admin\ReviewUpdateRequest;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\Product;
use App\Models\Review;
use App\Models\Customer;
class ReviewController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:review-list|review-create|review-edit|review-delete', ['only' => ['index', 'pending', 'show']]);
         $this->middleware('permission:review-create', ['only' => ['create', 'store']]);
         $this->middleware('permission:review-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
         $this->middleware('permission:review-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = Review::with(['product.image', 'customer'])->orderBy('id', 'DESC')->get();
        $metrics = $this->getMetrics();

        return view('backEnd.review.index', compact('show_data', 'metrics'));
    }

    public function pending()
    {
        $data = Review::with(['product.image', 'customer'])->where('status', 'pending')->orderBy('id', 'DESC')->get();
        $metrics = $this->getMetrics();

        return view('backEnd.review.pending', compact('data', 'metrics'));
    }

    /**
     * Compute summary metrics for review management.
     */
    private function getMetrics(): array
    {
        return [
            'total' => Review::count(),
            'active' => Review::where('status', 'active')->count(),
            'pending' => Review::where('status', 'pending')->count(),
            'avg_rating' => Review::averageRating(),
        ];
    }

    public function create()
    {
        $products = Product::where('status', 1)->select('id', 'name')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'email')->get();
        return view('backEnd.review.create', compact('products', 'customers'));
    }

    public function store(ReviewStoreRequest $request)
    {
        $input = $request->validated();

        if (!empty($request->customer_id)) {
            $customer = Customer::find($request->customer_id);
            if ($customer) {
                $input['name'] = $customer->name ?: ($input['name'] ?? 'Customer');
                $input['email'] = $customer->email ?: ($input['email'] ?? null);
            }
        }

        if (empty($input['name'])) {
            $input['name'] = 'Customer';
        }

        $input['status'] = (!empty($request->status) && ($request->status == 1 || $request->status === 'active')) ? 'active' : 'pending';

        Review::create($input);

        Toastr::success('Success', 'Review created successfully');
        return redirect()->route('reviews.index');
    }
    
    public function edit($id)
    {
        $edit_data = Review::with(['product', 'customer'])->find($id);

        if (!$edit_data) {
            Toastr::error('Error', 'Review not found');
            return redirect()->route('reviews.index');
        }

        $products = Product::where('status', 1)->select('id', 'name')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'email')->get();
        return view('backEnd.review.edit', compact('edit_data', 'products', 'customers'));
    }
    
    public function update(ReviewUpdateRequest $request)
    {
        $id = $request->id ?? $request->hidden_id;
        $update_data = Review::find($id);

        if (!$update_data) {
            Toastr::error('Error', 'Review not found');
            return redirect()->route('reviews.index');
        }

        $input = $request->validated();
        $input['status'] = (!empty($request->status) && ($request->status == 1 || $request->status === 'active')) ? 'active' : 'pending';
        
        $update_data->update($input);

        Toastr::success('Success', 'Review updated successfully');
        return redirect()->route('reviews.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Review::find($request->hidden_id);
        if ($inactive) {
            $inactive->status = 'pending';
            $inactive->save();
            Toastr::success('Success', 'Review marked as pending');
        }
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = Review::find($request->hidden_id);
        if ($active) {
            $active->status = 'active';
            $active->save();
            Toastr::success('Success', 'Review approved successfully');
        }
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $delete_data = Review::find($request->hidden_id);
        if ($delete_data) {
            $delete_data->delete();
            Toastr::success('Success', 'Review deleted successfully');
        }
        return redirect()->back();
    }
}

