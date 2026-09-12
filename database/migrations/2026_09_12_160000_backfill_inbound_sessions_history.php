<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Call the backfill artisan command to populate all missing historical inbound session records
        Artisan::call('inventory:backfill-inbound-sessions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reversal is not required as it only backfills missing audit records
    }
};
