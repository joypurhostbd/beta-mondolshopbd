<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapAndSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_returns_valid_xml_with_products_and_categories()
    {
        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Summer T-Shirt',
            'slug' => 'summer-t-shirt',
            'product_code' => 'TS-101',
            'new_price' => 500,
            'old_price' => 600,
            'purchase_price' => 300,
            'stock' => 25,
            'status' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('sitemap'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $this->assertStringContainsString('<urlset', $response->getContent());
        $this->assertStringContainsString('category/fashion', $response->getContent());
        $this->assertStringContainsString('product/summer-t-shirt', $response->getContent());
    }

    public function test_product_details_page_renders_schema_org_json_ld()
    {
        $category = Category::create([
            'name' => 'Gadgets',
            'slug' => 'gadgets',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Bluetooth Earbuds',
            'slug' => 'bluetooth-earbuds',
            'product_code' => 'BE-202',
            'new_price' => 1200,
            'old_price' => 1500,
            'purchase_price' => 800,
            'stock' => 10,
            'status' => 1,
            'category_id' => $category->id,
        ]);

        $response = $this->get(route('product', $product->slug));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/ld+json', $response->getContent());
        $this->assertStringContainsString('Bluetooth Earbuds', $response->getContent());
        $this->assertStringContainsString('https://schema.org/InStock', $response->getContent());
    }

    public function test_homepage_renders_schema_org_json_ld_and_semantic_mobile_nav()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Schema.org JSON-LD assertions
        $this->assertStringContainsString('application/ld+json', $content);
        $this->assertStringContainsString('https://schema.org', $content);
        $this->assertStringContainsString('"@type":"WebSite"', str_replace(' ', '', $content));
        $this->assertStringContainsString('"@type":"Organization"', str_replace(' ', '', $content));
        $this->assertStringContainsString('"@type":"SearchAction"', str_replace(' ', '', $content));

        // Semantic Mobile Navigation assertion
        $this->assertStringContainsString('<nav class="footer_nav" aria-label="Mobile Bottom Navigation">', $content);
    }
}
