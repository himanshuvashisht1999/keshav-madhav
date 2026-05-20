<?php

// Patch AgentOrderController.php
$controller_path = 'app/Http/Controllers/Admin/AgentOrderController.php';
$controller_content = file_get_contents($controller_path);

$orig_controller_search = <<<EOT
        if (\$request->filled('dispatch_type')) {
            \$dispatchType = \$request->dispatch_type;
            if (\$dispatchType === 'item') {
                \$query->whereExists(function (\$subQ) {
                    \$subQ->select(\Illuminate\Support\Facades\DB::raw(1))
                         ->from('agent_order_items')
                         ->whereColumn('agent_order_items.agent_order_dispatch_id', 'agent_order_dispatches.id');
                });
            } elseif (\$dispatchType === 'fabric') {
                \$query->whereExists(function (\$subQ) {
                    \$subQ->select(\Illuminate\Support\Facades\DB::raw(1))
                         ->from('agent_order_fabric_items')
                         ->whereColumn('agent_order_fabric_items.agent_order_dispatch_id', 'agent_order_dispatches.id');
                });
            }
        }
EOT;

$new_controller_search = <<<EOT
        if (\$request->filled('dispatch_type')) {
            \$dispatchType = \$request->dispatch_type;
            \$query->whereHas('orders', function (\$q) use (\$dispatchType) {
                \$q->where('sale_type', \$dispatchType);
            });
        }
EOT;

$controller_content = str_replace($orig_controller_search, $new_controller_search, $controller_content);
file_put_contents($controller_path, $controller_content);

echo "Patch applied successfully\n";
