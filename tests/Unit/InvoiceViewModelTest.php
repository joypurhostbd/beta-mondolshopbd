<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Payment;
use App\Models\Shipping;
use PHPUnit\Framework\TestCase;
use Shared\Domain\ValueObjects\Money;
use Shared\Infrastructure\Views\ViewModels\InvoiceViewModel;

class InvoiceViewModelTest extends TestCase
{
    public function test_invoice_view_model_computes_item_totals_and_subtotal(): void
    {
        $order = new Order();
        $order->id = 101;
        $order->invoice_id = 'INV-998877';
        $order->shipping_charge = 120;
        $order->discount = 50;
        $order->amount = 1270;
        $order->consignment_id = 'TRK-554433';
        $order->courier_name = 'steadfast';

        $detail1 = new OrderDetails();
        $detail1->product_name = 'Casual Cotton Shirt';
        $detail1->product_size = 'XL';
        $detail1->product_color = 'Navy';
        $detail1->sale_price = 400;
        $detail1->qty = 2; // 400 * 2 = 800

        $detail2 = new OrderDetails();
        $detail2->product_name = 'Leather Wallet';
        $detail2->sale_price = 400;
        $detail2->qty = 1; // 400 * 1 = 400

        $order->setRelation('orderdetails', collect([$detail1, $detail2]));

        $shipping = new Shipping();
        $shipping->name = 'Rahim Khan';
        $shipping->phone = '01700000000';
        $shipping->address = 'House 10, Road 5';
        $shipping->area = 'Dhaka City';
        $order->setRelation('shipping', $shipping);

        $payment = new Payment();
        $payment->payment_method = 'Cash on Delivery';
        $order->setRelation('payment', $payment);

        $setting = new GeneralSetting();
        $setting->name = 'MondolShopBD';
        $setting->white_logo = 'uploads/logo.png';

        $contact = new Contact();
        $contact->phone = '01800000000';
        $contact->email = 'info@mondolshopbd.com';
        $contact->address = 'Dhaka, Bangladesh';

        $vm = InvoiceViewModel::fromOrder($order, $setting, $contact);

        $this->assertEquals(101, $vm->id);
        $this->assertEquals('INV-998877', $vm->invoiceId);
        $this->assertEquals('Rahim Khan', $vm->customerName);
        $this->assertEquals('01700000000', $vm->customerPhone);
        $this->assertEquals('House 10, Road 5', $vm->customerAddress);
        $this->assertEquals('Dhaka City', $vm->customerArea);
        $this->assertEquals('Cash on Delivery', $vm->paymentMethod);
        $this->assertEquals('MondolShopBD', $vm->companyName);

        // Check subtotal calculation (800 + 400 = 1200)
        $this->assertEquals(1200.0, $vm->subtotal->getAmount());
        $this->assertEquals('৳1,200.00', $vm->getFormattedSubtotal());
        $this->assertEquals('৳120.00', $vm->getFormattedShipping());
        $this->assertEquals('৳50.00', $vm->getFormattedDiscount());
        $this->assertEquals('৳1,270.00', $vm->getFormattedTotal());

        // Check courier info
        $this->assertTrue($vm->hasCourierTracking());
        $this->assertEquals('SteadFast', $vm->courierName);
        $this->assertEquals('TRK-554433', $vm->courierTrackingId);

        // Check items
        $this->assertCount(2, $vm->items);
        $this->assertEquals('Casual Cotton Shirt', $vm->items[0]['name']);
        $this->assertEquals('XL', $vm->items[0]['size']);
        $this->assertEquals('Navy', $vm->items[0]['color']);
        $this->assertEquals('৳400.00', $vm->items[0]['formatted_unit_price']);
        $this->assertEquals(2, $vm->items[0]['quantity']);
        $this->assertEquals('৳800.00', $vm->items[0]['formatted_line_total']);
    }

    public function test_invoice_view_model_handles_null_courier_and_empty_discount(): void
    {
        $order = new Order();
        $order->id = 102;
        $order->invoice_id = 'INV-102';
        $order->shipping_charge = 60;
        $order->discount = 0;
        $order->amount = 560;

        $order->setRelation('orderdetails', collect([]));
        $order->setRelation('shipping', null);
        $order->setRelation('payment', null);

        $vm = InvoiceViewModel::fromOrder($order);

        $this->assertFalse($vm->hasCourierTracking());
        $this->assertNull($vm->courierName);
        $this->assertNull($vm->courierTrackingId);
        $this->assertFalse($vm->hasDiscount());
        $this->assertEquals('N/A', $vm->customerName);
        $this->assertEquals('Cash On Delivery', $vm->paymentMethod);
        $this->assertEquals('৳0.00', $vm->getFormattedSubtotal());
        $this->assertEquals('৳60.00', $vm->getFormattedShipping());
        $this->assertEquals('৳560.00', $vm->getFormattedTotal());
    }
}
