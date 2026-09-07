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
        Schema::table('storerooms', function (Blueprint $table) {
            $table->string('order_dispatch', 10)->nullable()->default('Yes')->after('order_priority');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('storerooms', function (Blueprint $table) {
            $table->dropColumn('order_dispatch');
        });
    }
};
