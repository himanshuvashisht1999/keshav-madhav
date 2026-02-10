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
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('gst_order', 8, 2)->default(5.00)->after('currency');
        });

        Schema::table('agent_orders', function (Blueprint $table) {
            $table->decimal('discount_percentage', 8, 2)->default(0)->after('status');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            $table->decimal('gst_percentage', 8, 2)->default(0)->after('discount_amount');
            $table->decimal('gst_amount', 15, 2)->default(0)->after('gst_percentage');
            $table->decimal('grand_total', 15, 2)->default(0)->after('gst_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('gst_order');
        });

        Schema::table('agent_orders', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percentage',
                'discount_amount',
                'gst_percentage',
                'gst_amount',
                'grand_total'
            ]);
        });
    }
};
