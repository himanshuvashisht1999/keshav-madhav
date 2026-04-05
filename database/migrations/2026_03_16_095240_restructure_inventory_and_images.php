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
        // 1. Rename the images table if the old name exists
        if (Schema::hasTable('inventory_price_images') && !Schema::hasTable('domestic_inventory_images')) {
            Schema::rename('inventory_price_images', 'domestic_inventory_images');
        }

        $tableName = 'domestic_inventory_images';

        // 2. Add columns if they don't exist
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn($tableName, 'color_id')) {
                $table->unsignedBigInteger('color_id')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn($tableName, 'size_set_id')) {
                $table->unsignedBigInteger('size_set_id')->nullable()->after('color_id');
            }
            if (!Schema::hasColumn($tableName, 'fitting_id')) {
                $table->unsignedBigInteger('fitting_id')->nullable()->after('size_set_id');
            }
            if (!Schema::hasColumn($tableName, 'pattern_id')) {
                $table->unsignedBigInteger('pattern_id')->nullable()->after('fitting_id');
            }
        });

        // 3. Data Migration: Map images to their variations if inventory_prices table still exists
        if (Schema::hasTable('inventory_prices')) {
            DB::table($tableName)
                ->join('inventory_prices', $tableName . '.inventory_price_id', '=', 'inventory_prices.id')
                ->update([
                    $tableName . '.product_id' => DB::raw('inventory_prices.design_id'),
                    $tableName . '.color_id' => DB::raw('inventory_prices.color_id'),
                    $tableName . '.size_set_id' => DB::raw('inventory_prices.size_set_id'),
                    $tableName . '.fitting_id' => DB::raw('inventory_prices.fitting_id'),
                    $tableName . '.pattern_id' => DB::raw('inventory_prices.pattern_id'),
                ]);
        }

        // 4. Remove the old foreign key and column
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'inventory_price_id')) {
                // Try to drop foreign key first. We use a raw statement to be sure about the name rename issue
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `inventory_price_images_inventory_price_id_foreign` ");
                } catch (\Exception $e) {
                    // Might already be dropped or have a different name
                    try {
                        $table->dropForeign(['inventory_price_id']);
                    } catch (\Exception $ex) {}
                }
                $table->dropColumn('inventory_price_id');
            }
        });

        // 5. Drop the inventory_prices table
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('inventory_prices');
        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        // Not easily reversible without manual data reconstruction
    }
};
