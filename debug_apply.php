<?php
$targetFile = 'c:\\xampp\\htdocs\\keshav-madhav\\resources\\views\\admin\\inventory\\fair_product\\create.blade.php';
$content = file_get_contents($targetFile);
$content = str_replace("\r\n", "\n", $content);

$edits = json_decode(file_get_contents('all_edits.json'), true);

$target = str_replace("\r\n", "\n", $edits[0]['target']);

echo "Target snippet:\n" . substr($target, 0, 100) . "\n";
echo "---\n";
echo "Content snippet:\n" . substr($content, 0, 100) . "\n";

$pos = strpos($content, substr($target, 0, 100));
if ($pos !== false) {
    echo "Found first 100 chars at pos $pos\n";
    $pos2 = strpos($content, substr($target, 0, 500));
    if ($pos2 !== false) {
        echo "Found first 500 chars\n";
    } else {
        echo "Failed to find first 500 chars\n";
    }
} else {
    echo "Failed to find first 100 chars\n";
}
