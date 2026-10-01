<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\BannerCategory;
use App\Models\Category;
use App\Models\Color;
use App\Models\Contact;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Productcolor;
use App\Models\Productimage;
use App\Models\Productsize;
use App\Models\ShippingCharge;
use App\Models\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultProductSeeder extends Seeder
{
    /**
     * Run the database seeds for homepage showcase.
     *
     * @return void
     */
    public function run(): void
    {
        $this->seedSettingsAndContact();
        $this->seedShippingCharges();
        $this->seedBanners();
        $categories = $this->seedCategories();
        $sizes = $this->seedSizes();
        $colors = $this->seedColors();
        $this->seedProducts($categories, $sizes, $colors);
    }

    /**
     * Ensure general settings and contact info exist.
     */
    protected function seedSettingsAndContact(): void
    {
        GeneralSetting::updateOrCreate(
            ['name' => 'MondolShopBD'],
            [
                'white_logo' => 'uploads/settings/1700974809-logo.webp',
                'dark_logo' => 'uploads/settings/1700974809-logo.webp',
                'favicon' => 'uploads/settings/1699935017-favicon.webp',
                'copyright' => '© ' . date('Y') . ' MondolShopBD. All Rights Reserved.',
                'description' => 'MondolShopBD - আপনার বিশ্বস্ত অনলাইন ফ্যাশন ও শপিং প্ল্যাটফর্ম।',
                'status' => 1,
            ]
        );

        Contact::updateOrCreate(
            ['phone' => '01877786651'],
            [
                'hotline' => '01877786651',
                'hotmail' => 'mondolshopbd@gmail.com',
                'email' => 'mondolshopbd@gmail.com',
                'address' => 'ঢাকা সাভার, বাইপাইল, বাংলাদেশ',
                'maplink' => '#',
                'status' => 1,
            ]
        );
    }

    /**
     * Ensure default shipping charges exist.
     */
    protected function seedShippingCharges(): void
    {
        $charges = [
            ['name' => 'ঢাকার ভিতরে', 'amount' => 70, 'status' => '1'],
            ['name' => 'ঢাকার বাইরে', 'amount' => 130, 'status' => '1'],
        ];

        foreach ($charges as $charge) {
            ShippingCharge::updateOrCreate(
                ['name' => $charge['name']],
                ['amount' => $charge['amount'], 'status' => $charge['status']]
            );
        }
    }

    /**
     * Seed banner categories and slider banners.
     */
    protected function seedBanners(): void
    {
        $sliderCat = BannerCategory::updateOrCreate(
            ['id' => 1],
            ['name' => 'Slider (1060x395)', 'status' => 1]
        );

        $banners = [
            ['image' => 'uploads/banner/1734535185T-Shirt.webp', 'link' => '#'],
            ['image' => 'uploads/banner/1738481867Untitled design (25).webp', 'link' => '#'],
            ['image' => 'uploads/banner/1701006127banner-1.webp', 'link' => '#'],
        ];

        foreach ($banners as $bannerData) {
            Banner::updateOrCreate(
                ['image' => $bannerData['image'], 'category_id' => $sliderCat->id],
                ['link' => $bannerData['link'], 'status' => 1]
            );
        }
    }

    /**
     * Seed featured categories for homepage showcase.
     *
     * @return array<string, Category>
     */
    protected function seedCategories(): array
    {
        $categoryConfigs = [
            'drop-shoulder' => [
                'name' => 'Drop Shoulder T-Shirt',
                'slug' => 'drop-shoulder-t-shirt',
                'image' => 'uploads/category/1742571997-coffe-single.webp',
                'front_view' => 1,
                'status' => 1,
            ],
            'sweatshirt' => [
                'name' => 'SWEATSHIRT',
                'slug' => 'sweatshirt',
                'image' => 'uploads/category/1734558408-untitled-design-(6).webp',
                'front_view' => 1,
                'status' => 1,
            ],
            'trouser' => [
                'name' => 'Trouser',
                'slug' => 'trouser',
                'image' => 'uploads/category/1738607807-9.webp',
                'front_view' => 1,
                'status' => 1,
            ],
            'polo-shirt' => [
                'name' => 'POLO-SHIRT',
                'slug' => 'polo-shirt',
                'image' => 'uploads/category/1770491488-3.webp',
                'front_view' => 1,
                'status' => 1,
            ],
            'full-sleeve' => [
                'name' => 'Full Sleeve T-shirt',
                'slug' => 'full-sleeve-t-shirt',
                'image' => 'uploads/category/1761733370-2.webp',
                'front_view' => 1,
                'status' => 1,
            ],
            'sleeveless' => [
                'name' => 'Sleeveless T-Shirt',
                'slug' => 'sleeveless-t-shirt',
                'image' => 'uploads/category/1770995314-4-(1).webp',
                'front_view' => 1,
                'status' => 1,
            ],
        ];

        $createdCategories = [];
        foreach ($categoryConfigs as $key => $data) {
            $createdCategories[$key] = Category::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'image' => $data['image'],
                    'front_view' => $data['front_view'],
                    'status' => $data['status'],
                    'parent_id' => 0,
                    'meta_title' => $data['name'],
                    'meta_description' => $data['name'] . ' - সেরা মানের পোশাক সুলভ মূল্যে কিনুন।',
                ]
            );
        }

        return $createdCategories;
    }

    /**
     * Seed clothing sizes.
     *
     * @return array<string, Size>
     */
    protected function seedSizes(): array
    {
        $sizeNames = ['M', 'L', 'XL', 'XXL'];
        $sizes = [];

        foreach ($sizeNames as $name) {
            $sizes[$name] = Size::firstOrCreate(
                ['sizeName' => $name],
                ['status' => '1']
            );
        }

        return $sizes;
    }

    /**
     * Seed clothing colors.
     *
     * @return array<string, Color>
     */
    protected function seedColors(): array
    {
        $colorConfigs = [
            'Black' => '#000000',
            'White' => '#ffffff',
            'Navy Blue' => '#000080',
            'Maroon' => '#800000',
            'Olive Green' => '#556b2f',
        ];

        $colors = [];
        foreach ($colorConfigs as $name => $hex) {
            $colors[$name] = Color::firstOrCreate(
                ['colorName' => $name],
                ['color' => $hex, 'status' => '1']
            );
        }

        return $colors;
    }

    /**
     * Seed default showcase products with images, sizes, and colors.
     *
     * @param array<string, Category> $categories
     * @param array<string, Size> $sizes
     * @param array<string, Color> $colors
     */
    protected function seedProducts(array $categories, array $sizes, array $colors): void
    {
        $defaultDescription = '<p><strong>পণ্যের বিবরণ:</strong></p>'
            . '<ul>'
            . '<li>ফেব্রিক: ১০০% প্রিমিয়াম সুতি / ফ্লিস ফ্যাব্রিক (কমফোর্টেবল ও টেকসই)</li>'
            . '<li>জিএসএম (GSM): ২২০ - ৩২০ (কালার ও সাইজ স্থায়ী)</li>'
            . '<li>ফিনিশিং: আন্তর্জাতিক মানের স্টিচিং ও কালার গ্যারান্টি</li>'
            . '<li>ডেলিভারি: সারাদেশে ক্যাশ অন ডেলিভারি এবং দ্রুত হোম ডেলিভারি সুবিধা</li>'
            . '</ul>';

        $productsData = [
            [
                'name' => 'Premium Drop Shoulder T-Shirt - Pitch Black',
                'slug' => 'premium-drop-shoulder-t-shirt-pitch-black',
                'product_code' => 'MS-DS-01',
                'category_key' => 'drop-shoulder',
                'purchase_price' => 350,
                'old_price' => 750,
                'new_price' => 490,
                'stock' => 150,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706962659-6.webp',
            ],
            [
                'name' => '2 Pieces Premium Winter Sweatshirt - Biscuit & Black',
                'slug' => '2-pieces-premium-winter-sweatshirt-biscuit-black',
                'product_code' => 'MS-SW-02',
                'category_key' => 'sweatshirt',
                'purchase_price' => 600,
                'old_price' => 1700,
                'new_price' => 1190,
                'stock' => 100,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706962760-1.webp',
            ],
            [
                'name' => 'Men\'s Stylish Casual Cotton Trouser - Navy Blue',
                'slug' => 'mens-stylish-casual-cotton-trouser-navy-blue',
                'product_code' => 'MS-TR-03',
                'category_key' => 'trouser',
                'purchase_price' => 400,
                'old_price' => 950,
                'new_price' => 650,
                'stock' => 120,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706962864-2.webp',
            ],
            [
                'name' => 'Exclusive Semi-Formal Polo Shirt - Maroon',
                'slug' => 'exclusive-semi-formal-polo-shirt-maroon',
                'product_code' => 'MS-PO-04',
                'category_key' => 'polo-shirt',
                'purchase_price' => 420,
                'old_price' => 990,
                'new_price' => 690,
                'stock' => 80,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706962961-3.webp',
            ],
            [
                'name' => 'Winter Full Sleeve High Neck T-Shirt - Dark Grey',
                'slug' => 'winter-full-sleeve-high-neck-t-shirt-dark-grey',
                'product_code' => 'MS-FS-05',
                'category_key' => 'full-sleeve',
                'purchase_price' => 320,
                'old_price' => 700,
                'new_price' => 480,
                'stock' => 90,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706963054-4.webp',
            ],
            [
                'name' => 'Athletic Gym Sleeveless T-Shirt - Olive Green',
                'slug' => 'athletic-gym-sleeveless-t-shirt-olive-green',
                'product_code' => 'MS-SL-06',
                'category_key' => 'sleeveless',
                'purchase_price' => 250,
                'old_price' => 550,
                'new_price' => 380,
                'stock' => 110,
                'topsale' => 1,
                'feature_product' => 1,
                'image' => 'uploads/product/1706963304-5.webp',
            ],
            [
                'name' => 'Oversized Streetwear Drop Shoulder T-Shirt - Coffee Brown',
                'slug' => 'oversized-streetwear-drop-shoulder-t-shirt-coffee-brown',
                'product_code' => 'MS-DS-07',
                'category_key' => 'drop-shoulder',
                'purchase_price' => 360,
                'old_price' => 800,
                'new_price' => 520,
                'stock' => 140,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706963666-968d4e20278e2db6759a2d2a44231f4c.jpg_750x750.jpg_.webp',
            ],
            [
                'name' => 'Heavy Fleece Winter Hoodie Sweatshirt - Off White',
                'slug' => 'heavy-fleece-winter-hoodie-sweatshirt-off-white',
                'product_code' => 'MS-SW-08',
                'category_key' => 'sweatshirt',
                'purchase_price' => 650,
                'old_price' => 1800,
                'new_price' => 1250,
                'stock' => 75,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706963947-7.webp',
            ],
            [
                'name' => 'Comfort Fit Jogger Trouser - Charcoal Black',
                'slug' => 'comfort-fit-jogger-trouser-charcoal-black',
                'product_code' => 'MS-TR-09',
                'category_key' => 'trouser',
                'purchase_price' => 380,
                'old_price' => 900,
                'new_price' => 590,
                'stock' => 95,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706964217-6.webp',
            ],
            [
                'name' => 'Classic Striped Cotton Polo T-Shirt - Royal Navy',
                'slug' => 'classic-striped-cotton-polo-t-shirt-royal-navy',
                'product_code' => 'MS-PO-10',
                'category_key' => 'polo-shirt',
                'purchase_price' => 450,
                'old_price' => 1050,
                'new_price' => 720,
                'stock' => 85,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706964324-8.webp',
            ],
            [
                'name' => 'Casual Solid Full Sleeve T-Shirt - Pure White',
                'slug' => 'casual-solid-full-sleeve-t-shirt-pure-white',
                'product_code' => 'MS-FS-11',
                'category_key' => 'full-sleeve',
                'purchase_price' => 300,
                'old_price' => 650,
                'new_price' => 450,
                'stock' => 130,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706964552-9.webp',
            ],
            [
                'name' => 'Summer Breathable Muscle Sleeveless T-Shirt - Jet Black',
                'slug' => 'summer-breathable-muscle-sleeveless-t-shirt-jet-black',
                'product_code' => 'MS-SL-12',
                'category_key' => 'sleeveless',
                'purchase_price' => 260,
                'old_price' => 580,
                'new_price' => 390,
                'stock' => 115,
                'topsale' => 0,
                'feature_product' => 1,
                'image' => 'uploads/product/1706964708-10.webp',
            ],
        ];

        foreach ($productsData as $data) {
            $category = $categories[$data['category_key']] ?? null;
            if (!$category) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['product_code' => $data['product_code']],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'category_id' => $category->id,
                    'purchase_price' => $data['purchase_price'],
                    'old_price' => $data['old_price'],
                    'new_price' => $data['new_price'],
                    'stock' => $data['stock'],
                    'description' => $defaultDescription,
                    'topsale' => $data['topsale'],
                    'feature_product' => $data['feature_product'],
                    'status' => 1,
                    'meta_title' => $data['name'],
                    'meta_description' => $data['name'] . ' - সেরা অফারে কিনুন MondolShopBD থেকে।',
                ]
            );

            // Seed featured image
            Productimage::updateOrCreate(
                ['product_id' => $product->id, 'image' => $data['image']],
                ['is_featured' => 1]
            );

            // Seed sizes for this product
            foreach ($sizes as $sizeModel) {
                Productsize::firstOrCreate([
                    'product_id' => $product->id,
                    'size_id' => $sizeModel->id,
                ]);
            }

            // Seed colors for this product
            foreach ($colors as $colorModel) {
                Productcolor::firstOrCreate([
                    'product_id' => $product->id,
                    'color_id' => $colorModel->id,
                ]);
            }
        }
    }
}
