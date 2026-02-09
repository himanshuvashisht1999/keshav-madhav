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
        Schema::create('order_printing_to_stiching_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('order_product_id')->nullable();
            $table->integer('from_stage_id')->nullable();
            $table->integer('to_stage_id')->nullable();
            $table->integer('sub_stage_id')->nullable();
            $table->integer('sub_stage_id_to')->nullable();
            $table->string('lot_no')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('remaining_quantity')->nullable();
            $table->string('processed_by')->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('production_datetime')->default('current_timestamp()');
            $table->integer('status')->default('1');
            $table->integer('production_slip_digitization_id')->nullable();
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
        Schema::dropIfExists('order_printing_to_stiching_transactions');
    }
};
