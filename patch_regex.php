<?php
$file_path = 'app/Http/Controllers/Admin/AgentOrderController.php';
$content = file_get_contents($file_path);

$regex = "/if \(\\\$request->filled\('to_date'\)\) \{\s*\\\$query->whereDate\('dispatch_date', '<=', \\\$request->to_date\);\s*\}/s";

$replacement = <<<EOT
if (\$request->filled('to_date')) {
            \$query->whereDate('dispatch_date', '<=', \$request->to_date);
        }

        if (\$request->filled('dispatch_type')) {
            \$dispatchType = \$request->dispatch_type;
            \$query->whereHas('orders', function (\$q) use (\$dispatchType) {
                \$q->where(function (\$sub) use (\$dispatchType) {
                    \$sub->where('sale_type', \$dispatchType)
                        ->orWhere('order_type', \$dispatchType);
                });
            });
        }
EOT;

$new_content = preg_replace($regex, $replacement, $content);

if ($new_content === $content) {
    echo "NO MATCH FOUND!\n";
} else {
    file_put_contents($file_path, $new_content);
    echo "PATCH APPLIED SUCCESSFULLY!\n";
}
