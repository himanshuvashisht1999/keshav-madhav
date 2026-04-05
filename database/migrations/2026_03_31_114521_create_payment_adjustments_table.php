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
        Schema::create('payment_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adjustment_master_id');
            $table->unsignedBigInteger('ref_id');
            $table->string('type'); // credit, debit
            $table->string('payment_mode'); // bank, cash
            $table->unsignedBigInteger('payment_account_id');
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('payment_adjustments');
    }
};
