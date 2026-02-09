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
        Schema::create('production_slip_digitization_parts', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('production_slip_digitization_id')->nullable();
            $table->string('slip_date_time')->nullable();
            $table->integer('from_stage_id')->nullable();
            $table->string('from_stage_name')->nullable();
            $table->integer('from_unit_id')->nullable();
            $table->string('from_unit_name')->nullable();
            $table->integer('to_stage_id')->nullable();
            $table->string('to_stage_name')->nullable();
            $table->integer('to_unit_id')->nullable();
            $table->string('to_unit_name')->nullable();
            $table->string('order_no')->nullable();
            $table->string('lot_no')->nullable();
            $table->string('design_number')->nullable();
            $table->integer('color_id')->nullable();
            $table->string('set_size')->nullable();
            $table->integer('single_size')->nullable();
            $table->integer('set_quantity')->nullable();
            $table->integer('single_quantity')->nullable();
            $table->string('allowed_time')->nullable();
            $table->string('time_type')->nullable();
            $table->dateTime('allowed_till_datetime')->nullable();
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
        Schema::dropIfExists('production_slip_digitization_parts');
    }
};
