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
            $table->string('series_name')->nullable()->after('name_of_garment');
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
            $table->dropColumn('series_name');
        });
    }
};
