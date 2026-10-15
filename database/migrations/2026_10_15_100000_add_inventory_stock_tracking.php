<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock tracking for sales:
 *  - agent_order_item_picks: which stock row (rack) every picked box of an order line came from,
 *    so un-picking / deleting a dispatch puts the boxes back exactly where they were.
 *  - agent_order_return_items.rack_id / domestic_inventory_id: where returned boxes were put,
 *    so editing / deleting the return takes them back from the same place.
 *  - an index on production_goods_variants (product + size set): every stock list joins on it.
 */
return new class extends Migration
{
    private function hasIndex(string $table, string $name): bool
    {
        return DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)->where('INDEX_NAME', $name)->exists();
    }

    public function up()
    {
        if (!$this->hasIndex('production_goods_variants', 'pgv_product_size_index')) {
            Schema::table('production_goods_variants', function (Blueprint $table) {
                $table->index(['production_goods_id', 'master_size_measurement_id'], 'pgv_product_size_index');
            });
        }

        if (!Schema::hasTable('agent_order_item_picks')) {
            Schema::create('agent_order_item_picks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('agent_order_item_id');
                $table->unsignedBigInteger('domestic_inventory_id')->nullable();
                $table->unsignedBigInteger('rack_id')->nullable();
                $table->integer('boxes');
                $table->integer('pcs_per_box')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->index('agent_order_item_id');
            });
        }

        Schema::table('agent_order_return_items', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_order_return_items', 'rack_id')) {
                $table->unsignedBigInteger('rack_id')->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('agent_order_return_items', 'domestic_inventory_id')) {
                $table->unsignedBigInteger('domestic_inventory_id')->nullable()->after('item_id');
            }
        });
    }

    public function down()
    {
        if ($this->hasIndex('production_goods_variants', 'pgv_product_size_index')) {
            Schema::table('production_goods_variants', function (Blueprint $table) {
                $table->dropIndex('pgv_product_size_index');
            });
        }
        Schema::dropIfExists('agent_order_item_picks');
        Schema::table('agent_order_return_items', function (Blueprint $table) {
            foreach (['rack_id', 'domestic_inventory_id'] as $col) {
                if (Schema::hasColumn('agent_order_return_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
