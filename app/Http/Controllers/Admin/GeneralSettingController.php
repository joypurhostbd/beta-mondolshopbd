<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SettingStoreRequest;
use App\Http\Requests\Admin\SettingUpdateRequest;
use Toastr;
use Image;
use File;
use DB;

class GeneralSettingController extends Controller
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
        $show_data = GeneralSetting::orderBy('id', 'DESC')->get();
        $activeSetting = GeneralSetting::where('status', 1)->first();

        $kpis = [
            'total_settings' => GeneralSetting::count(),
            'active_settings' => GeneralSetting::where('status', 1)->count(),
            'inactive_settings' => GeneralSetting::where('status', 0)->count(),
            'active_brand_name' => $activeSetting ? $activeSetting->name : 'Not Configured',
        ];

        return view('backEnd.settings.index', compact('show_data', 'kpis'));
    }

    public function create()
    {
        return view('backEnd.settings.create');
    }

    public function store(SettingStoreRequest $request)
    {
        $input = $request->validated();
        $uploadDir = public_path('uploads/settings');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0777, true, true);
        }

        // White logo
        $imageUrl = null;
        if ($request->hasFile('white_logo')) {
            $image = $request->file('white_logo');
            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destination = 'uploads/settings/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->encode('webp', 90);
                $img->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save(public_path($destination));
                $imageUrl = $destination;
            } catch (\Throwable $e) {
                $image->move($uploadDir, $name);
                $imageUrl = 'uploads/settings/' . $name;
            }
        }

        // Dark logo
        $image2Url = null;
        if ($request->hasFile('dark_logo')) {
            $image2 = $request->file('dark_logo');
            $name2 = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image2->getClientOriginalExtension();
            $name2 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name2);
            $name2 = strtolower(preg_replace('/\s+/', '-', $name2));
            $destination2 = 'uploads/settings/' . $name2;

            try {
                $img2 = Image::make($image2->getRealPath());
                $img2->encode('webp', 90);
                $img2->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img2->save(public_path($destination2));
                $image2Url = $destination2;
            } catch (\Throwable $e) {
                $image2->move($uploadDir, $name2);
                $image2Url = 'uploads/settings/' . $name2;
            }
        }

        // Favicon
        $image3Url = null;
        if ($request->hasFile('favicon')) {
            $image3 = $request->file('favicon');
            $name3 = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image3->getClientOriginalExtension();
            $name3 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name3);
            $name3 = strtolower(preg_replace('/\s+/', '-', $name3));
            $destination3 = 'uploads/settings/' . $name3;

            try {
                $img3 = Image::make($image3->getRealPath());
                $img3->encode('webp', 90);
                $img3->resize(32, 32);
                $img3->save(public_path($destination3));
                $image3Url = $destination3;
            } catch (\Throwable $e) {
                $image3->move($uploadDir, $name3);
                $image3Url = 'uploads/settings/' . $name3;
            }
        }

        $paymentImgUrl = null;
        if ($request->hasFile('payment_methods_image')) {
            $paymentImg = $request->file('payment_methods_image');
            $pName = time() . '-payments-' . \Illuminate\Support\Str::random(8) . '.' . $paymentImg->getClientOriginalExtension();
            $paymentImg->move($uploadDir, $pName);
            $paymentImgUrl = 'uploads/settings/' . $pName;
        }

        $existing = GeneralSetting::where('status', 1)->first() ?? GeneralSetting::latest()->first();

        $input['white_logo'] = $imageUrl ?? $existing?->white_logo;
        $input['dark_logo'] = $image2Url ?? $existing?->dark_logo;
        $input['favicon'] = $image3Url ?? $existing?->favicon;
        $input['payment_methods_image'] = $paymentImgUrl ?? $existing?->payment_methods_image;
        $input['status'] = $request->has('status') ? 1 : 0;
        $input['newsletter_status'] = $request->has('newsletter_status') ? 1 : 0;
        $input['app_download_status'] = $request->has('app_download_status') ? 1 : 0;
        $input['features_status'] = $request->has('features_status') ? 1 : 0;
        $input['show_payment_methods'] = $request->has('show_payment_methods') ? 1 : 0;
        $input['whatsapp_status'] = $request->has('whatsapp_status') ? 1 : 0;
        $input['whatsapp_dynamic_context'] = $request->has('whatsapp_dynamic_context') ? 1 : 0;
        $input['whatsapp_product_button'] = $request->has('whatsapp_product_button') ? 1 : 0;

        GeneralSetting::create($input);
        Toastr::success('Success', 'General setting created successfully');
        return redirect()->route('settings.index');
    }

    public function edit($id)
    {
        $edit_data = GeneralSetting::findOrFail($id);
        return view('backEnd.settings.edit', compact('edit_data'));
    }

    public function update(SettingUpdateRequest $request)
    {
        $targetId = $request->hidden_id ?? $request->id;
        $update_data = GeneralSetting::findOrFail($targetId);
        $input = $request->validated();
        $uploadDir = public_path('uploads/settings');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0777, true, true);
        }

        // White logo
        if ($request->hasFile('white_logo')) {
            $image = $request->file('white_logo');
            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destination = 'uploads/settings/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->encode('webp', 90);
                $img->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save(public_path($destination));
                $imageUrl = $destination;
            } catch (\Throwable $e) {
                $image->move($uploadDir, $name);
                $imageUrl = 'uploads/settings/' . $name;
            }

            if ($update_data->white_logo && File::exists(public_path($update_data->white_logo))) {
                File::delete(public_path($update_data->white_logo));
            }
            $input['white_logo'] = $imageUrl;
        } else {
            $input['white_logo'] = $update_data->white_logo;
        }

        // Dark logo
        if ($request->hasFile('dark_logo')) {
            $image2 = $request->file('dark_logo');
            $name2 = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image2->getClientOriginalExtension();
            $name2 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name2);
            $name2 = strtolower(preg_replace('/\s+/', '-', $name2));
            $destination2 = 'uploads/settings/' . $name2;

            try {
                $img2 = Image::make($image2->getRealPath());
                $img2->encode('webp', 90);
                $img2->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img2->save(public_path($destination2));
                $image2Url = $destination2;
            } catch (\Throwable $e) {
                $image2->move($uploadDir, $name2);
                $image2Url = 'uploads/settings/' . $name2;
            }

            if ($update_data->dark_logo && File::exists(public_path($update_data->dark_logo))) {
                File::delete(public_path($update_data->dark_logo));
            }
            $input['dark_logo'] = $image2Url;
        } else {
            $input['dark_logo'] = $update_data->dark_logo;
        }

        // Favicon
        if ($request->hasFile('favicon')) {
            $image3 = $request->file('favicon');
            $name3 = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image3->getClientOriginalExtension();
            $name3 = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name3);
            $name3 = strtolower(preg_replace('/\s+/', '-', $name3));
            $destination3 = 'uploads/settings/' . $name3;

            try {
                $img3 = Image::make($image3->getRealPath());
                $img3->encode('webp', 90);
                $img3->resize(32, 32);
                $img3->save(public_path($destination3));
                $image3Url = $destination3;
            } catch (\Throwable $e) {
                $image3->move($uploadDir, $name3);
                $image3Url = 'uploads/settings/' . $name3;
            }

            if ($update_data->favicon && File::exists(public_path($update_data->favicon))) {
                File::delete(public_path($update_data->favicon));
            }
            $input['favicon'] = $image3Url;
        } else {
            $input['favicon'] = $update_data->favicon;
        }

        // Payment Methods Image
        if ($request->hasFile('payment_methods_image')) {
            $paymentImg = $request->file('payment_methods_image');
            $pName = time() . '-payments-' . \Illuminate\Support\Str::random(8) . '.' . $paymentImg->getClientOriginalExtension();
            $paymentImg->move($uploadDir, $pName);
            if ($update_data->payment_methods_image && File::exists(public_path($update_data->payment_methods_image))) {
                File::delete(public_path($update_data->payment_methods_image));
            }
            $input['payment_methods_image'] = 'uploads/settings/' . $pName;
        } else {
            $input['payment_methods_image'] = $update_data->payment_methods_image;
        }

        $input['status'] = $request->has('status') ? 1 : 0;
        $input['newsletter_status'] = $request->has('newsletter_status') ? 1 : 0;
        $input['app_download_status'] = $request->has('app_download_status') ? 1 : 0;
        $input['features_status'] = $request->has('features_status') ? 1 : 0;
        $input['show_payment_methods'] = $request->has('show_payment_methods') ? 1 : 0;
        $input['whatsapp_status'] = $request->has('whatsapp_status') ? 1 : 0;
        $input['whatsapp_dynamic_context'] = $request->has('whatsapp_dynamic_context') ? 1 : 0;
        $input['whatsapp_product_button'] = $request->has('whatsapp_product_button') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'General setting updated successfully');
        return redirect()->route('settings.index');
    }

    public function inactive(Request $request)
    {
        $inactive = GeneralSetting::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'Setting deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = GeneralSetting::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'Setting activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $delete_data = GeneralSetting::findOrFail($request->hidden_id);

        if ($delete_data->white_logo && File::exists(public_path($delete_data->white_logo))) {
            File::delete(public_path($delete_data->white_logo));
        }
        if ($delete_data->dark_logo && File::exists(public_path($delete_data->dark_logo))) {
            File::delete(public_path($delete_data->dark_logo));
        }
        if ($delete_data->favicon && File::exists(public_path($delete_data->favicon))) {
            File::delete(public_path($delete_data->favicon));
        }

        $delete_data->delete();
        Toastr::success('Success', 'Setting deleted successfully');
        return redirect()->back();
    }
}
