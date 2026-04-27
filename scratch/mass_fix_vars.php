<?php
$file = 'app/Http/Controllers/Unit/UnitAuthController.php';
$content = file_get_contents($file);
$lines = explode("\n", $content);

for ($i = 211; $i < 650; $i++) {
    if (isset($lines[$i])) {
        $lines[$i] = str_replace('$orderNo', '$customerSearch', $lines[$i]);
    }
}

$content = implode("\n", $lines);
file_put_contents($file, $content);
echo "Mass replaced \$orderNo in history method of $file\n";
