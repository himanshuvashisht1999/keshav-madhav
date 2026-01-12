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
        Schema::create('order_stage_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_stage_transaction_id');
            $table->string('size');
            $table->integer('quantity');
            $table->timestamps();
            
            // Foreign key constraint (shortened name)
            $table->foreign('order_stage_transaction_id', 'ostd_ost_id_foreign')
                  ->references('id')->on('order_stage_transactions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('order_stage_transaction_details');
    }
};
