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
        Schema::create('order_products_sets', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_main_id')->nullable();
            $table->string('product_sku')->nullable();
            $table->string('bar_code')->nullable();
            $table->integer('production_goods_id')->nullable();
            $table->string('design_number')->nullable();
            $table->string('set_size')->nullable();
            $table->integer('color_id')->nullable();
            $table->integer('no_of_pcs')->nullable();
            $table->integer('set_quantity')->default('1');
            $table->integer('total_quantity')->nullable();
            $table->string('remain_set_quantity')->nullable();
            $table->string('remain_total_quantity')->nullable();
            $table->integer('stage_master_unit_id')->nullable();
            $table->integer('fabric_id')->nullable();
            $table->integer('master_product_fitting_id')->nullable();
            $table->integer('master_design_pattern_id')->nullable();
            $table->text('remark')->nullable();
            $table->integer('status')->default('1');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('order_products_sets');
    }
};
