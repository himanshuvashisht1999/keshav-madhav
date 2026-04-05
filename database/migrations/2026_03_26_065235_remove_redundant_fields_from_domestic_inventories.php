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
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn([
                'product_name',
                'color_name',
                'size_set_name',
                'design_number',
                'fitting_name',
                'pattern_name',
                'mrp',
                'selling_price',
                'price'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->string('product_name')->nullable();
            $table->string('color_name')->nullable();
            $table->string('size_set_name')->nullable();
            $table->string('design_number')->nullable();
            $table->string('fitting_name')->nullable();
            $table->string('pattern_name')->nullable();
            $table->decimal('mrp', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
        });
    }
};
