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
        DB::statement('ALTER TABLE order_products_sets MODIFY fabric_id VARCHAR(500) NULL');
        DB::statement('ALTER TABLE order_cutting_stage MODIFY fabric_id VARCHAR(500) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE order_products_sets MODIFY fabric_id INT NULL');
        DB::statement('ALTER TABLE order_cutting_stage MODIFY fabric_id INT NULL');
    }
};

