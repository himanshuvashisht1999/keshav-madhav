<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// Helper function to replace task push blocks
function replaceTaskPush($content, $type, $isReceived) {
    // This regex looks for the tasks->push([...]) pattern for a specific type
    // It's quite complex because it needs to match the array content
    $pattern = "/\\\$tasks->push\(\[\s*\'id\'\s*=>\s*\\\$item->id,\s*\'event_type\'\s*=>\s*\'" . ($isReceived ? 'received' : 'sent') . "\',\s*\'type\'\s*=>\s*" . ($type === 'tx' ? '\\\$txTypes\[\\\$idx\]' : "\'".$type."\'") . ".*?\s*\]\);/s";
    
    // We want to add design_no, customer, size_sets if missing
    // But since I want to be 100% sure, I will rewrite the whole block if it matches partially
    
    // Let's try a simpler approach: Match the start of the block and the whole array
    if ($type === 'cutting') {
        $replacement = "\$tasks->push([
                            'id' => \$item->id,
                            'event_type' => '" . ($isReceived ? 'received' : 'sent') . "',
                            'type' => 'cutting',
                            'lot_no' => \$item->lot_no ?? '-',
                            'design_no' => \$item->productSet->design_number ?? '-',
                            'customer' => \$item->productSet->orderMain->customer->name ?? '-',
                            'size_sets' => \$item->productSet->size_set_name ?? '-',
                            'from_stage' => " . ($isReceived ? "'Admin Assignment'" : "\$item->stageMasterUnit->name ?? '-'") . ",
                            'quantity' => " . ($isReceived ? "\$item->quantity" : "\$item->fabricRollAssigningsDetail->sum('quantity') ?: 0") . ",
                            'created_at' => \$item->created_at,
                            'status' => " . ($isReceived ? "(\$item->status == 1 || \$item->image) ? 1 : 0" : "1") . "
                        ]);";
        // Search for the existing block for cutting
        $searchPattern = "/\\\$tasks->push\(\[\s*\'id\'\s*=>\s*\\\$item->id,\s*\'event_type\'\s*=>\s*\'" . ($isReceived ? 'received' : 'sent') . "\',\s*\'type\'\s*=>\s*\'cutting\'.*?\]\);/s";
        $content = preg_replace($searchPattern, $replacement, $content);
    } 
    elseif ($type === 'tx') {
        $replacement = "\$tasks->push([
                            'id' => \$item->id,
                            'event_type' => '" . ($isReceived ? 'received' : 'sent') . "',
                            'type' => \$txTypes[\$idx],
                            'lot_no' => \$item->lot_no ?? '-',
                            'design_no' => \$item->orderProduct?->orderProductSet?->design_number ?? '-',
                            'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'size_sets' => \$item->orderProduct?->orderProductSet?->size_set_name ?? '-',
                            'from_stage' => " . ($isReceived ? "\$item->from_stage->name ?? '-'" : "\$item->to_stage->name ?? 'Next Stage'") . ",
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => " . ($isReceived ? "(\$item->image) ? 1 : 0" : "1") . "
                        ]);";
        $searchPattern = "/\\\$tasks->push\(\[\s*\'id\'\s*=>\s*\\\$item->id,\s*\'event_type\'\s*=>\s*\'" . ($isReceived ? 'received' : 'sent') . "\',\s*\'type\'\s*=>\s*\\\$txTypes\[\\\$idx\].*?\]\);/s";
        $content = preg_replace($searchPattern, $replacement, $content);
    }
    elseif ($type === 'fabric') {
         $replacement = "\$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'design_no' => \$item->orderProductSet->design_number ?? '-',
                        'customer' => \$customerName,
                        'size_sets' => \$item->orderProductSet->size_set_name ?? '-',
                        'from_stage' => 'Rolls Alotted',
                        'quantity' => \$item->fabricRollAssigningsDetail->sum('quantity') ?: 0,
                        'created_at' => \$item->created_at,
                        'status' => 1
                    ]);";
        $searchPattern = "/\\\$tasks->push\(\[\s*\'id\'\s*=>\s*\\\$item->id,\s*\'event_type\'\s*=>\s*\'sent\',\s*\'type\'\s*=>\s*\'fabric\'.*?\]\);/s";
        $content = preg_replace($searchPattern, $replacement, $content);
    }

    return $content;
}

$content = replaceTaskPush($content, 'cutting', true);
$content = replaceTaskPush($content, 'tx', true);
$content = replaceTaskPush($content, 'fabric', false);
$content = replaceTaskPush($content, 'tx', false);

// Also fix the filter logic variables once and for all
$content = str_replace('use ($orderNo)', 'use ($customerSearch)', $content);
$content = preg_replace('/if \(\$orderNo\)/', 'if ($customerSearch)', $content);
$content = preg_replace('/\$sq->where\(\'sku\'/', '$sq->where(\'name\'', $content); // Risky but let's see

file_put_contents($file, $content);
echo "Fixed Task Data structure via REGEX\n";
