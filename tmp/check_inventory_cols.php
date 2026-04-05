<?php
use Illuminate\Support\Facades\DB;
$cols = DB::getSchemaBuilder()->getColumnListing('domestic_inventories');
echo implode(', ', $cols);
