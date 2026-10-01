<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;

class ModuleContractsTest extends TestCase
{
    public function test_module_interfaces_exist_and_have_contracted_methods(): void
    {
        $orderRef = new ReflectionClass(OrderModuleInterface::class);
        $this->assertTrue($orderRef->isInterface());
        $this->assertTrue($orderRef->hasMethod('findOrderById'));
        $this->assertTrue($orderRef->hasMethod('createOrder'));
        $this->assertTrue($orderRef->hasMethod('updateOrderStatus'));

        $catalogRef = new ReflectionClass(CatalogModuleInterface::class);
        $this->assertTrue($catalogRef->isInterface());
        $this->assertTrue($catalogRef->hasMethod('checkStock'));

        $customerRef = new ReflectionClass(CustomerModuleInterface::class);
        $this->assertTrue($customerRef->isInterface());
        $this->assertTrue($customerRef->hasMethod('findCustomerByPhone'));

        $paymentRef = new ReflectionClass(PaymentModuleInterface::class);
        $this->assertTrue($paymentRef->isInterface());
        $this->assertTrue($paymentRef->hasMethod('processPayment'));

        $shippingRef = new ReflectionClass(ShippingModuleInterface::class);
        $this->assertTrue($shippingRef->isInterface());
        $this->assertTrue($shippingRef->hasMethod('calculateShippingCharge'));

        $invRef = new ReflectionClass(InventoryModuleInterface::class);
        $this->assertTrue($invRef->isInterface());
        $this->assertTrue($invRef->hasMethod('reserveStock'));

        $promoRef = new ReflectionClass(\Shared\Domain\Contracts\Modules\PromotionModuleInterface::class);
        $this->assertTrue($promoRef->isInterface());
        $this->assertTrue($promoRef->hasMethod('getActiveCampaigns'));

        $settingRef = new ReflectionClass(\Shared\Domain\Contracts\Modules\SettingModuleInterface::class);
        $this->assertTrue($settingRef->isInterface());
        $this->assertTrue($settingRef->hasMethod('getGeneralSetting'));
    }
}