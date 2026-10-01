<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSettingsRouteTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_is_redirected_from_settings_root(): void
    {
        $response = $this->get('/admin/settings');
        $response->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_from_settings_manage(): void
    {
        $response = $this->get('/admin/settings/manage');
        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_admin_can_access_settings_root_without_404(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertViewIs('backEnd.settings.index');
        $response->assertViewHas(['show_data', 'kpis']);
    }

    public function test_authenticated_admin_can_access_settings_manage(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/settings/manage');

        $response->assertStatus(200);
        $response->assertViewIs('backEnd.settings.index');
        $response->assertViewHas(['show_data', 'kpis']);
    }

    public function test_named_route_settings_root_resolves_correctly(): void
    {
        $this->assertStringEndsWith('/admin/settings', route('settings.root'));
        $this->assertStringEndsWith('/admin/settings/manage', route('settings.index'));
    }
}
