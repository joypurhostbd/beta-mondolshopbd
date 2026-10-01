<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class BulkUpdateProductPricesAction
{
    public function execute(array $ids, array $oldPrices, array $newPrices, array $stocks): bool
    {
        return DB::transaction(function () use ($ids, $oldPrices, $newPrices, $stocks) {
            foreach ($ids as $key => $id) {
                $product = Product::find($id);
                if ($product) {
                    $product->update([
                        'old_price' => $oldPrices[$key] ?? $product->old_price,
                        'new_price' => $newPrices[$key] ?? $product->new_price,
                        'stock' => $stocks[$key] ?? $product->stock,
                    ]);
                }
            }
            return true;
        });
    }
}