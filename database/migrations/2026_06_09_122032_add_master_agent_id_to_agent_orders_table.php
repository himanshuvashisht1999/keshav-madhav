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
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('master_agent_id')->nullable()->after('sales_agent_id');
            $table->foreign('master_agent_id')->references('id')->on('sales_agents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->dropForeign(['master_agent_id']);
            $table->dropColumn('master_agent_id');
        });
    }
};
