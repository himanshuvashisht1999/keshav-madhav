<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn(['pattern_id', 'fitting_id']);
        });

        Schema::table('production_outflow_inventories', function (Blueprint $table) {
            $table->dropColumn(['pattern_id']);
        });

        Schema::table('sampling_inventories', function (Blueprint $table) {
            $table->dropColumn(['pattern_id', 'fitting_id']);
        });

        Schema::table('dead_stock_inventories', function (Blueprint $table) {
            $table->dropColumn(['pattern_id', 'fitting_id']);
        });

        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->dropColumn(['old_pattern_id', 'old_fitting_id', 'new_pattern_id', 'new_fitting_id']);
        });
        
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->dropColumn(['pattern_id', 'fitting_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            //
        });
    }
};
