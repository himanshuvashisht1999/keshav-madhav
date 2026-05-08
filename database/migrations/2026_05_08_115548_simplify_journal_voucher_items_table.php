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
        Schema::table('journal_voucher_items', function (Blueprint $table) {
            $table->dropColumn(['debit_amount', 'credit_amount']);
            $table->decimal('amount', 15, 2)->after('master_id');
            $table->string('type')->after('amount'); // debit, credit
        });
    }

    public function down()
    {
        Schema::table('journal_voucher_items', function (Blueprint $table) {
            $table->dropColumn(['amount', 'type']);
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
        });
    }
};
