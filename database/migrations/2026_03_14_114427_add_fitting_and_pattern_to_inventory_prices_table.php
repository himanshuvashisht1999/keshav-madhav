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
        Schema::table('inventory_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('fitting_id')->nullable()->after('size_set_id');
            $table->unsignedBigInteger('pattern_id')->nullable()->after('fitting_id');
            
            $table->foreign('fitting_id')->references('id')->on('master_product_fittings')->onDelete('cascade');
            $table->foreign('pattern_id')->references('id')->on('master_design_patterns')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('inventory_prices', function (Blueprint $table) {
            $table->dropForeign(['fitting_id']);
            $table->dropForeign(['pattern_id']);
            $table->dropColumn(['fitting_id', 'pattern_id']);
        });
    }
};
