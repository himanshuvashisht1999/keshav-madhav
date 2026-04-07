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
        if (!Schema::hasColumn('agent_order_items', 'agent_order_dispatch_id')) {
            Schema::table('agent_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('agent_order_dispatch_id')->nullable()->after('agent_order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->dropColumn('agent_order_dispatch_id');
        });
    }
};
