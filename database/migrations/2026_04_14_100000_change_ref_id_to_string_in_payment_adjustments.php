<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE payment_adjustments MODIFY ref_id VARCHAR(255)');
    }

    public function down()
    {
        DB::statement('ALTER TABLE payment_adjustments MODIFY ref_id BIGINT UNSIGNED');
    }
};
