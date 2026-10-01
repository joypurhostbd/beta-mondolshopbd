<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSettingCharacterizationTest extends TestCase
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

    public function test_admin_can_view_settings_index_with_kpis(): void
    {
        GeneralSetting::create([
            'name' => 'Primary Brand',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 Primary Brand',
            'status' => 1,
        ]);

        GeneralSetting::create([
            'name' => 'Secondary Brand',
            'white_logo' => 'uploads/settings/white2.png',
            'dark_logo' => 'uploads/settings/dark2.png',
            'favicon' => 'uploads/settings/favicon2.png',
            'copyright' => '© 2026 Secondary Brand',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['total_settings'] === 2
                && $kpis['active_settings'] === 1
                && $kpis['inactive_settings'] === 1
                && $kpis['active_brand_name'] === 'Primary Brand';
        });

        $response->assertSee('Primary Brand');
        $response->assertSee('Secondary Brand');
    }

    public function test_admin_can_view_create_setting_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('settings.create'));

        $response->assertStatus(200);
        $response->assertSee('General Setting Create');
        $response->assertSee('Company / Website Name');
    }

    public function test_admin_can_store_new_general_setting(): void
    {
        $whiteLogo = UploadedFile::fake()->image('white_logo.png', 200, 50);
        $darkLogo = UploadedFile::fake()->image('dark_logo.png', 200, 50);
        $favicon = UploadedFile::fake()->image('favicon.png', 32, 32);

        $response = $this->actingAs($this->adminUser)->post(route('settings.store'), [
            'name' => 'Mondol Mega Shop',
            'copyright' => '© 2026 Mondol Mega Shop',
            'white_logo' => $whiteLogo,
            'dark_logo' => $darkLogo,
            'favicon' => $favicon,
            'status' => 1,
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'name' => 'Mondol Mega Shop',
            'copyright' => '© 2026 Mondol Mega Shop',
            'status' => 1,
        ]);

        $created = GeneralSetting::where('name', 'Mondol Mega Shop')->first();
        $this->assertNotNull($created);
        $this->assertNotEmpty($created->white_logo);
        $this->assertNotEmpty($created->dark_logo);
        $this->assertNotEmpty($created->favicon);
    }

    public function test_admin_can_create_setting_without_uploading_new_logos_inheriting_existing(): void
    {
        GeneralSetting::create([
            'name' => 'Initial Brand',
            'white_logo' => 'uploads/settings/initial_white.webp',
            'dark_logo' => 'uploads/settings/initial_dark.webp',
            'favicon' => 'uploads/settings/initial_favicon.webp',
            'copyright' => '© 2026 Initial',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('settings.store'), [
            'name' => 'Brand Without Logos',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => '1',
            'whatsapp_number' => '01972101994',
            'whatsapp_title' => 'WhatsApp Support',
            'whatsapp_message' => 'Hello!',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'name' => 'Brand Without Logos',
            'white_logo' => 'uploads/settings/initial_white.webp',
            'dark_logo' => 'uploads/settings/initial_dark.webp',
            'favicon' => 'uploads/settings/initial_favicon.webp',
            'whatsapp_number' => '01972101994',
        ]);
    }

    public function test_admin_can_view_edit_page_and_update_setting(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Old Brand Name',
            'white_logo' => 'uploads/settings/old_white.png',
            'dark_logo' => 'uploads/settings/old_dark.png',
            'favicon' => 'uploads/settings/old_favicon.png',
            'copyright' => '© 2025 Old Brand',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('settings.edit', $setting->id));
        $response->assertStatus(200);
        $response->assertViewHas('edit_data');
        $response->assertSee('Old Brand Name');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('settings.update'), [
            'hidden_id' => $setting->id,
            'name' => 'Updated Brand Name',
            'copyright' => '© 2026 Updated Brand',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'id' => $setting->id,
            'name' => 'Updated Brand Name',
            'copyright' => '© 2026 Updated Brand',
        ]);
    }

    public function test_admin_can_toggle_setting_status(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Toggle Brand',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('settings.inactive'), [
            'hidden_id' => $setting->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('general_settings', ['id' => $setting->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('settings.active'), [
            'hidden_id' => $setting->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('general_settings', ['id' => $setting->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_setting(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Delete Target Brand',
            'white_logo' => 'uploads/settings/white_del.png',
            'dark_logo' => 'uploads/settings/dark_del.png',
            'favicon' => 'uploads/settings/favicon_del.png',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('settings.destroy'), [
            'hidden_id' => $setting->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('general_settings', ['id' => $setting->id]);
    }
}
