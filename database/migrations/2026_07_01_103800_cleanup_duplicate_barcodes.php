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
        // Query duplicate barcodes in production_goods_variant_colors
        $duplicates = DB::select("
            SELECT barcode, COUNT(*) as cnt 
            FROM production_goods_variant_colors 
            WHERE barcode IS NOT NULL AND barcode != '' 
            GROUP BY barcode 
            HAVING cnt > 1
        ");

        DB::transaction(function() use ($duplicates) {
            foreach ($duplicates as $dup) {
                $bc = $dup->barcode;
                $rows = DB::select("SELECT * FROM production_goods_variant_colors WHERE barcode = ?", [$bc]);
                
                // Determine the best row to keep: prioritize having an image, then lower ID
                usort($rows, function($a, $b) {
                    $aHasImage = !empty($a->image);
                    $bHasImage = !empty($b->image);
                    if ($aHasImage && !$bHasImage) return -1;
                    if (!$aHasImage && $bHasImage) return 1;
                    return $a->id - $b->id;
                });
                
                $keepRow = $rows[0];
                $deleteRows = array_slice($rows, 1);
                
                foreach ($deleteRows as $del) {
                    DB::delete("DELETE FROM production_goods_variant_colors WHERE id = ?", [$del->id]);
                }
            }
            
            // Clean up empty variants (variants with no colors)
            $emptyVariants = DB::select("
                SELECT pgv.id 
                FROM production_goods_variants pgv
                LEFT JOIN production_goods_variant_colors pgvc ON pgv.id = pgvc.variant_id
                WHERE pgvc.id IS NULL
            ");
            
            foreach ($emptyVariants as $ev) {
                DB::delete("DELETE FROM production_goods_variants WHERE id = ?", [$ev->id]);
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
        // One-way migration, no down action possible
    }
};
