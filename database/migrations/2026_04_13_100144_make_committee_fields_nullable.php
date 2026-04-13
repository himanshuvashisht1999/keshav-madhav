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
        DB::statement('ALTER TABLE committees MODIFY amount DECIMAL(15,2) NULL');
        DB::statement('ALTER TABLE committees MODIFY period VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE committees MODIFY amount DECIMAL(15,2) NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE committees MODIFY period VARCHAR(255) NOT NULL');
    }
};
