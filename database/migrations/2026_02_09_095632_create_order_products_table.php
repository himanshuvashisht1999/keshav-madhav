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
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_product_set_id')->nullable();
            $table->integer('order_main_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_type_sku')->nullable();
            $table->string('design_number')->nullable();
            $table->string('size')->nullable();
            $table->integer('color_id')->nullable();
            $table->integer('quantity')->default('1');
            $table->integer('completed_quantity')->default('0');
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
        Schema::dropIfExists('order_products');
    }
};
