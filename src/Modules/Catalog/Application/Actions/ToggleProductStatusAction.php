<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Shared\Domain\Enums\ProductStatusEnum;
use Shared\Domain\Exceptions\EntityNotFoundException;

class ToggleProductStatusAction
{
    public function execute(int|string $productId, ProductStatusEnum $status): bool
    {
        $product = Product::find($productId);
        if (!$product) {
            throw EntityNotFoundException::forEntity('Product', $productId);
        }

        $product->status = $status->value;
        return $product->save();
    }
}