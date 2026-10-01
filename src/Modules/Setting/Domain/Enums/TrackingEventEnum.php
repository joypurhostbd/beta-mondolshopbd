<?php

namespace Modules\Setting\Domain\Enums;

enum TrackingEventEnum: string
{
    case PageView = 'page_view';
    case ViewContent = 'view_content';
    case AddToCart = 'add_to_cart';
    case InitiateCheckout = 'initiate_checkout';
    case Purchase = 'purchase';

    public function label(): string
    {
        return match ($this) {
            self::PageView => 'Page View',
            self::ViewContent => 'View Content (view_item)',
            self::AddToCart => 'Add To Cart',
            self::InitiateCheckout => 'Initiate Checkout (begin_checkout)',
            self::Purchase => 'Purchase & Revenue',
        };
    }

    public function ga4EventName(): string
    {
        return match ($this) {
            self::PageView => 'page_view',
            self::ViewContent => 'view_item',
            self::AddToCart => 'add_to_cart',
            self::InitiateCheckout => 'begin_checkout',
            self::Purchase => 'purchase',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PageView => 'Fires on every storefront page load.',
            self::ViewContent => 'Fires when a customer views a product details page.',
            self::AddToCart => 'Fires when an item is added to the cart (AJAX and page).',
            self::InitiateCheckout => 'Fires when a customer begins the checkout process.',
            self::Purchase => 'Fires on order completion with transaction ID, items, and revenue value.',
        };
    }

    public function defaultParameters(): array
    {
        return match ($this) {
            self::PageView => [
                'track_page_title' => true,
                'track_page_location' => true,
            ],
            self::ViewContent => [
                'currency' => 'BDT',
                'track_items' => true,
            ],
            self::AddToCart => [
                'currency' => 'BDT',
                'track_items' => true,
                'track_value' => true,
            ],
            self::InitiateCheckout => [
                'currency' => 'BDT',
                'track_items' => true,
                'track_value' => true,
            ],
            self::Purchase => [
                'currency' => 'BDT',
                'track_items' => true,
                'track_value' => true,
                'send_user_data' => true,
                'hash_personal_data' => true,
            ],
        };
    }
}
