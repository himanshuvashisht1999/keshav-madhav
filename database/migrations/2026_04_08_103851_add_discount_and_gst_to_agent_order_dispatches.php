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
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->decimal('discount_amount', 20, 2)->default(0)->after('grand_total');
            $table->decimal('gst_percentage', 5, 2)->default(5)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->dropColumn(['discount_amount', 'gst_percentage']);
        });
    }
};
