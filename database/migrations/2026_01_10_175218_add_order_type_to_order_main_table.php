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
        Schema::table('order_main', function (Blueprint $table) {
            $table->enum('order_type', ['domestic', 'corporate'])->default('domestic')->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_main', function (Blueprint $table) {
            $table->dropColumn('order_type');
        });
    }
};
