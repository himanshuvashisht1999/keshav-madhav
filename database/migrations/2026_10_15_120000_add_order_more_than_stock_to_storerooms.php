<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Storeroom setting "Order more than stock" (Yes / No): items kept in such a storeroom can be ordered
 * beyond the boxes in stock (backorder). Until now this was hard-coded to the storeroom named ADVANCE SAMPLE,
 * so that storeroom gets "Yes" here and everything keeps working the same.
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('storerooms', 'order_more_than_stock')) {
            Schema::table('storerooms', function (Blueprint $table) {
                $table->string('order_more_than_stock')->default('No')->after('order_dispatch');
            });
        }
        DB::table('storerooms')->whereRaw("UPPER(TRIM(name)) = 'ADVANCE SAMPLE'")->update(['order_more_than_stock' => 'Yes']);
    }

    public function down()
    {
        if (Schema::hasColumn('storerooms', 'order_more_than_stock')) {
            Schema::table('storerooms', function (Blueprint $table) {
                $table->dropColumn('order_more_than_stock');
            });
        }
    }
};
