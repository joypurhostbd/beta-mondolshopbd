<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\CreatePage;
use App\Models\GeneralSetting;
use App\Models\SocialMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Setting\Domain\Events\SettingUpdatedEvent;
use Shared\Domain\Contracts\Modules\SettingModuleInterface;
use Tests\TestCase;

class SettingModuleTest extends TestCase
{
    use RefreshDatabase;

    private SettingModuleInterface $settingModule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->settingModule = $this->app->make(SettingModuleInterface::class);

        GeneralSetting::query()->delete();
        SocialMedia::query()->delete();
        Contact::query()->delete();
        CreatePage::query()->delete();
    }

    public function test_get_and_update_general_setting(): void
    {
        Event::fake([SettingUpdatedEvent::class]);

        $updated = $this->settingModule->updateGeneralSetting([
            'name' => 'MondolShop Official',
            'white_logo' => 'uploads/logo-white.png',
            'dark_logo' => 'uploads/logo-dark.png',
            'favicon' => 'uploads/favicon.ico',
            'description' => 'Best lifestyle e-commerce brand',
            'status' => 1,
        ]);

        $this->assertEquals('MondolShop Official', $updated['name']);
        $this->assertEquals('uploads/logo-white.png', $updated['white_logo']);

        $retrieved = $this->settingModule->getGeneralSetting();
        $this->assertNotNull($retrieved);
        $this->assertEquals('MondolShop Official', $retrieved['name']);

        Event::assertDispatched(SettingUpdatedEvent::class, function ($e) {
            return $e->settingType === 'general';
        });
    }

    public function test_get_social_media_and_contact_info(): void
    {
        SocialMedia::create([
            'title' => 'Facebook',
            'icon' => 'fa fa-facebook',
            'link' => 'https://facebook.com/mondolshop',
            'status' => 1,
        ]);

        $socialLinks = $this->settingModule->getSocialMediaLinks();
        $this->assertCount(1, $socialLinks);
        $this->assertEquals('Facebook', $socialLinks[0]['title']);

        Contact::create([
            'phone' => '01711223344',
            'email' => 'support@mondolshopbd.com',
            'address' => 'Dhaka, Bangladesh',
            'hotline' => '16222',
            'status' => 1,
        ]);

        $contact = $this->settingModule->getContactInfo();
        $this->assertNotNull($contact);
        $this->assertEquals('01711223344', $contact['phone']);
        $this->assertEquals('support@mondolshopbd.com', $contact['email']);
    }

    public function test_get_page_by_slug(): void
    {
        CreatePage::create([
            'name' => 'Privacy Policy',
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'description' => 'Our detailed privacy terms.',
            'status' => 1,
        ]);

        $page = $this->settingModule->getPageBySlug('privacy-policy');
        $this->assertNotNull($page);
        $this->assertEquals('Privacy Policy', $page['title']);
        $this->assertEquals('privacy-policy', $page['slug']);
    }
}