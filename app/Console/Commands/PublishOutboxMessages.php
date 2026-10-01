<?php

namespace App\Console\Commands;

use App\Services\OutboxService;
use Illuminate\Console\Command;

class PublishOutboxMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbox:publish {--limit=50 : The number of messages to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish pending domain outbox messages to event handlers';

    /**
     * Execute the console command.
     *
     * @param OutboxService $outboxService
     * @return int
     */
    public function handle(OutboxService $outboxService): int
    {
        $limit = (int) $this->option('limit');
        $this->info("Processing up to {$limit} pending outbox messages...");

        $published = $outboxService->publishPending($limit);

        $this->info("Successfully published {$published} outbox messages.");

        return Command::SUCCESS;
    }
}