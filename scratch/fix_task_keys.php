<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// 1. Fix filtering logic (Variable name mismatch)
$content = str_replace('use ($orderNo)', 'use ($customerSearch)', $content);

// 2. Fix task data structure for all push operations in history method

// A. Assignments (Cutting Received)
$oldA = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'received',
                            'type' => 'cutting',
                            'lot_no' => \$item->productSet->design_number ?? '-',
                            'customer' => \$item->productSet->orderMain->customer->name ?? '-',
                            'from_stage' => 'Admin Assignment',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => (\$item->status == 1 || \$item->image) ? 1 : 0
                        ]);";
$newA = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'received',
                            'type' => 'cutting',
                            'lot_no' => '-', // Assignments don't have a lot no yet
                            'design_no' => \$item->productSet->design_number ?? '-',
                            'customer' => \$item->productSet->orderMain->customer->name ?? '-',
                            'order_no' => \$item->productSet->orderMain->sku ?? '-',
                            'size_sets' => \$item->productSet->size_set_name ?? '-',
                            'from_stage' => 'Admin Assignment',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => (\$item->status == 1 || \$item->image) ? 1 : 0
                        ]);";
$content = str_replace($oldA, $newA, $content);

// B. Received Transactions
$oldB = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'received',
                            'type' => \$txTypes[\$idx],
                            'lot_no' => \$item->lot_no ?? '-',
                            'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'from_stage' => \$item->from_stage->name ?? '-',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => (\$item->image) ? 1 : 0
                        ]);";
$newB = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'received',
                            'type' => \$txTypes[\$idx],
                            'lot_no' => \$item->lot_no ?? '-',
                            'design_no' => \$item->orderProduct?->orderProductSet?->design_number ?? '-',
                            'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'order_no' => \$item->orderProduct?->orderMain?->sku ?? '-',
                            'size_sets' => \$item->orderProduct?->orderProductSet?->size_set_name ?? '-',
                            'from_stage' => \$item->from_stage->name ?? '-',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => (\$item->image) ? 1 : 0
                        ]);";
$content = str_replace($oldB, $newB, $content);

// C. Fabric Rolls (Sent)
$oldC = "                    \$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'customer' => \$customerName,
                        'from_stage' => 'Rolls Alotted',
                        'quantity' => \$item->fabricRollAssigningsDetail->sum('quantity') ?: 0,
                        'created_at' => \$item->created_at,
                        'status' => 1
                    ]);";
$newC = "                    \$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'design_no' => \$item->orderProductSet->design_number ?? '-',
                        'customer' => \$customerName,
                        'order_no' => \$item->orderProductSet->orderMain->sku ?? '-',
                        'size_sets' => \$item->orderProductSet->size_set_name ?? '-',
                        'from_stage' => 'Rolls Alotted',
                        'quantity' => \$item->fabricRollAssigningsDetail->sum('quantity') ?: 0,
                        'created_at' => \$item->created_at,
                        'status' => 1
                    ]);";
$content = str_replace($oldC, $newC, $content);

// D. Sent Transactions
$oldD = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'sent',
                            'type' => \$txTypes[\$idx],
                            'lot_no' => \$item->lot_no ?? '-',
                            'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'from_stage' => \$item->to_stage->name ?? 'Next Stage',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => 1
                        ]);";
$newD = "                        \$tasks->push([
                            'id' => \$item->id,
                            'event_type' => 'sent',
                            'type' => \$txTypes[\$idx],
                            'lot_no' => \$item->lot_no ?? '-',
                            'design_no' => \$item->orderProduct?->orderProductSet?->design_number ?? '-',
                            'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',
                            'order_no' => \$item->orderProduct?->orderMain?->sku ?? '-',
                            'size_sets' => \$item->orderProduct?->orderProductSet?->size_set_name ?? '-',
                            'from_stage' => \$item->to_stage->name ?? 'Next Stage',
                            'quantity' => \$item->quantity,
                            'created_at' => \$item->created_at,
                            'status' => 1
                        ]);";
$content = str_replace($oldD, $newD, $content);

file_put_contents($file, $content);
echo "Fixed Task Data structure in $file\n";
