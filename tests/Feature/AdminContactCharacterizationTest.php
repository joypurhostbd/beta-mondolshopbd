<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminContactCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'contact-list',
            'contact-create',
            'contact-edit',
            'contact-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_contact_manage(): void
    {
        $response = $this->get(route('contact.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_contact_index_with_kpis(): void
    {
        Contact::create([
            'phone' => '+880 1700-111222',
            'hotline' => '09600-111222',
            'email' => 'info@mondolshop.com',
            'hotmail' => 'support@mondolshop.com',
            'address' => 'Mirpur, Dhaka, Bangladesh',
            'status' => 1,
        ]);

        Contact::create([
            'phone' => '+880 1800-333444',
            'email' => 'old@mondolshop.com',
            'address' => 'Old Town, Dhaka',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('contact.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('total_contacts', 2);
        $response->assertViewHas('active_contacts', 1);
        $response->assertViewHas('inactive_contacts', 1);
        $response->assertSee('+880 1700-111222');
        $response->assertSee('+880 1800-333444');
    }

    public function test_admin_can_view_create_contact_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('contact.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Contact Info');
        $response->assertSee('Primary Phone Number');
    }

    public function test_admin_can_store_contact_info(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('contact.store'), [
            'phone' => '+880 1900-555666',
            'hotline' => '09600-555666',
            'email' => 'sales@mondolshop.com',
            'hotmail' => 'care@mondolshop.com',
            'address' => 'Dhanmondi 27, Dhaka',
            'maplink' => 'https://maps.google.com/?q=dhaka',
            'status' => 1,
        ]);

        $response->assertRedirect(route('contact.index'));

        $this->assertDatabaseHas('contacts', [
            'phone' => '+880 1900-555666',
            'hotline' => '09600-555666',
            'email' => 'sales@mondolshop.com',
            'hotmail' => 'care@mondolshop.com',
            'address' => 'Dhanmondi 27, Dhaka',
            'status' => 1,
        ]);
    }

    public function test_admin_can_view_edit_and_update_contact_info(): void
    {
        $contact = Contact::create([
            'phone' => '+880 1500-777888',
            'email' => 'old_branch@mondolshop.com',
            'address' => 'Banani, Dhaka',
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('contact.edit', $contact->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('+880 1500-777888');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('contact.update'), [
            'id' => $contact->id,
            'hidden_id' => $contact->id,
            'phone' => '+880 1500-999000',
            'hotline' => '09600-999000',
            'email' => 'updated_branch@mondolshop.com',
            'hotmail' => 'updated_care@mondolshop.com',
            'address' => 'Gulshan 2, Dhaka',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('contact.index'));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'phone' => '+880 1500-999000',
            'email' => 'updated_branch@mondolshop.com',
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_contact_status(): void
    {
        $contact = Contact::create([
            'phone' => '+880 1600-111222',
            'email' => 'toggle@mondolshop.com',
            'address' => 'Uttara, Dhaka',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('contact.inactive'), [
            'hidden_id' => $contact->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('contact.active'), [
            'hidden_id' => $contact->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_contact_info(): void
    {
        $contact = Contact::create([
            'phone' => '+880 1300-444555',
            'email' => 'delete_target@mondolshop.com',
            'address' => 'Chittagong, Bangladesh',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('contact.destroy'), [
            'hidden_id' => $contact->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }
}
