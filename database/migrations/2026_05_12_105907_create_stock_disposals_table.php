<?php
// C:\xampp\htdocs\keshav-madhav\database\migrations/2026_05_12_105907_create_stock_disposals_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_disposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('item_type'); // 'fabric' or 'box'
            $table->unsignedBigInteger('item_id'); // fabric_receipt_detail_id or domestic_inventory_id
            $table->string('barcode')->nullable();
            $table->decimal('quantity', 15, 2)->default(0); // Meter for fabric, boxes for inventory
            $table->string('reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_disposals');
    }
};
