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
        Schema::table('contractor_voucher_items', function (Blueprint $table) {
            $table->unsignedBigInteger('order_lot_id')->nullable()->after('contractor_voucher_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('contractor_voucher_items', function (Blueprint $table) {
            $table->dropColumn('order_lot_id');
        });
    }
};
