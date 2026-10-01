<?php

namespace Modules\Catalog\Application\Actions;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\Application\DTOs\ProductDTO;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Domain\Events\ProductCreatedEvent;
use Illuminate\Support\Str;

class CreateProductAction
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(array $data, array $colorIds = [], array $sizeIds = []): ProductDTO
    {
        // Remove empty/null values and keep only valid numeric IDs.
        $colorIds = array_values(array_filter($colorIds, fn ($id) => is_numeric($id) && (int) $id > 0));
        $sizeIds  = array_values(array_filter($sizeIds, fn ($id) => is_numeric($id) && (int) $id > 0));

        return DB::transaction(function () use ($data, $colorIds, $sizeIds) {
            $last = Product::orderBy('id', 'desc')->select('id')->first();
            $lastId = $last ? $last->id + 1 : 1;

            $slug = strtolower(preg_replace('/[\/\s]+/', '-', ($data['name'] ?? 'product') . '-' . $lastId));
            $productCode = 'P' . str_pad((string) $lastId, 4, '0', STR_PAD_LEFT);

            $payload = array_merge($data, [
                'slug' => $slug,
                'product_code' => $data['product_code'] ?? $productCode,
                'status' => $data['status'] ?? 1,
            ]);

            $product = Product::create($payload);

            if (!empty($colorIds)) {
                $product->colors()->attach($colorIds);
            }

            if (!empty($sizeIds)) {
                $product->sizes()->attach($sizeIds);
            }

            event(new ProductCreatedEvent($product->id, $product->name));

            $entity = $this->productRepository->findById($product->id);
            return ProductDTO::fromEntity($entity);
        });
    }
}