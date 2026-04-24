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
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->string('party_type')->default('customer')->after('master_customer_id');
            $table->unsignedBigInteger('master_vendor_id')->nullable()->after('party_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            //
        });
    }
};
