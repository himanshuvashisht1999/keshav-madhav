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
        Schema::create('order_product_details', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_product_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('product_size')->nullable();
            $table->integer('product_color_id')->nullable();
            $table->string('fabric_sku')->nullable();
            $table->decimal('meter', 10, 2)->default('0.00');
            $table->integer('order_quantity');
            $table->decimal('total_meter', 10, 2)->default('0.00');
            $table->integer('status')->default('1');
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
        Schema::dropIfExists('order_product_details');
    }
};
