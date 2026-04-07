<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->integer('total_boxes')->default(1)->after('quantity');
        });

        // Consolidate existing data (Keep one row per unique barcode/rack/order combination)
        // Grouping criteria: Product, Color, Fitting, Pattern, Size Set, Barcode, Rack, Order ID
        $inventories = DB::table('domestic_inventories')
            ->select(
                'product_id', 'color_id', 'fitting_id', 'pattern_id', 'size_set_id', 
                'barcode', 'rack_id', 'order_main_id', 'quantity', 'status',
                DB::raw('COUNT(*) as box_count'),
                DB::raw('MIN(id) as first_id')
            )
            ->groupBy(
                'product_id', 'color_id', 'fitting_id', 'pattern_id', 'size_set_id', 
                'barcode', 'rack_id', 'order_main_id', 'quantity', 'status'
            )
            ->get();

        foreach ($inventories as $inv) {
            if ($inv->box_count > 1) {
                // Update the first row with the total count
                DB::table('domestic_inventories')
                    ->where('id', $inv->first_id)
                    ->update(['total_boxes' => $inv->box_count]);

                // Delete the redundant rows
                DB::table('domestic_inventories')
                    ->where('product_id', $inv->product_id)
                    ->where('color_id', $inv->color_id)
                    ->where('size_set_id', $inv->size_set_id)
                    ->where('barcode', $inv->barcode)
                    ->where('rack_id', $inv->rack_id)
                    ->where('order_main_id', $inv->order_main_id)
                    ->where('id', '!=', $inv->first_id)
                    ->delete();
            }
        }

        // Drop the images table as requested
        Schema::dropIfExists('domestic_inventory_images');
    }

    public function down()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn('total_boxes');
        });
    }
};
