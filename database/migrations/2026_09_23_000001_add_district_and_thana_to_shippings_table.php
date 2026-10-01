<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (!Schema::hasColumn('shippings', 'district')) {
                $table->string('district', 100)->nullable()->after('area')->index();
            }
            if (!Schema::hasColumn('shippings', 'thana')) {
                $table->string('thana', 100)->nullable()->after('district')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shippings', function (Blueprint $table) {
            if (Schema::hasColumn('shippings', 'thana')) {
                $table->dropIndex(['thana']);
                $table->dropColumn('thana');
            }
            if (Schema::hasColumn('shippings', 'district')) {
                $table->dropIndex(['district']);
                $table->dropColumn('district');
            }
        });
    }
};
