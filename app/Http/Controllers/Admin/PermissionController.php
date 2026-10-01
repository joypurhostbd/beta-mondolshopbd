<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\PermissionStoreRequest;
use App\Http\Requests\Admin\PermissionUpdateRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Toastr;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:permission-list|permission-create|permission-edit|permission-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:permission-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:permission-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:permission-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $show_data = Permission::withCount('roles')->orderBy('id', 'DESC')->get();

        $modulesCount = $show_data->map(function ($perm) {
            $parts = explode('-', $perm->name);
            if (count($parts) > 1) {
                array_pop($parts);
                return implode('-', $parts);
            }
            return 'general';
        })->unique()->count();

        $kpis = [
            'total_permissions' => Permission::count(),
            'total_modules' => $modulesCount,
            'assigned_roles' => Role::has('permissions')->count(),
            'total_roles' => Role::count(),
        ];

        return view('backEnd.permissions.index', compact('show_data', 'kpis'));
    }

    public function create()
    {
        return view('backEnd.permissions.create');
    }

    public function store(PermissionStoreRequest $request)
    {
        $input = $request->validated();
        $input['guard_name'] = $request->input('guard_name', 'web');

        Permission::create($input);

        Toastr::success('Success', 'Permission created successfully');
        return redirect()->route('permissions.index');
    }

    public function show($id)
    {
        $permission = Permission::with('roles')->withCount('roles')->findOrFail($id);
        $moduleName = $this->getModuleName($permission->name);

        return view('backEnd.permissions.show', compact('permission', 'moduleName'));
    }

    public function edit($id)
    {
        $edit_data = Permission::findOrFail($id);
        return view('backEnd.permissions.edit', compact('edit_data'));
    }

    public function update(PermissionUpdateRequest $request)
    {
        $update_data = Permission::findOrFail($request->hidden_id);
        $input = $request->validated();
        $input['guard_name'] = $request->input('guard_name', 'web');

        $update_data->update($input);

        Toastr::success('Success', 'Permission updated successfully');
        return redirect()->route('permissions.index');
    }

    public function destroy(Request $request)
    {
        $delete_data = Permission::withCount('roles')->findOrFail($request->hidden_id);
        $delete_data->delete();

        Toastr::success('Success', 'Permission deleted successfully');
        return redirect()->back();
    }

    /**
     * Helper to get human-friendly module name from permission name
     */
    private function getModuleName(string $permissionName): string
    {
        $parts = explode('-', $permissionName);
        if (count($parts) > 1) {
            array_pop($parts);
            return implode(' ', array_map('ucfirst', $parts));
        }
        return 'General';
    }
}

