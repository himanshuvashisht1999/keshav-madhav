<?php

$files = [
    'resources/views/owner/party-ledger/index.blade.php',
    'resources/views/owner/party-ledger/show.blade.php',
    'resources/views/owner/reports/inventory.blade.php',
    'resources/views/owner/reports/lots.blade.php',
    'resources/views/owner/reports/payment_adjustment_details.blade.php',
    'resources/views/owner/reports/payment_adjustments.blade.php',
    'resources/views/owner/reports/selling_items.blade.php',
    'resources/views/owner/reports/stock.blade.php',
    'resources/views/owner/reports/stock_roll_details.blade.php',
    'resources/views/owner/reports/stock_rolls.blade.php',
    'resources/views/owner/reports/unit_assignments.blade.php',
    'resources/views/owner/reports/orders.blade.php',
    'resources/views/owner/reports/order_summary_index.blade.php',
    'resources/views/owner/reports/order_summary_view.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Remove local --primary-gradient so it falls back to app.blade.php
        $content = preg_replace('/\s*--primary-gradient:[^;]+;/i', '', $content);
        
        // Replace slate dark text colors and primary colors with var(--text-main)
        // Note: we leave alone explicit #ef4444 (red) and #10b981 (green) for financials/status 
        // as the user requested to keep red/green.
        $content = preg_replace('/#(0f172a|1e293b|334155|475569|1e3a8a|3b82f6|2563eb|4f46e5|0ea5e9|0284c7|ec4899|be185d|6366f1|a855f7|f59e0b|d97706|8b5cf6|6f42c1)/i', 'var(--text-main)', $content);
        
        // Replace slate muted text colors with var(--text-muted)
        $content = preg_replace('/#(64748b|94a3b8|cbd5e1)/i', 'var(--text-muted)', $content);
        
        // Also replace inline linear-gradient definitions to use var(--primary-gradient) or var(--accent-gradient)
        // Some headers have inline styles like style="background: linear-gradient(135deg, ...)"
        $content = preg_replace('/linear-gradient\([^)]+\)/i', 'var(--primary-gradient)', $content);
        
        // Ensure that text-muted tailwind classes in these files are safely rendering the new color.
        // Tailwind text utility classes like text-slate-800, text-gray-700
        $content = preg_replace('/text-(slate|gray|blue|indigo|purple|pink|yellow|orange)-(700|800|900)/i', 'text-dark', $content);
        $content = preg_replace('/text-(slate|gray|blue|indigo|purple|pink|yellow|orange)-(400|500|600)/i', 'text-muted', $content);
        
        file_put_contents($file, $content);
        echo $file . " updated.\n";
    }
}
