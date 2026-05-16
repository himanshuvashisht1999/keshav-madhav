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
        Schema::table('order_products_sets', function (Blueprint $table) {
            $table->tinyInteger('is_printing')->default(0)->after('remark');
            $table->unsignedBigInteger('printing_unit_id')->nullable()->after('is_printing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_products_sets', function (Blueprint $table) {
            $table->dropColumn(['is_printing', 'printing_unit_id']);
        });
    }
};
