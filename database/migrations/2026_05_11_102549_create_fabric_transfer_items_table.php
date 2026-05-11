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
        Schema::create('fabric_transfer_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fabric_transfer_id');
            $table->integer('fabric_receipt_detail_id');
            $table->integer('fabric_id');
            $table->decimal('meter', 15, 2);
            $table->timestamps();

            $table->foreign('fabric_transfer_id')->references('id')->on('fabric_transfers')->onDelete('cascade');
            $table->foreign('fabric_receipt_detail_id')->references('id')->on('fabric_receipt_details');
            $table->foreign('fabric_id')->references('id')->on('fabrics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fabric_transfer_items');
    }
};
