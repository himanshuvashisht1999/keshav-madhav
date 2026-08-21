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
            $table->decimal('gst_amount', 15, 2)->default(0.00)->after('gst_percentage');
            $table->decimal('other_charges', 15, 2)->default(0.00)->after('total_amount');
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
            $table->dropColumn(['gst_amount', 'other_charges']);
        });
    }
};
