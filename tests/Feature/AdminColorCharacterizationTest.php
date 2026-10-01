<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminColorCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'color-list',
            'color-create',
            'color-edit',
            'color-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_color_manage_renders_with_kpi_metrics_and_product_counts(): void
    {
        $color1 = Color::create([
            'colorName' => 'Midnight Black',
            'color' => '#000000',
            'status' => 1,
        ]);

        $color2 = Color::create([
            'colorName' => 'Pure White',
            'color' => '#FFFFFF',
            'status' => 0,
        ]);

        $product = Product::create([
            'name' => 'Cotton T-Shirt',
            'slug' => 'cotton-t-shirt',
            'category_id' => 1,
            'product_code' => 'TS-01',
            'old_price' => 800,
            'new_price' => 700,
            'purchase_price' => 500,
            'stock' => 50,
            'status' => 1,
        ]);

        $color1->products()->attach($product->id);

        $response = $this->actingAs($this->adminUser)->get(route('colors.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['inactive'] === 1
                && $metrics['with_products'] === 1;
        });

        $response->assertSee('Midnight Black');
        $response->assertSee('Pure White');
        $response->assertSee('#000000');
        $response->assertSee('1 Products');
    }

    public function test_admin_color_can_toggle_active_and_inactive(): void
    {
        $color = Color::create([
            'colorName' => 'Ocean Blue',
            'color' => '#0000FF',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('colors.inactive'), [
            'hidden_id' => $color->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('colors', [
            'id' => $color->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('colors.active'), [
            'hidden_id' => $color->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('colors', [
            'id' => $color->id,
            'status' => 1,
        ]);
    }

    public function test_admin_color_can_be_deleted(): void
    {
        $color = Color::create([
            'colorName' => 'Crimson Red',
            'color' => '#DC143C',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('colors.destroy'), [
            'hidden_id' => $color->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('colors', [
            'id' => $color->id,
        ]);
    }
}
