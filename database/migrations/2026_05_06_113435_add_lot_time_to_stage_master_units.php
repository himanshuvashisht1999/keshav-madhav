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
        Schema::table('stage_master_units', function (Blueprint $table) {
            $table->integer('lot_time_in_days')->default(1)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stage_master_units', function (Blueprint $table) {
            $table->dropColumn('lot_time_in_days');
        });
    }
};
