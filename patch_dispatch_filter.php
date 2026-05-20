<?php

// 1. Patch AgentOrderController.php
$controller_path = 'app/Http/Controllers/Admin/AgentOrderController.php';
$controller_content = file_get_contents($controller_path);

$orig_controller = <<<EOT
        if (\$request->filled('to_date')) {
            \$query->whereDate('dispatch_date', '<=', \$request->to_date);
        }

        \$dispatches = \$query->paginate(20);
EOT;

$new_controller = <<<EOT
        if (\$request->filled('to_date')) {
            \$query->whereDate('dispatch_date', '<=', \$request->to_date);
        }

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

        \$dispatches = \$query->paginate(20);
EOT;

$controller_content = str_replace($orig_controller, $new_controller, $controller_content);
file_put_contents($controller_path, $controller_content);


// 2. Patch index.blade.php
$view_path = 'resources/views/admin/agent_orders/dispatches/index.blade.php';
$view_content = file_get_contents($view_path);

// Replace col-md-3 with col-md-2 for first two, etc.
// Add mb-2 to handle wrapping nicely.
$orig_form = <<<EOT
                        <form action="{{ route('admin.agent-orders.dispatches.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Party</label>
                                <select name="shop_id" class="form-control select2">
EOT;

$new_form = <<<EOT
                        <form action="{{ route('admin.agent-orders.dispatches.index') }}" method="GET" class="row align-items-end">
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Filter by Party</label>
                                <select name="shop_id" class="form-control select2">
EOT;
$view_content = str_replace($orig_form, $new_form, $view_content);

$orig_vendor = <<<EOT
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted font-weight-bold">Filter by Vendor</label>
                                <select name="vendor_id" class="form-control select2">
EOT;
$new_vendor = <<<EOT
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Filter by Vendor</label>
                                <select name="vendor_id" class="form-control select2">
EOT;
$view_content = str_replace($orig_vendor, $new_vendor, $view_content);

$orig_from_date = <<<EOT
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">From Date</label>
EOT;
$new_from_date = <<<EOT
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">Item/Fabric Search</label>
                                <input type="text" name="item_search" class="form-control" value="{{ request('item_search') }}" placeholder="Name/Design">
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">From Date</label>
EOT;
$view_content = str_replace($orig_from_date, $new_from_date, $view_content);

$orig_to_date = <<<EOT
                            </div>
                            <div class="col-md-2">
                                <label class="small text-muted font-weight-bold">To Date</label>
EOT;
$new_to_date = <<<EOT
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="small text-muted font-weight-bold">To Date</label>
EOT;
$view_content = str_replace($orig_to_date, $new_to_date, $view_content);

$orig_btn = <<<EOT
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-block">
EOT;
$new_btn = <<<EOT
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary btn-block">
EOT;
$view_content = str_replace($orig_btn, $new_btn, $view_content);

file_put_contents($view_path, $view_content);

echo "Patch applied successfully\n";
