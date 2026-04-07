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
        Schema::table('production_goods_variant_colors', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('master_color_id');
        });

        // Populate existing records
        $items = \DB::table('production_goods_variant_colors')
            ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
            ->join('production_goods', 'production_goods_variants.production_goods_id', '=', 'production_goods.id')
            ->select(
                'production_goods_variant_colors.id',
                'production_goods.id as product_id',
                'production_goods_variants.master_size_measurement_id as size_set_id',
                'production_goods_variant_colors.master_color_id as color_id',
                'production_goods.master_pattern_id as pattern_id',
                'production_goods.master_product_fitting_id as fitting_id'
            )->get();

        foreach ($items as $item) {
            $barcode = 'D' . $item->product_id . 'S' . $item->size_set_id . 'C' . $item->color_id . 'P' . $item->pattern_id . 'F' . $item->fitting_id;
            \DB::table('production_goods_variant_colors')
                ->where('id', $item->id)
                ->update(['barcode' => $barcode]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('production_goods_variant_colors', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
