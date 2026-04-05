<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_outflow_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'dead', 'sampling', 'debit'
            $table->unsignedBigInteger('order_main_id')->nullable();
            $table->unsignedBigInteger('slip_id')->nullable();
            $table->unsignedBigInteger('rack_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();
            $table->unsignedBigInteger('fitting_id')->nullable();
            $table->unsignedBigInteger('pattern_id')->nullable();
            $table->unsignedBigInteger('size_id')->nullable();
            $table->integer('quantity');
            $table->decimal('per_piece_amount', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->unsignedBigInteger('responsible_stage_id')->nullable(); // For debit
            $table->unsignedBigInteger('responsible_unit_id')->nullable(); // For debit
            $table->string('barcode')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_outflow_inventories');
    }
};
