<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Application\DTOs\ProductDTO;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class UpdateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(
        int|string $productId,
        array $data,
        ?array $colorIds = null,
        ?array $sizeIds = null
    ): ProductDTO {
        /*
         * IMPORTANT:
         * null / empty / invalid values must NEVER reach the pivot tables.
         *
         * [] means:
         *   - remove all colors
         *   - remove all sizes
         *
         * Valid IDs mean:
         *   - sync only selected colors/sizes
         */

        $colorIds = array_values(array_unique(array_map(
            'intval',
            array_filter(
                $colorIds ?? [],
                fn ($id) => is_numeric($id) && (int) $id > 0
            )
        )));

        $sizeIds = array_values(array_unique(array_map(
            'intval',
            array_filter(
                $sizeIds ?? [],
                fn ($id) => is_numeric($id) && (int) $id > 0
            )
        )));

        return DB::transaction(function () use (
            $productId,
            $data,
            $colorIds,
            $sizeIds
        ) {
            $product = Product::find($productId);

            if (!$product) {
                throw EntityNotFoundException::forEntity(
                    'Product',
                    $productId
                );
            }

            $product->update($data);

            /*
             * Always sync.
             *
             * Selected colors  => keep selected colors
             * No colors        => remove all colors
             */
            $product->colors()->sync($colorIds);

            /*
             * Selected sizes   => keep selected sizes
             * No sizes         => remove all sizes
             */
            $product->sizes()->sync($sizeIds);

            $entity = $this->productRepository->findById($product->id);

            return ProductDTO::fromEntity($entity);
        });
    }
}
