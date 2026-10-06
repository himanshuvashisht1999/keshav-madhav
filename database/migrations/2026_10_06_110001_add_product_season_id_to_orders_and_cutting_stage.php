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
        if (Schema::hasTable('order_cutting_stage') && !Schema::hasColumn('order_cutting_stage', 'product_season_id')) {
            Schema::table('order_cutting_stage', function (Blueprint $table) {
                $table->unsignedBigInteger('product_season_id')->nullable()->after('master_pattern_id');
            });
        }

        if (Schema::hasTable('order_products_sets') && !Schema::hasColumn('order_products_sets', 'product_season_id')) {
            Schema::table('order_products_sets', function (Blueprint $table) {
                $table->unsignedBigInteger('product_season_id')->nullable()->after('master_design_pattern_id');
            });
        }

        if (Schema::hasTable('order_main') && !Schema::hasColumn('order_main', 'product_season_id')) {
            Schema::table('order_main', function (Blueprint $table) {
                $table->unsignedBigInteger('product_season_id')->nullable()->after('master_customer_id');
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
        if (Schema::hasTable('order_cutting_stage') && Schema::hasColumn('order_cutting_stage', 'product_season_id')) {
            Schema::table('order_cutting_stage', function (Blueprint $table) {
                $table->dropColumn('product_season_id');
            });
        }

        if (Schema::hasTable('order_products_sets') && Schema::hasColumn('order_products_sets', 'product_season_id')) {
            Schema::table('order_products_sets', function (Blueprint $table) {
                $table->dropColumn('product_season_id');
            });
        }

        if (Schema::hasTable('order_main') && Schema::hasColumn('order_main', 'product_season_id')) {
            Schema::table('order_main', function (Blueprint $table) {
                $table->dropColumn('product_season_id');
            });
        }
    }
};
