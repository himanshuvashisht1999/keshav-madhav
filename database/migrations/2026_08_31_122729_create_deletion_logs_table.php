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
        Schema::create('deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module')->index();
            $table->unsignedBigInteger('record_id')->nullable()->index();
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deletion_logs');
    }
};
