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
        Schema::table('production_goods', function (Blueprint $table) {
            $table->unsignedBigInteger('product_nature_id')->nullable()->after('type_of_garment');
            $table->unsignedBigInteger('fabric_type_id')->nullable()->after('product_nature_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_goods', function (Blueprint $table) {
            $table->dropColumn(['product_nature_id', 'fabric_type_id']);
        });
    }
};
