<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\User;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminWhatsAppButtonTest extends TestCase
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

    public function test_admin_create_and_edit_pages_show_whatsapp_configuration(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Mondol Shop',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 Mondol Shop',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01712345678',
            'whatsapp_title' => 'Chat with our support',
            'whatsapp_message' => 'Hi, I need assistance.',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 1,
        ]);

        $createResponse = $this->actingAs($this->adminUser)->get(route('settings.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('WhatsApp Floating Button Settings');
        $createResponse->assertSee('WhatsApp Button');
        $createResponse->assertSee('WhatsApp Number');
        $createResponse->assertSee('Smart Contextual Messages');
        $createResponse->assertSee('Product Page \'WhatsApp Order\' Button', false);

        $editResponse = $this->actingAs($this->adminUser)->get(route('settings.edit', $setting->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('WhatsApp Floating Button Settings');
        $editResponse->assertSee('01712345678');
        $editResponse->assertSee('Chat with our support');
        $editResponse->assertSee('Smart Contextual Messages');
        $editResponse->assertSee('Product Page \'WhatsApp Order\' Button', false);
    }

    public function test_admin_can_store_setting_with_whatsapp_configuration(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('settings.store'), [
            'name' => 'Store With WhatsApp',
            'copyright' => '© 2026',
            'white_logo' => UploadedFile::fake()->image('white.png', 200, 50),
            'dark_logo' => UploadedFile::fake()->image('dark.png', 200, 50),
            'favicon' => UploadedFile::fake()->image('favicon.png', 32, 32),
            'status' => 1,
            'whatsapp_status' => '1',
            'whatsapp_number' => '01811223344',
            'whatsapp_title' => 'Customer Care',
            'whatsapp_message' => 'Hello team, I need help with an order.',
            'whatsapp_dynamic_context' => '1',
            'whatsapp_product_button' => '1',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'name' => 'Store With WhatsApp',
            'whatsapp_status' => 1,
            'whatsapp_number' => '01811223344',
            'whatsapp_title' => 'Customer Care',
            'whatsapp_message' => 'Hello team, I need help with an order.',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 1,
        ]);
    }

    public function test_admin_can_update_setting_with_whatsapp_configuration(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Existing Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01711111111',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('settings.update'), [
            'id' => $setting->id,
            'name' => 'Updated Store',
            'copyright' => '© 2026 Updated',
            'status' => 1,
            'whatsapp_status' => '1',
            'whatsapp_number' => '01999888777',
            'whatsapp_title' => 'Direct Sales Hotline',
            'whatsapp_message' => 'Hello, I want to make a wholesale inquiry.',
            'whatsapp_dynamic_context' => '1',
            'whatsapp_product_button' => '1',
        ]);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'id' => $setting->id,
            'name' => 'Updated Store',
            'whatsapp_status' => 1,
            'whatsapp_number' => '01999888777',
            'whatsapp_title' => 'Direct Sales Hotline',
            'whatsapp_message' => 'Hello, I want to make a wholesale inquiry.',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 1,
        ]);
    }

    public function test_frontend_displays_floating_whatsapp_button_when_enabled(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Mondol Live Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01799887766',
            'whatsapp_title' => 'Live WhatsApp Support',
            'whatsapp_message' => 'Assalamu Alaikum, I need support.',
        ]);

        $contact = Contact::create([
            'phone' => '01700000000',
            'email' => 'support@mondolshopbd.com',
            'address' => 'Dhaka, Bangladesh',
            'hotline' => '01700000000',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('whatsapp-widget-container', false);
        $response->assertSee('whatsapp-floating-btn', false);
        $response->assertSee('8801799887766', false);
        $response->assertSee('Live WhatsApp Support', false);
    }

    public function test_frontend_hides_floating_whatsapp_button_when_disabled(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'Store With WhatsApp Off',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 0,
            'whatsapp_number' => '01799887766',
        ]);

        $contact = Contact::create([
            'phone' => '01700000000',
            'email' => 'support@mondolshopbd.com',
            'address' => 'Dhaka, Bangladesh',
            'hotline' => '01700000000',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('whatsapp-widget-container', false);
        $response->assertDontSee('whatsapp-floating-btn', false);
    }

    public function test_product_page_renders_contextual_whatsapp_details_and_order_button(): void
    {
        $category = Category::create([
            'name' => 'Panjabi Category',
            'slug' => 'panjabi-category',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Premium Silk Panjabi',
            'slug' => 'premium-silk-panjabi',
            'product_code' => 'PSP-990',
            'new_price' => 1850,
            'old_price' => 2200,
            'purchase_price' => 1200,
            'stock' => 15,
            'status' => 1,
            'category_id' => $category->id,
        ]);

        $setting = GeneralSetting::create([
            'name' => 'Mondol Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01972101994',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 1,
        ]);

        $contact = Contact::create([
            'phone' => '01972101994',
            'email' => 'sales@mondolshopbd.com',
            'address' => 'Dhaka',
            'hotline' => '01972101994',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('product', $product->slug));

        $response->assertStatus(200);
        // Assert dedicated WhatsApp product button is present
        $response->assertSee('whatsapp-product-order-btn', false);
        $response->assertSee('হোয়াটসঅ্যাপে অর্ডার করুন', false);

        // Assert floating widget contains product name, code and price in url
        $response->assertSee('whatsapp-widget-container', false);
        $response->assertSee('8801972101994', false);
        $this->assertStringContainsString('Premium%20Silk%20Panjabi', $response->getContent());
        $this->assertStringContainsString('1850', $response->getContent());
        $this->assertStringContainsString('PSP-990', $response->getContent());
    }

    public function test_product_page_hides_order_button_when_whatsapp_product_button_disabled(): void
    {
        $category = Category::create([
            'name' => 'Shirt Category',
            'slug' => 'shirt-category',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Casual Cotton Shirt',
            'slug' => 'casual-cotton-shirt',
            'product_code' => 'CCS-100',
            'new_price' => 850,
            'old_price' => 1000,
            'purchase_price' => 500,
            'stock' => 10,
            'status' => 1,
            'category_id' => $category->id,
        ]);

        $setting = GeneralSetting::create([
            'name' => 'Mondol Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01972101994',
            'whatsapp_dynamic_context' => 1,
            'whatsapp_product_button' => 0, // Disabled
        ]);

        $contact = Contact::create([
            'phone' => '01972101994',
            'email' => 'sales@mondolshopbd.com',
            'address' => 'Dhaka',
            'hotline' => '01972101994',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        $response = $this->get(route('product', $product->slug));

        $response->assertStatus(200);
        $response->assertDontSee('id="whatsapp-product-order-btn"', false);
        $response->assertDontSee('হোয়াটসঅ্যাপে অর্ডার করুন', false);
        // Floating button remains active
        $response->assertSee('whatsapp-widget-container', false);
    }

    public function test_checkout_page_renders_contextual_whatsapp_message(): void
    {
        \App\Models\ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 60,
            'status' => 1,
        ]);

        $setting = GeneralSetting::create([
            'name' => 'Mondol Store',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026',
            'status' => 1,
            'whatsapp_status' => 1,
            'whatsapp_number' => '01972101994',
            'whatsapp_dynamic_context' => 1,
        ]);

        $contact = Contact::create([
            'phone' => '01972101994',
            'email' => 'sales@mondolshopbd.com',
            'address' => 'Dhaka',
            'hotline' => '01972101994',
            'status' => 1,
        ]);

        view()->share('generalsetting', $setting);
        view()->share('contact', $contact);

        // Add item to cart
        Cart::instance('shopping')->add([
            'id' => 999,
            'name' => 'Demo Item',
            'qty' => 2,
            'price' => 500,
            'weight' => 1,
            'options' => ['image' => 'demo.png', 'slug' => 'demo-item'],
        ]);

        $response = $this->get(route('customer.checkout'));

        $response->assertStatus(200);
        $response->assertSee('whatsapp-widget-container', false);
        $response->assertSee('8801972101994', false);
        $this->assertStringContainsString('data-context="checkout"', $response->getContent());
    }
}