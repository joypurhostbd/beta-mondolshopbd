<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Productimage;
use DB;

class SetDefaultFeaturedImages extends Command
{
    protected $signature = 'product:set-featured-defaults';
    protected $description = 'Set the first gallery image as featured for all products that have no featured image';

    public function handle(): int
    {
        // Find products that have images but no featured image
        $productsWithoutFeatured = Product::whereHas('images')
            ->whereDoesntHave('images', function ($q) {
                $q->where('is_featured', true);
            })
            ->with('images')
            ->get();

        if ($productsWithoutFeatured->isEmpty()) {
            $this->info('All products already have a featured image. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$productsWithoutFeatured->count()} products without featured image.");

        $bar = $this->output->createProgressBar($productsWithoutFeatured->count());
        $bar->start();

        $updated = 0;

        foreach ($productsWithoutFeatured as $product) {
            $firstImage = $product->images->sortBy('id')->first();
            if ($firstImage) {
                Productimage::where('id', $firstImage->id)->update(['is_featured' => true]);
                $updated++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! Set featured image for {$updated} products.");

        return self::SUCCESS;
    }
}
