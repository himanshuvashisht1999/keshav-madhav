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
        Schema::table('agent_order_returns', function (Blueprint $table) {
            $table->decimal('gst_percentage', 5, 2)->default(5)->after('total_amount');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_amount');
            $table->decimal('other_charges', 15, 2)->default(0)->after('gst_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_returns', function (Blueprint $table) {
            $table->dropColumn(['gst_percentage', 'discount_percentage', 'other_charges']);
        });
    }
};
