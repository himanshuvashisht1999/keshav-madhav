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
        Schema::create('inventory_price_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_price_id');
            $table->string('image_path');
            $table->boolean('is_main')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('inventory_price_id')->references('id')->on('inventory_prices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_price_images');
    }
};
