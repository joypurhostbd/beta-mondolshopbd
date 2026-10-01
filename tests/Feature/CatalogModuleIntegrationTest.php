<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Catalog\Application\Actions\BulkUpdateProductPricesAction;
use Modules\Catalog\Application\Actions\CreateBrandAction;
use Modules\Catalog\Application\Actions\CreateCategoryAction;
use Modules\Catalog\Application\Actions\CreateColorAction;
use Modules\Catalog\Application\Actions\CreateProductAction;
use Modules\Catalog\Application\Actions\CreateSizeAction;
use Modules\Catalog\Application\Actions\DeleteProductAction;
use Modules\Catalog\Application\Actions\GetCatalogAttributesAction;
use Modules\Catalog\Application\Actions\GetCategoryTreeAction;
use Modules\Catalog\Application\Actions\ToggleProductStatusAction;
use Modules\Catalog\Application\Actions\UpdateProductStockAction;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Domain\Events\ProductCreatedEvent;
use Modules\Catalog\Domain\Events\ProductStockUpdatedEvent;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;
use Shared\Domain\Enums\ProductStatusEnum;
use Tests\TestCase;

class CatalogModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_catalog_module_end_to_end_integration(): void
    {
        Event::fake([ProductCreatedEvent::class, ProductStockUpdatedEvent::class]);

        $productRepo = $this->app->make(ProductRepositoryInterface::class);
        $categoryRepo = $this->app->make(CategoryRepositoryInterface::class);
        $attributeRepo = $this->app->make(AttributeRepositoryInterface::class);
        $catalogService = $this->app->make(CatalogModuleInterface::class);

        // 1. Create Category Hierarchy
        $createCatAction = new CreateCategoryAction($categoryRepo);
        $catDto = $createCatAction->execute(['name' => 'Fashion', 'status' => 1]);

        // 2. Create Attributes (Brand, Size, Color)
        $brandAction = new CreateBrandAction($attributeRepo);
        $brandDto = $brandAction->execute(['name' => 'Richman', 'name_bn' => 'রিচম্যান']);

        $sizeAction = new CreateSizeAction($attributeRepo);
        $sizeDto = $sizeAction->execute('XL');

        $colorAction = new CreateColorAction($attributeRepo);
        $colorDto = $colorAction->execute('Navy', '#000080');

        // 3. Verify Batch Attribute Retrieval
        $getAttAction = new GetCatalogAttributesAction($attributeRepo);
        $attributes = $getAttAction->execute();
        $this->assertCount(1, $attributes['brands']);
        $this->assertCount(1, $attributes['sizes']);
        $this->assertCount(1, $attributes['colors']);

        // 4. Create Product
        $createProductAction = new CreateProductAction($productRepo);
        $productDto = $createProductAction->execute(
            data: [
                'name' => 'Executive Blazer',
                'category_id' => $catDto->id,
                'brand_id' => $brandDto->id,
                'purchase_price' => 2500,
                'new_price' => 4500,
                'old_price' => 5000,
                'stock' => 20,
            ],
            colorIds: [$colorDto->id],
            sizeIds: [$sizeDto->id]
        );

        Event::assertDispatched(ProductCreatedEvent::class);
        $this->assertEquals('Executive Blazer', $productDto->name);
        $this->assertEquals(4500.0, $productDto->price);

        // 5. Query via Public CatalogModuleInterface
        $found = $catalogService->findProductById($productDto->id);
        $this->assertNotNull($found);
        $this->assertEquals('Executive Blazer', $found['name']);
        $this->assertEquals(4500.0, $found['price']);
        $this->assertEquals(20, $found['stock']);

        $hasStock = $catalogService->checkStock($productDto->id, 15);
        $this->assertTrue($hasStock);

        $hasExcessStock = $catalogService->checkStock($productDto->id, 50);
        $this->assertFalse($hasExcessStock);

        // 6. Update Product Stock
        $updateStockAction = new UpdateProductStockAction($productRepo);
        $stockUpdated = $updateStockAction->execute($productDto->id, 35);
        $this->assertTrue($stockUpdated);
        Event::assertDispatched(ProductStockUpdatedEvent::class);

        $foundAfterStock = $catalogService->findProductById($productDto->id);
        $this->assertEquals(35, $foundAfterStock['stock']);

        // 7. Bulk Update Prices
        $bulkPriceAction = new BulkUpdateProductPricesAction();
        $bulkSuccess = $bulkPriceAction->execute(
            ids: [$productDto->id],
            oldPrices: [5500],
            newPrices: [4200],
            stocks: [30]
        );
        $this->assertTrue($bulkSuccess);

        $foundAfterBulk = $catalogService->findProductById($productDto->id);
        $this->assertEquals(4200.0, $foundAfterBulk['price']);
        $this->assertEquals(30, $foundAfterBulk['stock']);

        // 8. Toggle Status
        $toggleAction = new ToggleProductStatusAction();
        $toggleAction->execute($productDto->id, ProductStatusEnum::INACTIVE);
        $this->assertEquals(0, Product::find($productDto->id)->status);

        // 9. Delete Product & Verify Cleanup
        $deleteAction = new DeleteProductAction($productRepo);
        $deleted = $deleteAction->execute($productDto->id);
        $this->assertTrue($deleted);
        $this->assertNull(Product::find($productDto->id));
    }
}