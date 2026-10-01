<?php

namespace Shared\Infrastructure\Views\ViewModels;

use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Order;
use Shared\Domain\ValueObjects\Money;

class InvoiceViewModel
{
    /**
     * @param array<int, array{
     *     sl: int,
     *     name: string,
     *     image: ?string,
     *     size: ?string,
     *     color: ?string,
     *     unit_price: Money,
     *     quantity: int,
     *     line_total: Money,
     *     formatted_unit_price: string,
     *     formatted_line_total: string
     * }> $items
     */
    public function __construct(
        public readonly int $id,
        public readonly string $invoiceId,
        public readonly string $date,
        public readonly string $paymentMethod,
        public readonly ?string $paymentStatus,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $customerAddress,
        public readonly string $customerArea,
        public readonly string $companyName,
        public readonly string $companyPhone,
        public readonly string $companyEmail,
        public readonly string $companyAddress,
        public readonly ?string $companyLogo,
        public readonly ?string $courierName,
        public readonly ?string $courierTrackingId,
        public readonly Money $subtotal,
        public readonly Money $shippingCharge,
        public readonly Money $discount,
        public readonly Money $totalAmount,
        public readonly array $items = []
    ) {}

    public static function fromOrder(
        Order $order,
        ?GeneralSetting $setting = null,
        ?Contact $contact = null
    ): self {
        $subtotalVal = 0.0;
        $items = [];
        $sl = 1;

        if ($order->orderdetails) {
            foreach ($order->orderdetails as $detail) {
                $unitPrice = (float) ($detail->sale_price ?? 0);
                $qty = (int) ($detail->qty ?? 1);
                $lineTotalVal = $unitPrice * $qty;
                $subtotalVal += $lineTotalVal;

                $unitMoney = Money::from(max(0, $unitPrice));
                $lineMoney = Money::from(max(0, $lineTotalVal));

                $detailImage = null;
                if ($detail->relationLoaded('featuredImageRelation') && $detail->featuredImageRelation) {
                    $detailImage = $detail->featuredImageRelation->image;
                } elseif ($detail->relationLoaded('image') && $detail->image) {
                    $detailImage = $detail->image->image;
                }
                $imageUrl = $detailImage ? asset($detailImage) : null;

                $items[] = [
                    'sl' => $sl++,
                    'name' => (string) ($detail->product_name ?? 'Product'),
                    'image' => $imageUrl,
                    'size' => !empty($detail->product_size) ? (string) $detail->product_size : null,
                    'color' => !empty($detail->product_color) ? (string) $detail->product_color : null,
                    'unit_price' => $unitMoney,
                    'quantity' => $qty,
                    'line_total' => $lineMoney,
                    'formatted_unit_price' => $unitMoney->format(),
                    'formatted_line_total' => $lineMoney->format(),
                ];
            }
        }

        $subtotalMoney = Money::from(max(0, $subtotalVal));
        $shippingMoney = Money::from(max(0, (float) ($order->shipping_charge ?? 0)));
        $discountMoney = Money::from(max(0, (float) ($order->discount ?? 0)));
        $totalMoney = Money::from(max(0, (float) ($order->amount ?? 0)));

        $shipping = $order->shipping;
        $payment = $order->payment;

        $trackingId = $order->courier_tracking_id ?: null;
        $courierDisplayName = !empty($trackingId) ? $order->courier_display_name : null;

        return new self(
            id: (int) $order->id,
            invoiceId: (string) $order->invoice_id,
            date: $order->created_at ? $order->created_at->format('d M, Y h:i A') : now()->format('d M, Y h:i A'),
            paymentMethod: $payment ? (string) ($payment->payment_method ?? 'Cash On Delivery') : 'Cash On Delivery',
            paymentStatus: $payment ? (string) ($payment->payment_status ?? '') : null,
            customerName: $shipping ? (string) ($shipping->name ?? 'N/A') : 'N/A',
            customerPhone: $shipping ? (string) ($shipping->phone ?? 'N/A') : 'N/A',
            customerAddress: $shipping ? (string) ($shipping->address ?? '') : '',
            customerArea: $shipping ? (string) ($shipping->area ?? '') : '',
            companyName: $setting ? (string) ($setting->name ?? 'MondolShopBD') : 'MondolShopBD',
            companyPhone: $contact ? (string) ($contact->phone ?? '') : '',
            companyEmail: $contact ? (string) ($contact->email ?? '') : '',
            companyAddress: $contact ? (string) ($contact->address ?? '') : '',
            companyLogo: $setting ? (string) ($setting->white_logo ?? '') : null,
            courierName: $courierDisplayName,
            courierTrackingId: $trackingId,
            subtotal: $subtotalMoney,
            shippingCharge: $shippingMoney,
            discount: $discountMoney,
            totalAmount: $totalMoney,
            items: $items
        );
    }

    public function getFormattedSubtotal(): string
    {
        return $this->subtotal->format();
    }

    public function getFormattedShipping(): string
    {
        return $this->shippingCharge->format();
    }

    public function getFormattedDiscount(): string
    {
        return $this->discount->format();
    }

    public function getFormattedTotal(): string
    {
        return $this->totalAmount->format();
    }

    public function hasCourierTracking(): bool
    {
        return !empty($this->courierTrackingId);
    }

    public function hasDiscount(): bool
    {
        return $this->discount->getAmount() > 0;
    }
}
