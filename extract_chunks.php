<?php
$data = json_decode(file_get_contents('step117.json'), true);
$chunks_string = $data['tool_calls'][0]['args']['ReplacementChunks'];

// Double-encoded JSON string inside a JSON string.
// $chunks_string is an actual string containing '[{"AllowMultiple"...'.
$chunks = json_decode($chunks_string, true);
if ($chunks === null) {
    // If it was wrapped in literal double quotes due to transcript logging:
    if (str_starts_with($chunks_string, '"') && str_ends_with($chunks_string, '"')) {
        $chunks_string = substr($chunks_string, 1, -1);
    }
    // Now we must unescape the JSON string
    // e.g., \" -> "
    // \\n -> \n
    // Actually, `json_decode('"' . $chunks_string . '"')` can unescape a JSON string.
    $chunks_string = json_decode('"' . $chunks_string . '"');
    $chunks = json_decode($chunks_string, true);
}

if ($chunks !== null) {
    foreach ($chunks as $idx => $chunk) {
        file_put_contents("target_$idx.txt", $chunk['TargetContent']);
        file_put_contents("replace_$idx.txt", $chunk['ReplacementContent']);
    }
    echo "Extracted " . count($chunks) . " chunks.\n";
} else {
    echo "Failed to decode chunks.\n";
}
