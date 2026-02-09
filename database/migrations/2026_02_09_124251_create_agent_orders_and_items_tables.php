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
        Schema::create('agent_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_agent_id');
            $table->unsignedBigInteger('master_customer_id');
            $table->integer('total_qty');
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('pending'); // pending, dispatched
            $table->timestamp('order_date')->useCurrent();
            $table->timestamps();
        });

        Schema::create('agent_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_order_id');
            $table->unsignedBigInteger('packing_box_id')->nullable();
            $table->string('box_no')->nullable();
            $table->string('carton_no')->nullable();
            $table->string('product_name')->nullable();
            $table->string('design_number')->nullable();
            $table->string('color_name')->nullable();
            $table->string('size_name')->nullable();
            $table->string('size_set_name')->nullable();
            $table->integer('quantity');
            $table->decimal('mrp', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->string('qrcode')->nullable();
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
        Schema::dropIfExists('agent_order_items');
        Schema::dropIfExists('agent_orders');
    }
};
