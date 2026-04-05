<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$columns = [
    'product_name',
    'color_name',
    'size_set_name',
    'design_number',
    'fitting_name',
    'pattern_name',
    'mrp',
    'selling_price',
    'price'
];

foreach ($columns as $column) {
    try {
        if (Schema::hasColumn('domestic_inventories', $column)) {
            Schema::table('domestic_inventories', function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
            echo "Successfully dropped $column\n";
        } else {
            echo "Column $column does not exist\n";
        }
    } catch (\Exception $e) {
        echo "Error dropping $column: " . $e->getMessage() . "\n";
    }
}
