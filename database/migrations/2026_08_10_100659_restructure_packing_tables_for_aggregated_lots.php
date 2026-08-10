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
    public function up(): void
    {
        // First, optionally clear existing data if the user resets completely, but since it's a live database, we should be careful. 
        // We will modify the packing_items table to add the new columns.
        Schema::table('packing_items', function (Blueprint $table) {
            $table->string('lot_no')->nullable()->after('size_id');
            $table->integer('total_boxes')->default(1)->after('lot_no');
            $table->unsignedBigInteger('rack_id')->nullable()->after('total_boxes');
            // We can drop the foreign key and column for packing_box_id if it exists.
            // But sometimes dropForeign throws errors if name is not standard. We will just drop the column if it exists.
        });

        if (Schema::hasColumn('packing_items', 'packing_box_id')) {
            Schema::table('packing_items', function (Blueprint $table) {
                // Ignore errors if foreign key doesn't exist by catching it or just dropping it directly.
                // In Laravel, dropping a foreign key is best done by array if it follows conventions.
                $table->dropForeign(['packing_box_id']);
                $table->dropColumn('packing_box_id');
            });
        }

        // Drop the packing_boxes table
        Schema::dropIfExists('packing_boxes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('packing_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_main_id')->nullable();
            $table->foreignId('packing_carton_id')->nullable();
            $table->string('box_no')->nullable();
            $table->string('barcode')->nullable();
            $table->string('box_type')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });

        Schema::table('packing_items', function (Blueprint $table) {
            $table->dropColumn('lot_no');
            $table->dropColumn('total_boxes');
            $table->dropColumn('rack_id');
            $table->foreignId('packing_box_id')->nullable()->after('packing_carton_id');
        });
    }
};
