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
        Schema::table('production_slip_digitization', function (Blueprint $table) {
            $table->string('bill_number')->nullable()->after('lot_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_slip_digitization', function (Blueprint $table) {
            $table->dropColumn('bill_number');
        });
    }
};
