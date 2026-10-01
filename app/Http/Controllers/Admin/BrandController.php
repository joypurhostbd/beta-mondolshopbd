<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\BrandStoreRequest;
use App\Http\Requests\Admin\BrandUpdateRequest;
use App\Models\Brand;
use Image;
use File;
use Toastr;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:brand-list|brand-create|brand-edit|brand-delete|attribute-list', ['only' => ['index', 'show']]);
        $this->middleware('permission:brand-create|attribute-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:brand-edit|attribute-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:brand-delete|attribute-delete', ['only' => ['destroy']]);
    }
    
    public function index(Request $request)
    {
        $data = Brand::orderBy('id', 'DESC')->withCount(['products'])->get();

        $metrics = [
            'total' => Brand::count(),
            'active' => Brand::where('status', 1)->count(),
            'inactive' => Brand::where('status', 0)->count(),
            'with_products' => Brand::has('products')->count(),
        ];

        return view('backEnd.brand.index', compact('data', 'metrics'));
    }
    public function create()
    {
        return view('backEnd.brand.create');
    }
    public function store(BrandStoreRequest $request)
    {
        // image with intervention 
        $image = $request->file('image');
        if($image){
            $name = time() . '-' . Str::random(10) . '.webp';
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $uploadpath = 'uploads/brand/';
            $imageUrl = $uploadpath.$name; 
            $img=Image::make($image->getRealPath());
            $img->encode('webp', 90);
            $width = 210;
            $height = 210;
            $img->height() > $img->width() ? $width=null : $height=null;
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($imageUrl); 
        }else{
            $imageUrl = NULL;
        }
       

        $input = $request->validated();
        $input['slug'] = strtolower(preg_replace('/\s+/u', '-', trim($request->name)));
        $input['name_bn'] = $request->name_bn ?? $request->name;
        $input['image'] = $imageUrl;
        Brand::create($input);
        Toastr::success('Success','Data insert successfully');
        return redirect()->route('brands.index');
    }
    
    public function edit($id)
    {
        $edit_data = Brand::find($id);
        return view('backEnd.brand.edit',compact('edit_data'));
    }
    
    public function update(BrandUpdateRequest $request)
    {
        $update_data = Brand::find($request->id);
        $input = $request->validated();
        $image = $request->file('image');
        if($image){
            // image with intervention 
            $name = time() . '-' . Str::random(10) . '.webp';
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp',$name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $uploadpath = 'uploads/brand/';
            $imageUrl = $uploadpath.$name; 
            $img=Image::make($image->getRealPath());
            $img->encode('webp', 90);
            $width = 210;
            $height = 210;
            $img->height() > $img->width() ? $width=null : $height=null;
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($imageUrl); 
            $input['image'] = $imageUrl;
            File::delete($update_data->image);
        }else{
            $input['image'] = $update_data->image;
        }
        $input['slug'] = strtolower(preg_replace('/\s+/u', '-', trim($request->name)));
        $input['name_bn'] = $request->name_bn ?? $request->name;
        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success','Data update successfully');
        return redirect()->route('brands.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Brand::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Brand::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Brand::find($request->hidden_id);
        if ($delete_data) {
            if ($delete_data->image && File::exists($delete_data->image)) {
                File::delete($delete_data->image);
            }
            $delete_data->delete();
            Toastr::success('Success', 'Data delete successfully');
        }
        return redirect()->back();
    }
}
