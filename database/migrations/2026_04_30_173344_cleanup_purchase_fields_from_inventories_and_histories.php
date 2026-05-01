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
            $table->dropColumn(['customer_id', 'vendor_id', 'mrp', 'purchase_rate', 'gst', 'other_amount', 'discount', 'total_amount']);
        });

        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->dropColumn(['gst', 'other_amount', 'discount', 'total_amount']);
        });
    }

    public function down()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('mrp', 15, 2)->default(0);
            $table->decimal('purchase_rate', 15, 2)->default(0);
            $table->decimal('gst', 15, 2)->default(0);
            $table->decimal('other_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
        });

        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->decimal('gst', 15, 2)->default(0);
            $table->decimal('other_amount', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
        });
    }
};
