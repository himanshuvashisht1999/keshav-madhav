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
    public function up()
    {
        Schema::table('deletion_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('deletion_logs', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('deleted_by');
            }
            if (!Schema::hasColumn('deletion_logs', 'restored_by')) {
                $table->unsignedBigInteger('restored_by')->nullable()->after('restored_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deletion_logs', function (Blueprint $table) {
            if (Schema::hasColumn('deletion_logs', 'restored_by')) {
                $table->dropColumn('restored_by');
            }
            if (Schema::hasColumn('deletion_logs', 'restored_at')) {
                $table->dropColumn('restored_at');
            }
        });
    }
};
