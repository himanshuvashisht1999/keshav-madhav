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
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->decimal('other_charges', 15, 2)->default(0)->after('gst_amount');
        });
    }

    public function down()
    {
        Schema::table('agent_order_dispatches', function (Blueprint $table) {
            $table->dropColumn('other_charges');
        });
    }
};
