<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FooterSettingsAndNewsletterTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_can_update_footer_settings(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'MondolShop BD',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 MondolShop BD',
            'status' => 1,
        ]);

        $updatePayload = [
            'id' => $setting->id,
            'hidden_id' => $setting->id,
            'name' => 'MondolShop BD Updated',
            'copyright' => '© 2026 MondolShop BD Custom Copyright',
            'footer_about' => 'Custom Bengali footer about text',
            'footer_phone' => '01877786651',
            'footer_email' => 'custom@mondolshopbd.com',
            'footer_address' => 'ঢাকা, বাংলাদেশ',
            'footer_useful_links_title' => 'Useful Links Title',
            'footer_info_links_title' => 'Information Title',
            'newsletter_title' => 'Stay Connected Title',
            'newsletter_text' => 'Join our club',
            'newsletter_status' => '1',
            'app_download_title' => 'Download App Now',
            'app_download_status' => '1',
            'play_store_url' => 'https://play.google.com/test',
            'app_store_url' => 'https://apple.com/test',
            'features_status' => '1',
            'feature1_title' => 'Secure Pay',
            'feature1_subtitle' => '100% Safe',
            'feature2_title' => 'Express Delivery',
            'feature2_subtitle' => 'Nationwide',
            'feature3_title' => '7 Days Return',
            'feature3_subtitle' => 'No Hassle',
            'feature4_title' => '24/7 Help',
            'feature4_subtitle' => 'Always Online',
            'show_payment_methods' => '1',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('settings.update'), $updatePayload);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'id' => $setting->id,
            'footer_about' => 'Custom Bengali footer about text',
            'footer_phone' => '01877786651',
            'footer_email' => 'custom@mondolshopbd.com',
            'feature1_title' => 'Secure Pay',
            'feature2_title' => 'Express Delivery',
        ]);
    }

    public function test_visitor_can_subscribe_to_newsletter_successfully(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'customer@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'customer@example.com',
            'status' => 1,
        ]);
    }

    public function test_duplicate_newsletter_subscriber_is_handled_gracefully(): void
    {
        NewsletterSubscriber::create([
            'email' => 'duplicate@example.com',
            'ip_address' => '127.0.0.1',
            'status' => 1,
        ]);

        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'duplicate@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'আপনি ইতিমধ্যে আমাদের নিউজলেটারে সাবস্ক্রাইব করেছেন।',
        ]);

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_newsletter_subscription_requires_valid_email(): void
    {
        $response = $this->postJson(route('newsletter.subscribe'), [
            'email' => 'invalid-email-format',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_storefront_home_renders_dynamic_footer_elements(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'MondolShop BD',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'footer_about' => 'আপনার বিশ্বস্ত অনলাইন শপ',
            'footer_phone' => '01877786651',
            'footer_email' => 'support@mondolshopbd.com',
            'footer_address' => 'ঢাকা, বাংলাদেশ',
            'footer_useful_links_title' => 'Useful Links',
            'footer_info_links_title' => 'Information',
            'newsletter_title' => 'Stay Connected',
            'newsletter_text' => 'Subscribe for offers',
            'feature1_title' => '100% Secure Payment',
            'feature2_title' => 'Fast Delivery',
            'feature3_title' => 'Easy Return',
            'feature4_title' => '24/7 Support',
            'copyright' => '© 2026 MondolShop BD',
            'status' => 1,
        ]);

        $contact = Contact::create([
            'phone' => '01877786651',
            'hotline' => '01877786651',
            'email' => 'support@mondolshopbd.com',
            'address' => 'ঢাকা, বাংলাদেশ',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('আপনার বিশ্বস্ত অনলাইন শপ');
        $response->assertSee('01877786651');
        $response->assertSee('support@mondolshopbd.com');
        $response->assertSee('100% Secure Payment');
        $response->assertSee('Fast Delivery');
        $response->assertSee('Easy Return');
        $response->assertSee('24/7 Support');
        $response->assertSee('bKash');
        $response->assertSee('Nagad');
        $response->assertSee('VISA');
    }
}
