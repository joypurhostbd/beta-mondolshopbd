<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PageStoreRequest;
use App\Http\Requests\Admin\PageUpdateRequest;
use App\Models\CreatePage;
use Toastr;
use Str;
class CreatePageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:page-list|page-create|page-edit|page-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:page-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:page-edit', ['only' => ['edit', 'update', 'inactive', 'active']]);
        $this->middleware('permission:page-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = CreatePage::orderBy('id', 'DESC')->get();

        $total_pages = CreatePage::count();
        $active_pages = CreatePage::where('status', 1)->count();
        $inactive_pages = CreatePage::where('status', 0)->count();
        $latest_page = CreatePage::latest('updated_at')->first()?->name ?? 'None';

        return view('backEnd.createpage.index', compact(
            'show_data',
            'total_pages',
            'active_pages',
            'inactive_pages',
            'latest_page'
        ));
    }

    public function create()
    {
        return view('backEnd.createpage.create');
    }

    public function store(PageStoreRequest $request)
    {
        $input = $request->validated();
        $slug = !empty($request->slug) ? Str::slug($request->slug) : (Str::slug($request->name) ?: preg_replace('/\s+/u', '-', mb_strtolower(trim($request->name))));
        $input['slug'] = $slug;
        $input['status'] = $request->has('status') ? 1 : 0;

        CreatePage::create($input);

        Toastr::success('Success', 'Page created successfully');
        return redirect()->route('pages.index');
    }

    public function show($id)
    {
        $page = CreatePage::findOrFail($id);
        return redirect()->route('pages.edit', $page->id);
    }

    public function edit($id)
    {
        $edit_data = CreatePage::findOrFail($id);
        return view('backEnd.createpage.edit', compact('edit_data'));
    }

    public function update(PageUpdateRequest $request)
    {
        $pageId = $request->hidden_id ?? $request->id;
        $update_data = CreatePage::findOrFail($pageId);

        $input = $request->validated();
        $slug = !empty($request->slug) ? Str::slug($request->slug) : (Str::slug($request->name) ?: preg_replace('/\s+/u', '-', mb_strtolower(trim($request->name))));
        $input['slug'] = $slug;
        $input['status'] = $request->has('status') ? 1 : 0;

        $update_data->update($input);

        Toastr::success('Success', 'Page updated successfully');
        return redirect()->route('pages.index');
    }

    public function inactive(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:create_pages,id',
        ]);

        $inactive = CreatePage::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Page inactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:create_pages,id',
        ]);

        $active = CreatePage::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Page activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'hidden_id' => 'required|integer|exists:create_pages,id',
        ]);

        $delete_data = CreatePage::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Page deleted successfully');
        return redirect()->back();
    }
}
