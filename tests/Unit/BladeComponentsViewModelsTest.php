<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Infrastructure\Views\Components\MoneyDisplay;
use Shared\Infrastructure\Views\Components\OrderStatusBadge;
use Shared\Infrastructure\Views\Components\PriceBadge;
use Shared\Infrastructure\Views\ViewModels\OrderSummaryViewModel;
use Shared\Infrastructure\Views\ViewModels\ProductCardViewModel;

class BladeComponentsViewModelsTest extends TestCase
{
    public function test_product_card_view_model_computes_discount_and_stock(): void
    {
        $vm = new ProductCardViewModel(
            id: 101,
            name: 'Men Slim Fit Shirt',
            slug: 'men-slim-fit-shirt',
            price: Money::from(1200.0),
            oldPrice: Money::from(1500.0),
            imageUrl: 'uploads/shirt.jpg',
            stock: 15,
            categoryName: 'Clothing'
        );

        $this->assertTrue($vm->isDiscounted());
        $this->assertEquals(20, $vm->getDiscountPercentage());
        $this->assertTrue($vm->isInStock());
        $this->assertEquals('৳1,200.00', $vm->getFormattedPrice());
        $this->assertEquals('৳1,500.00', $vm->getFormattedOldPrice());
    }

    public function test_order_summary_view_model_formats_amounts_and_badge_classes(): void
    {
        $orderVm = new OrderSummaryViewModel(
            id: 501,
            invoiceId: 'INV-501',
            customerName: 'Sizar Babu',
            customerPhone: '01711223344',
            status: OrderStatusEnum::COMPLETED,
            subtotal: Money::from(3000.0),
            shippingCharge: Money::from(60.0),
            discount: Money::from(200.0),
            totalAmount: Money::from(2860.0),
            items: []
        );

        $this->assertEquals('Completed', $orderVm->getStatusLabel());
        $this->assertStringContainsString('badge-success', $orderVm->getStatusBadgeClass());
        $this->assertEquals('৳3,000.00', $orderVm->getFormattedSubtotal());
        $this->assertEquals('৳60.00', $orderVm->getFormattedShipping());
        $this->assertEquals('৳200.00', $orderVm->getFormattedDiscount());
        $this->assertEquals('৳2,860.00', $orderVm->getFormattedTotal());
    }

    public function test_price_badge_component_discount_logic(): void
    {
        $comp = new PriceBadge(
            price: Money::from(800.0),
            oldPrice: Money::from(1000.0)
        );

        $this->assertTrue($comp->isDiscounted());
        $this->assertEquals(20, $comp->discountPercentage());
    }

    public function test_order_status_badge_component_resolution(): void
    {
        $pendingBadge = new OrderStatusBadge(1);
        $this->assertEquals('Pending', $pendingBadge->label());
        $this->assertStringContainsString('bg-yellow-100', $pendingBadge->badgeClass());

        $deliveredBadge = new OrderStatusBadge(OrderStatusEnum::COMPLETED);
        $this->assertEquals('Completed', $deliveredBadge->label());
        $this->assertStringContainsString('bg-green-100', $deliveredBadge->badgeClass());
    }

    public function test_money_display_component_formatting(): void
    {
        $moneyComp = new MoneyDisplay(2500.50);
        $this->assertEquals('৳2,500.50', $moneyComp->formatted());

        $voComp = new MoneyDisplay(Money::from(1000.0));
        $this->assertEquals('৳1,000.00', $voComp->formatted());
    }
}