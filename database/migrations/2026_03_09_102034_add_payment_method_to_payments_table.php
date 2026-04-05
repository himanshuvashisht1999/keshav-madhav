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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method_type')->nullable()->after('payment_mode');
            $table->unsignedBigInteger('payment_method_id')->nullable()->after('payment_method_type');
            $table->index(['payment_method_type', 'payment_method_id'], 'payment_method_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payment_method_index');
            $table->dropColumn(['payment_method_type', 'payment_method_id']);
        });
    }
};
