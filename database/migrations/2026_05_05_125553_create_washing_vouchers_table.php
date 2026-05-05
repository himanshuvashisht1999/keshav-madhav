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
        Schema::create('washing_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('washing_master_id');
            $table->unsignedBigInteger('order_lot_id')->nullable();
            $table->date('voucher_date');
            $table->string('voucher_number')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('gst', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('round_off', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('document')->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('washing_master_id')->references('id')->on('washing_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('washing_vouchers');
    }
};
