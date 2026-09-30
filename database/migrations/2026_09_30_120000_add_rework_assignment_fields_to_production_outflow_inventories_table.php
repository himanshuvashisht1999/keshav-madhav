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
        Schema::table('production_outflow_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('production_outflow_inventories', 'assigned_stage_id')) {
                $table->unsignedBigInteger('assigned_stage_id')->nullable()->after('responsible_unit_id');
            }
            if (!Schema::hasColumn('production_outflow_inventories', 'assigned_unit_id')) {
                $table->unsignedBigInteger('assigned_unit_id')->nullable()->after('assigned_stage_id');
            }
            if (!Schema::hasColumn('production_outflow_inventories', 'assigned_transaction_id')) {
                $table->unsignedBigInteger('assigned_transaction_id')->nullable()->after('assigned_unit_id');
            }
            if (!Schema::hasColumn('production_outflow_inventories', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_transaction_id');
            }
            if (!Schema::hasColumn('production_outflow_inventories', 'assigned_by')) {
                $table->unsignedBigInteger('assigned_by')->nullable()->after('assigned_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_outflow_inventories', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_stage_id',
                'assigned_unit_id',
                'assigned_transaction_id',
                'assigned_at',
                'assigned_by',
            ]);
        });
    }
};
