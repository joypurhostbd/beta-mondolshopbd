<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ColorStoreRequest;
use App\Http\Requests\Admin\ColorUpdateRequest;
use App\Models\Color;
use Toastr;

class ColorController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:color-list|color-create|color-edit|color-delete', ['only' => ['index','show']]);
         $this->middleware('permission:color-create', ['only' => ['create','store']]);
         $this->middleware('permission:color-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:color-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $show_data = Color::orderBy('colorName', 'ASC')->withCount(['products'])->get();

        $metrics = [
            'total' => Color::count(),
            'active' => Color::where('status', 1)->count(),
            'inactive' => Color::where('status', 0)->count(),
            'with_products' => Color::has('products')->count(),
        ];

        return view('backEnd.color.index', compact('show_data', 'metrics'));
    }
    public function create()
    {
        return view('backEnd.color.create');
    }
    public function store(ColorStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        Color::create($input);        
        
        Toastr::success('Success', 'Data insert successfully');
        return redirect()->route('colors.index');
    }
    
    public function edit($id)
    {
        $edit_data = Color::find($id);
        return view('backEnd.color.edit', compact('edit_data'));
    }
    
    public function update(ColorUpdateRequest $request)
    { 
        $id = $request->id ?? $request->hidden_id;
        $update_data = Color::find($id);
        $input = $request->validated();
        $input['status'] = $request->status ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Data update successfully');
        return redirect()->route('colors.index');
    }
 
    public function inactive(Request $request)
    {
        $inactive = Color::find($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'Data inactive successfully');
        return redirect()->back();
    }
    public function active(Request $request)
    {
        $active = Color::find($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'Data active successfully');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {       
        $delete_data = Color::find($request->hidden_id);
        if ($delete_data) {
            $delete_data->delete();
            Toastr::success('Success', 'Data delete successfully');
        }
        return redirect()->back();
    }
}
