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
        Schema::create('order_lots', function (Blueprint $table) {
            $table->id();
            $table->integer('order_main_id')->nullable();
            $table->integer('order_products_set_id')->nullable();
            $table->integer('production_slip_digitization_id')->nullable();
            $table->string('lot_no')->nullable();
            $table->integer('is_printing')->default('0');
            $table->integer('is_stitching')->default('0');
            $table->dateTime('production_datetime')->default('current_timestamp()');
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
        Schema::dropIfExists('order_lots');
    }
};
