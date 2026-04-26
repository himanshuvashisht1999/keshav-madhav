<?php

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
        Schema::create('domestic_inventory_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            
            // Old attributes
            $table->unsignedBigInteger('old_product_id')->nullable();
            $table->unsignedBigInteger('old_size_set_id')->nullable();
            $table->unsignedBigInteger('old_color_id')->nullable();
            $table->unsignedBigInteger('old_fitting_id')->nullable();
            $table->unsignedBigInteger('old_pattern_id')->nullable();
            $table->unsignedBigInteger('old_rack_id')->nullable();
            
            // New attributes
            $table->unsignedBigInteger('new_product_id')->nullable();
            $table->unsignedBigInteger('new_size_set_id')->nullable();
            $table->unsignedBigInteger('new_color_id')->nullable();
            $table->unsignedBigInteger('new_fitting_id')->nullable();
            $table->unsignedBigInteger('new_pattern_id')->nullable();
            $table->unsignedBigInteger('new_rack_id')->nullable();
            
            $table->integer('box_quantity')->default(0);
            $table->string('type')->default('attribute_change');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domestic_inventory_histories');
    }
};
