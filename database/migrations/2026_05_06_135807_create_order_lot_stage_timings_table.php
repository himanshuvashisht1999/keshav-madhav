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
        Schema::create('order_lot_stage_timings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_lot_id')->nullable()->index();
            $table->string('lot_no')->nullable()->index();
            $table->unsignedInteger('master_stage_id')->index();
            $table->unsignedInteger('unit_id')->nullable()->index();
            $table->decimal('days_allocated', 8, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->dateTime('complete_date')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Pending, 1: Ongoing, 2: Completed
            $table->timestamps();

            // Unique constraint to prevent duplicate stage entries for the same lot
            $table->unique(['lot_no', 'master_stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_lot_stage_timings');
    }
};
