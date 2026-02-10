<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdsToAgentOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('carton_no');
            $table->unsignedBigInteger('color_id')->nullable()->after('product_id');
            $table->unsignedBigInteger('size_id')->nullable()->after('color_id');
            $table->unsignedBigInteger('size_set_id')->nullable()->after('size_id');

            $table->index(['agent_order_id', 'product_id', 'color_id', 'size_set_id'], 'variation_index');
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
            $table->dropIndex('variation_index');
            $table->dropColumn(['product_id', 'color_id', 'size_id', 'size_set_id']);
        });
    }
}
