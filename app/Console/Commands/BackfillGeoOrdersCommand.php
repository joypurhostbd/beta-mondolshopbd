<?php

namespace App\Console\Commands;

use App\Models\Shipping;
use App\Services\GeoOrderAnalyticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillGeoOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:backfill-orders {--chunk=1000 : Chunk size} {--force : Force re-parse existing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse shipping addresses to backfill district and thana fields for orders';

    /**
     * Execute the console command.
     */
    public function handle(GeoOrderAnalyticsService $geoService): int
    {
        $chunkSize = (int) $this->option('chunk');
        $force = (bool) $this->option('force');

        $query = DB::table('shippings')->select('id', 'address', 'area', 'district', 'thana');
        if (!$force) {
            $query->whereNull('district')->orWhere('district', '');
        }

        $total = $query->count();
        $this->info("Found {$total} shipping records to process.");

        if ($total === 0) {
            $this->info('Nothing to process.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $matched = 0;

        DB::table('shippings')
            ->select('id', 'address', 'area')
            ->when(!$force, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('district')->orWhere('district', '');
                });
            })
            ->chunkById($chunkSize, function ($rows) use ($geoService, &$processed, &$matched, $bar) {
                foreach ($rows as $row) {
                    $geo = $geoService->resolveFromAddress($row->address, $row->area);
                    if (!empty($geo['district']) || !empty($geo['thana'])) {
                        DB::table('shippings')
                            ->where('id', $row->id)
                            ->update([
                                'district' => $geo['district'],
                                'thana' => $geo['thana'],
                            ]);
                        $matched++;
                    }
                    $processed++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();

        $percentage = $processed > 0 ? round(($matched / $processed) * 100, 1) : 0;
        $this->info("Successfully processed {$processed} records. Identified {$matched} locations ({$percentage}%).");

        return Command::SUCCESS;
    }
}
