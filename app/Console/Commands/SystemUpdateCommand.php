<?php

namespace App\Console\Commands;

use App\Services\SystemUpdateService;
use Illuminate\Console\Command;

class SystemUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the 5-step automated Git-backed system update pipeline';

    /**
     * Execute the console command.
     */
    public function handle(SystemUpdateService $service): int
    {
        $this->info("==================================================");
        $this->info("   🚀 MondolShopBD / MACCPRO Live System Update   ");
        $this->info("==================================================");

        $exitCode = Command::SUCCESS;

        $service->executePipeline(function (string $event, int $step, string $message, array $extra = []) use (&$exitCode) {
            match ($event) {
                'step_start' => $this->line("\n<fg=cyan;options=bold>[STEP 0{$step}]</> {$message}"),
                'output' => $this->line("  {$message}"),
                'step_success' => $this->info("  ✔ {$message}"),
                'pipeline_complete' => $this->info("\n🎉 {$message}"),
                'pipeline_error' => function () use ($message, &$exitCode) {
                    $this->error("\n❌ Pipeline Error: {$message}");
                    $exitCode = Command::FAILURE;
                },
                default => null,
            };
        });

        return $exitCode;
    }
}
