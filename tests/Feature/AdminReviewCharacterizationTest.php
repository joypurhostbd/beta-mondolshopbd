<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminReviewCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'review-list',
            'review-create',
            'review-edit',
            'review-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_reviews_index_renders_with_kpi_metrics_and_product_details(): void
    {
        $product = Product::create([
            'name' => 'Smart Watch',
            'slug' => 'smart-watch',
            'category_id' => 1,
            'product_code' => 'SW-01',
            'old_price' => 2500,
            'new_price' => 2000,
            'purchase_price' => 1500,
            'stock' => 10,
            'status' => 1,
        ]);

        $customer = Customer::create([
            'name' => 'Rahim Ahmed',
            'slug' => 'rahim-ahmed',
            'phone' => '01711000000',
            'email' => 'rahim@example.com',
            'password' => bcrypt('12345678'),
            'status' => 'active',
        ]);

        Review::create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'name' => 'Rahim Ahmed',
            'email' => 'rahim@example.com',
            'review' => 'Great product, high quality!',
            'ratting' => 5,
            'status' => 'active',
        ]);

        Review::create([
            'product_id' => $product->id,
            'name' => 'Guest User',
            'review' => 'Waiting for feedback verification',
            'ratting' => 4,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reviews.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('metrics', function ($metrics) {
            return $metrics['total'] === 2
                && $metrics['active'] === 1
                && $metrics['pending'] === 1
                && $metrics['avg_rating'] === 4.5;
        });

        $response->assertSee('Smart Watch');
        $response->assertSee('Rahim Ahmed');
        $response->assertSee('Guest User');
    }

    public function test_admin_pending_reviews_renders_only_pending_items(): void
    {
        $product = Product::create([
            'name' => 'Wireless Headphone',
            'slug' => 'wireless-headphone',
            'category_id' => 1,
            'product_code' => 'WH-01',
            'old_price' => 1800,
            'new_price' => 1500,
            'purchase_price' => 1100,
            'stock' => 15,
            'status' => 1,
        ]);

        Review::create([
            'product_id' => $product->id,
            'name' => 'Active Customer',
            'review' => 'Approved already',
            'ratting' => 5,
            'status' => 'active',
        ]);

        Review::create([
            'product_id' => $product->id,
            'name' => 'Pending Reviewer',
            'review' => 'Needs admin moderation',
            'ratting' => 4,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reviews.pending'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertSee('Pending Reviewer');
        $response->assertDontSee('Approved already');
    }

    public function test_admin_can_store_new_review(): void
    {
        $product = Product::create([
            'name' => 'Bluetooth Speaker',
            'slug' => 'bluetooth-speaker',
            'category_id' => 1,
            'product_code' => 'BS-01',
            'old_price' => 3000,
            'new_price' => 2500,
            'purchase_price' => 1800,
            'stock' => 5,
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'name' => 'Karim Ali',
            'ratting' => 5,
            'review' => 'Excellent sound quality and fast delivery!',
            'status' => 1,
        ]);

        $response->assertRedirect(route('reviews.index'));
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'name' => 'Karim Ali',
            'ratting' => 5,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_review(): void
    {
        $product = Product::create([
            'name' => 'Casual Shoes',
            'slug' => 'casual-shoes',
            'category_id' => 1,
            'product_code' => 'CS-01',
            'old_price' => 2200,
            'new_price' => 1800,
            'purchase_price' => 1200,
            'stock' => 8,
            'status' => 1,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'name' => 'Original Name',
            'ratting' => 3,
            'review' => 'Initial review text',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('reviews.update'), [
            'id' => $review->id,
            'product_id' => $product->id,
            'name' => 'Updated Name',
            'ratting' => 5,
            'review' => 'Updated review text with 5 stars',
            'status' => 1,
        ]);

        $response->assertRedirect(route('reviews.index'));
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'name' => 'Updated Name',
            'ratting' => 5,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_toggle_review_active_and_pending(): void
    {
        $product = Product::create([
            'name' => 'Backpack',
            'slug' => 'backpack',
            'category_id' => 1,
            'product_code' => 'BP-01',
            'old_price' => 1200,
            'new_price' => 950,
            'purchase_price' => 600,
            'stock' => 20,
            'status' => 1,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'name' => 'John Doe',
            'ratting' => 4,
            'review' => 'Good backpack',
            'status' => 'pending',
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('reviews.active'), [
            'hidden_id' => $review->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'active',
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('reviews.inactive'), [
            'hidden_id' => $review->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_delete_review(): void
    {
        $product = Product::create([
            'name' => 'Water Bottle',
            'slug' => 'water-bottle',
            'category_id' => 1,
            'product_code' => 'WB-01',
            'old_price' => 500,
            'new_price' => 350,
            'purchase_price' => 200,
            'stock' => 50,
            'status' => 1,
        ]);

        $review = Review::create([
            'product_id' => $product->id,
            'name' => 'Spam Reviewer',
            'ratting' => 1,
            'review' => 'Spam content',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('reviews.destroy'), [
            'hidden_id' => $review->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_superadmin_role_can_access_reviews_without_direct_permissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminUserWithoutDirectPerms = User::factory()->create(['status' => 1]);
        $adminUserWithoutDirectPerms->assignRole($role);

        $response = $this->actingAs($adminUserWithoutDirectPerms)->get(route('reviews.pending'));
        $response->assertStatus(200);
    }

    public function test_review_supports_both_rating_and_ratting_input_and_calculates_average(): void
    {
        $product = Product::create([
            'name' => 'Portable Charger',
            'slug' => 'portable-charger',
            'category_id' => 1,
            'product_code' => 'PC-99',
            'old_price' => 1200,
            'new_price' => 1000,
            'purchase_price' => 700,
            'stock' => 15,
            'status' => 1,
        ]);

        // Test storing with 'rating' field
        $response1 = $this->actingAs($this->adminUser)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'review' => 'Excellent build quality!',
            'rating' => 5,
            'status' => 1,
        ]);
        $response1->assertRedirect(route('reviews.index'));

        // Test storing with 'ratting' field
        $response2 = $this->actingAs($this->adminUser)->post(route('reviews.store'), [
            'product_id' => $product->id,
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'review' => 'Pretty good battery life.',
            'ratting' => 4,
            'status' => 1,
        ]);
        $response2->assertRedirect(route('reviews.index'));

        $reviews = Review::where('product_id', $product->id)->get();
        $this->assertCount(2, $reviews);

        foreach ($reviews as $rev) {
            $this->assertNotNull($rev->rating);
            $this->assertNotNull($rev->ratting);
            $this->assertEquals($rev->rating, $rev->ratting);
        }

        $this->assertEquals(4.5, Review::averageRating());
    }
}
