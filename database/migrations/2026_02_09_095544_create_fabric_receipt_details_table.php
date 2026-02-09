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
        Schema::create('fabric_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('fabric_receipt_id')->nullable();
            $table->integer('purchase_order_id')->nullable();
            $table->integer('purchase_order_item_id')->nullable();
            $table->string('fabric_sku')->nullable();
            $table->integer('fabric_id')->nullable();
            $table->integer('roll')->nullable();
            $table->integer('meter')->nullable();
            $table->integer('remaining_quantity')->nullable();
            $table->string('batch_no')->nullable();
            $table->integer('roll_number')->nullable();
            $table->string('price_per_meter')->nullable();
            $table->integer('master_fabric_warehouse_id')->nullable();
            $table->string('barcode')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('qrcode_number')->nullable();
            $table->string('shipment_number')->nullable();
            $table->integer('status')->default('1');
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
        Schema::dropIfExists('fabric_receipt_details');
    }
};
