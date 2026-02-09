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
        Schema::create('order_printing_stage_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_printing_stage_transaction_id')->nullable();
            $table->integer('size')->nullable();
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('order_printing_stage_transaction_details');
    }
};
