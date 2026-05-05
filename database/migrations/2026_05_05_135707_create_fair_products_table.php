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
        Schema::create('fair_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fair_batch_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('size_set_id');
            $table->string('barcode')->nullable();
            $table->string('qrcode')->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
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
        Schema::dropIfExists('fair_products');
    }
};
