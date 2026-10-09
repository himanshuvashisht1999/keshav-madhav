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
        Schema::table('fabric_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('fabric_returns', 'vendor_id')) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('return_number');
            }
        });

        \DB::statement('ALTER TABLE fabric_returns MODIFY fabric_receipt_id BIGINT UNSIGNED NULL');

        \DB::statement("UPDATE fabric_returns fr 
            JOIN fabric_receipts r ON fr.fabric_receipt_id = r.id 
            SET fr.vendor_id = r.vendor_id 
            WHERE fr.vendor_id IS NULL");
    }

    public function down()
    {
        Schema::table('fabric_returns', function (Blueprint $table) {
            if (Schema::hasColumn('fabric_returns', 'vendor_id')) {
                $table->dropColumn('vendor_id');
            }
        });
    }
};
