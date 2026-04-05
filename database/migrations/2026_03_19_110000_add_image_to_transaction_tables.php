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
        Schema::table('order_printing_to_stiching_transactions', function (Blueprint $table) {
            $table->string('image')->nullable()->after('production_slip_digitization_id');
        });

        Schema::table('order_stage_transactions', function (Blueprint $table) {
            $table->string('image')->nullable()->after('production_slip_digitization_id');
        });

        Schema::table('order_printing_stage_transactions', function (Blueprint $table) {
            $table->string('image')->nullable()->after('production_slip_digitization_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_printing_to_stiching_transactions', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('order_stage_transactions', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('order_printing_stage_transactions', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
