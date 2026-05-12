<?php
// C:\xampp\htdocs\keshav-madhav\database\migrations/2026_05_12_105911_create_fabric_inventory_histories_table.php

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
        Schema::create('fabric_inventory_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('fabric_id')->nullable();
            $table->unsignedBigInteger('old_warehouse_id')->nullable();
            $table->unsignedBigInteger('new_warehouse_id')->nullable();
            $table->string('roll_number')->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->string('type')->default('transfer'); // creation, transfer, disposal, consume
            $table->string('reference_id')->nullable(); // e.g. purchase_id, transfer_id
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabric_inventory_histories');
    }
};
