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
            $table->unsignedBigInteger('fabric_receipt_detail_id')->nullable()->after('roll_no');
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
            $table->dropColumn('fabric_receipt_detail_id');
        });
    }
};
