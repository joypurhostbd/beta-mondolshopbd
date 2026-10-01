<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\CategoryStoreRequest;
use App\Http\Requests\Admin\CategoryUpdateRequest;
use App\Models\Category;
use Toastr;
use Image;
use File;
use Str;

class CategoryController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:category-list|category-create|category-edit|category-delete', ['only' => ['index','store']]);
         $this->middleware('permission:category-create', ['only' => ['create','store']]);
         $this->middleware('permission:category-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Category::orderBy('id', 'DESC')->with('category')->withCount(['subcategories', 'products'])->get();

        $metrics = [
            'total' => Category::count(),
            'active' => Category::where('status', 1)->count(),
            'inactive' => Category::where('status', 0)->count(),
            'front_view' => Category::where('front_view', 1)->count(),
        ];

        return view('backEnd.category.index', compact('data', 'metrics'));
    }

    public function create()
    {
        $categories = Category::orderBy('id','DESC')->select('id','name')->get();
        return view('backEnd.category.create',compact('categories'));
    }

    public function store(CategoryStoreRequest $request)
    {
        $input = $request->validated();
        $image = $request->file('image');
        $imageUrl = 'uploads/category/default.png';

        if ($image) {
            $uploadpath = public_path('uploads/category/');
            if (!File::isDirectory($uploadpath)) {
                File::makeDirectory($uploadpath, 0755, true, true);
            }

            $name = time() . '-' . Str::random(10) . '.webp';
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destinationPath = 'uploads/category/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->encode('webp', 90);
                $img->save(public_path($destinationPath));
                $imageUrl = $destinationPath;
            } catch (\Exception $e) {
                $image->move($uploadpath, $name);
                $imageUrl = 'uploads/category/' . $name;
            }
        }

        $baseSlug = Str::slug($request->name) ?: strtolower(preg_replace('/\s+/u', '-', trim($request->name)));
        $slug = $baseSlug ?: 'category-' . Str::random(6);
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $input['slug'] = $slug;
        $input['parent_id'] = $request->parent_id ? (int)$request->parent_id : 0;
        $input['front_view'] = $request->has('front_view') && $request->front_view ? 1 : 0;
        $input['status'] = $request->has('status') && $request->status ? 1 : 0;
        $input['meta_title'] = $request->meta_title ?: null;
        $input['meta_description'] = $request->meta_description ?: null;
        $input['image'] = $imageUrl;

        Category::create($input);
        Toastr::success('Success', 'Data insert successfully');
        return redirect()->route('categories.index');
    }

    public function edit($id)
    {
        $edit_data = Category::find($id);
        $categories = Category::select('id', 'name')->get();
        return view('backEnd.category.edit', compact('edit_data', 'categories'));
    }

    public function update(CategoryUpdateRequest $request)
    {
        $update_data = Category::findOrFail($request->id);
        $input = $request->validated();
        $image = $request->file('image');

        if ($image) {
            $uploadpath = public_path('uploads/category/');
            if (!File::isDirectory($uploadpath)) {
                File::makeDirectory($uploadpath, 0755, true, true);
            }

            $name = time() . '-' . Str::random(10) . '.webp';
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destinationPath = 'uploads/category/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->encode('webp', 90);
                $img->save(public_path($destinationPath));
                $imageUrl = $destinationPath;
            } catch (\Exception $e) {
                $image->move($uploadpath, $name);
                $imageUrl = 'uploads/category/' . $name;
            }

            if ($update_data->image && $update_data->image !== 'uploads/category/default.png' && File::exists(public_path($update_data->image))) {
                File::delete(public_path($update_data->image));
            }

            $input['image'] = $imageUrl;
        } else {
            $input['image'] = $update_data->image ?: 'uploads/category/default.png';
        }

        if ($request->name !== $update_data->name) {
            $baseSlug = Str::slug($request->name) ?: strtolower(preg_replace('/\s+/u', '-', trim($request->name)));
            $slug = $baseSlug ?: 'category-' . Str::random(6);
            $originalSlug = $slug;
            $counter = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $update_data->id)->exists()) {
                $slug = "{$originalSlug}-{$counter}";
                $counter++;
            }
            $input['slug'] = $slug;
        } else {
            $input['slug'] = $update_data->slug;
        }

        $parentId = $request->parent_id ? (int)$request->parent_id : 0;
        if ($parentId === (int)$update_data->id) {
            $parentId = 0;
        }

        $input['parent_id'] = $parentId;
        $input['front_view'] = $request->has('front_view') && $request->front_view ? 1 : 0;
        $input['status'] = $request->has('status') && $request->status ? 1 : 0;
        $input['meta_title'] = $request->meta_title ?: null;
        $input['meta_description'] = $request->meta_description ?: null;

        $update_data->update($input);

        Toastr::success('Success', 'Data update successfully');
        return redirect()->route('categories.index');
    }

    public function inactive(Request $request)
    {
        $inactive = Category::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success','Data inactive successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = Category::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success','Data active successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $delete_data = Category::find($request->hidden_id);
        if ($delete_data) {
            if ($delete_data->image && File::exists(public_path($delete_data->image))) {
                File::delete(public_path($delete_data->image));
            }
            $delete_data->delete();
            Toastr::success('Success', 'Data delete successfully');
        }
        return redirect()->back();
    }
}