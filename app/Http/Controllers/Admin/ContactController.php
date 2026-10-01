<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\ContactStoreRequest;
use App\Http\Requests\Admin\ContactUpdateRequest;
use App\Models\Contact;
use Toastr;

class ContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:contact-list|contact-create|contact-edit|contact-delete', ['only' => ['index', 'store']]);
        $this->middleware('permission:contact-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:contact-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:contact-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = Contact::orderBy('id', 'DESC')->get();
        $total_contacts = Contact::count();
        $active_contacts = Contact::where('status', 1)->count();
        $inactive_contacts = Contact::where('status', 0)->count();
        $primary_contact = Contact::where('status', 1)->latest('id')->first();

        return view('backEnd.contact.index', compact('show_data', 'total_contacts', 'active_contacts', 'inactive_contacts', 'primary_contact'));
    }

    public function create()
    {
        return view('backEnd.contact.create');
    }

    public function show($id)
    {
        $edit_data = Contact::findOrFail($id);
        return view('backEnd.contact.edit', compact('edit_data'));
    }

    public function store(ContactStoreRequest $request)
    {
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        Contact::create($input);

        Toastr::success('Success', 'Contact info created successfully');
        return redirect()->route('contact.index');
    }

    public function edit($id)
    {
        $edit_data = Contact::findOrFail($id);
        return view('backEnd.contact.edit', compact('edit_data'));
    }

    public function update(ContactUpdateRequest $request)
    {
        $contactId = $request->id ?? $request->hidden_id;
        $update_data = Contact::findOrFail($contactId);
        $input = $request->validated();
        $input['status'] = $request->has('status') ? 1 : 0;
        $update_data->update($input);

        Toastr::success('Success', 'Contact info updated successfully');
        return redirect()->route('contact.index');
    }

    public function inactive(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:contacts,id']);
        $inactive = Contact::findOrFail($request->hidden_id);
        $inactive->status = 0;
        $inactive->save();

        Toastr::success('Success', 'Contact info deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:contacts,id']);
        $active = Contact::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();

        Toastr::success('Success', 'Contact info activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $request->validate(['hidden_id' => 'required|integer|exists:contacts,id']);
        $delete_data = Contact::findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Contact info deleted successfully');
        return redirect()->back();
    }
}
