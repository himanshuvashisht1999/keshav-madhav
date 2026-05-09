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
        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('new_warehouse_id')->nullable()->after('new_rack_id');
            $table->unsignedBigInteger('old_warehouse_id')->nullable()->after('old_rack_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domestic_inventory_histories', function (Blueprint $table) {
            $table->dropColumn(['new_warehouse_id', 'old_warehouse_id']);
        });
    }
};
