<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per order booked offline in the sales agent app: the phone's own order id (local_id) and the order it became.
 * The unique local_id makes uploads idempotent: a retry or a second tap can never create a second order.
 * (OfflineOrderController::ensureTable() creates the same table on first use, so no manual step is needed.)
 */
return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('agent_offline_orders')) {
            return;
        }
        Schema::create('agent_offline_orders', function (Blueprint $table) {
            $table->id();
            $table->string('local_id', 64)->unique();
            $table->unsignedBigInteger('sales_agent_id')->index();
            $table->unsignedBigInteger('agent_order_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_offline_orders');
    }
};
