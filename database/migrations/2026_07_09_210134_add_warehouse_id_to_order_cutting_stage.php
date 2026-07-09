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
        Schema::table('order_cutting_stage', function (Blueprint $table) {
            $table->integer('warehouse_id')->nullable()->after('to_assign_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_cutting_stage', function (Blueprint $table) {
            $table->dropColumn('warehouse_id');
        });
    }
};
