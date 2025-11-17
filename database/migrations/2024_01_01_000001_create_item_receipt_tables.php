<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {

        Schema::create('items_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('vendor_id')->nullable();
            $table->string('truck_number')->nullable();
            $table->dateTime('time')->nullable();
            $table->integer('box')->nullable();
            $table->string('received_by')->nullable();
            $table->string('shipment_photo')->nullable();
            $table->string('challan_photo')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        Schema::create('items_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('item_receipt_id')->nullable();
            $table->integer('purchase_order_id')->nullable();
            $table->integer('purchase_order_item_id')->nullable();
            $table->string('item_attribute_value_sku')->nullable();
            $table->integer('box')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('batch_no')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->foreign('item_receipt_id')->references('id')->on('items_receipts')->onDelete('cascade');
        });

        Schema::create('item_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->date('date')->nullable();
            $table->string('goods_entry_number')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('box')->nullable();
            $table->integer('purchase_order_id')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->integer('box_no')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('unique_number')->nullable();
            $table->string('batch_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_stocks');
        Schema::dropIfExists('items_receipt_details');
        Schema::dropIfExists('items_receipts');
    }
};
