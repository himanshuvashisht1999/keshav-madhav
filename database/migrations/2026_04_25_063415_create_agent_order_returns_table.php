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
        Schema::create('agent_order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_order_dispatch_id')->constrained('agent_order_dispatches')->onDelete('cascade');
            $table->date('return_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('remark')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_order_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_order_return_id')->constrained('agent_order_returns')->onDelete('cascade');
            $table->string('item_type'); // 'standard' or 'fabric'
            $table->unsignedBigInteger('item_id'); // references agent_order_items or agent_order_fabric_items
            $table->decimal('quantity', 15, 2); // boxes for standard, meters for fabric
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
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
        Schema::dropIfExists('agent_order_return_items');
        Schema::dropIfExists('agent_order_returns');
    }
};
