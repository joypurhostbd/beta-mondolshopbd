<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix image paths: remove 'public/' prefix from all upload paths.
     * 
     * Before: asset('public/uploads/...') → mondolshopbd.test/public/uploads/... (404)
     * After:  asset('uploads/...')        → mondolshopbd.test/uploads/...        (OK)
     *
     * Covers: banners, brands, campaigns, categories, subcategories,
     *         childcategories, products, product_images, users, customers, generalsettings
     */
    public function up(): void
    {
        $tables = [
            'banners'    => ['image'],
            'brands'     => ['image'],
            'campaigns'  => ['image_one', 'image_two', 'image_three'],
            'categories' => ['image'],
            'subcategories' => ['image'],
            'childcategories' => ['image'],
            'products'   => ['image'],
            'productimages' => ['image'],
            'users'      => ['image'],
            'customers'  => ['image'],
            'general_settings' => ['logo', 'white_logo', 'favicon'],
        ];

        foreach ($tables as $table => $columns) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where($column, 'like', 'public/%')
                    ->update([$column => DB::raw("REPLACE(`{$column}`, 'public/', '')")]);
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'banners'    => ['image'],
            'brands'     => ['image'],
            'campaigns'  => ['image_one', 'image_two', 'image_three'],
            'categories' => ['image'],
            'subcategories' => ['image'],
            'childcategories' => ['image'],
            'products'   => ['image'],
            'productimages' => ['image'],
            'users'      => ['image'],
            'customers'  => ['image'],
            'general_settings' => ['logo', 'white_logo', 'favicon'],
        ];

        foreach ($tables as $table => $columns) {
            if (!DB::getSchemaBuilder()->hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (!DB::getSchemaBuilder()->hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where($column, 'like', 'uploads/%')
                    ->update([$column => DB::raw("CONCAT('public/', `{$column}`)")]);
            }
        }
    }
};
