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
            $table->unsignedBigInteger('size_set_id')->nullable()->after('size_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn('size_set_id');
        });
    }
};
