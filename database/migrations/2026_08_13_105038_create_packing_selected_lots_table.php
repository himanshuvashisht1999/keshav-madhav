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
        Schema::create('packing_selected_lots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('packing_main_id')->nullable();
            $table->unsignedBigInteger('slip_id');
            $table->string('lot_no');
            $table->timestamps();

            $table->foreign('packing_main_id')->references('id')->on('packing_mains')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packing_selected_lots');
    }
};
