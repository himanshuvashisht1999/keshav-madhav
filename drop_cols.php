<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Schema::table('packing_items', function(Blueprint $table) {
    try {
        $table->dropForeign(['packing_box_id']);
    } catch (\Exception $e) {
        echo "Foreign key not found or error: " . $e->getMessage() . "\n";
    }
});

Schema::table('packing_items', function(Blueprint $table) {
    if (Schema::hasColumn('packing_items', 'packing_box_id')) {
        $table->dropColumn('packing_box_id');
        echo "Column packing_box_id dropped.\n";
    }
});

Schema::dropIfExists('packing_boxes');
echo "packing_boxes table dropped.\n";
