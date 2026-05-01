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
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->decimal('mrp', 15, 2)->default(0)->after('vendor_id');
            $table->decimal('purchase_rate', 15, 2)->default(0)->after('mrp');
            $table->decimal('gst', 15, 2)->default(0)->after('purchase_rate');
            $table->decimal('other_amount', 15, 2)->default(0)->after('gst');
            $table->decimal('discount', 15, 2)->default(0)->after('other_amount');
            $table->decimal('total_amount', 15, 2)->default(0)->after('discount');
        });

        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->decimal('mrp', 15, 2)->default(0)->after('box_quantity');
            $table->integer('pieces_per_box')->default(0)->after('mrp');
            $table->decimal('purchase_rate', 15, 2)->default(0)->after('pieces_per_box');
            $table->decimal('gst', 15, 2)->default(0)->after('purchase_rate');
            $table->decimal('other_amount', 15, 2)->default(0)->after('gst');
            $table->decimal('discount', 15, 2)->default(0)->after('other_amount');
            $table->decimal('total_amount', 15, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'purchase_rate', 'gst', 'other_amount', 'discount', 'total_amount']);
        });

        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->dropColumn(['mrp', 'pieces_per_box', 'purchase_rate', 'gst', 'other_amount', 'discount', 'total_amount']);
        });
    }
};
