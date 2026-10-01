<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SocialMediaStoreRequest;
use App\Http\Requests\Admin\SocialMediaUpdateRequest;
use App\Models\SocialMedia;
use Toastr;

class SocialMediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:social-list|social-create|social-edit|social-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:social-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:social-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:social-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = SocialMedia::orderBy('id', 'DESC')->get();
        $total_social = SocialMedia::count();
        $active_social = SocialMedia::where('status', 1)->count();
        $inactive_social = SocialMedia::where('status', 0)->count();
        $latest_social = SocialMedia::where('status', 1)->latest('id')->first();

        return view('backEnd.socialmedia.index', compact('show_data', 'total_social', 'active_social', 'inactive_social', 'latest_social'));
    }

    public function create()
    {
        return view('backEnd.socialmedia.create');
    }

    public function show($id)
    {
        $edit_data = SocialMedia::findOrFail($id);
        return view('backEnd.socialmedia.edit', compact('edit_data'));
    }

    public function store(SocialMediaStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        SocialMedia::create($input);

        Toastr::success('Success', 'Social media link created successfully');
        return redirect()->route('socialmedias.index');
    }

    public function edit($id)
    {
        $edit_data = SocialMedia::findOrFail($id);
        return view('backEnd.socialmedia.edit', compact('edit_data'));
    }

    public function update(SocialMediaUpdateRequest $request)
    {
        $socialId = $request->id ?? $request->hidden_id;
        $update_data = SocialMedia::findOrFail($socialId);
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Social media link updated successfully');
        return redirect()->route('socialmedias.index');
    }

    public function inactive(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:social_media,id']);
        $inactive = SocialMedia::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Social media deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:social_media,id']);
        $active = SocialMedia::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Social media activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:social_media,id']);
        $delete_data = SocialMedia::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Social media deleted successfully');
        return redirect()->back();
    }
}
