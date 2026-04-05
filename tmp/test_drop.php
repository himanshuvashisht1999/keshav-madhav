<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    Schema::table('domestic_inventories', function (Blueprint $table) {
        $table->dropColumn('product_name');
    });
    echo "Success dropping product_name\n";
} catch (\Exception $e) {
    echo "Error dropping product_name: " . $e->getMessage() . "\n";
}
