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
        Schema::create('fabric_return_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fabric_return_id');
            $table->unsignedBigInteger('fabric_receipt_detail_id');
            $table->unsignedBigInteger('fabric_id');
            $table->decimal('return_meter', 15, 2);
            $table->decimal('price_per_meter', 15, 2);
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
        Schema::dropIfExists('fabric_return_details');
    }
};
