<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'size-list',
            'size-create',
            'size-edit',
            'size-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'size-list',
            'size-create',
            'size-edit',
            'size-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
        ];

        Permission::whereIn('name', $permissions)->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
