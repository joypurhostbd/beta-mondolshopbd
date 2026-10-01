<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\SystemUpdateSetting;
use App\Models\User;
use App\Services\SystemUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSystemUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralSetting::create([
            'name' => 'MondolShop',
            'white_logo' => 'test-white.png',
            'dark_logo' => 'test-dark.png',
            'status' => 1,
        ]);

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

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.system.update'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_system_update_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.system.update'));

        $response->assertStatus(200);
        $response->assertViewIs('backEnd.system.update');
        $response->assertViewHas(['info', 'recentCommits', 'deploymentLogs', 'systemMetrics', 'remoteStatus']);
        $response->assertSee('Git & Live Server Synchronization', false);
        $response->assertSee('Server SSH Deploy Key (Ed25519)');
        $response->assertSee('Git Fetch & Pull', false);
    }

    public function test_admin_can_save_system_update_settings(): void
    {
        $payload = [
            'protocol' => 'ssh',
            'repository_url' => 'git@github.com:maccpro/mondolshopbd.git',
            'branch' => 'main',
            'auto_run_composer' => '1',
            'auto_run_migrations' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.system.update.save'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('system_update_settings', [
            'protocol' => 'ssh',
            'repository_url' => 'git@github.com:maccpro/mondolshopbd.git',
            'branch' => 'main',
            'auto_run_composer' => 1,
            'auto_run_migrations' => 1,
        ]);
    }

    public function test_admin_can_generate_deploy_key_via_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.system.update.generate-key'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'public_key',
            'private_key_path',
        ]);
        $this->assertTrue($response->json('status'));
        $this->assertNotEmpty($response->json('public_key'));

        // Verify stored in DB
        $settings = SystemUpdateSetting::getSettings();
        $this->assertEquals($response->json('public_key'), $settings->ssh_public_key);
    }

    public function test_system_update_service_reports_telemetry(): void
    {
        $service = app(SystemUpdateService::class);
        $telemetry = $service->getGitInfo();

        $this->assertArrayHasKey('current_branch', $telemetry);
        $this->assertArrayHasKey('head_commit', $telemetry);
        $this->assertArrayHasKey('short_commit', $telemetry);
        $this->assertArrayHasKey('target_server', $telemetry);
        $this->assertArrayHasKey('webhook_url', $telemetry);
        $this->assertArrayHasKey('webhook_secret', $telemetry);
    }

    public function test_webhook_deploy_rejects_unauthorized_requests(): void
    {
        $response = $this->postJson(route('api.system.webhook-deploy'), [], [
            'X-Deploy-Secret' => 'invalid-secret',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Unauthorized: Invalid or missing deployment secret.',
        ]);
    }

    public function test_webhook_deploy_accepts_valid_secret(): void
    {
        $settings = SystemUpdateSetting::getSettings();
        $this->assertNotEmpty($settings->webhook_secret);

        // Mock the service so we don't run real git commands during unit test
        $mockService = \Mockery::mock(SystemUpdateService::class);
        $mockService->shouldReceive('executePipeline')
            ->once()
            ->andReturnUsing(function ($callback) {
                $callback('step_start', 1, 'Mocked Git pull');
                $callback('step_success', 1, 'Mocked success');
                $callback('pipeline_complete', 5, 'Done');
            });

        $this->app->instance(SystemUpdateService::class, $mockService);

        $response = $this->postJson(route('api.system.webhook-deploy'), [], [
            'X-Deploy-Secret' => $settings->webhook_secret,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Automated deployment completed successfully.',
        ]);
    }

    public function test_init_git_endpoint(): void
    {
        $mockService = \Mockery::mock(SystemUpdateService::class);
        $mockService->shouldReceive('ensureGitInitialized')
            ->once()
            ->andReturn(['status' => true, 'message' => 'Git repository verified.']);

        $this->app->instance(SystemUpdateService::class, $mockService);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.system.update.init-git'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Git repository verified.',
        ]);
    }

    public function test_admin_can_view_deployment_log_modal(): void
    {
        $log = \App\Models\SystemDeploymentLog::create([
            'status' => 'success',
            'commit_hash' => 'abcdef1234567890',
            'commit_message' => 'Test release log',
            'author' => 'Tester',
            'trigger_type' => 'manual_ui',
            'duration_seconds' => 12,
            'log_output' => 'Step 1 success\nStep 2 success',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.system.update.log', $log->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'status' => 'success',
                'commit_hash' => 'abcdef1234567890',
            ],
        ]);
    }

    public function test_admin_can_check_remote_updates_via_ajax(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.system.update.check-updates'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'remote_status' => [
                'is_clean',
                'behind_count',
                'status_text',
                'detail_text',
            ],
        ]);
    }
}
