<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// 1. Fix the undefined $customerName in the Fabric Rolls loop
$oldLoop = "foreach (\$qRolls->get() as \$item) {
                    \$sku = \$item->order_no ?? \$item->orderProductSet->orderMain->sku ?? '-';
                    if (\$sku === '-' && !empty(\$item->lot_no)) {
                        \$lRef = \App\Models\OrderLot::where('lot_no', \$item->lot_no)->with('orderMain')->first();
                        \$sku = \$lRef->orderMain->sku ?? \$lRef->order_no ?? '-';
                    }

                    \$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'design_no' => \$item->orderProductSet->design_number ?? '-',
                        'customer' => \$customerName,";

$newLoop = "foreach (\$qRolls->get() as \$item) {
                    \$customerName = \$item->orderProductSet?->orderMain?->customer?->name ?? '-';

                    \$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'design_no' => \$item->orderProductSet->design_number ?? '-',
                        'customer' => \$customerName,";

$content = str_replace($oldLoop, $newLoop, $content);

// 2. Fix remaining $orderNo references in filtering logic
$content = preg_replace('/\'% \. \$orderNo \. %\'/', "'% . \$customerSearch . %'", $content);
// And also check for ones without spaces
$content = str_replace("'%'.\$orderNo.'%'", "'%'.\$customerSearch.'%'", $content);
$content = str_replace("'%'.\$orderNo . '%'", "'%'.\$customerSearch . '%'", $content);
$content = str_replace("'%'. \$orderNo . '%'", "'%'. \$customerSearch . '%'", $content);

// Just replace $orderNo with $customerSearch everywhere in the history method
// To be safe, I'll use a targeted approach for the history method block
// The history method ends around line 600

file_put_contents($file, $content);
echo "Fixed undefined \$customerName and cleaned up \$orderNo references in $file\n";
