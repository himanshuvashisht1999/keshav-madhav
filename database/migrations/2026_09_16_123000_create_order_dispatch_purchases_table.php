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
        Schema::create('order_dispatch_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_dispatch_id')->index();
            $table->unsignedBigInteger('domestic_inventory_purchase_id')->nullable()->index();
            $table->unsignedBigInteger('purchase_history_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('size_set_id')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->integer('boxes_count')->default(0);
            $table->integer('pieces_per_box')->default(0);
            $table->integer('total_pieces')->default(0);
            $table->decimal('mrp', 10, 2)->default(0.00);
            $table->decimal('selling_price', 10, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_dispatch_purchases');
    }
};
