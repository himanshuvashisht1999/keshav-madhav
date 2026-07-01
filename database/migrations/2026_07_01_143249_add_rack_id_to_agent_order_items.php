<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_order_items', 'rack_id')) {
                $table->unsignedBigInteger('rack_id')->nullable()->after('agent_order_dispatch_id');
            }
        });
    }

    public function down()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('agent_order_items', 'rack_id')) {
                $table->dropColumn('rack_id');
            }
        });
    }
};
