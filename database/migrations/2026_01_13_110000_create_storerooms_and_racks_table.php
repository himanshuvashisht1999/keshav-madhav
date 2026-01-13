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
        // 1. Storerooms
        Schema::create('storerooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Racks
        Schema::create('racks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('storeroom_id');
            $table->string('name'); // e.g., 'Rack A', 'Row 1'
            $table->integer('capacity')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('storeroom_id')->references('id')->on('storerooms')->onDelete('cascade');
        });

        // 3. Add rack_id to packing_cartons
        Schema::table('packing_cartons', function (Blueprint $table) {
            $table->unsignedBigInteger('rack_id')->nullable()->after('carton_no');
            $table->foreign('rack_id')->references('id')->on('racks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packing_cartons', function (Blueprint $table) {
            $table->dropForeign(['rack_id']);
            $table->dropColumn('rack_id');
        });
        Schema::dropIfExists('racks');
        Schema::dropIfExists('storerooms');
    }
};
