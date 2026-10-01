<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Application\DTOs\ProductDTO;
use Modules\Catalog\Domain\Entities\ProductEntity;
use Modules\Catalog\Domain\Events\ProductCreatedEvent;
use Modules\Catalog\Domain\Events\ProductStockUpdatedEvent;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;
use Shared\Domain\Enums\ProductStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Tests\TestCase;

class CatalogModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_entity_encapsulates_domain_rules(): void
    {
        $entity = new ProductEntity(
            id: 1,
            name: 'Premium Punjabi',
            slug: 'premium-punjabi',
            newPrice: Money::from(1200),
            oldPrice: Money::from(1500),
            stock: 10,
            status: ProductStatusEnum::ACTIVE,
            productCode: 'P0001'
        );

        $this->assertEquals(1, $entity->getId());
        $this->assertEquals('Premium Punjabi', $entity->getName());
        $this->assertTrue($entity->isAvailable());
        $this->assertTrue($entity->hasStock(5));
        $this->assertFalse($entity->hasStock(15));
    }

    public function test_product_dto_serialization(): void
    {
        $entity = new ProductEntity(
            id: 2,
            name: 'Polo Shirt',
            slug: 'polo-shirt',
            newPrice: Money::from(450),
            oldPrice: null,
            stock: 25,
            status: ProductStatusEnum::ACTIVE,
            productCode: 'P0002'
        );

        $dto = ProductDTO::fromEntity($entity);
        $array = $dto->toArray();

        $this->assertEquals(2, $array['id']);
        $this->assertEquals('Polo Shirt', $array['name']);
        $this->assertEquals(450.0, $array['price']);
        $this->assertEquals(25, $array['stock']);
    }

    public function test_domain_events_payload_integrity(): void
    {
        $created = new ProductCreatedEvent(10, 'Silk Saree');
        $this->assertEquals('catalog.product.created', $created->getEventName());
        $this->assertEquals(['product_id' => 10, 'product_name' => 'Silk Saree'], $created->toPayload());
        $this->assertNotEmpty($created->getOccurredAt());

        $stockUpdated = new ProductStockUpdatedEvent(10, 50, 45);
        $this->assertEquals('catalog.product.stock_updated', $stockUpdated->getEventName());
        $this->assertEquals(['product_id' => 10, 'old_stock' => 50, 'new_stock' => 45], $stockUpdated->toPayload());
        $this->assertNotEmpty($stockUpdated->getOccurredAt());
    }

    public function test_catalog_service_resolves_and_implements_module_contract(): void
    {
        $prod = new Product();
        $prod->name = 'Test Kurti';
        $prod->slug = 'test-kurti';
        $prod->category_id = 1;
        $prod->product_code = 'P999';
        $prod->purchase_price = 600;
        $prod->new_price = 850;
        $prod->old_price = 1000;
        $prod->stock = 30;
        $prod->status = 1;
        $prod->save();

        $service = $this->app->make(CatalogModuleInterface::class);

        $this->assertInstanceOf(CatalogModuleInterface::class, $service);

        $foundById = $service->findProductById($prod->id);
        $this->assertNotNull($foundById);
        $this->assertEquals('Test Kurti', $foundById['name']);

        $foundBySlug = $service->findProductBySlug('test-kurti');
        $this->assertNotNull($foundBySlug);
        $this->assertEquals('Test Kurti', $foundBySlug['name']);

        $this->assertTrue($service->checkStock($prod->id, 20));
        $this->assertFalse($service->checkStock($prod->id, 50));
    }
}