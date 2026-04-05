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
        Schema::table('order_main', function (Blueprint $table) {
            $table->boolean('is_paid')->default(0)->after('status');
        });

        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->boolean('is_paid')->default(0)->after('status');
        });
    }

    public function down()
    {
        Schema::table('order_main', function (Blueprint $table) {
            $table->dropColumn('is_paid');
        });

        Schema::table('order_dispatch', function (Blueprint $table) {
            $table->dropColumn('is_paid');
        });
    }
};
