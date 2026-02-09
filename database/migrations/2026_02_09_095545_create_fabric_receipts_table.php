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
        Schema::create('fabric_receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('shipment_id')->nullable();
            $table->integer('vendor_id')->nullable();
            $table->string('truck_number')->nullable();
            $table->dateTime('time')->nullable()->default('current_timestamp()');
            $table->integer('roll')->nullable();
            $table->string('received_by')->nullable();
            $table->string('shipment_photo')->nullable();
            $table->string('challan_photo')->nullable();
            $table->integer('master_fabric_warehouse_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('gst_amount', 10, 2)->nullable();
            $table->decimal('gst_percentage', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
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
        Schema::dropIfExists('fabric_receipts');
    }
};
