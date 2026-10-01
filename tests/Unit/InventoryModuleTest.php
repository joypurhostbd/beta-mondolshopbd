<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Inventory\Application\Actions\CommitStockAction;
use Modules\Inventory\Application\Actions\ReleaseStockAction;
use Modules\Inventory\Application\Actions\ReserveStockAction;
use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Modules\Inventory\Domain\Entities\InventoryLedgerEntity;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Exceptions\InsufficientStockException;
use Shared\Domain\ValueObjects\Quantity;
use Tests\TestCase;

class InventoryModuleTest extends TestCase
{
    use RefreshDatabase;

    private InventoryRepositoryInterface $repo;
    private InventoryModuleInterface $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(InventoryRepositoryInterface::class);
        $this->service = $this->app->make(InventoryModuleInterface::class);
    }

    public function test_inventory_ledger_entity_properties(): void
    {
        $ledger = new InventoryLedgerEntity(1, 100, Quantity::from(5), 'credit', 'PO-1234', 'Stock inbound');

        $this->assertEquals(1, $ledger->getId());
        $this->assertEquals(100, $ledger->getProductId());
        $this->assertEquals(5, $ledger->getQuantity()->getValue());
        $this->assertEquals('credit', $ledger->getType());
        $this->assertEquals('PO-1234', $ledger->getReference());
        $this->assertEquals('Stock inbound', $ledger->getNotes());
    }

    public function test_reserve_stock_and_release_lifecycle(): void
    {
        $product = Product::create([
            'name' => 'Premium Polo Shirt',
            'slug' => 'premium-polo-shirt',
            'product_code' => 'POLO-001',
            'category_id' => 1,
            'purchase_price' => 500,
            'old_price' => 800,
            'new_price' => 650,
            'stock' => 10,
            'status' => 1,
        ]);

        $reserveAction = new ReserveStockAction($this->repo);
        $reserved = $reserveAction->execute($product->id, 3);
        $this->assertTrue($reserved);

        $product->refresh();
        $this->assertEquals(7, $product->stock);

        $releaseAction = new ReleaseStockAction($this->repo);
        $released = $releaseAction->execute($product->id, 2);
        $this->assertTrue($released);

        $product->refresh();
        $this->assertEquals(9, $product->stock);

        $commitAction = new CommitStockAction($this->repo);
        $this->assertTrue($commitAction->execute($product->id, 1, 'ORD-999'));
    }

    public function test_reserve_stock_throws_insufficient_stock_exception(): void
    {
        $product = Product::create([
            'name' => 'Limited Edition Watch',
            'slug' => 'limited-edition-watch',
            'product_code' => 'WATCH-001',
            'category_id' => 1,
            'purchase_price' => 3500,
            'old_price' => 5000,
            'new_price' => 4500,
            'stock' => 2,
            'status' => 1,
        ]);

        $this->expectException(InsufficientStockException::class);

        // Attempting to reserve 5 when only 2 are available
        $this->service->reserveStock($product->id, 5);
    }
}