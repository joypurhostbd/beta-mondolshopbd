<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->text('footer_about')->nullable()->after('copyright');
            $table->string('footer_phone', 50)->nullable()->after('footer_about');
            $table->string('footer_email', 100)->nullable()->after('footer_phone');
            $table->string('footer_address', 255)->nullable()->after('footer_email');

            $table->string('footer_useful_links_title', 100)->nullable()->default('Useful Links')->after('footer_address');
            $table->string('footer_info_links_title', 100)->nullable()->default('Information')->after('footer_useful_links_title');

            $table->string('newsletter_title', 100)->nullable()->default('Stay Connected')->after('footer_info_links_title');
            $table->text('newsletter_text')->nullable()->after('newsletter_title');
            $table->tinyInteger('newsletter_status')->default(1)->after('newsletter_text');

            $table->string('app_download_title', 100)->nullable()->default('Download Our App')->after('newsletter_status');
            $table->tinyInteger('app_download_status')->default(1)->after('app_download_title');
            $table->string('play_store_url', 255)->nullable()->after('app_download_status');
            $table->string('app_store_url', 255)->nullable()->after('play_store_url');

            $table->tinyInteger('features_status')->default(1)->after('app_store_url');
            $table->string('feature1_title', 100)->nullable()->default('100% Secure Payment')->after('features_status');
            $table->string('feature1_subtitle', 150)->nullable()->default('Safe & Secure Transactions')->after('feature1_title');
            $table->string('feature2_title', 100)->nullable()->default('Fast Delivery')->after('feature1_subtitle');
            $table->string('feature2_subtitle', 150)->nullable()->default('All Over Bangladesh')->after('feature2_title');
            $table->string('feature3_title', 100)->nullable()->default('Easy Return')->after('feature2_subtitle');
            $table->string('feature3_subtitle', 150)->nullable()->default('Within 7 Days')->after('feature3_title');
            $table->string('feature4_title', 100)->nullable()->default('24/7 Support')->after('feature3_subtitle');
            $table->string('feature4_subtitle', 150)->nullable()->default("We're Here to Help")->after('feature4_title');

            $table->tinyInteger('show_payment_methods')->default(1)->after('feature4_subtitle');
            $table->string('payment_methods_image', 255)->nullable()->after('show_payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
                'footer_about',
                'footer_phone',
                'footer_email',
                'footer_address',
                'footer_useful_links_title',
                'footer_info_links_title',
                'newsletter_title',
                'newsletter_text',
                'newsletter_status',
                'app_download_title',
                'app_download_status',
                'play_store_url',
                'app_store_url',
                'features_status',
                'feature1_title',
                'feature1_subtitle',
                'feature2_title',
                'feature2_subtitle',
                'feature3_title',
                'feature3_subtitle',
                'feature4_title',
                'feature4_subtitle',
                'show_payment_methods',
                'payment_methods_image',
            ]);
        });
    }
};
