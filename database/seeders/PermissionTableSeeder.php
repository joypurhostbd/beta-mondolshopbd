<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            'subcategory-list',
            'subcategory-create',
            'subcategory-edit',
            'subcategory-delete',
            'childcategory-list',
            'childcategory-create',
            'childcategory-edit',
            'childcategory-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
            'color-list',
            'color-create',
            'color-edit',
            'color-delete',
            'size-list',
            'size-create',
            'size-edit',
            'size-delete',
            'attribute-list',
            'attribute-create',
            'attribute-edit',
            'attribute-delete',
            'banner-category-list',
            'banner-category-create',
            'banner-category-edit',
            'banner-category-delete',
            'banner-list',
            'banner-create',
            'banner-edit',
            'banner-delete',
            'page-list',
            'page-create',
            'page-edit',
            'page-delete',
            'contact-list',
            'contact-create',
            'contact-edit',
            'contact-delete',
            'social-list',
            'social-create',
            'social-edit',
            'social-delete',
            'shipping-list',
            'shipping-create',
            'shipping-edit',
            'shipping-delete',
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
        ];
      
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
    }
}

