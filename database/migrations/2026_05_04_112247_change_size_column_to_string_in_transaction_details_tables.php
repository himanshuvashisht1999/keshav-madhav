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
        Schema::table('order_godam_stage_transaction_details', function (Blueprint $table) {
            $table->string('size', 255)->nullable()->change();
        });
        Schema::table('order_printing_stage_transaction_details', function (Blueprint $table) {
            $table->string('size', 255)->nullable()->change();
        });
        Schema::table('order_printing_to_stiching_transaction_details', function (Blueprint $table) {
            $table->string('size', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_godam_stage_transaction_details', function (Blueprint $table) {
            $table->integer('size')->nullable()->change();
        });
        Schema::table('order_printing_stage_transaction_details', function (Blueprint $table) {
            $table->integer('size')->nullable()->change();
        });
        Schema::table('order_printing_to_stiching_transaction_details', function (Blueprint $table) {
            $table->integer('size')->nullable()->change();
        });
    }
};
