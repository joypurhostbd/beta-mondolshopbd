<?php

namespace Tests\Feature;

use Tests\TestCase;

class LogContextMiddlewareTest extends TestCase
{
    public function test_incoming_requests_receive_x_request_id_header(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-Request-ID'));
        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
}