<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courierapis', function (Blueprint $table) {

            if (!Schema::hasColumn('courierapis', 'client_id')) {
                $table->text('client_id')->nullable()->after('api_key');
            }

            if (!Schema::hasColumn('courierapis', 'client_secret')) {
                $table->text('client_secret')->nullable()->after('client_id');
            }

            if (!Schema::hasColumn('courierapis', 'username')) {
                $table->string('username')->nullable()->after('client_secret');
            }

            if (!Schema::hasColumn('courierapis', 'password')) {
                $table->text('password')->nullable()->after('username');
            }

            if (!Schema::hasColumn('courierapis', 'grant_type')) {
                $table->string('grant_type')->default('password')->after('password');
            }

            if (!Schema::hasColumn('courierapis', 'refresh_token')) {
                $table->text('refresh_token')->nullable()->after('token');
            }

            if (!Schema::hasColumn('courierapis', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable()->after('refresh_token');
            }

            if (!Schema::hasColumn('courierapis', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('token_expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courierapis', function (Blueprint $table) {
            foreach ([
                'client_id',
                'client_secret',
                'username',
                'password',
                'grant_type',
                'refresh_token',
                'token_expires_at',
                'store_id'
            ] as $column) {
                if (Schema::hasColumn('courierapis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
