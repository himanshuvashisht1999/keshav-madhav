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
        Schema::table('agent_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_order_items', 'box_qty')) {
                $table->integer('box_qty')->default(0)->after('quantity');
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
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->dropColumn('box_qty');
        });
    }
};
