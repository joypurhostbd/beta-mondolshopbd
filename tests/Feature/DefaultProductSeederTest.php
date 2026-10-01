<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Database\Seeders\DefaultProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_product_seeder_seeds_homepage_data_correctly(): void
    {
        $this->seed(DefaultProductSeeder::class);

        // Verify categories
        $this->assertGreaterThanOrEqual(6, Category::where('status', 1)->count());
        $this->assertDatabaseHas('categories', [
            'slug' => 'drop-shoulder-t-shirt',
            'front_view' => 1,
            'status' => 1,
        ]);

        // Verify banners
        $this->assertGreaterThanOrEqual(3, Banner::where('status', 1)->count());

        // Verify sizes & colors
        $this->assertDatabaseHas('sizes', ['sizeName' => 'XL']);
        $this->assertDatabaseHas('colors', ['colorName' => 'Black']);

        // Verify products & hot deals
        $this->assertGreaterThanOrEqual(12, Product::where('status', 1)->count());
        $this->assertGreaterThanOrEqual(6, Product::where(['status' => 1, 'topsale' => 1])->count());

        // Verify product images
        $product = Product::where('slug', 'premium-drop-shoulder-t-shirt-pitch-black')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
        $this->assertGreaterThan(0, $product->prosizes()->count());
        $this->assertGreaterThan(0, $product->procolors()->count());

        // Verify frontend homepage response
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Drop Shoulder T-Shirt');
        $response->assertSee('Hot Deal');
        $response->assertSee($product->name);
    }

    public function test_default_product_seeder_is_idempotent(): void
    {
        $this->seed(DefaultProductSeeder::class);
        $initialProductCount = Product::count();
        $initialCategoryCount = Category::count();
        $initialBannerCount = Banner::count();

        // Run second time
        $this->seed(DefaultProductSeeder::class);

        $this->assertEquals($initialProductCount, Product::count());
        $this->assertEquals($initialCategoryCount, Category::count());
        $this->assertEquals($initialBannerCount, Banner::count());
    }
}
