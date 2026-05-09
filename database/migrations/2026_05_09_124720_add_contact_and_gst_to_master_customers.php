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
        Schema::table('master_customers', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('name');
            $table->string('gst_number')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_customers', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'gst_number']);
        });
    }
};
