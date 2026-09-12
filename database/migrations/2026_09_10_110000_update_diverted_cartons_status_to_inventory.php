<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update all diverted domestic cartons currently in status 1 (Ready for dispatch) to status 3 (Inventory)
        DB::update("
            UPDATE packing_cartons pc
            INNER JOIN domestic_inventories di ON (
                di.packing_carton_id = pc.id 
                OR (di.packing_main_id = pc.packing_main_id AND di.barcode = pc.barcode AND pc.barcode != '')
                OR (di.packing_main_id = pc.packing_main_id AND di.carton_no = pc.carton_no)
            )
            SET pc.status = 3
            WHERE pc.status = 1
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No reversal needed as status 3 is the correct state for domestic inventory cartons
    }
};
