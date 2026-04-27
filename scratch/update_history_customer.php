<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// 1. Rename variable
$content = str_replace('$orderNo = $request->input(\'order_no\');', '$customerSearch = $request->input(\'customer\');', $content);
$content = str_replace('if ($orderNo)', 'if ($customerSearch)', $content);

// 2. Update filtering in Assignments (Cutting Only)
$oldAssign = "\$qAssign->whereHas('productSet.orderMain', function (\$qs) use (\$orderNo) {
                            \$qs->where('sku', 'like', '%' . \$orderNo . '%'); });";
$newAssign = "\$qAssign->whereHas('productSet.orderMain.customer', function (\$qs) use (\$customerSearch) {
                            \$qs->where('name', 'like', '%' . \$customerSearch . '%'); });";
$content = str_replace($oldAssign, $newAssign, $content);

// 3. Update task creation (Cutting Received)
$oldTaskCut = "'order_no' => \$item->productSet->orderMain->sku ?? '-',";
$newTaskCut = "'customer' => \$item->productSet->orderMain->customer->name ?? '-',";
$content = str_replace($oldTaskCut, $newTaskCut, $content);

// 4. Update filtering in Transactions (Received)
$oldTxFilter = "\$q->where(function (\$sq) use (\$orderNo) {
                            \$sq->where('sku', 'like', '%' . \$orderNo . '%')
                                ->orWhereHas('orderProduct.orderMain', function (\$ssq) use (\$orderNo) {
                                    \$ssq->where('sku', 'like', '%' . \$orderNo . '%'); });
                        });";
$newTxFilter = "\$q->whereHas('orderProduct.orderMain.customer', function (\$ssq) use (\$customerSearch) {
                            \$ssq->where('name', 'like', '%' . \$customerSearch . '%'); });";
$content = str_replace($oldTxFilter, $newTxFilter, $content);

// 5. Update task creation (Received Transactions)
$oldTaskTx = "'order_no' => \$sku,";
$newTaskTx = "'customer' => \$item->orderProduct?->orderMain?->customer?->name ?? '-',";
// Note: This might replace multiple instances, which is fine if they are all order_no -> customer.
$content = str_replace($oldTaskTx, $newTaskTx, $content);

// 6. Update filtering in Rolls (Sent)
$oldRollsFilter = "if (\$customerSearch) {
                    \$qRolls->where(function (\$sq) use (\$customerSearch) {
                        \$sq->where('order_no', 'like', '%' . \$customerSearch . '%')
                            ->orWhereHas('orderProductSet.orderMain', function (\$ssq) use (\$customerSearch) {
                                \$ssq->where('sku', 'like', '%' . \$customerSearch . '%'); });
                    });
                }";
$newRollsFilter = "if (\$customerSearch) {
                    \$qRolls->whereHas('orderProductSet.orderMain.customer', function (\$ssq) use (\$customerSearch) {
                        \$ssq->where('name', 'like', '%' . \$customerSearch . '%');
                    });
                }";
$content = str_replace($oldRollsFilter, $newRollsFilter, $content);

// 7. Update task creation (Sent)
$oldSentLoop = "foreach (\$qRolls->get() as \$item) {
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
                        'order_no' => \$sku,";
$newSentLoop = "foreach (\$qRolls->get() as \$item) {
                    \$customerName = \$item->orderProductSet->orderMain->customer->name ?? '-';

                    \$tasks->push([
                        'id' => \$item->id,
                        'event_type' => 'sent',
                        'type' => 'fabric',
                        'lot_no' => \$item->lot_no ?? '-',
                        'customer' => \$customerName,";
$content = str_replace($oldSentLoop, $newSentLoop, $content);

// 8. Update Fabric Slips Logic
$oldFabricSlipsFilter = "if (\$customerSearch) {
            \$fabricQuery->where('order_no', 'like', '%' . \$customerSearch . '%');
        }";
$newFabricSlipsFilter = "if (\$customerSearch) {
            \$fabricQuery->whereHas('orderProductSet.orderMain.customer', function (\$q) use (\$customerSearch) {
                \$q->where('name', 'like', '%' . \$customerSearch . '%');
            });
        }";
$content = str_replace($oldFabricSlipsFilter, $newFabricSlipsFilter, $content);

// 9. Update Fabric Slips Data
$oldFabricSlipData = "'order_no' => \$slip->order_no ?? '-',";
$newFabricSlipData = "'customer' => \$slip->orderProductSet?->orderMain?->customer?->name ?? '-',";
$content = str_replace($oldFabricSlipData, $newFabricSlipData, $content);

// 10. Update Production Slips Filter
$oldProductionSlipsFilter = "if (\$customerSearch) {
            \$productionQuery->where(function (\$q) use (\$customerSearch) {
                \$q->whereHas('orderProductSet.orderMain', function (\$sq) use (\$customerSearch) {
                    \$sq->where('sku', 'like', '%' . \$customerSearch . '%');
                })
                    ->orWhereHas('orderLots.orderMain', function (\$sq) use (\$customerSearch) {
                        \$sq->where('sku', 'like', '%' . \$customerSearch . '%');
                    })
                    ->orWhereHas('orderPrintingStageTransaction.orderProduct.orderProductSet.size_measurement',
                'orderPrintingStageTransaction.orderProduct.orderProductSet.orderMain', function (\$sq) use (\$customerSearch) {
                        \$sq->where('sku', 'like', '%' . \$customerSearch . '%');
                    });
            });
        }";
$newProductionSlipsFilter = "if (\$customerSearch) {
            \$productionQuery->where(function (\$q) use (\$customerSearch) {
                \$q->whereHas('orderProductSet.orderMain.customer', function (\$sq) use (\$customerSearch) {
                    \$sq->where('name', 'like', '%' . \$customerSearch . '%');
                })
                ->orWhereHas('orderLots.orderMain.customer', function (\$sq) use (\$customerSearch) {
                    \$sq->where('name', 'like', '%' . \$customerSearch . '%');
                });
            });
        }";
$content = str_replace($oldProductionSlipsFilter, $newProductionSlipsFilter, $content);

file_put_contents($file, $content);
echo "Updated $file\n";
