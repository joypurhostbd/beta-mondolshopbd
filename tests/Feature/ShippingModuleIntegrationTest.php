<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\IpBlock;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\ShippingCharge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Modules\Shipping\Application\Services\CourierGatewayManager;
use Modules\Shipping\Application\Services\FraudCheckService;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class ShippingModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private ShippingModuleInterface $shippingModule;
    private CourierGatewayManager $gatewayManager;
    private FraudCheckService $fraudService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shippingModule = $this->app->make(ShippingModuleInterface::class);
        $this->gatewayManager = $this->app->make(CourierGatewayManager::class);
        $this->fraudService = $this->app->make(FraudCheckService::class);

        ShippingCharge::query()->delete();
        Order::query()->delete();
        Customer::query()->delete();
        IpBlock::query()->delete();
        Shipping::query()->delete();
    }

    public function test_shipping_charge_calculation_with_configured_rates(): void
    {
        // 1. Configured custom district rates in database
        $dhaka = ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 60.0,
            'status' => 1,
        ]);

        $outside = ShippingCharge::create([
            'name' => 'Outside Dhaka',
            'amount' => 120.0,
            'status' => 1,
        ]);

        $chittagong = ShippingCharge::create([
            'name' => 'Chittagong City',
            'amount' => 80.0,
            'status' => 1,
        ]);

        // Configured Chittagong charge
        $ctgCharge = $this->shippingModule->calculateShippingCharge($chittagong->id, Money::from(3000.0));
        $this->assertEquals(80.0, $ctgCharge->getAmount());

        // Configured Dhaka charge
        $dhakaCharge = $this->shippingModule->calculateShippingCharge($dhaka->id, Money::from(1500.0));
        $this->assertEquals(60.0, $dhakaCharge->getAmount());

        // Configured Outside Dhaka charge
        $outsideCharge = $this->shippingModule->calculateShippingCharge($outside->id, Money::from(1500.0));
        $this->assertEquals(120.0, $outsideCharge->getAmount());

        // Unconfigured district fallback
        $fallbackCharge = $this->shippingModule->calculateShippingCharge(9999, Money::from(1500.0));
        $this->assertEquals(120.0, $fallbackCharge->getAmount());
    }

    public function test_steadfast_shipment_creation_and_order_sync(): void
    {
        $customer = Customer::create([
            'name' => 'Sizar Babu',
            'slug' => 'sizar-babu-01711223344',
            'phone' => '01711223344',
            'password' => bcrypt('password'),
            'status' => 1,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'shipping_id' => 1,
            'subtotal' => 4500.0,
            'discount' => 0.0,
            'shipping_charge' => 60.0,
            'amount' => 4560.0,
            'order_status' => 2, // Processing
        ]);

        $result = $this->shippingModule->createShipment($order->id, [
            'courier' => 'steadfast',
            'invoice_id' => 'INV-' . $order->id,
            'name' => 'Sizar Babu',
            'phone' => '01711223344',
            'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
            'amount' => 4560.0,
            'district' => 'Dhaka',
            'weight' => 1.2,
            'note' => 'Deliver before 5 PM',
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['consignment_id']);
        $this->assertNotEmpty($result['tracking_code']);
        $this->assertEquals('steadfast', $result['courier']);

        $order->refresh();
        $this->assertEquals('steadfast', $order->courier_name);
        $this->assertEquals($result['tracking_code'], $order->courier_status);
    }

    public function test_pathao_shipment_creation(): void
    {
        $result = $this->shippingModule->createShipment(9002, [
            'courier' => 'pathao',
            'invoice_id' => 'INV-9002',
            'name' => 'Tanvir Ahmed',
            'phone' => '01811223344',
            'address' => 'Sector 4, Uttara, Dhaka',
            'amount' => 2200.0,
            'district' => 'Dhaka',
            'weight' => 0.8,
        ]);

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['consignment_id']);
        $this->assertEquals('pathao', $result['courier']);
    }

    public function test_multi_courier_tracking_lookup(): void
    {
        $steadfast = $this->gatewayManager->resolve('steadfast');
        $sfTrack = $steadfast->trackParcel('TRK-STDF-00123');
        $this->assertEquals('TRK-STDF-00123', $sfTrack->trackingCode);
        $this->assertEquals('in_review', $sfTrack->status);

        $pathao = $this->gatewayManager->resolve('pathao');
        $ptTrack = $pathao->trackParcel('TRK-PTH-00456');
        $this->assertEquals('TRK-PTH-00456', $ptTrack->trackingCode);
        $this->assertEquals('in_transit', $ptTrack->status);
    }

    public function test_end_to_end_fraud_check_evaluation(): void
    {
        // 1. Clean customer
        $cleanScore = $this->fraudService->evaluate('01711223344');
        $this->assertEquals('low', $cleanScore->riskLevel);
        $this->assertFalse($cleanScore->isBlacklisted);

        // 2. Blacklisted Phone Number
        IpBlock::create([
            'ip_no' => '01999888777',
            'reason' => 'Multiple parcel returns fraud',
        ]);

        $blockedScore = $this->fraudService->evaluate('01999888777');
        $this->assertEquals('high', $blockedScore->riskLevel);
        $this->assertTrue($blockedScore->isBlacklisted);
    }
}