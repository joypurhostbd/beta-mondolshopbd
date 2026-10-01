<?php

namespace Tests\Feature;

use App\Models\EcomPixel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPixelCharacterizationTest extends TestCase
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

    public function test_guest_cannot_access_pixel_manage(): void
    {
        $response = $this->get(route('pixels.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('pixels.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_pixel_index_with_kpis(): void
    {
        EcomPixel::create([
            'code' => 'FB-PIXEL-11111',
            'status' => 1,
        ]);

        EcomPixel::create([
            'code' => 'FB-PIXEL-22222',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('pixels.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('total_pixels', 2);
        $response->assertViewHas('active_pixels', 1);
        $response->assertViewHas('inactive_pixels', 1);
        $response->assertSee('FB-PIXEL-11111');
        $response->assertSee('FB-PIXEL-22222');
    }

    public function test_admin_can_view_create_pixel_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('pixels.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Facebook Pixel');
        $response->assertSee('New Facebook Pixel');
        $response->assertSee('Facebook Pixel ID');
        $response->assertSee('Save Pixel');
    }

    public function test_admin_can_store_pixel(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('pixels.store'), [
            'code' => '987654321098765',
            'status' => 1,
        ]);

        $response->assertRedirect(route('pixels.index'));

        $this->assertDatabaseHas('ecom_pixels', [
            'code' => '987654321098765',
            'status' => 1,
        ]);
    }

    public function test_store_validation_requires_code(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('pixels.store'), [
            'code' => '',
            'status' => 1,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_storefront_renders_active_facebook_pixel(): void
    {
        EcomPixel::create([
            'code' => '111222333444555',
            'status' => 1,
        ]);

        EcomPixel::create([
            'code' => '999888777666555',
            'status' => 0,
        ]);

        view()->share('pixels', EcomPixel::where('status', 1)->get());

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('fbq("init", "111222333444555")', false);
        $response->assertSee('https://www.facebook.com/tr?id=111222333444555&ev=PageView&noscript=1', false);
        $response->assertDontSee('111222333444555999888777666555', false);
        $response->assertDontSee('fbq("init", "999888777666555")', false);
    }

    public function test_admin_can_view_edit_and_update_pixel(): void
    {
        $pixel = EcomPixel::create([
            'code' => 'OLD-PIXEL-001',
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('pixels.edit', $pixel->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('OLD-PIXEL-001');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('pixels.update'), [
            'id' => $pixel->id,
            'hidden_id' => $pixel->id,
            'code' => 'NEW-PIXEL-999',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('pixels.index'));

        $this->assertDatabaseHas('ecom_pixels', [
            'id' => $pixel->id,
            'code' => 'NEW-PIXEL-999',
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_pixel_status(): void
    {
        $pixel = EcomPixel::create([
            'code' => 'TOGGLE-PIXEL-123',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('pixels.inactive'), [
            'hidden_id' => $pixel->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('ecom_pixels', ['id' => $pixel->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('pixels.active'), [
            'hidden_id' => $pixel->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('ecom_pixels', ['id' => $pixel->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_pixel(): void
    {
        $pixel = EcomPixel::create([
            'code' => 'DELETE-PIXEL-456',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('pixels.destroy'), [
            'hidden_id' => $pixel->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('ecom_pixels', ['id' => $pixel->id]);
    }
}
