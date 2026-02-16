<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->decimal('gst_percentage', 5, 2)->default(5.00)->after('total_quantity');
            $table->decimal('discount_percentage', 5, 2)->default(0.00)->after('gst_percentage');
            $table->decimal('total_amount', 15, 2)->default(0.00)->after('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->dropColumn(['gst_percentage', 'discount_percentage', 'total_amount']);
        });
    }
};
