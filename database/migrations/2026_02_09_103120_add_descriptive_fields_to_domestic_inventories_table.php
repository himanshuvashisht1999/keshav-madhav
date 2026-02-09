<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('color_name')->nullable()->after('color_id');
            $table->string('size_name')->nullable()->after('size_id');
            $table->string('size_set_name')->nullable()->after('size_name');
            $table->string('design_number')->nullable()->after('size_set_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'color_name', 'size_name', 'size_set_name', 'design_number']);
        });
    }
};
