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
        Schema::table('packing_cartons', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->comment('1=Packed, 2=Dispatched');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packing_cartons', function (Blueprint $table) {
            //
        });
    }
};
