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
            'order_products_sets',
            'order_stage_transactions',
            'order_printing_stage_transactions',
            'order_printing_to_stiching_transactions',
            'order_godam_stage_transactions'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dateTime('start_date')->nullable()->after('status');
                $table->dateTime('end_date')->nullable()->after('start_date');
                $table->dateTime('complete_date')->nullable()->after('end_date');
            });
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
            'order_products_sets',
            'order_stage_transactions',
            'order_printing_stage_transactions',
            'order_printing_to_stiching_transactions',
            'order_godam_stage_transactions'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['start_date', 'end_date', 'complete_date']);
            });
        }
    }
};
