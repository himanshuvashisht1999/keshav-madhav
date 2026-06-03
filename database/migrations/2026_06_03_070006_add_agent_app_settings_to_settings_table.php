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
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->integer('agent_app_show_stock')->default(1)->after('gst_order');
            $table->integer('agent_app_allow_over_stock')->default(0)->after('agent_app_show_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['agent_app_show_stock', 'agent_app_allow_over_stock']);
        });
    }
};
