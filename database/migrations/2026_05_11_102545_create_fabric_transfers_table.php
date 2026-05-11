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
        Schema::create('fabric_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transfer_no')->unique();
            $table->integer('from_warehouse_id');
            $table->integer('to_warehouse_id');
            $table->date('transfer_date');
            $table->integer('transferred_by');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('from_warehouse_id')->references('id')->on('master_fabric_warehouse');
            $table->foreign('to_warehouse_id')->references('id')->on('master_fabric_warehouse');
            $table->foreign('transferred_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fabric_transfers');
    }
};
