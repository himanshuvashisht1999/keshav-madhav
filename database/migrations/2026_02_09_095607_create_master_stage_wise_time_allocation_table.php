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
        Schema::create('master_stage_wise_time_allocation', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('production_slip_digitization_id')->nullable();
            $table->dateTime('start_date_time')->nullable();
            $table->integer('lot_no')->nullable();
            $table->string('stage_id_1')->nullable();
            $table->string('stage_id_2')->nullable();
            $table->string('stage_id_3')->nullable();
            $table->string('stage_id_4')->nullable();
            $table->string('stage_id_5')->nullable();
            $table->string('stage_id_6')->nullable();
            $table->string('stage_id_7')->nullable();
            $table->string('stage_id_8')->nullable();
            $table->string('stage_id_9')->nullable();
            $table->string('stage_id_10')->nullable();
            $table->string('stage_id_11')->nullable();
            $table->string('stage_id_12')->nullable();
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
        Schema::dropIfExists('master_stage_wise_time_allocation');
    }
};
