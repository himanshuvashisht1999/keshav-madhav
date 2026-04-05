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
    public function up()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('branch_name');
        });

        Schema::table('cash_payments', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('balance');
        });

        Schema::table('cash_payments', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
