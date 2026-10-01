<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SizeStoreRequest;
use App\Http\Requests\Admin\SizeUpdateRequest;
use App\Models\Size;
use Toastr;

class SizeController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:size-list|size-create|size-edit|size-delete|attribute-list', ['only' => ['index','show']]);
         $this->middleware('permission:size-create|attribute-create', ['only' => ['create','store']]);
         $this->middleware('permission:size-edit|attribute-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:size-delete|attribute-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = Size::orderBy('id', 'DESC')->withCount(['products'])->get();

        $metrics = [
            'total' => Size::count(),
            'active' => Size::where('status', 1)->count(),
            'inactive' => Size::where('status', 0)->count(),
            'with_products' => Size::has('products')->count(),
        ];

        return view('backEnd.size.index', compact('show_data', 'metrics'));
    }

    public function create()
    {
        return view('backEnd.size.create');
    }

    public function store(SizeStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        
        Size::create($input);        
        
        Toastr::success('Success', 'Data insert successfully');
        return redirect()->route('sizes.index');
    }
    
    public function edit($id)
    {
        $edit_data = Size::find($id);
        return view('backEnd.size.edit', compact('edit_data'));
    }
    
    public function update(SizeUpdateRequest $request)
    { 
        $id = $request->id ?? $request->hidden_id;
        $update_data = Size::find($id);

        if (!$update_data) {
            Toastr::error('Error', 'Size not found');
            return redirect()->route('sizes.index');
        }

        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Data update successfully');
        return redirect()->route('sizes.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Size::find($request->hidden_id);
        if ($inactive) {
            $inactive->status = 0;
            $inactive->save();
            Toastr::success('Success', 'Data inactive successfully');
        }
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = Size::find($request->hidden_id);
        if ($active) {
            $active->status = 1;
            $active->save();
            Toastr::success('Success', 'Data active successfully');
        }
        return redirect()->back();
    }

    public function destroy(Request $request)
    {       
        $delete_data = Size::find($request->hidden_id);
        if ($delete_data) {
            $delete_data->delete();
            Toastr::success('Success', 'Data delete successfully');
        }
        return redirect()->back();
    }
}