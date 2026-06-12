<?php
$data = json_decode(file_get_contents('step117.json'), true);
$chunks_string = $data['tool_calls'][0]['args']['ReplacementChunks'];

// If the string starts with " and ends with ", and has escaped quotes, let's fix it.
if (str_starts_with($chunks_string, '"') && str_ends_with($chunks_string, '"')) {
    $chunks_string = substr($chunks_string, 1, -1);
    $chunks_string = stripslashes($chunks_string);
}

$chunks = json_decode($chunks_string, true);

if ($chunks === null) {
    echo "JSON error: " . json_last_error_msg() . "\n";
    // Try without stripslashes?
    $chunks_string2 = $data['tool_calls'][0]['args']['ReplacementChunks'];
    $chunks_string2 = json_decode($chunks_string2); // Decodes a JSON string into a PHP string
    $chunks = json_decode($chunks_string2, true);
    if ($chunks === null) {
        echo "JSON error 2: " . json_last_error_msg() . "\n";
        exit;
    }
}

$targetFile = 'c:\\xampp\\htdocs\\keshav-madhav\\resources\\views\\admin\\inventory\\fair_product\\create.blade.php';
$content = file_get_contents($targetFile);
$content = str_replace("\r\n", "\n", $content);

foreach ($chunks as $chunk) {
    $target = str_replace("\r\n", "\n", $chunk['TargetContent']);
    $replacement = str_replace("\r\n", "\n", $chunk['ReplacementContent']);
    
    if (strpos($content, $target) !== false) {
        echo "Found target. Replacing.\n";
        $content = str_replace($target, $replacement, $content);
    } else {
        echo "Could not find target! Length: " . strlen($target) . "\n";
    }
}
file_put_contents('restored_create.blade.php', $content);
echo "Written to restored_create.blade.php. Length: " . strlen($content) . "\n";
