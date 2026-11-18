<?php

use App\Services\LotPhotoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Photos a Stitching unit sends for one of its lots from the worker app (/unit > Lot Photo).
 * They are shown on the lot to every stage except Cutting and Printing, and in the admin lot report.
 * (LotPhotoService::ensureTable() creates the same table on first use, so no manual step is needed.)
 */
return new class extends Migration
{
    public function up()
    {
        LotPhotoService::ensureTable();
    }

    public function down()
    {
        Schema::dropIfExists('lot_photos');
    }
};
