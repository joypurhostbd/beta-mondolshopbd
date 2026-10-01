<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\BannerCategoryStoreRequest;
use App\Http\Requests\Admin\BannerCategoryUpdateRequest;
use App\Models\Banner;
use App\Models\BannerCategory;
use Toastr;

class BannerCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:banner-category-list|banner-category-create|banner-category-edit|banner-category-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:banner-category-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:banner-category-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:banner-category-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = BannerCategory::withCount('banners')->orderBy('id', 'DESC')->get();
        $total_categories = BannerCategory::count();
        $active_categories = BannerCategory::where('status', 1)->count();
        $inactive_categories = BannerCategory::where('status', 0)->count();
        $total_banners = Banner::count();

        return view('backEnd.banner.category.index', compact(
            'data',
            'total_categories',
            'active_categories',
            'inactive_categories',
            'total_banners'
        ));
    }

    public function create()
    {
        return view('backEnd.banner.category.create');
    }

    public function store(BannerCategoryStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        BannerCategory::create($input);

        Toastr::success('Success', 'Banner category created successfully');
        return redirect()->route('banner_category.index');
    }
    
    public function edit($id)
    {
        $edit_data = BannerCategory::findOrFail($id);
        return view('backEnd.banner.category.edit', compact('edit_data'));
    }
    
    public function update(BannerCategoryUpdateRequest $request)
    {
        $catId = $request->id ?? $request->hidden_id;
        $update_data = BannerCategory::findOrFail($catId);
        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Banner category updated successfully');
        return redirect()->route('banner_category.index');
    }
 
    public function inactive(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:banner_categories,id']);
        $inactive = BannerCategory::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Banner category deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:banner_categories,id']);
        $active = BannerCategory::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Banner category activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        Toastr::error('Error', 'Banner category deletion is disabled');
        return redirect()->back();
    }
}

