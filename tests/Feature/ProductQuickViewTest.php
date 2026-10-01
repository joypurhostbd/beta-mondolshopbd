<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Productcolor;
use App\Models\Productsize;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductQuickViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_quickview_modal_returns_product_html_with_attributes_and_buttons(): void
    {
        $category = Category::create([
            'name' => 'Trouser',
            'slug' => 'trouser',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => '2 Pieces Premium Quality Solid Trouser',
            'slug' => '2-pieces-premium-quality-solid-trouser',
            'category_id' => $category->id,
            'product_code' => 'P-252',
            'purchase_price' => 800,
            'old_price' => 1843,
            'new_price' => 1290,
            'stock' => 50,
            'status' => 1,
        ]);

        $sizeM = Size::create(['sizeName' => 'M', 'status' => 1]);
        $sizeL = Size::create(['sizeName' => 'L', 'status' => 1]);
        Productsize::create(['product_id' => $product->id, 'size_id' => $sizeM->id]);
        Productsize::create(['product_id' => $product->id, 'size_id' => $sizeL->id]);

        $colorBlack = Color::create(['colorName' => 'Black', 'color' => '#000000', 'status' => 1]);
        Productcolor::create(['product_id' => $product->id, 'color_id' => $colorBlack->id]);

        $response = $this->get(route('quickview', ['id' => $product->id]));

        $response->assertStatus(200);
        $response->assertSee('2 Pieces Premium Quality Solid Trouser');
        $response->assertSee('1290');
        $response->assertSee('1843');
        $response->assertSee('Size -');
        $response->assertSee('Color -');
        $response->assertSee('name="product_size"', false);
        $response->assertSee('name="product_color"', false);
        $response->assertSee('checked', false);
        $response->assertSee('কার্টে যোগ করুন');
        $response->assertSee('অর্ডার করুন');
        $response->assertSee('close-modal');
        $response->assertSee('sale-badge');
        $response->assertSee('ছাড়');
        $response->assertSee('quick-actions-buttons');
    }

    public function test_quickview_modal_returns_404_when_product_not_found_or_inactive(): void
    {
        $response = $this->get(route('quickview', ['id' => 999999]));
        $response->assertStatus(404);

        $category = Category::create([
            'name' => 'Inactive Cat',
            'slug' => 'inactive-cat',
            'status' => 1,
        ]);

        $inactiveProduct = Product::create([
            'name' => 'Inactive Trouser',
            'slug' => 'inactive-trouser',
            'category_id' => $category->id,
            'product_code' => 'P-999',
            'purchase_price' => 800,
            'old_price' => 1500,
            'new_price' => 1000,
            'stock' => 10,
            'status' => 0,
        ]);

        $responseInactive = $this->get(route('quickview', ['id' => $inactiveProduct->id]));
        $responseInactive->assertStatus(404);
    }
}
