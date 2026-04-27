<?php
$file = 'app/Http/Controllers/Admin/AgentOrderController.php';
$content = file_get_contents($file);

$oldCode = '        $discount_percentage = $request->discount_percentage ?? 0;
        $other_charges = $request->other_charges ?? 0;
        $discount_amount = ($total_amount * $discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;

        $gst_percentage = $request->has(\'gst_percentage\') ? $request->gst_percentage : ($order->gst_percentage ?: 5.00);

        $gst_amount = $taxable_amount * ($gst_percentage / 100);
        $grand_total = $taxable_amount + $gst_amount + $other_charges;';

$newCode = '        $other_charges = $request->other_charges ?? 0;

        if ($request->filled(\'discount_amount\')) {
            $discount_amount = (float) $request->discount_amount;
            $discount_percentage = ($total_amount > 0) ? ($discount_amount / $total_amount * 100) : 0;
        } else {
            $discount_percentage = $request->discount_percentage ?? 0;
            $discount_amount = ($total_amount * $discount_percentage / 100);
        }

        $taxable_amount = $total_amount - $discount_amount;

        if ($request->filled(\'gst_amount\')) {
            $gst_amount = (float) $request->gst_amount;
            $gst_percentage = ($taxable_amount > 0) ? ($gst_amount / $taxable_amount * 100) : 0;
        } else {
            $gst_percentage = $request->has(\'gst_percentage\') ? $request->gst_percentage : ($order->gst_percentage ?: 5.00);
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
        }

        $grand_total = $taxable_amount + $gst_amount + $other_charges;';

if (strpos($content, '$discount_percentage = $request->discount_percentage ?? 0;') !== false) {
    // Find the occurrence within the update() method
    // We know update starts around line 1022
    $lines = explode("\n", $content);
    $startLine = -1;
    foreach($lines as $index => $line) {
        if ($index > 1022 && strpos($line, '$discount_percentage = $request->discount_percentage ?? 0;') !== false) {
            $startLine = $index;
            break;
        }
    }
    
    if ($startLine !== -1) {
        array_splice($lines, $startLine, 9, explode("\n", $newCode));
        file_put_contents($file, implode("\n", $lines));
        echo "Successfully updated update() method in AgentOrderController.php\n";
    } else {
        echo "Could not find start line in update() method.\n";
    }
} else {
    echo "Base code not found in AgentOrderController.php\n";
}
