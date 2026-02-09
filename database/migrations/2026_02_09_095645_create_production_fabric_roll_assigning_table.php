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
        Schema::create('production_fabric_roll_assigning', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_products_set_id')->nullable();
            $table->integer('production_slip_digitization_id')->nullable();
            $table->integer('order_lot_id')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('order_no')->nullable();
            $table->integer('stage_master_unit_id')->nullable();
            $table->unsignedBigInteger('to_stage_id')->nullable();
            $table->integer('roll_no')->nullable();
            $table->integer('meter')->nullable();
            $table->string('slip_file')->nullable();
            $table->dateTime('slip_create_date_time')->nullable();
            $table->integer('status')->default('0');
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
        Schema::dropIfExists('production_fabric_roll_assigning');
    }
};
