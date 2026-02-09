<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_prices', function (Blueprint $table) {
            $table->dropColumn('size_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('size_id')->nullable()->after('color_id');
        });
    }
};
