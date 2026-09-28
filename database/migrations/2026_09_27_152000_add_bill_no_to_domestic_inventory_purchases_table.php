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
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->string('bill_no')->nullable()->after('production_po_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->dropColumn('bill_no');
        });
    }
};
