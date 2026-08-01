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
            $table->unsignedBigInteger('company_id')->nullable()->after('status');
            $table->string('bill_no')->nullable()->after('company_id');
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
            $table->dropColumn(['company_id', 'bill_no']);
        });
    }
};
