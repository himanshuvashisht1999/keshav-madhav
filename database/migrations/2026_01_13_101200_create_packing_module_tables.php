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
        // 1. Packing Main Table
        Schema::dropIfExists('packing_items');
        Schema::dropIfExists('packing_boxes');
        Schema::dropIfExists('packing_cartons');
        Schema::dropIfExists('packing_mains');
        Schema::create('packing_mains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_main_id');
            $table->unsignedBigInteger('slip_id'); // From production_slip_digitization
            $table->date('packing_date');
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(0); // 0: Draft, 1: Finalized
            $table->timestamps();

            // Foreign Keys
            // $table->foreign('order_main_id')->references('id')->on('order_main')->onDelete('cascade');
             // $table->foreign('slip_id')->references('id')->on('production_slip_digitization'); // Optional
        });

        // 2. Packing Cartons Table
        Schema::create('packing_cartons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('packing_main_id');
            $table->string('carton_no')->nullable(); 
            $table->string('barcode')->nullable();
            $table->decimal('weight', 8, 2)->nullable(); // kg
            $table->string('dimensions')->nullable(); // LxWxH
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('packing_main_id')->references('id')->on('packing_mains')->onDelete('cascade');
        });

        // 3. Packing Boxes Table
        Schema::create('packing_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('packing_main_id');
            $table->unsignedBigInteger('packing_carton_id')->nullable(); // Nullable because box might be created before putting in carton
            $table->string('box_no')->nullable();
            $table->string('box_type')->default('mixed'); // mixed, solid
            $table->timestamps();

            $table->foreign('packing_main_id')->references('id')->on('packing_mains')->onDelete('cascade');
            $table->foreign('packing_carton_id')->references('id')->on('packing_cartons')->onDelete('cascade');
        });

        // 4. Packing Items Table
        Schema::create('packing_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('packing_main_id');
            $table->unsignedBigInteger('packing_carton_id')->nullable(); // Can be directly in carton
            $table->unsignedBigInteger('packing_box_id')->nullable();    // Or in a box
            $table->unsignedBigInteger('size_id'); // master_size_measurements id
            $table->integer('quantity');
            $table->timestamps();

            $table->foreign('packing_main_id')->references('id')->on('packing_mains')->onDelete('cascade');
            $table->foreign('packing_carton_id')->references('id')->on('packing_cartons')->onDelete('cascade');
            $table->foreign('packing_box_id')->references('id')->on('packing_boxes')->onDelete('cascade');
            // $table->foreign('size_id')->references('id')->on('master_size_measurements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('packing_items');
        Schema::dropIfExists('packing_boxes');
        Schema::dropIfExists('packing_cartons');
        Schema::dropIfExists('packing_mains');
    }
};
