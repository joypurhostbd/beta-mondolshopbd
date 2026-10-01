<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPermissionCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'product-list',
            'order-list',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo([
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
        ]);
    }

    public function test_admin_can_view_permissions_index_with_kpis(): void
    {
        $role = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $role->givePermissionTo('product-list');

        $response = $this->actingAs($this->adminUser)->get(route('permissions.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['total_permissions'] >= 2
                && $kpis['total_modules'] >= 1
                && $kpis['total_roles'] >= 1;
        });

        $response->assertSee('product-list');
        $response->assertSee('Product');
    }

    public function test_admin_can_view_create_permission_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('permissions.create'));

        $response->assertStatus(200);
        $response->assertSee('Create System Permission');
    }

    public function test_admin_can_store_permission(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('permissions.store'), [
            'name' => 'voucher-generate',
            'guard_name' => 'web',
        ]);

        $response->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'name' => 'voucher-generate',
            'guard_name' => 'web',
        ]);
    }

    public function test_admin_can_view_permission_details(): void
    {
        $perm = Permission::where('name', 'product-list')->first();
        $role = Role::firstOrCreate(['name' => 'Catalog Admin', 'guard_name' => 'web']);
        $role->givePermissionTo('product-list');

        $response = $this->actingAs($this->adminUser)->get(route('permissions.show', $perm->id));

        $response->assertStatus(200);
        $response->assertViewHas('permission');
        $response->assertViewHas('moduleName', 'Product');
        $response->assertSee('product-list');
        $response->assertSee('Catalog Admin');
    }

    public function test_admin_can_view_edit_page_and_update_permission(): void
    {
        $perm = Permission::create(['name' => 'temporary-permission', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->get(route('permissions.edit', $perm->id));
        $response->assertStatus(200);
        $response->assertSee('temporary-permission');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('permissions.update'), [
            'hidden_id' => $perm->id,
            'name' => 'temporary-permission-updated',
        ]);

        $updateResponse->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'id' => $perm->id,
            'name' => 'temporary-permission-updated',
        ]);
    }

    public function test_admin_can_delete_permission(): void
    {
        $perm = Permission::create(['name' => 'to-be-deleted', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->post(route('permissions.destroy'), [
            'hidden_id' => $perm->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('permissions', ['id' => $perm->id]);
    }
}