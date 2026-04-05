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
        Schema::table('order_printing_stage_transactions', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('status')->comment('1: Regular, 2: Damage');
        });
        Schema::table('order_printing_to_stiching_transactions', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('status')->comment('1: Regular, 2: Damage');
        });
        Schema::table('order_godam_stage_transactions', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->after('status')->comment('1: Regular, 2: Damage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_printing_stage_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('order_printing_to_stiching_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
        Schema::table('order_godam_stage_transactions', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
