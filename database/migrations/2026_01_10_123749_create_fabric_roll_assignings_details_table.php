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
        Schema::create('production_fabric_roll_assigning_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_fabric_roll_assigning_id'); // Link to main roll assignment
            $table->unsignedBigInteger('order_product_set_detail_id')->nullable(); // Link to specific size master data
            $table->string('size');
            $table->decimal('quantity', 10, 2);
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('production_fabric_roll_assigning_details');
    }
};
