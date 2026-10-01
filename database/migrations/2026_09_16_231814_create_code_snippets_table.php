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
        Schema::create('code_snippets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('type', 30)->default('html'); // html, javascript, css, text
            $table->string('location', 30)->default('head'); // head, body_open, footer
            $table->longText('code');
            $table->boolean('status')->default(true)->index();
            $table->integer('priority')->default(10);
            $table->string('device_target', 20)->default('all'); // all, desktop, mobile
            $table->string('target_pages', 30)->default('all'); // all, homepage, checkout, thank_you, custom
            $table->text('custom_page_urls')->nullable();
            $table->string('auth_condition', 20)->default('all'); // all, logged_in, guest
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_snippets');
    }
};
