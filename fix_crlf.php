<?php
$file = 'apply_subagent_2.php';
$content = file_get_contents($file);
$content = str_replace("\r\n", "\n", $content);
file_put_contents($file, $content);

$file = 'resources/views/admin/packing/process.blade.php';
$content = file_get_contents($file);
$content = str_replace("\r\n", "\n", $content);
file_put_contents($file, $content);

echo "Normalized line endings.\n";
?>
