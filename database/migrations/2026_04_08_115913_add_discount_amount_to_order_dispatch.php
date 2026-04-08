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
        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->decimal('discount_amount', 15, 2)->default(0.00)->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->dropColumn('discount_amount');
        });
    }
};
