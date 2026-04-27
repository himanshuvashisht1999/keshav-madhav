<?php
$file = 'app/Http/Controllers/Admin/AgentOrderController.php';
$content = file_get_contents($file);

$oldCode = '        $gst_percentage = $request->gst_percentage ?? 5.00;
        $other_charges = $request->other_charges ?? 0;
        $discount_percentage = $request->discount_percentage ?? 0;
        $discount_amount = ($total_amount * $discount_percentage / 100);
        $taxable_amount = $total_amount - $discount_amount;
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
            $gst_percentage = $request->gst_percentage ?? 5.00;
            $gst_amount = $taxable_amount * ($gst_percentage / 100);
        }

        $grand_total = $taxable_amount + $gst_amount + $other_charges;';

if (strpos($content, '$gst_percentage = $request->gst_percentage ?? 5.00;') !== false) {
    // Try to find the block
    $lines = explode("\n", $content);
    $startLine = -1;
    foreach($lines as $index => $line) {
        if (strpos($line, '$gst_percentage = $request->gst_percentage ?? 5.00;') !== false) {
            $startLine = $index;
            break;
        }
    }
    
    if ($startLine !== -1) {
        array_splice($lines, $startLine, 7, explode("\n", $newCode));
        file_put_contents($file, implode("\n", $lines));
        echo "Successfully updated AgentOrderController.php\n";
    } else {
        echo "Could not find start line exactly.\n";
    }
} else {
    echo "Base code not found.\n";
}
