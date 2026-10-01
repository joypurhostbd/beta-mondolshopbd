<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PixelStoreRequest;
use App\Http\Requests\Admin\PixelUpdateRequest;
use App\Models\EcomPixel;
use Toastr;

class PixelsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:setting-list|setting-create|setting-edit|setting-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:setting-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:setting-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:setting-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = EcomPixel::orderBy('id', 'DESC')->get();
        $total_pixels = EcomPixel::count();
        $active_pixels = EcomPixel::where('status', 1)->count();
        $inactive_pixels = EcomPixel::where('status', 0)->count();
        $latest_pixel = EcomPixel::where('status', 1)->latest('id')->first();

        return view('backEnd.pixels.index', compact('data', 'total_pixels', 'active_pixels', 'inactive_pixels', 'latest_pixel'));
    }

    public function create()
    {
        return view('backEnd.pixels.create');
    }

    public function show($id)
    {
        $edit_data = EcomPixel::findOrFail($id);
        return view('backEnd.pixels.edit', compact('edit_data'));
    }

    public function store(PixelStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        $input['capi_status'] = $request->has('capi_status') ? 1 : 0;
        EcomPixel::create($input);

        Toastr::success('Success', 'Facebook Pixel and Conversions API configuration saved successfully');
        return redirect()->route('pixels.index');
    }

    public function edit($id)
    {
        $edit_data = EcomPixel::findOrFail($id);
        return view('backEnd.pixels.edit', compact('edit_data'));
    }

    public function update(PixelUpdateRequest $request)
    {
        $pixelId = $request->id ?? $request->hidden_id;
        $update_data = EcomPixel::findOrFail($pixelId);
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        $input['capi_status'] = $request->has('capi_status') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Facebook Pixel and Conversions API configuration updated successfully');
        return redirect()->route('pixels.index');
    }

    public function inactive(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:ecom_pixels,id']);
        $inactive = EcomPixel::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Pixel deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:ecom_pixels,id']);
        $active = EcomPixel::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Pixel activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:ecom_pixels,id']);
        $delete_data = EcomPixel::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Pixel deleted successfully');
        return redirect()->back();
    }
}
