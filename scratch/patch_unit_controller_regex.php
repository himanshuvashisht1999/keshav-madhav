<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// Regex for Printing to Stitching
$pattern1 = "/'size_sets'\s*=>\s*\\\$optst->details->pluck\('size'\)->unique\(\)->filter\(\)->values\(\)->join\(\', \'\)\s*\?\:\s*\'-\',/";
$replacement1 = "'size_sets' => \$optst->orderProduct?->orderProductSet?->size_set_name ?? '-',";
$content = preg_replace($pattern1, $replacement1, $content);

// Regex for viewSlip block
$pattern2 = "/\\\$standard_sizes\s*=\s*\[\];\s*if\s*\(\\\$orderSet\)\s*\{\s*\\\$orderSet->loadMissing\(\'size_measurement\'\);\s*if\s*\(\\\$orderSet->size_measurement\s*&&\s*!empty\(\\\$orderSet->size_measurement->size_group\)\)\s*\{\s*\\\$standard_sizes\s*=\s*array_filter\(array_map\(\'trim\',\s*explode\(\',\',\s*\\\$orderSet->size_measurement->size_group\)\)\);\s*\}\s*\}\s*\\\$summary\[\'size_group\'\]\s*=\s*count\(\\\$standard_sizes\)\s*>\s*0\s*\?\s*min\(\\\$standard_sizes\)\s*\.\s*\'-\'\s*\.\s*max\(\\\$standard_sizes\)\s*:\s*\'-\';/s";

$replacement2 = "\$pcs_in_set = '-';
        if (\$orderSet) {
            \$orderSet->loadMissing('size_measurement');
            if (\$orderSet->size_measurement) {
                \$pcs_in_set = \$orderSet->size_measurement->no_of_pcs ?? '-';
            }
        }
        \$summary['size_group'] = \$orderSet ? \$orderSet->size_set_name : '-';
        \$summary['pcs_in_set'] = \$pcs_in_set;";

$content = preg_replace($pattern2, $replacement2, $content);

file_put_contents($file, $content);
echo "Updated $file via regex\n";
