<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\AdminUserStoreRequest;
use App\Http\Requests\Admin\AdminUserUpdateRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Arr;
use App\Models\User;
use Toastr;
use Image;
use File;
use DB;
use Hash;
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit', 'update', 'active', 'inactive']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $data = User::with('roles')->orderBy('id', 'DESC')->get();

        $kpis = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 1)->count(),
            'inactive_users' => User::where('status', 0)->count(),
            'total_roles' => Role::count(),
        ];

        return view('backEnd.users.index', compact('data', 'kpis'));
    }

    public function create()
    {
        $roles = Role::select('name')->get();
        return view('backEnd.users.create', compact('roles'));
    }

    public function store(AdminUserStoreRequest $request)
    {
        $input = $request->validated();
        $input['password'] = Hash::make($input['password']);
        $input['status'] = $request->has('status') ? 1 : 0;

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $uploadDir = public_path('uploads/users');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0777, true, true);
            }

            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destination = 'uploads/users/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->encode('webp', 90);
                $width = 200;
                $height = 200;
                $img->height() > $img->width() ? $width = null : $height = null;
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save(public_path($destination));
                $imageUrl = $destination;
            } catch (\Throwable $e) {
                $image->move($uploadDir, $name);
                $imageUrl = 'uploads/users/' . $name;
            }
        }

        $input['image'] = $imageUrl;
        $user = User::create($input);
        if ($request->filled('roles')) {
            $user->assignRole($request->input('roles'));
        }

        Toastr::success('Success', 'User created successfully');
        return redirect()->route('users.index');
    }

    public function edit($id)
    {
        $edit_data = User::with('roles')->findOrFail($id);
        $roles = Role::get();
        return view('backEnd.users.edit', compact('edit_data', 'roles'));
    }

    public function update(AdminUserUpdateRequest $request)
    {
        $update_data = User::findOrFail($request->hidden_id);
        $input = $request->validated();

        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, ['password']);
        }

        $image = $request->file('image');
        if ($image) {
            $uploadDir = public_path('uploads/users');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0777, true, true);
            }

            $name = time() . '-' . \Illuminate\Support\Str::random(10) . '.' . $image->getClientOriginalExtension();
            $name = preg_replace('"\.(jpg|jpeg|png|webp)$"', '.webp', $name);
            $name = strtolower(preg_replace('/\s+/', '-', $name));
            $destination = 'uploads/users/' . $name;

            try {
                $img = Image::make($image->getRealPath());
                $img->encode('webp', 90);
                $width = 200;
                $height = 200;
                $img->height() > $img->width() ? $width = null : $height = null;
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save(public_path($destination));
                $imageUrl = $destination;
            } catch (\Throwable $e) {
                $image->move($uploadDir, $name);
                $imageUrl = 'uploads/users/' . $name;
            }

            if ($update_data->image && File::exists(public_path($update_data->image))) {
                File::delete(public_path($update_data->image));
            }
            $input['image'] = $imageUrl;
        } else {
            $input['image'] = $update_data->image;
        }

        $input['status'] = $request->has('status') ? 1 : 0;
        $update_data->update($input);

        if ($request->filled('roles')) {
            $update_data->syncRoles($request->input('roles'));
        }

        Toastr::success('Success', 'User updated successfully');
        return redirect()->route('users.index');
    }

    public function inactive(Request $request)
    {
        $inactive = User::findOrFail($request->hidden_id);

        if (auth()->check() && (int)$inactive->id === (int)auth()->id()) {
            Toastr::error('Action Restricted', 'You cannot deactivate your own active account.');
            return redirect()->back();
        }

        $inactive->status = 0;
        $inactive->save();
        Toastr::success('Success', 'User deactivated successfully');
        return redirect()->back();
    }

    public function active(Request $request)
    {
        $active = User::findOrFail($request->hidden_id);
        $active->status = 1;
        $active->save();
        Toastr::success('Success', 'User activated successfully');
        return redirect()->back();
    }

    public function destroy(Request $request)
    {
        $delete_data = User::findOrFail($request->hidden_id);

        if (auth()->check() && (int)$delete_data->id === (int)auth()->id()) {
            Toastr::error('Action Restricted', 'You cannot delete your own account.');
            return redirect()->back();
        }

        if ($delete_data->image && File::exists(public_path($delete_data->image))) {
            File::delete(public_path($delete_data->image));
        }

        $delete_data->delete();
        Toastr::success('Success', 'User deleted successfully');
        return redirect()->back();
    }
}
