<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the party ledger (PartyLedgerService / BankLedgerService / balances:check).
 * Every party ledger looks up the party's own rows in these tables; without an index each
 * lookup reads the whole table, once per party. Indexes only: no data is changed.
 *
 * Text columns (varchar 255) are indexed on their first 191 characters, so the index also fits
 * older servers and COMPACT tables (767 byte limit). The stored values are far shorter than 191
 * characters, so the index finds exactly the same rows. A number column is indexed whole.
 *
 * up() checks all 9 indexes first and only then adds the missing ones:
 *  - skipped when the table already has an index (any name) starting with the same columns;
 *  - stops with an error, before adding anything, when a name below is already used by an index
 *    on other columns.
 * MySQL cannot undo an ALTER TABLE, so if the server stops part-way, fix the cause and run it
 * again: the indexes already added are skipped and the rest are added.
 * down() removes an index named here only when it is on exactly the columns below.
 */
return new class extends Migration
{
    /** [table, columns, index name]; 'column(191)' = index the first 191 characters */
    private $indexes = [
        ['payments', ['party_type(191)', 'party_id'], 'payments_party_index'],
        ['journal_voucher_items', ['master_id', 'master_type(191)'], 'jv_items_master_index'],
        ['payment_adjustments', ['adjustment_master_id', 'ref_id(191)'], 'payment_adjustments_party_index'],
        ['agent_order_dispatches', ['master_customer_id'], 'agent_order_dispatches_customer_index'],
        ['agent_order_dispatches', ['master_vendor_id'], 'agent_order_dispatches_vendor_index'],
        ['order_dispatch', ['customer_id'], 'order_dispatch_customer_index'],
        ['domestic_inventory_purchases', ['customer_id'], 'domestic_inventory_purchases_customer_index'],
        ['fabric_receipts', ['vendor_id'], 'fabric_receipts_vendor_index'],
        ['master_customers', ['sales_agent_id'], 'master_customers_sales_agent_index'],
    ];

    /** Column name without the (191) length. */
    private function columnName(string $column): string
    {
        return preg_replace('/\(\d+\)$/', '', $column);
    }

    /** Lower-case column names, in index order. */
    private function columnNames(array $columns): array
    {
        return array_map(fn($c) => strtolower($this->columnName($c)), $columns);
    }

    /** True when the column holds text (only text columns take a (191) length). */
    private function isText(string $table, string $column): bool
    {
        $type = DB::table('information_schema.COLUMNS')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', DB::getTablePrefix() . $table)->where('COLUMN_NAME', $column)->value('DATA_TYPE');
        return in_array(strtolower((string) $type), ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext']);
    }

    /** The table's indexes: lower-case index name => its column names in order (length ignored). */
    private function tableIndexes(string $table): array
    {
        $rows = DB::table('information_schema.STATISTICS')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', DB::getTablePrefix() . $table)->orderBy('INDEX_NAME')->orderBy('SEQ_IN_INDEX')
            ->get(['INDEX_NAME', 'COLUMN_NAME']);
        $indexes = [];
        foreach ($rows as $r) {
            $indexes[strtolower($r->INDEX_NAME)][] = strtolower((string) $r->COLUMN_NAME);
        }
        return $indexes;
    }

    public function up()
    {
        // 1. Check all of them before changing anything
        $toAdd = [];
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (!Schema::hasTable($table) || !Schema::hasColumns($table, array_map(fn($c) => $this->columnName($c), $columns))) {
                continue;
            }
            $wanted = $this->columnNames($columns);
            $existing = $this->tableIndexes($table);
            $sameName = $existing[strtolower($name)] ?? null;
            if ($sameName !== null && $sameName !== $wanted) {
                throw new \RuntimeException("Index $name on $table already exists on other columns ("
                    . implode(', ', $sameName) . '). Nothing was changed: rename or remove that index first.');
            }
            if ($sameName !== null) {
                continue; // already added
            }
            foreach ($existing as $indexColumns) {
                if (array_slice($indexColumns, 0, count($wanted)) === $wanted) {
                    continue 2; // another index already starts with these columns
                }
            }
            // The (191) length only on a text column (if a server keeps the column as a number, the whole column)
            $columns = array_map(fn($c) => $this->isText($table, $this->columnName($c)) ? $c : $this->columnName($c), $columns);
            $toAdd[] = [$table, $columns, $name];
        }

        // 2. Add the missing ones
        $grammar = DB::getQueryGrammar();
        foreach ($toAdd as [$table, $columns, $name]) {
            $list = implode(', ', array_map(function ($c) use ($grammar) {
                $column = $this->columnName($c);
                return $grammar->wrap($column) . substr($c, strlen($column)); // `party_type`(191)
            }, $columns));
            DB::statement('ALTER TABLE ' . $grammar->wrapTable($table) . ' ADD INDEX ' . $grammar->wrap($name) . " ($list)");
        }
    }

    public function down()
    {
        $grammar = DB::getQueryGrammar();
        foreach ($this->indexes as [$table, $columns, $name]) {
            if (!Schema::hasTable($table)) {
                continue;
            }
            // Only our own index: this name on exactly these columns
            if (($this->tableIndexes($table)[strtolower($name)] ?? null) === $this->columnNames($columns)) {
                DB::statement('ALTER TABLE ' . $grammar->wrapTable($table) . ' DROP INDEX ' . $grammar->wrap($name));
            }
        }
    }
};
