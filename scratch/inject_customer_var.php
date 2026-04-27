<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);

// Find the loop and insert the variable definition
$pattern = '/(foreach\s*\(\$qRolls->get\(\)\s*as\s*\$item\)\s*\{)/';
$replacement = '$1' . "\n" . '                    $customerName = $item->orderProductSet?->orderMain?->customer?->name ?? \'-\';';

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $content);
echo "Injected \$customerName definition via REGEX\n";
