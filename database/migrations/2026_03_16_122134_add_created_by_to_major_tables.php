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
        $tables = [
            'purchase_orders',
            'agent_orders',
            'order_main',
            'fabric_receipts',
            'production_fabric_roll_assigning',
            'production_goods',
            'order_dispatch',
            'packing_mains',
            'stocks',
            'item_receipts',
            'order_cutting_stage',
            'order_printing_stage_transactions',
            'order_godam_stage_transactions',
            'order_stage_transactions',
            'production_slip_digitization'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'created_by')) {
                        $table->unsignedBigInteger('created_by')->nullable();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'purchase_orders',
            'agent_orders',
            'order_main',
            'fabric_receipts',
            'production_fabric_roll_assigning',
            'production_goods',
            'order_dispatch',
            'packing_mains',
            'stocks',
            'item_receipts',
            'order_cutting_stage',
            'order_printing_stage_transactions',
            'order_godam_stage_transactions',
            'order_stage_transactions',
            'production_slip_digitization'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'created_by')) {
                        $table->dropColumn('created_by');
                    }
                });
            }
        }
    }
};
