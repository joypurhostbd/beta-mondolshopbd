<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Productsize;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSizeCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'size-list',
            'size-create',
            'size-edit',
            'size-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_size_manage_renders_with_kpi_metrics_and_product_counts(): void
    {
        $size1 = Size::create([
            'sizeName' => 'XL',
            'status' => 1,
        ]);

        $size2 = Size::create([
            'sizeName' => 'XXL',
            'status' => 0,
        ]);

        $product = Product::create([
            'name' => 'Cotton Shirt',
            'slug' => 'cotton-shirt',
            'category_id' => 1,
            'product_code' => 'S-01',
            'old_price' => 1200,
            'new_price' => 1000,
            'purchase_price' => 700,
            'stock' => 30,
            'status' => 1,
        ]);

        $size1->products()->attach($product->id);

        $response = $this->actingAs($this->adminUser)->get(route('sizes.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['with_products'] === 1;
        });

        $response->assertSee('XL');
        $response->assertSee('XXL');
        $response->assertSee('1 Products');
    }

    public function test_admin_size_can_be_created_via_store(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('sizes.store'), [
            'sizeName' => 'Medium',
            'status' => 1,
        ]);

        $response->assertRedirect(route('sizes.index'));
        $this->assertDatabaseHas('sizes', [
            'sizeName' => 'Medium',
            'status' => 1,
        ]);
    }

    public function test_admin_size_can_be_updated(): void
    {
        $size = Size::create([
            'sizeName' => 'Small',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('sizes.update'), [
            'id' => $size->id,
            'sizeName' => 'Small (S)',
            'status' => 1,
        ]);

        $response->assertRedirect(route('sizes.index'));
        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'sizeName' => 'Small (S)',
        ]);
    }

    public function test_admin_size_can_toggle_active_and_inactive(): void
    {
        $size = Size::create([
            'sizeName' => 'Large',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('sizes.inactive'), [
            'hidden_id' => $size->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('sizes.active'), [
            'hidden_id' => $size->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('sizes', [
            'id' => $size->id,
            'status' => 1,
        ]);
    }

    public function test_admin_size_can_be_deleted(): void
    {
        $size = Size::create([
            'sizeName' => 'Custom Size',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('sizes.destroy'), [
            'hidden_id' => $size->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('sizes', [
            'id' => $size->id,
        ]);
    }

    public function test_admin_role_user_can_access_size_manage_without_direct_permission(): void
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminUserWithoutDirectPerms = User::factory()->create(['status' => 1]);
        $adminUserWithoutDirectPerms->assignRole($role);

        $response = $this->actingAs($adminUserWithoutDirectPerms)->get(route('sizes.index'));
        $response->assertStatus(200);
    }
}

