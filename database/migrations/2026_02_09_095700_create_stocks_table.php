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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('sno')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('sub_company_id')->nullable();
            $table->integer('project_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('fabric_id')->nullable();
            $table->integer('master_fabric_warehouse_id')->nullable();
            $table->date('date')->nullable();
            $table->string('goods_entry_number')->nullable();
            $table->integer('meter')->nullable()->default('1');
            $table->integer('roll')->default('1');
            $table->integer('purchase_order_id')->nullable();
            $table->integer('status')->default('1');
            $table->integer('roll_no')->nullable();
            $table->string('qrcode')->nullable();
            $table->string('unique_number')->nullable();
            $table->string('batch_no')->nullable();
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
        Schema::dropIfExists('stocks');
    }
};
