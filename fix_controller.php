<?php
$file = 'app/Http/Controllers/Admin/PackingController.php';
$content = file_get_contents($file);

$target = <<<EOF
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    'order_stage_transactions.quantity',
                    'order_stage_transactions.remaining_quantity'
                )
EOF;
$replacement = <<<EOF
                ->select(
                    'order_stage_transactions.lot_no',
                    'order_products_sets.id as set_id',
                    'order_products_sets.design_number',
                    'master_size_measurements.name as size_set_name',
                    'order_stage_transactions.quantity',
                    'order_stage_transactions.remaining_quantity'
                )
EOF;

// Replace \r\n with \n in strings if necessary to match file content
$target = str_replace("\r\n", "\n", $target);
$content = str_replace("\r\n", "\n", $content);
$replacement = str_replace("\r\n", "\n", $replacement);

$content = str_replace($target, $replacement, $content);
file_put_contents($file, $content);
echo "Updated controller\n";
?>
