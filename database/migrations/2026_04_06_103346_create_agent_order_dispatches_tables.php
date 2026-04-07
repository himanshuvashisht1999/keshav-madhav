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
        Schema::dropIfExists('agent_order_dispatch_items');
        Schema::dropIfExists('agent_order_dispatches');

        Schema::create('agent_order_dispatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_customer_id');
            $table->unsignedBigInteger('sales_agent_id')->nullable();
            $table->dateTime('dispatch_date')->useCurrent();
            $table->string('lr_no')->nullable();
            $table->string('transport_name')->nullable();
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->decimal('gst_amount', 20, 2)->default(0);
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->string('status')->default('created'); // created, dispatched, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_order_dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_order_dispatch_id');
            $table->unsignedBigInteger('agent_order_id');
            $table->timestamps();
        });

        // Add dispatch_id to agent_orders for easier tracking if needed, 
        // though link table is okay.
    }

    public function down()
    {
        Schema::dropIfExists('agent_order_dispatch_items');
        Schema::dropIfExists('agent_order_dispatches');
    }
};
