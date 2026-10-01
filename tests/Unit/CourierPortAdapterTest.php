<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\ShippingCharge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shipping\Application\DTOs\CourierParcelDTO;
use Modules\Shipping\Application\Services\CourierGatewayManager;
use Modules\Shipping\Application\Services\ShippingService;
use Modules\Shipping\Domain\Contracts\CourierGatewayInterface;
use Modules\Shipping\Infrastructure\Gateways\MockCourierGateway;
use Modules\Shipping\Infrastructure\Gateways\PathaoCourierGateway;
use Modules\Shipping\Infrastructure\Gateways\SteadfastCourierGateway;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class CourierPortAdapterTest extends TestCase
{
    use RefreshDatabase;

    private CourierGatewayManager $manager;
    private ShippingService $shippingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->app->make(CourierGatewayManager::class);
        $this->shippingService = $this->app->make(ShippingService::class);
    }

    public function test_courier_gateway_manager_resolves_all_drivers(): void
    {
        $this->assertTrue($this->manager->has('steadfast'));
        $this->assertTrue($this->manager->has('pathao'));
        $this->assertTrue($this->manager->has('redx'));
        $this->assertTrue($this->manager->has('paperfly'));

        $this->assertInstanceOf(CourierGatewayInterface::class, $this->manager->resolve('steadfast'));
        $this->assertInstanceOf(CourierGatewayInterface::class, $this->manager->resolve('pathao'));
    }

    public function test_unregistered_courier_throws_domain_exception(): void
    {
        $this->expectException(EntityNotFoundException::class);
        $this->manager->resolve('unknown_courier');
    }

    public function test_steadfast_courier_send_parcel(): void
    {
        $dto = new CourierParcelDTO(
            orderId: 801,
            invoiceId: 'INV-801',
            recipientName: 'Sizar Babu',
            recipientPhone: PhoneNumber::fromString('01711223344'),
            recipientAddress: 'Banani, Dhaka',
            amountToCollect: Money::from(2500.0),
            district: 'Dhaka',
            weightKg: 1.0,
            note: 'Urgent delivery'
        );

        $gateway = $this->manager->resolve('steadfast');
        $response = $gateway->sendParcel($dto);

        $this->assertTrue($response->isSuccessful);
        $this->assertNotEmpty($response->consignmentId);
        $this->assertNotEmpty($response->trackingCode);
        $this->assertEquals('steadfast', $response->courierName);
    }

    public function test_pathao_courier_send_parcel(): void
    {
        $dto = new CourierParcelDTO(
            orderId: 802,
            invoiceId: 'INV-802',
            recipientName: 'Sizar Babu',
            recipientPhone: PhoneNumber::fromString('01711223344'),
            recipientAddress: 'Uttara, Dhaka',
            amountToCollect: Money::from(3100.0),
            district: 'Dhaka'
        );

        $gateway = $this->manager->resolve('pathao');
        $response = $gateway->sendParcel($dto);

        $this->assertTrue($response->isSuccessful);
        $this->assertNotEmpty($response->consignmentId);
        $this->assertEquals('pathao', $response->courierName);
    }

    public function test_shipping_service_calculates_charges_and_creates_shipment(): void
    {
        $charge = $this->shippingService->calculateShippingCharge(1, Money::from(2000.0));
        $this->assertEquals(60.0, $charge->getAmount());

        $outsideCharge = $this->shippingService->calculateShippingCharge(55, Money::from(2000.0));
        $this->assertEquals(120.0, $outsideCharge->getAmount());

        $shipment = $this->shippingService->createShipment(803, [
            'courier' => 'steadfast',
            'invoice_id' => 'INV-803',
            'name' => 'Sizar Babu',
            'phone' => '01711223344',
            'address' => 'Dhaka',
            'amount' => 2500.0,
        ]);

        $this->assertTrue($shipment['success']);
        $this->assertNotEmpty($shipment['consignment_id']);
        $this->assertEquals('steadfast', $shipment['courier']);
    }
}