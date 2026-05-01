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
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->string('gst_type')->default('percentage')->after('sub_total');
            $table->decimal('gst_value', 15, 2)->default(0)->after('gst_type');
        });
    }

    public function down()
    {
        Schema::table('domestic_inventory_purchases', function (Blueprint $table) {
            $table->dropColumn(['gst_type', 'gst_value']);
        });
    }
};
