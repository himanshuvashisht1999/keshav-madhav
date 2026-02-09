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
        Schema::create('production_slip_digitization', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('from_stage_id')->nullable();
            $table->integer('to_stage_id')->nullable();
            $table->integer('stage_master_unit_id')->nullable();
            $table->string('lot_no')->nullable();
            $table->unsignedBigInteger('order_product_set_id')->nullable();
            $table->string('slip_file')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('status')->default('1');
            $table->integer('save_type')->default('3');
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
        Schema::dropIfExists('production_slip_digitization');
    }
};
