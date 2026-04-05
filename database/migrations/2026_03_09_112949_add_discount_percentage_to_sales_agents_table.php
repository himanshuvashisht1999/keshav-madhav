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
        Schema::table('sales_agents', function (Blueprint $table) {
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sales_agents', function (Blueprint $table) {
            $table->dropColumn('discount_percentage');
        });
    }
};
