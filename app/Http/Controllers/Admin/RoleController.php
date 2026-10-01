<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\RoleStoreRequest;
use App\Http\Requests\Admin\RoleUpdateRequest;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Toastr;
use DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = Role::withCount(['permissions', 'users'])->orderBy('id', 'DESC')->get();

        $adminRole = Role::where('name', 'Admin')->first();
        $kpis = [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'assigned_users' => User::has('roles')->count(),
            'admin_users' => $adminRole ? $adminRole->users()->count() : 0,
        ];

        return view('backEnd.roles.index', compact('show_data', 'kpis'));
    }

    public function create()
    {
        $permission = Permission::orderBy('name', 'ASC')->get();
        $groupedPermissions = $this->groupPermissions($permission);

        return view('backEnd.roles.create', compact('permission', 'groupedPermissions'));
    }

    public function store(RoleStoreRequest $request)
    {
        $role = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'web',
        ]);

        if ($request->filled('permission')) {
            $role->syncPermissions($request->input('permission'));
        }

        Toastr::success('Success', 'Role created successfully');
        return redirect()->route('roles.index');
    }

    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])->withCount(['permissions', 'users'])->findOrFail($id);
        $groupedPermissions = $this->groupPermissions($role->permissions);

        return view('backEnd.roles.show', compact('role', 'groupedPermissions'));
    }

    public function edit($id)
    {
        $edit_data = Role::with('permissions')->findOrFail($id);
        $permission = Permission::orderBy('name', 'ASC')->get();
        $groupedPermissions = $this->groupPermissions($permission);

        return view('backEnd.roles.edit', compact('edit_data', 'permission', 'groupedPermissions'));
    }

    public function update(RoleUpdateRequest $request)
    {
        $update_data = Role::findOrFail($request->hidden_id);
        $update_data->name = $request->input('name');
        $update_data->save();

        if ($request->has('permission')) {
            $update_data->syncPermissions($request->input('permission'));
        } else {
            $update_data->syncPermissions([]);
        }

        Toastr::success('Success', 'Role updated successfully');
        return redirect()->route('roles.index');
    }

    public function destroy(Request $request)
    {
        $delete_data = Role::withCount('users')->findOrFail($request->hidden_id);

        if (strtolower($delete_data->name) === 'admin') {
            Toastr::error('Action Restricted', 'The core Admin role is protected and cannot be deleted.');
            return redirect()->back();
        }

        if ($delete_data->users_count > 0) {
            Toastr::error('Action Restricted', 'Cannot delete role assigned to ' . $delete_data->users_count . ' active user(s). Please reassign users first.');
            return redirect()->back();
        }

        $delete_data->delete();
        Toastr::success('Success', 'Role deleted successfully');
        return redirect()->back();
    }

    /**
     * Helper to group permissions by module prefix
     */
    private function groupPermissions($permissions)
    {
        return $permissions->groupBy(function ($perm) {
            $parts = explode('-', $perm->name);
            if (count($parts) > 1) {
                array_pop($parts);
                return implode(' ', array_map('ucfirst', $parts));
            }
            return 'General';
        });
    }
}

