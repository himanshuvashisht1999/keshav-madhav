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
        Schema::table('agent_order_fabric_items', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_order_dispatch_id')->nullable()->after('agent_order_id');
            $table->string('status')->default('pending')->after('selling_price');
            $table->timestamp('dispatched_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_fabric_items', function (Blueprint $table) {
            $table->dropColumn(['agent_order_dispatch_id', 'status', 'dispatched_at']);
        });
    }
};
