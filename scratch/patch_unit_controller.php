<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// 1. Fix Printing to Stitching size_sets
$old1 = "'size_sets' => \$optst->details->pluck('size')->unique()->filter()->values()->join(', ') ?: '-',";
$new1 = "'size_sets' => \$optst->orderProduct?->orderProductSet?->size_set_name ?? '-',";
$content = str_replace($old1, $new1, $content);

// 2. Fix viewSlip size_group calculation
$old2 = "        \$standard_sizes = [];
        if (\$orderSet) {
            \$orderSet->loadMissing('size_measurement');
            if (\$orderSet->size_measurement && !empty(\$orderSet->size_measurement->size_group)) {
                \$standard_sizes = array_filter(array_map('trim', explode(',', \$orderSet->size_measurement->size_group)));
            }
        }
        \$summary['size_group'] = count(\$standard_sizes) > 0 ? min(\$standard_sizes) . '-' . max(\$standard_sizes) : '-';";

$new2 = "        \$pcs_in_set = '-';
        if (\$orderSet) {
            \$orderSet->loadMissing('size_measurement');
            if (\$orderSet->size_measurement) {
                \$pcs_in_set = \$orderSet->size_measurement->no_of_pcs ?? '-';
            }
        }
        \$summary['size_group'] = \$orderSet ? \$orderSet->size_set_name : '-';
        \$summary['pcs_in_set'] = \$pcs_in_set;";

// Since str_replace might fail with whitespace, I'll use a more flexible approach if needed, 
// but let's try direct replace first.
$content = str_replace($old2, $new2, $content);

file_put_contents($file, $content);
echo "Updated $file\n";
