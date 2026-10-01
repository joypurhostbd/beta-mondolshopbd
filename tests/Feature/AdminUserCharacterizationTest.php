<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_can_view_users_index_with_kpis(): void
    {
        Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $otherUser = User::factory()->create(['name' => 'John Doe', 'status' => 1]);
        $otherUser->assignRole('Manager');

        $response = $this->actingAs($this->adminUser)->get(route('users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['total_users'] === 2
                && $kpis['active_users'] === 2
                && $kpis['inactive_users'] === 0
                && $kpis['total_roles'] >= 1;
        });

        $response->assertSee('John Doe');
        $response->assertSee('Manager');
    }

    public function test_admin_can_view_create_user_page(): void
    {
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);

        $response = $this->actingAs($this->adminUser)->get(route('users.create'));

        $response->assertStatus(200);
        $response->assertViewHas('roles');
        $response->assertSee('Create System User');
        $response->assertSee('Editor');
    }

    public function test_admin_can_store_user_with_role_and_avatar(): void
    {
        Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);
        $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $response = $this->actingAs($this->adminUser)->post(route('users.store'), [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'password' => 'secret123',
            'confirm-password' => 'secret123',
            'roles' => ['Staff'],
            'image' => $avatar,
            'status' => 1,
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'status' => 1,
        ]);

        $created = User::where('email', 'sarah@example.com')->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasRole('Staff'));
        $this->assertNotEmpty($created->image);
    }

    public function test_admin_can_view_edit_page_and_update_user(): void
    {
        Role::firstOrCreate(['name' => 'Editor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Support', 'guard_name' => 'web']);

        $targetUser = User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
            'status' => 1,
        ]);
        $targetUser->assignRole('Editor');

        $response = $this->actingAs($this->adminUser)->get(route('users.edit', $targetUser->id));
        $response->assertStatus(200);
        $response->assertSee('Bob Smith');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('users.update'), [
            'hidden_id' => $targetUser->id,
            'name' => 'Bob Smith Updated',
            'email' => 'bob.updated@example.com',
            'roles' => ['Support'],
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Bob Smith Updated',
            'email' => 'bob.updated@example.com',
        ]);

        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('Support'));
        $this->assertFalse($targetUser->hasRole('Editor'));
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['status' => 1]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('users.inactive'), [
            'hidden_id' => $targetUser->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('users.active'), [
            'hidden_id' => $targetUser->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $targetUser->id, 'status' => 1]);
    }

    public function test_self_account_deactivation_and_deletion_are_blocked(): void
    {
        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('users.inactive'), [
            'hidden_id' => $this->adminUser->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id, 'status' => 1]);

        $deleteResponse = $this->actingAs($this->adminUser)->post(route('users.destroy'), [
            'hidden_id' => $this->adminUser->id,
        ]);
        $deleteResponse->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->adminUser->id]);
    }

    public function test_admin_can_delete_other_user(): void
    {
        $otherUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($this->adminUser)->post(route('users.destroy'), [
            'hidden_id' => $otherUser->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }
}