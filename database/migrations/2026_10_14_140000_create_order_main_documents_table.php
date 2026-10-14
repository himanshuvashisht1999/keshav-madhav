<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/** Documents attached to a sales order (order_main). Same definition as OrderDocumentService::ensureTable(). */
return new class extends Migration
{
    public function up()
    {
        \App\Services\Admin\OrderDocumentService::ensureTable();
    }

    public function down()
    {
        Schema::dropIfExists('order_main_documents');
    }
};
