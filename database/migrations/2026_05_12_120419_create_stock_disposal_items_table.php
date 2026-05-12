<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_disposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_disposal_main_id')->constrained('stock_disposal_mains')->onDelete('cascade');
            $table->unsignedBigInteger('item_id'); // Reference to fabric_receipt_details or domestic_inventories
            $table->string('barcode')->nullable();
            $table->decimal('quantity', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_disposal_items');
    }
};
