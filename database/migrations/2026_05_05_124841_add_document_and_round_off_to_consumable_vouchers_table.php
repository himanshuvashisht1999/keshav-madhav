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
        Schema::table('consumable_vouchers', function (Blueprint $table) {
            $table->decimal('round_off', 15, 2)->default(0)->after('other_charges');
            $table->string('document')->nullable()->after('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumable_vouchers', function (Blueprint $table) {
            $table->dropColumn(['round_off', 'document']);
        });
    }
};
