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
        Schema::create('order_cutting_stage', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_main_id')->nullable();
            $table->integer('from_assign_id')->nullable();
            $table->integer('to_assign_id')->nullable();
            $table->integer('set_product_id')->nullable();
            $table->integer('lot_no')->nullable();
            $table->integer('fabric_id')->nullable();
            $table->integer('master_fitting_id')->nullable();
            $table->integer('master_pattern_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('remaining_quantity')->default('0');
            $table->dateTime('till_allowed_time')->nullable();
            $table->string('time_type')->nullable();
            $table->integer('allowed_time')->nullable();
            $table->string('processed_by');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('order_cutting_stage');
    }
};
