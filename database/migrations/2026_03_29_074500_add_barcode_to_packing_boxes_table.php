<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('packing_boxes', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('box_no');
        });
    }

    public function down()
    {
        Schema::table('packing_boxes', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
