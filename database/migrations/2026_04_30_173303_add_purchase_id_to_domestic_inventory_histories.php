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
        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_id')->nullable()->after('vendor_id');
        });
    }

    public function down()
    {
        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->dropColumn('purchase_id');
        });
    }
};
