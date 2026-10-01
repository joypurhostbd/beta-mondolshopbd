<?php

namespace Tests\Feature;

use App\Models\GoogleTagManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminTagManagerCharacterizationTest extends TestCase
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

    public function test_guest_cannot_access_tag_manager(): void
    {
        $response = $this->get(route('tagmanagers.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('tagmanagers.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_tag_manager_index_with_kpis(): void
    {
        GoogleTagManager::create([
            'code' => 'GTM-TEST001',
            'status' => 1,
        ]);

        GoogleTagManager::create([
            'code' => 'GTM-TEST002',
            'status' => 1,
        ]);

        GoogleTagManager::create([
            'code' => 'GTM-TEST003',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('tagmanagers.index'));

        $response->assertStatus(200);
        $response->assertSee('Google Tag Manager');
        $response->assertSee('Total Tags');
        $response->assertSee('Active Tags');
        $response->assertSee('Inactive Tags');
        $response->assertSee('Active GTM ID');
        $response->assertSee('GTM-TEST001');
        $response->assertSee('GTM-TEST002');
        $response->assertSee('GTM-TEST003');
    }

    public function test_admin_can_view_create_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('tagmanagers.create'));
        $response->assertStatus(200);
        $response->assertSee('Google Tag Manager Create');
        $response->assertSee('New GTM Container');
        $response->assertSee('Tag Manager ID');
        $response->assertSee('GTM-K89745P');
    }

    public function test_storefront_renders_active_gtm_with_normalized_prefix(): void
    {
        GoogleTagManager::create([
            'code' => 'TM3R5R4L', // entered without GTM- prefix
            'status' => 1,
        ]);

        GoogleTagManager::create([
            'code' => 'GTM-CUSTOM888', // entered with GTM- prefix
            'status' => 1,
        ]);

        GoogleTagManager::create([
            'code' => 'GTM-INACTIVE999', // inactive
            'status' => 0,
        ]);

        view()->share('gtm_code', GoogleTagManager::where('status', 1)->get());

        $response = $this->get('/');

        $response->assertStatus(200);
        // Head script assertions
        $response->assertSee('dataLayer\',\'GTM-TM3R5R4L\'', false);
        $response->assertSee('dataLayer\',\'GTM-CUSTOM888\'', false);
        $response->assertDontSee('dataLayer\',\'GTM-INACTIVE999\'', false);
        $response->assertDontSee('dataLayer\',\'GTM-GTM-', false);

        // Noscript iframe assertions
        $response->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-TM3R5R4L', false);
        $response->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-CUSTOM888', false);
        $response->assertDontSee('https://www.googletagmanager.com/ns.html?id=GTM-INACTIVE999', false);
    }

    public function test_admin_can_store_new_tag_manager(): void
    {
        $payload = [
            'code' => 'GTM-NEWTAG99',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseHas('google_tag_managers', [
            'code' => 'GTM-NEWTAG99',
            'status' => 1,
        ]);
    }

    public function test_store_validation_requires_code(): void
    {
        $payload = [
            'code' => '',
            'status' => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_admin_can_view_edit_page(): void
    {
        $tag = GoogleTagManager::create([
            'code' => 'GTM-EDITME',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('tagmanagers.edit', $tag->id));
        $response->assertStatus(200);
        $response->assertSee('Google Tag Manager Edit');
        $response->assertSee('GTM-EDITME');
    }

    public function test_admin_can_update_tag_manager(): void
    {
        $tag = GoogleTagManager::create([
            'code' => 'GTM-OLDKEY',
            'status' => 1,
        ]);

        $payload = [
            'id' => $tag->id,
            'code' => 'GTM-NEWUPDATED',
            'status' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.update'), $payload);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseHas('google_tag_managers', [
            'id' => $tag->id,
            'code' => 'GTM-NEWUPDATED',
            'status' => 0,
        ]);
    }

    public function test_admin_can_toggle_active_and_inactive(): void
    {
        $tag = GoogleTagManager::create([
            'code' => 'GTM-TOGGLE',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('tagmanagers.index'))
            ->post(route('tagmanagers.inactive'), [
                'hidden_id' => $tag->id,
            ]);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseHas('google_tag_managers', [
            'id' => $tag->id,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('tagmanagers.index'))
            ->post(route('tagmanagers.active'), [
                'hidden_id' => $tag->id,
            ]);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseHas('google_tag_managers', [
            'id' => $tag->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_destroy_tag_manager(): void
    {
        $tag = GoogleTagManager::create([
            'code' => 'GTM-DELETE',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('tagmanagers.index'))
            ->post(route('tagmanagers.destroy'), [
                'hidden_id' => $tag->id,
            ]);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseMissing('google_tag_managers', [
            'id' => $tag->id,
        ]);
    }

    public function test_admin_can_view_show_route(): void
    {
        $tag = GoogleTagManager::create([
            'title' => 'Test Show Container',
            'code' => 'GTM-SHOWTEST',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('tagmanagers.show', $tag->id));
        $response->assertStatus(200);
        $response->assertSee('GTM-SHOWTEST');
    }

    public function test_admin_can_store_tag_with_title_and_description(): void
    {
        $payload = [
            'title' => 'Primary Marketing GTM',
            'code' => 'GTM-PRIMARY1',
            'description' => 'Configured for GA4 events and pixel tracking',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);

        $response->assertRedirect(route('tagmanagers.index'));
        $this->assertDatabaseHas('google_tag_managers', [
            'title' => 'Primary Marketing GTM',
            'code' => 'GTM-PRIMARY1',
            'description' => 'Configured for GA4 events and pixel tracking',
            'status' => 1,
        ]);
    }

    public function test_store_validation_rejects_duplicate_code(): void
    {
        GoogleTagManager::create([
            'code' => 'GTM-DUPLICATE',
            'status' => 1,
        ]);

        $payload = [
            'code' => 'GTM-DUPLICATE',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_store_validation_rejects_malformed_code(): void
    {
        $payload = [
            'code' => 'invalid code with spaces and !@#$%',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_hidden_id_is_required_for_status_toggle(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.active'), [
            'hidden_id' => 999999, // non-existent
        ]);

        $response->assertSessionHasErrors(['hidden_id']);
    }

    public function test_cache_is_invalidated_when_tag_is_updated(): void
    {
        $service = app(\Modules\Setting\Application\Services\TagManagerService::class);

        $tag = GoogleTagManager::create([
            'code' => 'GTM-CACHE1',
            'status' => 1,
        ]);

        // Prime cache
        $cached = $service->getActiveCached();
        $this->assertTrue($cached->contains('code', 'GTM-CACHE1'));

        // Toggle inactive via controller
        $this->actingAs($this->adminUser)->post(route('tagmanagers.inactive'), [
            'hidden_id' => $tag->id,
        ]);

        // Verify cache was cleared and fresh query returns 0 active
        $fresh = $service->getActiveCached();
        $this->assertFalse($fresh->contains('code', 'GTM-CACHE1'));
    }

    public function test_admin_can_store_tag_manager_with_server_side_config_and_auto_seed_events(): void
    {
        $payload = [
            'code' => 'GTM-SERVER01',
            'title' => 'Main Server Container',
            'description' => 'sGTM container on Stape/Cloud Run',
            'is_server_side' => '1',
            'server_container_url' => 'https://gtm.mondolshopbd.com',
            'measurement_id' => 'G-ABC1234567',
            'api_secret' => 'secret_xyz987',
            'custom_loader_domain' => '1',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.store'), $payload);
        $response->assertRedirect(route('tagmanagers.index'));

        $this->assertDatabaseHas('google_tag_managers', [
            'code' => 'GTM-SERVER01',
            'is_server_side' => 1,
            'server_container_url' => 'https://gtm.mondolshopbd.com',
            'measurement_id' => 'G-ABC1234567',
            'custom_loader_domain' => 1,
        ]);

        $tag = GoogleTagManager::where('code', 'GTM-SERVER01')->firstOrFail();

        // Verify standard events were auto-seeded
        $this->assertCount(5, $tag->eventConfigs);
        $this->assertTrue($tag->isServerSide());
        $this->assertTrue($tag->isServerEventEnabled('purchase'));
        $this->assertTrue($tag->isWebEventEnabled('purchase'));
        $this->assertEquals('https://gtm.mondolshopbd.com', $tag->getEffectiveScriptBaseUrl());
    }

    public function test_admin_can_update_event_switches(): void
    {
        $tag = GoogleTagManager::create([
            'code' => 'GTM-SWITCHES',
            'status' => 1,
            'is_server_side' => 1,
            'server_container_url' => 'https://sgtm.example.com',
        ]);

        $service = app(\Modules\Setting\Application\Services\TagManagerService::class);
        $service->syncEventConfigs($tag->id, []); // seeds standard events

        // Disable server track for purchase, disable web track for page_view
        $updatePayload = [
            'id' => $tag->id,
            'code' => 'GTM-SWITCHES',
            'status' => '1',
            'is_server_side' => '1',
            'server_container_url' => 'https://sgtm.example.com',
            'events' => [
                'purchase' => [
                    'is_web_enabled' => '1',
                    'is_server_enabled' => '0',
                ],
                'page_view' => [
                    'is_web_enabled' => '0',
                    'is_server_enabled' => '1',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tagmanagers.update', $tag->id), $updatePayload);
        $response->assertRedirect(route('tagmanagers.index'));

        $tag->refresh();
        $this->assertTrue($tag->isWebEventEnabled('purchase'));
        $this->assertFalse($tag->isServerEventEnabled('purchase'));
        $this->assertFalse($tag->isWebEventEnabled('page_view'));
        $this->assertTrue($tag->isServerEventEnabled('page_view'));
    }

    public function test_server_gtm_service_dispatches_http_event(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://gtm.mondolshopbd.com/mp/collect*' => \Illuminate\Support\Facades\Http::response('OK', 204),
        ]);

        $tag = GoogleTagManager::create([
            'code' => 'GTM-DISPATCH',
            'status' => 1,
            'is_server_side' => 1,
            'server_container_url' => 'https://gtm.mondolshopbd.com',
            'measurement_id' => 'G-REAL123',
            'api_secret' => 'real_secret',
        ]);

        $service = app(\Modules\Setting\Application\Services\TagManagerService::class);
        $service->syncEventConfigs($tag->id, []);

        $sgtmService = app(\Modules\Setting\Application\Services\ServerGoogleTagManagerService::class);

        $result = $sgtmService->sendEvent(
            'view_item',
            ['currency' => 'BDT', 'value' => 1500],
            ['client_ip_address' => '127.0.0.1', 'email' => 'customer@test.com'],
            'evt_test_123'
        );

        $this->assertEquals(1, $result['dispatched_count']);

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            return str_starts_with($request->url(), 'https://gtm.mondolshopbd.com/mp/collect')
                && $request['client_id'] !== null;
        });
    }

    public function test_order_placed_dispatches_server_gtm_purchase_job(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $order = new \App\Models\Order();
        $order->id = 777;
        $order->invoice_id = 'INV-777';
        $order->amount = 2500;

        event(new \App\Events\OrderPlaced($order));

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendServerGtmEventJob::class, function ($job) {
            return $job->eventName === 'purchase'
                && $job->orderId === 777
                && $job->eventId === 'order_INV-777';
        });
    }
}