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
            $table->string('order_type')->default('normal')->after('status');
            $table->string('sale_type')->default('item')->after('order_type');
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
            $table->dropColumn(['order_type', 'sale_type']);
        });
    }
};
