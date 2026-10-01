<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\ProductStatusEnum;
use Shared\Domain\Enums\CustomerStatusEnum;
use Shared\Domain\Enums\AuditActionEnum;
use Shared\Domain\Enums\OutboxStatusEnum;

class DomainEnumsTest extends TestCase
{
    public function test_order_status_enum_values_and_labels(): void
    {
        $this->assertEquals(1, OrderStatusEnum::PENDING->value);
        $this->assertEquals('Pending', OrderStatusEnum::PENDING->label());
        $this->assertEquals('warning', OrderStatusEnum::PENDING->badgeColor());
        $this->assertTrue(OrderStatusEnum::PENDING->isCancellable());
        $this->assertFalse(OrderStatusEnum::COMPLETED->isCancellable());
    }

    public function test_payment_status_enum(): void
    {
        $this->assertEquals('paid', PaymentStatusEnum::PAID->value);
        $this->assertEquals('Paid', PaymentStatusEnum::PAID->label());
        $this->assertTrue(PaymentStatusEnum::PAID->isSuccessful());
        $this->assertFalse(PaymentStatusEnum::PENDING->isSuccessful());
    }

    public function test_payment_method_enum(): void
    {
        $this->assertEquals('cod', PaymentMethodEnum::COD->value);
        $this->assertFalse(PaymentMethodEnum::COD->isOnline());
        $this->assertTrue(PaymentMethodEnum::BKASH->isOnline());
        $this->assertTrue(PaymentMethodEnum::SHURJOPAY->isOnline());
    }

    public function test_product_and_customer_status_enums(): void
    {
        $this->assertEquals(1, ProductStatusEnum::ACTIVE->value);
        $this->assertEquals('Active', ProductStatusEnum::ACTIVE->label());
        $this->assertEquals('active', CustomerStatusEnum::ACTIVE->value);
        $this->assertEquals('banned', CustomerStatusEnum::BANNED->value);
    }

    public function test_audit_action_and_outbox_status_enums(): void
    {
        $this->assertEquals('create', AuditActionEnum::CREATE->value);
        $this->assertEquals('pending', OutboxStatusEnum::PENDING->value);
        $this->assertEquals('processed', OutboxStatusEnum::PROCESSED->value);
    }
}