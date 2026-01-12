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
        Schema::table('production_fabric_roll_assigning', function (Blueprint $table) {
            $table->unsignedBigInteger('to_stage_id')->nullable()->after('stage_master_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_fabric_roll_assigning', function (Blueprint $table) {
            $table->dropColumn('to_stage_id');
        });
    }
};
