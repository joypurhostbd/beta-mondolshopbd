<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Childcategory;
use App\Models\Product;
use App\Models\Productimage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function createCategory(array $attributes = []): Category
    {
        $cat = new Category();
        $cat->name = $attributes['name'] ?? 'Test Category';
        $cat->slug = $attributes['slug'] ?? 'test-category';
        $cat->status = $attributes['status'] ?? 1;
        $cat->front_view = $attributes['front_view'] ?? 1;
        $cat->save();
        return $cat;
    }

    protected function createSubcategory(int $categoryId, array $attributes = []): Subcategory
    {
        $sub = new Subcategory();
        $sub->subcategoryName = $attributes['name'] ?? 'Test Subcategory';
        $sub->slug = $attributes['slug'] ?? 'test-subcategory';
        $sub->category_id = $categoryId;
        $sub->status = $attributes['status'] ?? 1;
        $sub->save();
        return $sub;
    }

    protected function createChildcategory(int $subcategoryId, array $attributes = []): Childcategory
    {
        $child = new Childcategory();
        $child->childcategoryName = $attributes['name'] ?? 'Test Childcategory';
        $child->slug = $attributes['slug'] ?? 'test-childcategory';
        $child->subcategory_id = $subcategoryId;
        $child->status = $attributes['status'] ?? 1;
        $child->save();
        return $child;
    }

    protected function createProduct(array $attributes = []): Product
    {
        $prod = new Product();
        $prod->name = $attributes['name'] ?? 'Test Product';
        $prod->slug = $attributes['slug'] ?? 'test-product';
        $prod->category_id = $attributes['category_id'] ?? 1;
        $prod->subcategory_id = $attributes['subcategory_id'] ?? null;
        $prod->childcategory_id = $attributes['childcategory_id'] ?? null;
        $prod->product_code = $attributes['product_code'] ?? 'P-' . rand(10000, 99999);
        $prod->purchase_price = $attributes['purchase_price'] ?? 500;
        $prod->old_price = $attributes['old_price'] ?? 800;
        $prod->new_price = $attributes['new_price'] ?? 700;
        $prod->stock = $attributes['stock'] ?? 50;
        $prod->status = $attributes['status'] ?? 1;
        $prod->topsale = $attributes['topsale'] ?? 0;
        $prod->save();

        $img = new Productimage();
        $img->product_id = $prod->id;
        $img->image = 'public/uploads/product/default.png';
        $img->save();

        return $prod;
    }

    public function test_homepage_displays_products_and_categories()
    {
        $category = $this->createCategory(['name' => 'Electronics', 'slug' => 'electronics', 'front_view' => 1]);
        $product = $this->createProduct(['name' => 'Smart Watch', 'slug' => 'smart-watch', 'category_id' => $category->id, 'topsale' => 1]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('frontcategory');
        $response->assertViewHas('homeproducts');
        $response->assertViewHas('hotdeal_top');
        $this->assertStringContainsString('trust-features-section', $response->getContent());
        $this->assertStringContainsString('ক্যাশ অন ডেলিভারি', $response->getContent());
        $this->assertStringContainsString('দ্রুততম ডেলিভারি', $response->getContent());
        $this->assertStringContainsString('সহজ রিটার্ন সুবিধা', $response->getContent());
        $this->assertStringContainsString('গ্রাহক সেবা', $response->getContent());
        $this->assertStringContainsString('style.css?v=', $response->getContent());
        $this->assertStringContainsString('responsive.css?v=', $response->getContent());
        $this->assertStringContainsString('topcategory', $response->getContent());
    }

    public function test_category_page_displays_filtered_products()
    {
        $category = $this->createCategory(['name' => 'Fashion', 'slug' => 'fashion']);
        $product = $this->createProduct(['name' => 'Polo T-Shirt', 'slug' => 'polo-t-shirt', 'category_id' => $category->id]);

        $response = $this->get(route('category', 'fashion'));

        $response->assertStatus(200);
        $response->assertViewHas('category');
        $response->assertViewHas('products');
        $response->assertSee('Polo T-Shirt');
    }

    public function test_subcategory_page_displays_products()
    {
        $category = $this->createCategory(['name' => 'Men Fashion', 'slug' => 'men-fashion']);
        $subcategory = $this->createSubcategory($category->id, ['name' => 'Shirts', 'slug' => 'shirts']);
        $product = $this->createProduct([
            'name' => 'Formal Shirt',
            'slug' => 'formal-shirt',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
        ]);

        $response = $this->get(route('subcategory', 'shirts'));

        $response->assertStatus(200);
        $response->assertViewHas('subcategory');
        $response->assertViewHas('products');
        $response->assertSee('Formal Shirt');
    }

    public function test_childcategory_page_displays_products()
    {
        $category = $this->createCategory(['name' => 'Men Fashion', 'slug' => 'men-fashion-2']);
        $subcategory = $this->createSubcategory($category->id, ['name' => 'Casual', 'slug' => 'casual']);
        $childcategory = $this->createChildcategory($subcategory->id, ['name' => 'Denim Shirt', 'slug' => 'denim-shirt']);
        $product = $this->createProduct([
            'name' => 'Blue Denim Shirt',
            'slug' => 'blue-denim-shirt',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'childcategory_id' => $childcategory->id,
        ]);

        $response = $this->get(route('products', 'denim-shirt'));

        $response->assertStatus(200);
        $response->assertViewHas('childcategory');
        $response->assertViewHas('products');
        $response->assertSee('Blue Denim Shirt');
    }

    public function test_product_details_page_renders_with_relations()
    {
        $category = $this->createCategory(['name' => 'Gadgets', 'slug' => 'gadgets']);
        $product = $this->createProduct([
            'name' => 'Wireless Earbuds',
            'slug' => 'wireless-earbuds',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('product', 'wireless-earbuds'));

        $response->assertStatus(200);
        $response->assertViewHas('details');
        $response->assertViewHas('products');
        $response->assertSee('Wireless Earbuds');
    }

    public function test_product_details_auto_selects_first_attribute_options()
    {
        $category = $this->createCategory(['name' => 'Apparel', 'slug' => 'apparel']);
        $product = $this->createProduct([
            'name' => 'Sweatshirt Premium',
            'slug' => 'sweatshirt-premium',
            'category_id' => $category->id,
        ]);

        $size1 = \App\Models\Size::create(['sizeName' => 'M', 'status' => 1]);
        $size2 = \App\Models\Size::create(['sizeName' => 'L', 'status' => 1]);
        \App\Models\Productsize::create(['product_id' => $product->id, 'size_id' => $size1->id]);
        \App\Models\Productsize::create(['product_id' => $product->id, 'size_id' => $size2->id]);

        $response = $this->get(route('product', 'sweatshirt-premium'));

        $response->assertStatus(200);
        $response->assertSee('name="product_size"', false);
        $response->assertSee('checked', false);
    }

    public function test_livesearch_returns_matching_products()
    {
        $category = $this->createCategory(['name' => 'Footwear', 'slug' => 'footwear']);
        $product = $this->createProduct([
            'name' => 'Running Shoes Pro',
            'slug' => 'running-shoes-pro',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('livesearch', ['keyword' => 'Running']));

        $response->assertStatus(200);
        $response->assertSee('Running Shoes Pro');
    }

    public function test_search_page_displays_results()
    {
        $category = $this->createCategory(['name' => 'Bags', 'slug' => 'bags']);
        $product = $this->createProduct([
            'name' => 'Leather Laptop Backpack',
            'slug' => 'leather-laptop-backpack',
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('search', ['keyword' => 'Backpack']));

        $response->assertStatus(200);
        $response->assertSee('Leather Laptop Backpack');
    }

    public function test_hotdeals_page_displays_topsale_products()
    {
        $category = $this->createCategory(['name' => 'Deals', 'slug' => 'deals']);
        $product = $this->createProduct([
            'name' => 'Hot Deal Smartphone',
            'slug' => 'hot-deal-smartphone',
            'category_id' => $category->id,
            'topsale' => 1,
        ]);

        $response = $this->get(route('hotdeals'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertSee('Hot Deal Smartphone');
    }
}
