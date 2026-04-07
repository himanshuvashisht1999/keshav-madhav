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
            $table->integer('scanned_box_qty')->default(0)->after('box_qty');
            $table->integer('scanned_quantity')->default(0)->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->dropColumn(['scanned_box_qty', 'scanned_quantity']);
        });
    }
};
