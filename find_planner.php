<?php
$content = file_get_contents('resources/views/admin/packing/pack_lots.blade.php');
preg_match_all('/Range|Quick|Planner|color|size_set/i', $content, $matches, PREG_OFFSET_CAPTURE);
$printed = [];
foreach($matches[0] as $match) {
    $offset = $match[1];
    $line = substr_count(substr($content, 0, $offset), "\n") + 1;
    if (in_array($line, $printed)) continue;
    $printed[] = $line;
    // get line content
    $lines = explode("\n", $content);
    echo "Line $line: " . trim($lines[$line-1]) . "\n";
}
