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
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->decimal('discount_percentage', 12, 6)->change();
            $table->decimal('gst_percentage', 12, 6)->change();
        });

        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->decimal('gst_percentage', 12, 6)->nullable()->change();
            $table->decimal('discount_percentage', 12, 6)->nullable()->change();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('gst_order', 12, 6)->change();
        });

        Schema::table('agent_order_returns', function (Blueprint $table) {
            $table->decimal('gst_percentage', 12, 6)->nullable()->change();
            $table->decimal('discount_percentage', 12, 6)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->decimal('discount_percentage', 8, 2)->change();
            $table->decimal('gst_percentage', 8, 2)->change();
        });

        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->decimal('gst_percentage', 8, 2)->nullable()->change();
            $table->decimal('discount_percentage', 8, 2)->nullable()->change();
        });

        Schema::table('agent_order_returns', function (Blueprint $table) {
            $table->decimal('gst_percentage', 8, 2)->nullable()->change();
            $table->decimal('discount_percentage', 8, 2)->nullable()->change();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('gst_order', 8, 2)->change();
        });
    }
};
