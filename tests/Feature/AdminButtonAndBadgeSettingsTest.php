<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminButtonAndBadgeSettingsTest extends TestCase
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

    public function test_admin_can_view_button_and_badge_settings_in_edit_form(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'MondolShop BD',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 MondolShop BD',
            'status' => 1,
            'order_btn_text' => 'অর্ডার করুন',
            'order_btn_bg_color' => '#fe5200',
            'order_btn_text_color' => '#ffffff',
            'order_btn_hover_bg_color' => '#e04800',
            'cart_btn_text' => 'কার্টে যোগ',
            'cart_btn_bg_color' => '#2f3543',
            'cart_btn_text_color' => '#ffffff',
            'cart_btn_hover_bg_color' => '#1e222b',
            'discount_badge_text' => 'ছাড়',
            'discount_badge_bg_color' => '#ffffff',
            'discount_badge_text_color' => '#fe5200',
            'discount_badge_border_color' => '#fe5200',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('settings.edit', $setting->id));

        $response->assertStatus(200);
        $response->assertSee('বাটন ও ব্যাজ স্টাইলিং ও কালার কাস্টমাইজেশন');
        $response->assertSee('name="order_btn_bg_color"', false);
        $response->assertSee('name="cart_btn_bg_color"', false);
        $response->assertSee('name="discount_badge_bg_color"', false);
        $response->assertSee('id="preview_order_btn"', false);
    }

    public function test_admin_can_update_button_and_badge_colors_and_labels(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'MondolShop BD',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 MondolShop BD',
            'status' => 1,
        ]);

        $updateData = [
            'id' => $setting->id,
            'hidden_id' => $setting->id,
            'name' => 'MondolShop BD',
            'order_btn_text' => 'এখনই অর্ডার করুন',
            'order_btn_bg_color' => '#ff0055',
            'order_btn_text_color' => '#ffffff',
            'order_btn_hover_bg_color' => '#cc0044',
            'cart_btn_text' => 'ব্যাগে যুক্ত করুন',
            'cart_btn_bg_color' => '#112233',
            'cart_btn_text_color' => '#e2e8f0',
            'cart_btn_hover_bg_color' => '#0a1520',
            'discount_badge_text' => 'অফ',
            'discount_badge_bg_color' => '#ffeb3b',
            'discount_badge_text_color' => '#d32f2f',
            'discount_badge_border_color' => '#d32f2f',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('settings.update'), $updateData);

        $response->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('general_settings', [
            'id' => $setting->id,
            'order_btn_text' => 'এখনই অর্ডার করুন',
            'order_btn_bg_color' => '#ff0055',
            'cart_btn_text' => 'ব্যাগে যুক্ত করুন',
            'cart_btn_bg_color' => '#112233',
            'discount_badge_text' => 'অফ',
            'discount_badge_bg_color' => '#ffeb3b',
        ]);
    }

    public function test_storefront_renders_customized_button_and_badge_styles(): void
    {
        $setting = GeneralSetting::create([
            'name' => 'MondolShop BD',
            'white_logo' => 'uploads/settings/white.png',
            'dark_logo' => 'uploads/settings/dark.png',
            'favicon' => 'uploads/settings/favicon.png',
            'copyright' => '© 2026 MondolShop BD',
            'status' => 1,
            'order_btn_text' => 'অর্ডার নাও',
            'order_btn_bg_color' => '#123456',
            'order_btn_text_color' => '#f0f0f0',
            'order_btn_hover_bg_color' => '#0f2b48',
            'cart_btn_text' => 'কার্ট প্লাস',
            'cart_btn_bg_color' => '#654321',
            'cart_btn_text_color' => '#ffffff',
            'cart_btn_hover_bg_color' => '#432100',
            'discount_badge_text' => 'ডিসকাউন্ট',
            'discount_badge_bg_color' => '#00ffcc',
            'discount_badge_text_color' => '#000000',
            'discount_badge_border_color' => '#00ccaa',
        ]);

        view()->share('generalsetting', $setting);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('--order-btn-bg: #123456', false);
        $response->assertSee('--cart-btn-bg: #654321', false);
        $response->assertSee('--discount-badge-bg: #00ffcc', false);
        $response->assertSee('.sale-badge-box,', false);
        $response->assertSee('.product-details-discount-badge span.sale-badge-text', false);
    }
}
