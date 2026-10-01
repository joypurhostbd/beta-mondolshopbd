<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRoleCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'order-list',
            'order-edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo([
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
        ]);
    }

    public function test_admin_can_view_roles_index_with_kpis_and_counts(): void
    {
        $role = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $role->givePermissionTo(['product-list', 'product-create']);

        $response = $this->actingAs($this->adminUser)->get(route('roles.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['total_roles'] >= 1
                && $kpis['total_permissions'] >= 1;
        });

        $response->assertSee('Manager');
        $response->assertSee('2 Permissions');
    }

    public function test_admin_can_view_create_role_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('roles.create'));

        $response->assertStatus(200);
        $response->assertViewHas('groupedPermissions');
        $response->assertSee('Create Role');
        $response->assertSee('Product');
    }

    public function test_admin_can_store_role_with_permissions(): void
    {
        $p1 = Permission::where('name', 'product-list')->first();
        $p2 = Permission::where('name', 'order-list')->first();

        $response = $this->actingAs($this->adminUser)->post(route('roles.store'), [
            'name' => 'Inventory Supervisor',
            'permission' => [$p1->id, $p2->id],
        ]);

        $response->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', [
            'name' => 'Inventory Supervisor',
            'guard_name' => 'web',
        ]);

        $role = Role::where('name', 'Inventory Supervisor')->first();
        $this->assertNotNull($role);
        $this->assertTrue($role->hasPermissionTo('product-list'));
        $this->assertTrue($role->hasPermissionTo('order-list'));
    }

    public function test_admin_can_view_role_details(): void
    {
        $role = Role::firstOrCreate(['name' => 'Auditor', 'guard_name' => 'web']);
        $role->givePermissionTo(['product-list', 'order-list']);

        $response = $this->actingAs($this->adminUser)->get(route('roles.show', $role->id));

        $response->assertStatus(200);
        $response->assertViewHas('role');
        $response->assertViewHas('groupedPermissions');
        $response->assertSee('Auditor');
        $response->assertSee('product-list');
    }

    public function test_admin_can_view_edit_page_and_update_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'Content Creator', 'guard_name' => 'web']);
        $role->givePermissionTo(['product-list']);

        $p2 = Permission::where('name', 'product-create')->first();

        $response = $this->actingAs($this->adminUser)->get(route('roles.edit', $role->id));
        $response->assertStatus(200);
        $response->assertSee('Content Creator');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('roles.update'), [
            'hidden_id' => $role->id,
            'name' => 'Content Creator Senior',
            'permission' => [$p2->id],
        ]);

        $updateResponse->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Content Creator Senior',
        ]);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('product-create'));
        $this->assertFalse($role->hasPermissionTo('product-list'));
    }

    public function test_core_admin_role_cannot_be_deleted(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->post(route('roles.destroy'), [
            'hidden_id' => $adminRole->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['name' => 'Admin']);
    }

    public function test_role_with_assigned_users_cannot_be_deleted(): void
    {
        $role = Role::firstOrCreate(['name' => 'Support Agent', 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 1]);
        $user->assignRole($role);

        $response = $this->actingAs($this->adminUser)->post(route('roles.destroy'), [
            'hidden_id' => $role->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_admin_can_delete_unassigned_custom_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'Temporary Helper', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->post(route('roles.destroy'), [
            'hidden_id' => $role->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}