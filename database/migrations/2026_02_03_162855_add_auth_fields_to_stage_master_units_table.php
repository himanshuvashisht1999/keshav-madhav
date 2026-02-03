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
        Schema::table('stage_master_units', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->after('phone');
            $table->string('password')->nullable()->after('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stage_master_units', function (Blueprint $table) {
            $table->dropColumn('employee_id');
            $table->dropColumn('password');
        });
    }
};
