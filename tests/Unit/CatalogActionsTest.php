<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Catalog\Application\Actions\BulkUpdateProductPricesAction;
use Modules\Catalog\Application\Actions\CreateProductAction;
use Modules\Catalog\Application\Actions\DeleteProductAction;
use Modules\Catalog\Application\Actions\ToggleProductStatusAction;
use Modules\Catalog\Application\Actions\UpdateProductAction;
use Modules\Catalog\Application\Actions\UpdateProductStockAction;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Domain\Events\ProductCreatedEvent;
use Modules\Catalog\Domain\Events\ProductStockUpdatedEvent;
use Shared\Domain\Enums\ProductStatusEnum;
use Tests\TestCase;

class CatalogActionsTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_create_product_action_creates_and_fires_event(): void
    {
        Event::fake([ProductCreatedEvent::class]);

        $action = new CreateProductAction($this->repo);
        $dto = $action->execute([
            'name' => 'Royal Sherwani',
            'category_id' => 1,
            'purchase_price' => 2000,
            'new_price' => 3500,
            'old_price' => 4000,
            'stock' => 15,
        ]);

        $this->assertEquals('Royal Sherwani', $dto->name);
        $this->assertEquals(3500.0, $dto->price);
        $this->assertDatabaseHas('products', ['name' => 'Royal Sherwani']);

        Event::assertDispatched(ProductCreatedEvent::class);
    }

    public function test_update_product_action_modifies_attributes(): void
    {
        $prod = Product::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'category_id' => 1,
            'product_code' => 'P111',
            'purchase_price' => 500,
            'new_price' => 800,
            'stock' => 10,
            'status' => 1,
        ]);

        $action = new UpdateProductAction($this->repo);
        $dto = $action->execute($prod->id, ['name' => 'Updated Name', 'new_price' => 950]);

        $this->assertEquals('Updated Name', $dto->name);
        $this->assertEquals(950.0, $dto->price);
    }

    public function test_update_product_stock_action_updates_and_fires_event(): void
    {
        Event::fake([ProductStockUpdatedEvent::class]);

        $prod = Product::create([
            'name' => 'Jeans Pant',
            'slug' => 'jeans-pant',
            'category_id' => 1,
            'product_code' => 'P222',
            'purchase_price' => 400,
            'new_price' => 700,
            'stock' => 10,
            'status' => 1,
        ]);

        $action = new UpdateProductStockAction($this->repo);
        $result = $action->execute($prod->id, 25);

        $this->assertTrue($result);
        $this->assertEquals(25, $prod->fresh()->stock);

        Event::assertDispatched(ProductStockUpdatedEvent::class);
    }

    public function test_bulk_update_product_prices_action(): void
    {
        $p1 = Product::create([
            'name' => 'Item 1',
            'slug' => 'item-1',
            'category_id' => 1,
            'product_code' => 'P301',
            'purchase_price' => 100,
            'new_price' => 200,
            'old_price' => 250,
            'stock' => 5,
            'status' => 1,
        ]);

        $action = new BulkUpdateProductPricesAction();
        $res = $action->execute([$p1->id], [300], [220], [12]);

        $this->assertTrue($res);
        $this->assertEquals(220.0, (float) $p1->fresh()->new_price);
        $this->assertEquals(12, $p1->fresh()->stock);
    }

    public function test_toggle_product_status_action(): void
    {
        $prod = Product::create([
            'name' => 'Item Active',
            'slug' => 'item-active',
            'category_id' => 1,
            'product_code' => 'P401',
            'purchase_price' => 100,
            'new_price' => 200,
            'stock' => 5,
            'status' => 1,
        ]);

        $action = new ToggleProductStatusAction();
        $action->execute($prod->id, ProductStatusEnum::INACTIVE);
        $this->assertEquals(0, $prod->fresh()->status);

        $action->execute($prod->id, ProductStatusEnum::ACTIVE);
        $this->assertEquals(1, $prod->fresh()->status);
    }

    public function test_delete_product_action_removes_entity(): void
    {
        $prod = Product::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'category_id' => 1,
            'product_code' => 'P501',
            'purchase_price' => 100,
            'new_price' => 200,
            'stock' => 5,
            'status' => 1,
        ]);

        $action = new DeleteProductAction($this->repo);
        $res = $action->execute($prod->id);

        $this->assertTrue($res);
        $this->assertNull(Product::find($prod->id));
    }
}