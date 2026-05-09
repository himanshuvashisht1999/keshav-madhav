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
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('production_po_id')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->dropColumn('production_po_id');
        });
    }
};
