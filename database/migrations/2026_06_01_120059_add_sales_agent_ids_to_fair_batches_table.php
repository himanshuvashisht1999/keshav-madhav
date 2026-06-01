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
        Schema::table('fair_batches', function (Blueprint $table) {
            $table->json('sales_agent_ids')->nullable()->after('batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fair_batches', function (Blueprint $table) {
            $table->dropColumn('sales_agent_ids');
        });
    }
};
