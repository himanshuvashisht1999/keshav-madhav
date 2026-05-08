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
        Schema::table('order_cutting_stage', function (Blueprint $table) {
            $table->integer('vendor_id')->nullable()->after('to_assign_id');
            $table->integer('customer_id')->nullable()->after('vendor_id');
            $table->tinyInteger('is_po')->default(0)->after('customer_id');
            $table->decimal('rate', 15, 2)->default(0)->after('is_po');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_cutting_stage', function (Blueprint $table) {
            $table->dropColumn(['vendor_id', 'customer_id', 'is_po', 'rate']);
        });
    }
};
