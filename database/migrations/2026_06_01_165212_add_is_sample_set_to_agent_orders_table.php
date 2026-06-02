<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->boolean('is_sample_set')->default(0)->after('sale_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_orders', function (Blueprint $table) {
            $table->dropColumn('is_sample_set');
        });
    }
};
