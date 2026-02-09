<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->dropColumn(['size_id', 'size_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('domestic_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('size_id')->nullable()->after('color_name');
            $table->string('size_name')->nullable()->after('size_id');
        });
    }
};
