<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Childcategory;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductModuleCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    protected function createCategory(array $attributes = []): Category
    {
        $cat = new Category();
        $cat->name = $attributes['name'] ?? 'Test Category';
        $cat->slug = $attributes['slug'] ?? 'test-category-' . rand(1000, 9999);
        $cat->status = $attributes['status'] ?? 1;
        $cat->parent_id = $attributes['parent_id'] ?? 0;
        $cat->front_view = 1;
        $cat->save();
        return $cat;
    }

    protected function createProduct(array $attributes = []): Product
    {
        $prod = new Product();
        $prod->name = $attributes['name'] ?? 'Test Product';
        $prod->slug = $attributes['slug'] ?? 'test-product-' . rand(1000, 9999);
        $prod->category_id = $attributes['category_id'] ?? 1;
        $prod->subcategory_id = $attributes['subcategory_id'] ?? null;
        $prod->childcategory_id = $attributes['childcategory_id'] ?? null;
        $prod->product_code = $attributes['product_code'] ?? 'P-' . rand(10000, 99999);
        $prod->purchase_price = $attributes['purchase_price'] ?? 500;
        $prod->old_price = $attributes['old_price'] ?? 800;
        $prod->new_price = $attributes['new_price'] ?? 750;
        $prod->stock = $attributes['stock'] ?? 50;
        $prod->status = $attributes['status'] ?? 1;
        $prod->topsale = $attributes['topsale'] ?? 0;
        $prod->feature_product = $attributes['feature_product'] ?? 0;
        $prod->description = $attributes['description'] ?? ('Description for ' . ($attributes['name'] ?? 'Test Product'));
        $prod->save();
        return $prod;
    }

    public function test_admin_can_view_products_list_and_filter(): void
    {
        $category = $this->createCategory(['name' => 'Fashion']);
        $product = $this->createProduct([
            'name' => 'Signature Cotton Panjabi',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/manage?keyword=Panjabi');

        $response->assertStatus(200);
        $response->assertViewHas('data');
    }

    public function test_admin_can_get_subcategories_and_childcategories_via_ajax(): void
    {
        $category = $this->createCategory();
        $subcategory = Subcategory::create([
            'subcategoryName' => 'Men Fashion',
            'slug' => 'men-fashion',
            'category_id' => $category->id,
            'status' => 1,
        ]);
        $childcategory = Childcategory::create([
            'childcategoryName' => 'Panjabi',
            'slug' => 'panjabi',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'status' => 1,
        ]);

        $subResponse = $this->actingAs($this->adminUser)
            ->get("/ajax-product-subcategory?category_id={$category->id}");
        $subResponse->assertStatus(200);
        $subResponse->assertJsonFragment(['Men Fashion']);

        $childResponse = $this->actingAs($this->adminUser)
            ->get("/ajax-product-childcategory?subcategory_id={$subcategory->id}");
        $childResponse->assertStatus(200);
        $childResponse->assertJsonFragment(['Panjabi']);
    }

    public function test_admin_can_view_create_product_page_with_lookups(): void
    {
        $this->createCategory(['status' => 1, 'parent_id' => 0]);
        Brand::create(['name' => 'Apex', 'name_bn' => 'এপেক্স', 'slug' => 'apex', 'status' => 1]);
        Color::create(['colorName' => 'Black', 'color' => '#000000', 'status' => 1]);
        Size::create(['sizeName' => 'XL', 'status' => 1]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/create');

        $response->assertStatus(200);
        $response->assertViewHasAll(['categories', 'brands', 'colors', 'sizes']);
    }

    public function test_admin_can_view_edit_product_page(): void
    {
        $category = $this->createCategory(['status' => 1, 'parent_id' => 0]);
        $product = $this->createProduct([
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/admin/products/{$product->id}/edit");

        $response->assertStatus(200);
        $response->assertViewHas('edit_data');
    }

    public function test_admin_can_bulk_update_product_prices_and_stock(): void
    {
        $category = $this->createCategory();
        $p1 = $this->createProduct(['category_id' => $category->id, 'old_price' => 500, 'new_price' => 450, 'stock' => 10, 'status' => 1]);
        $p2 = $this->createProduct(['category_id' => $category->id, 'old_price' => 800, 'new_price' => 750, 'stock' => 20, 'status' => 1]);

        $response = $this->actingAs($this->adminUser)
            ->post('/admin/products/price-update', [
                'ids' => [$p1->id, $p2->id],
                'old_price' => [600, 900],
                'new_price' => [550, 850],
                'stock' => [15, 25],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $p1->id,
            'new_price' => 550,
            'stock' => 15,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $p2->id,
            'new_price' => 850,
            'stock' => 25,
        ]);
    }

    public function test_admin_can_toggle_product_status(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct(['category_id' => $category->id, 'status' => 1]);

        $inactiveResponse = $this->actingAs($this->adminUser)
            ->post('/admin/products/inactive', ['hidden_id' => $product->id]);
        $inactiveResponse->assertRedirect();
        $this->assertEquals(0, $product->fresh()->status);

        $activeResponse = $this->actingAs($this->adminUser)
            ->post('/admin/products/active', ['hidden_id' => $product->id]);
        $activeResponse->assertRedirect();
        $this->assertEquals(1, $product->fresh()->status);
    }

    public function test_admin_can_search_products_by_product_code_and_barcode(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct([
            'name' => 'Premium Silk Saree',
            'product_code' => 'SKU-SAR-9988',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/manage?keyword=SKU-SAR-9988');

        $response->assertStatus(200);
        $response->assertSee('Premium Silk Saree');
        $response->assertSee('SKU-SAR-9988');
    }

    public function test_admin_products_manage_page_loads_with_kpi_metrics(): void
    {
        $category = $this->createCategory();
        $this->createProduct(['category_id' => $category->id, 'stock' => 2, 'status' => 1, 'topsale' => 1]);
        $this->createProduct(['category_id' => $category->id, 'stock' => 50, 'status' => 0, 'feature_product' => 1]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/manage');

        $response->assertStatus(200);
        $response->assertViewHas('total_products');
        $response->assertViewHas('total_active');
        $response->assertViewHas('low_stock_count');
        $response->assertViewHas('deals_count');
        $response->assertSee('Total Products');
        $response->assertSee('Low Stock');
    }

    public function test_admin_can_bulk_update_feature_products(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct(['category_id' => $category->id, 'feature_product' => 0]);

        $response = $this->actingAs($this->adminUser)
            ->getJson("/admin/products/update-feature?product_ids[]={$product->id}&status=1");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertEquals(1, $product->fresh()->feature_product);
    }

    public function test_admin_products_manage_displays_preview_link_in_action_column(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct([
            'name' => 'Signature Cotton Panjabi',
            'slug' => 'signature-cotton-panjabi',
            'category_id' => $category->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/products/manage');

        $response->assertStatus(200);
        $previewUrl = route('product', $product->slug);
        $response->assertSee($previewUrl, false);
        $response->assertSee('target="_blank"', false);
    }

    public function test_product_details_renders_size_chart_base64_image_in_description(): void
    {
        $category = $this->createCategory();
        $sampleBase64Img = '<img src="data:image/webp;base64,UklGRkAAAABXRUJQVlA4WAoAAAAgAAAAAAAAAAAAQUxQSAwAAAARBxAR/Q9ERP8DAABWUDggGAAAADABAJ0BKgEAAQAAAP4AAA3AAP7mt+AAA=" alt="Size Chart" style="width: 500px;">';
        $product = $this->createProduct([
            'name' => 'Signature T-Shirt with Size Chart',
            'slug' => 'signature-t-shirt-with-size-chart',
            'category_id' => $category->id,
            'status' => 1,
            'description' => '<p>Product Details</p>' . $sampleBase64Img,
        ]);

        $response = $this->get('/product/' . $product->slug);

        $response->assertStatus(200);
        $response->assertSee('data:image/webp;base64', false);
        $response->assertSee('alt="Size Chart"', false);
    }
}