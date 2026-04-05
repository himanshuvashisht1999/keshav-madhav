<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_capitals', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method_type')->comment('Bank or Cash');
            $table->unsignedBigInteger('payment_method_id');
            $table->date('transaction_date');
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
        Schema::dropIfExists('company_capitals');
    }
};
