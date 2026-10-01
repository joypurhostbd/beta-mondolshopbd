<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_liveness_endpoint_returns_ok(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_live_alias_endpoint_returns_ok(): void
    {
        $response = $this->get('/health/live');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }

    public function test_readiness_endpoint_returns_healthy_with_checks(): void
    {
        $response = $this->get('/health/ready');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'checks' => [
                    'database',
                    'cache',
                    'storage',
                ],
                'timestamp',
            ])
            ->assertJson([
                'status' => 'healthy',
                'checks' => [
                    'database' => 'ok',
                    'cache' => 'ok',
                    'storage' => 'ok',
                ],
            ]);
    }
}