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
        Schema::create('consumable_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consumable_good_id');
            $table->date('voucher_date');
            $table->string('voucher_number')->nullable();
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('gst', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('consumable_good_id')->references('id')->on('consumable_goods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumable_vouchers');
    }
};
