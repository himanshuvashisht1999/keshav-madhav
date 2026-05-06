<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\AgentOrderReturn;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Recalculating Returns...\n";
$returns = AgentOrderReturn::all();
foreach ($returns as $return) {
    if ($return->total_amount > 0) {
        // Calculate implied discount percentage
        if ($return->discount_amount > 0) {
            $new_discount_percent = ($return->discount_amount / $return->total_amount) * 100;
            $return->discount_percentage = $new_discount_percent;
        }
        
        // Calculate implied GST percentage
        $taxable = $return->total_amount - ($return->discount_amount ?? 0);
        if ($return->gst_amount > 0 && $taxable > 0) {
            $new_gst_percent = ($return->gst_amount / $taxable) * 100;
            $return->gst_percentage = $new_gst_percent;
        }
        
        $return->save();
        echo "Return #{$return->id}: Disc % set to {$return->discount_percentage}, GST % set to {$return->gst_percentage}\n";
    }
}

echo "Done!\n";
