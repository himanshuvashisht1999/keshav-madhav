<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_main', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('sku');
            $table->date('po_date')->nullable()->after('po_number');
        });
    }

    public function down()
    {
        Schema::table('order_main', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'po_date']);
        });
    }
};
