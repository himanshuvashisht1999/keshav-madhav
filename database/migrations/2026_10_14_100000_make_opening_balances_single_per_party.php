<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opening balance = the party's starting balance, one row per party, not per financial year.
 *
 * - Refuses to run if any party still has more than one row (e.g. a past carry-forward on live):
 *   those must be reviewed by hand first, otherwise the wrong amount could be kept.
 * - Stores the fixed key 'OPENING' in the legacy financial_year column and adds a unique key
 *   (master_type, master_id) so a second row can never appear again.
 */
return new class extends Migration
{
    public function up()
    {
        $dupes = DB::table('payment_opening_balances')
            ->select('master_type', 'master_id', DB::raw('COUNT(*) as n'), DB::raw("GROUP_CONCAT(CONCAT(financial_year, '=', amount, ' ', balance_type) SEPARATOR ' | ') as rows_found"))
            ->groupBy('master_type', 'master_id')
            ->having('n', '>', 1)
            ->get();

        if ($dupes->isNotEmpty()) {
            $list = $dupes->take(30)->map(function ($d) {
                return "{$d->master_type} #{$d->master_id}: {$d->rows_found}";
            })->implode("\n");
            throw new RuntimeException(
                "Cannot make opening balances single: {$dupes->count()} part(ies) have more than one opening row.\n"
                . "Decide which amount is correct for each, delete the others, then run the migration again.\n" . $list
            );
        }

        DB::table('payment_opening_balances')->update(['financial_year' => 'OPENING']);

        Schema::table('payment_opening_balances', function (Blueprint $table) {
            $table->unique(['master_type', 'master_id'], 'opening_balance_one_per_party');
        });
    }

    public function down()
    {
        Schema::table('payment_opening_balances', function (Blueprint $table) {
            $table->dropUnique('opening_balance_one_per_party');
        });
        // Original year labels are not restorable per row; leave the key value in place.
    }
};
