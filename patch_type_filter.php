<?php

// 1. Patch AgentOrderController.php
$controller_path = 'app/Http/Controllers/Admin/AgentOrderController.php';
$controller_content = file_get_contents($controller_path);

// Find the previously added item_search block
$orig_controller_search = <<<EOT
        if (\$request->filled('item_search')) {
            \$searchTerm = \$request->item_search;
            \$query->where(function (\$q) use (\$searchTerm) {
                // Check if any matching item exists for this dispatch
                \$q->whereExists(function (\$subQ) use (\$searchTerm) {
                    \$subQ->select(\Illuminate\Support\Facades\DB::raw(1))
                         ->from('agent_order_items')
                         ->whereColumn('agent_order_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                         ->where(function (\$itemQ) use (\$searchTerm) {
                             \$itemQ->where('product_name', 'like', '%' . \$searchTerm . '%')
                                   ->orWhere('design_number', 'like', '%' . \$searchTerm . '%');
                         });
                })
                // Check if any matching fabric exists for this dispatch
                ->orWhereExists(function (\$subQ) use (\$searchTerm) {
                    \$subQ->select(\Illuminate\Support\Facades\DB::raw(1))
                         ->from('agent_order_fabric_items')
                         ->join('fabrics', 'agent_order_fabric_items.fabric_id', '=', 'fabrics.id')
                         ->whereColumn('agent_order_fabric_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                         ->where('fabrics.name', 'like', '%' . \$searchTerm . '%');
                });
            });
        }
EOT;

$new_controller_search = <<<EOT
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

$controller_content = str_replace($orig_controller_search, $new_controller_search, $controller_content);
file_put_contents($controller_path, $controller_content);

// 2. Patch index.blade.php
$view_path = 'resources/views/admin/agent_orders/dispatches/index.blade.php';
$view_content = file_get_contents($view_path);

$orig_view_search = <<<EOT
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Item/Fabric Search</label>
                                <input type="text" name="item_search" class="form-control" value="{{ request('item_search') }}" placeholder="Name/Design">
                            </div>
EOT;

$new_view_search = <<<EOT
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Dispatch Type</label>
                                <select name="dispatch_type" class="form-control">
                                    <option value="">All Types</option>
                                    <option value="item" {{ request('dispatch_type') === 'item' ? 'selected' : '' }}>Item</option>
                                    <option value="fabric" {{ request('dispatch_type') === 'fabric' ? 'selected' : '' }}>Fabric</option>
                                </select>
                            </div>
EOT;

$view_content = str_replace($orig_view_search, $new_view_search, $view_content);
file_put_contents($view_path, $view_content);

echo "Patch applied successfully\n";
