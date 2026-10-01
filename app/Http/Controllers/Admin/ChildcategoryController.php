<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ChildcategoryStoreRequest;
use App\Http\Requests\Admin\ChildcategoryUpdateRequest;
use App\Models\Childcategory;
use App\Models\Subcategory;
use Toastr;
use Image;
use File;
use Str;
use DB;

class ChildcategoryController extends Controller
{
    public function getSubCategory(Request $request){
        $category = DB::table("subcategories")
        ->where("subcategorytype", $request->childcategorytype)
        ->pluck('subcategoryName', 'id');
        return response()->json($category);
    }        

    function __construct()
    {
        $this->middleware('permission:childcategory-list|childcategory-create|childcategory-edit|childcategory-delete', ['only' => ['index','store']]);
        $this->middleware('permission:childcategory-create', ['only' => ['create','store']]);
        $this->middleware('permission:childcategory-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:childcategory-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = Childcategory::orderBy('id', 'DESC')
            ->with(['subcategory.category'])
            ->withCount(['products'])
            ->get();

        $metrics = [
            'total' => Childcategory::count(),
            'active' => Childcategory::where('status', 1)->count(),
            'inactive' => Childcategory::where('status', 0)->count(),
            'linked_subcategories' => Childcategory::distinct('subcategory_id')->count('subcategory_id'),
        ];

        return view('backEnd.childcategory.index', compact('data', 'metrics'));
    }
    public function create()
    {
        return view('backEnd.childcategory.create');
    }
    public function store(ChildcategoryStoreRequest $request)
    {
        $input = $request->validated();

        $input['slug'] = strtolower(preg_replace('/\s+/', '-', $request->childcategoryName));
        $input['slug'] = str_replace('/', '', $input['slug']);
        
        Childcategory::create($input);
        Toastr::success('Success', 'Data insert successfully');
        return redirect()->route('childcategories.index');
    }
    
    public function edit($id)
    {
        $edit_data = Childcategory::find($id);
        $categories = Subcategory::select('id', 'subcategoryName')->get();
        return view('backEnd.childcategory.edit', compact('edit_data', 'categories'));
    }
    
    public function update(ChildcategoryUpdateRequest $request)
    {
        $update_data = Childcategory::find($request->id);
        $input = $request->validated();
        
        $input['slug'] = strtolower(preg_replace('/\s+/', '-', $request->childcategoryName));
        $input['slug'] = str_replace('/', '', $input['slug']);
        $input['status'] = $request->status ? 1 : 0;
        
        $update_data->update($input);

        Toastr::success('Success', 'Data update successfully');
        return redirect()->route('childcategories.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Childcategory::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Childcategory::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $delete_data = Childcategory::find($request->hidden_id);
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
