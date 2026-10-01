<?php

namespace Tests\Feature;

use App\Models\OutboxMessage;
use App\Services\OutboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionalOutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_outbox_service_records_pending_message(): void
    {
        $service = app(OutboxService::class);

        $message = $service->record('order.created', [
            'order_id' => 9999,
            'amount' => 1500,
        ]);

        $this->assertDatabaseHas('outbox_messages', [
            'id' => $message->id,
            'event_name' => 'order.created',
            'status' => 'pending',
        ]);
    }

    public function test_outbox_publish_command_processes_pending_messages(): void
    {
        $service = app(OutboxService::class);

        $message = $service->record('order.created', [
            'order_id' => 8888,
            'amount' => 2500,
        ]);

        $this->artisan('outbox:publish', ['--limit' => 10])
            ->assertSuccessful();

        $this->assertDatabaseHas('outbox_messages', [
            'id' => $message->id,
            'status' => 'processed',
        ]);
    }
}