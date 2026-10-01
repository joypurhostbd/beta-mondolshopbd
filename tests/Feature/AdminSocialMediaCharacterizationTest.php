<?php

namespace Tests\Feature;

use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSocialMediaCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'social-list',
            'social-create',
            'social-edit',
            'social-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_social_media_manage(): void
    {
        $response = $this->get(route('socialmedias.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_social_media_index_with_kpis(): void
    {
        SocialMedia::create([
            'title' => 'Facebook',
            'icon' => 'fe-facebook',
            'link' => 'https://facebook.com/mondolshop',
            'color' => '#1877f2',
            'status' => 1,
        ]);

        SocialMedia::create([
            'title' => 'YouTube',
            'icon' => 'fe-youtube',
            'link' => 'https://youtube.com/mondolshop',
            'color' => '#ff0000',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('socialmedias.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('total_social', 2);
        $response->assertViewHas('active_social', 1);
        $response->assertViewHas('inactive_social', 1);
        $response->assertSee('Facebook');
        $response->assertSee('YouTube');
    }

    public function test_admin_can_view_create_social_media_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('socialmedias.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Social Media Link');
        $response->assertSee('Platform Name');
    }

    public function test_admin_can_store_social_media_link(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('socialmedias.store'), [
            'title' => 'Instagram',
            'icon' => 'fe-instagram',
            'link' => 'https://instagram.com/mondolshop',
            'color' => '#e4405f',
            'status' => 1,
        ]);

        $response->assertRedirect(route('socialmedias.index'));

        $this->assertDatabaseHas('social_media', [
            'title' => 'Instagram',
            'icon' => 'fe-instagram',
            'link' => 'https://instagram.com/mondolshop',
            'color' => '#e4405f',
            'status' => 1,
        ]);
    }

    public function test_admin_can_view_edit_and_update_social_media(): void
    {
        $social = SocialMedia::create([
            'title' => 'Old Twitter',
            'icon' => 'fe-twitter',
            'link' => 'https://twitter.com/old',
            'color' => '#1da1f2',
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('socialmedias.edit', $social->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Old Twitter');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('socialmedias.update'), [
            'id' => $social->id,
            'hidden_id' => $social->id,
            'title' => 'X (Twitter)',
            'icon' => 'fe-twitter',
            'link' => 'https://x.com/mondolshop',
            'color' => '#000000',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('socialmedias.index'));

        $this->assertDatabaseHas('social_media', [
            'id' => $social->id,
            'title' => 'X (Twitter)',
            'link' => 'https://x.com/mondolshop',
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_social_media_status(): void
    {
        $social = SocialMedia::create([
            'title' => 'LinkedIn',
            'icon' => 'fe-linkedin',
            'link' => 'https://linkedin.com/mondolshop',
            'color' => '#0077b5',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('socialmedias.inactive'), [
            'hidden_id' => $social->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('social_media', ['id' => $social->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('socialmedias.active'), [
            'hidden_id' => $social->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('social_media', ['id' => $social->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_social_media(): void
    {
        $social = SocialMedia::create([
            'title' => 'Threads',
            'icon' => 'fe-at-sign',
            'link' => 'https://threads.net/mondolshop',
            'color' => '#000000',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('socialmedias.destroy'), [
            'hidden_id' => $social->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('social_media', ['id' => $social->id]);
    }
}
