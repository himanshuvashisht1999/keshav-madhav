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
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('fitting_id')->nullable()->after('size_set_id');
            $table->string('fitting_name')->nullable()->after('fitting_id');
            $table->unsignedBigInteger('pattern_id')->nullable()->after('fitting_name');
            $table->string('pattern_name')->nullable()->after('pattern_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_order_items', function (Blueprint $table) {
            $table->dropColumn(['fitting_id', 'fitting_name', 'pattern_id', 'pattern_name']);
        });
    }
};
