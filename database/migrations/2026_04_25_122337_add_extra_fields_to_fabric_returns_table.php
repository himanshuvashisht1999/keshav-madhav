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
        Schema::table('fabric_returns', function (Blueprint $table) {
            $table->decimal('sub_total', 15, 2)->default(0)->after('remarks');
            $table->decimal('gst_percentage', 5, 2)->default(0)->after('sub_total');
            $table->decimal('gst_amount', 15, 2)->default(0)->after('gst_percentage');
            $table->decimal('discount', 15, 2)->default(0)->after('gst_amount');
            $table->decimal('other_charges', 15, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fabric_returns', function (Blueprint $table) {
            //
        });
    }
};
