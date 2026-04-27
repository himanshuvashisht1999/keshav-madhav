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
            $table->unsignedBigInteger('sample_batch_id')->nullable()->after('id');
            $table->foreign('sample_batch_id')->references('id')->on('sample_batches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sample_products', function (Blueprint $table) {
            $table->dropForeign(['sample_batch_id']);
            $table->dropColumn('sample_batch_id');
        });
    }
};
