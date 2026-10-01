<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\IpBlock;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shipping\Application\Services\FraudCheckService;
use Modules\Shipping\Infrastructure\Fraud\InternalFraudChecker;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class FraudCheckServiceTest extends TestCase
{
    use RefreshDatabase;

    private FraudCheckService $fraudService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fraudService = $this->app->make(FraudCheckService::class);

        IpBlock::query()->delete();
        Customer::query()->delete();
        Order::query()->delete();
        Shipping::query()->delete();
    }

    public function test_clean_customer_returns_low_risk_score(): void
    {
        $score = $this->fraudService->evaluate('01711223344');

        $this->assertEquals('low', $score->riskLevel);
        $this->assertFalse($score->isBlacklisted);
        $this->assertEquals(100.0, $score->successRatio);
    }

    public function test_blocked_customer_returns_high_risk(): void
    {
        IpBlock::create([
            'ip_no' => '01799887766',
            'reason' => 'Fraud courier return spam',
        ]);

        $score = $this->fraudService->evaluate('01799887766');

        $this->assertEquals('high', $score->riskLevel);
        $this->assertTrue($score->isBlacklisted);
    }

    public function test_cancelled_order_history_elevates_risk(): void
    {
        $customer = Customer::create([
            'name' => 'Bad Customer',
            'slug' => 'bad-customer',
            'phone' => '01811223344',
            'password' => bcrypt('secret123'),
            'status' => 'active'
        ]);

        // 3 Cancelled orders
        for ($i = 1; $i <= 3; $i++) {
            Order::create([
                'customer_id' => $customer->id,
                'shipping_id' => 1,
                'subtotal' => 1000.0,
                'discount' => 0.0,
                'shipping_charge' => 60.0,
                'amount' => 1060.0,
                'order_status' => 5,
            ]);
        }

        $checker = new InternalFraudChecker();
        $score = $checker->checkCustomer(PhoneNumber::fromString('01811223344'));

        $this->assertEquals(3, $score->totalOrders);
        $this->assertEquals(3, $score->cancelledOrders);
        $this->assertEquals('high', $score->riskLevel);
    }
}