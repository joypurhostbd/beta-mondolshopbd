<?php

namespace Tests\Feature;

use App\Models\CreatePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPageCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'page-list',
            'page-create',
            'page-edit',
            'page-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_page_manage_or_create(): void
    {
        $responseManage = $this->get(route('pages.index'));
        $responseManage->assertRedirect('/admin/login');

        $responseCreate = $this->get(route('pages.create'));
        $responseCreate->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_page_index_with_kpis(): void
    {
        CreatePage::create([
            'name' => 'About Us',
            'title' => 'About MondolShop',
            'slug' => 'about-us',
            'description' => '<p>Leading e-commerce platform.</p>',
            'status' => 1,
        ]);

        CreatePage::create([
            'name' => 'Return Policy',
            'title' => 'Return Refund Policy',
            'slug' => 'return-policy',
            'description' => '<p>Easy return process.</p>',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('pages.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('total_pages', 2);
        $response->assertViewHas('active_pages', 1);
        $response->assertViewHas('inactive_pages', 1);
        $response->assertSee('About Us');
        $response->assertSee('Return Policy');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('pages.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Page');
        $response->assertSee('Page Name');
    }

    public function test_admin_can_store_page_active_and_inactive(): void
    {
        // Store Active Page
        $response = $this->actingAs($this->adminUser)->post(route('pages.store'), [
            'name' => 'Terms Conditions',
            'title' => 'Terms Conditions of MondolShop',
            'description' => '<p>All legal terms and rules.</p>',
            'status' => 1,
        ]);

        $response->assertRedirect(route('pages.index'));

        $this->assertDatabaseHas('create_pages', [
            'name' => 'Terms Conditions',
            'slug' => 'terms-conditions',
            'title' => 'Terms Conditions of MondolShop',
            'status' => 1,
        ]);

        // Store Inactive Page (no status checkbox in request)
        $responseInactive = $this->actingAs($this->adminUser)->post(route('pages.store'), [
            'name' => 'FAQ Page',
            'title' => 'Frequently Asked Questions',
            'description' => '<p>Help center and FAQs.</p>',
        ]);

        $responseInactive->assertRedirect(route('pages.index'));

        $this->assertDatabaseHas('create_pages', [
            'name' => 'FAQ Page',
            'slug' => 'faq-page',
            'title' => 'Frequently Asked Questions',
            'status' => 0,
        ]);
    }

    public function test_admin_can_view_edit_and_update_page(): void
    {
        $page = CreatePage::create([
            'name' => 'Delivery Information',
            'title' => 'Shipping Delivery',
            'slug' => 'delivery-info',
            'description' => '<p>Old shipping rules.</p>',
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('pages.edit', $page->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Delivery Information');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('pages.update'), [
            'id' => $page->id,
            'hidden_id' => $page->id,
            'name' => 'Express Delivery Info',
            'title' => 'Express Nationwide Shipping Delivery',
            'description' => '<p>Updated 24h shipping terms.</p>',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('pages.index'));

        $this->assertDatabaseHas('create_pages', [
            'id' => $page->id,
            'name' => 'Express Delivery Info',
            'slug' => 'express-delivery-info',
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_page_status(): void
    {
        $page = CreatePage::create([
            'name' => 'Disclaimer',
            'title' => 'Legal Disclaimer',
            'slug' => 'disclaimer',
            'description' => '<p>Disclaimer contents.</p>',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('pages.inactive'), [
            'hidden_id' => $page->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('create_pages', ['id' => $page->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('pages.active'), [
            'hidden_id' => $page->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('create_pages', ['id' => $page->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_page(): void
    {
        $page = CreatePage::create([
            'name' => 'Draft Page',
            'title' => 'Draft Title',
            'slug' => 'draft-page',
            'description' => '<p>Draft details.</p>',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('pages.destroy'), [
            'hidden_id' => $page->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('create_pages', ['id' => $page->id]);
    }
}