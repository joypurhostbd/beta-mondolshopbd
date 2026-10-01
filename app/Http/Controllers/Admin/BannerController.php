<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\BannerStoreRequest;
use App\Http\Requests\Admin\BannerUpdateRequest;
use App\Models\BannerCategory;
use App\Models\Banner;
use Toastr;
use File;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banner-list|banner-create|banner-edit|banner-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:banner-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:banner-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:banner-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Banner::with('category')->orderBy('id', 'DESC')->get();
        $total_banners = Banner::count();
        $active_banners = Banner::where('status', 1)->count();
        $inactive_banners = Banner::where('status', 0)->count();
        $total_categories = BannerCategory::count();

        return view('backEnd.banner.index', compact(
            'data',
            'total_banners',
            'active_banners',
            'inactive_banners',
            'total_categories'
        ));
    }

    public function create()
    {
        $categories = BannerCategory::orderBy('name', 'ASC')->select('id', 'name')->get();
        return view('backEnd.banner.create', compact('categories'));
    }

    public function store(BannerStoreRequest $request)
    {
        $input = $request->validated();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            $uploadPath = 'uploads/banner/';
            $file->move(public_path($uploadPath), $name);
            $input['image'] = $uploadPath . $name;
        }

        $input['status'] = $request->status ? 1 : 0;
        Banner::create($input);

        Toastr::success('Success', 'Banner created successfully');
        return redirect()->route('banners.index');
    }
    
    public function edit($id)
    {
        $edit_data = Banner::findOrFail($id);
        $categories = BannerCategory::orderBy('name', 'ASC')->select('id', 'name')->get();
        return view('backEnd.banner.edit', compact('edit_data', 'categories'));
    }
    
    public function update(BannerUpdateRequest $request)
    {
        $bannerId = $request->id ?? $request->hidden_id;
        $update_data = Banner::findOrFail($bannerId);
        $input = $request->validated();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            $uploadPath = 'uploads/banner/';
            $file->move(public_path($uploadPath), $name);
            $input['image'] = $uploadPath . $name;

            if (!empty($update_data->image) && File::exists(public_path($update_data->image))) {
                File::delete(public_path($update_data->image));
            }
        } else {
            $input['image'] = $update_data->image;
        }

        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Banner updated successfully');
        return redirect()->route('banners.index');
    }
 
    public function inactive(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:banners,id']);
        $inactive = Banner::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Banner deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:banners,id']);
        $active = Banner::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Banner activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:banners,id']);
        $delete_data = Banner::findOrFail($request->hidden_id);

        if (!empty($delete_data->image) && File::exists(public_path($delete_data->image))) {
            File::delete(public_path($delete_data->image));
        }

        $delete_data->delete();

        Toastr::success('Success', 'Banner deleted successfully');
        return redirect()->back();
    }
}
