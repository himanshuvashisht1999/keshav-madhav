<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('domestic_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_main_id');
            $table->unsignedBigInteger('packing_main_id');
            $table->unsignedBigInteger('packing_carton_id');
            $table->unsignedBigInteger('packing_box_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('size_id');
            $table->integer('quantity');
            $table->string('box_no')->nullable();
            $table->string('carton_no')->nullable();
            $table->string('barcode')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=In Stock, 2=Out/Dispatched');
            $table->timestamps();

            // Optional: Add indexes for better performance
            $table->index('order_main_id');
            $table->index('packing_carton_id');
            $table->index('packing_box_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domestic_inventories');
    }
};
