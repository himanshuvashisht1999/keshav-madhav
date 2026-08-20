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
        Schema::table('production_outflow_inventories', function (Blueprint $table) {
            $table->string('status')->default('active')->after('remarks');
        });

        Schema::create('production_outflow_disposes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_outflow_inventory_id');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('production_outflow_inventory_id', 'poi_foreign')
                  ->references('id')
                  ->on('production_outflow_inventories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_outflow_disposes');
        
        Schema::table('production_outflow_inventories', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
