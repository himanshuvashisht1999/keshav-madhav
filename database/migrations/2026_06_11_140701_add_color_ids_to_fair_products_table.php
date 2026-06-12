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
        Schema::table('fair_products', function (Blueprint $table) {
            $table->json('color_ids')->nullable()->after('size_set_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fair_products', function (Blueprint $table) {
            $table->dropColumn('color_ids');
        });
    }
};
