<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_debits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_main_id')->nullable();
            $table->unsignedBigInteger('slip_id')->nullable(); // Slip where identified
            $table->unsignedBigInteger('stage_id')->nullable(); // Stage responsible
            $table->unsignedBigInteger('unit_id')->nullable(); // Unit responsible
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('size_id')->nullable();
            $table->integer('quantity');
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_debits');
    }
};
