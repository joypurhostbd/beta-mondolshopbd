<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Shared\Domain\Exceptions\DomainException;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Domain\Exceptions\InvalidStateTransitionException;
use Shared\Domain\Exceptions\InsufficientStockException;
use Shared\Domain\Exceptions\PaymentFailedException;
use Shared\Domain\Exceptions\UnauthorizedModuleAccessException;
use Shared\Domain\Exceptions\OrderAlreadyProcessedException;

class SharedDomainExceptionsTest extends TestCase
{
    public function test_entity_not_found_exception_carries_context(): void
    {
        $e = EntityNotFoundException::forEntity('Order', 101);
        $this->assertEquals(404, $e->getCode());
        $this->assertEquals('Order', $e->getContext()['entity']);
        $this->assertEquals(101, $e->getContext()['id']);
        $this->assertStringContainsString('101', $e->getMessage());
    }

    public function test_invalid_state_transition_exception(): void
    {
        $e = InvalidStateTransitionException::forTransition('Order', 'delivered', 'cancelled');
        $this->assertEquals(422, $e->getCode());
        $this->assertEquals('delivered', $e->getContext()['from_state']);
        $this->assertEquals('cancelled', $e->getContext()['to_state']);
    }

    public function test_insufficient_stock_exception(): void
    {
        $e = InsufficientStockException::forProduct(55, 10, 2);
        $this->assertEquals(409, $e->getCode());
        $this->assertEquals(10, $e->getContext()['requested']);
        $this->assertEquals(2, $e->getContext()['available']);
    }

    public function test_payment_failed_exception(): void
    {
        $e = PaymentFailedException::withReason('bkash', 'Insufficient customer balance', 'TRX9988');
        $this->assertEquals(402, $e->getCode());
        $this->assertEquals('bkash', $e->getContext()['gateway']);
        $this->assertEquals('TRX9988', $e->getContext()['transaction_id']);
    }

    public function test_unauthorized_and_order_already_processed_exceptions(): void
    {
        $e1 = UnauthorizedModuleAccessException::forModule('AdminReports', 'user_5');
        $this->assertEquals(403, $e1->getCode());

        $e2 = OrderAlreadyProcessedException::forOrder(500, 'completed');
        $this->assertEquals(409, $e2->getCode());
    }
}