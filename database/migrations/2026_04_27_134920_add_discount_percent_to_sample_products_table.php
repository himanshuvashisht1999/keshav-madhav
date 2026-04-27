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
        Schema::table('sample_products', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('size_set_id');
        });
    }

    public function down()
    {
        Schema::table('sample_products', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
