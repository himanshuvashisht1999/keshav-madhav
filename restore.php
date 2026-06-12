<?php
$logPath = 'C:/Users/ADMIN/.gemini/antigravity/brain/ac854de0-384a-45a7-a6b3-110fc595e6fe/.system_generated/logs/transcript.jsonl';
$lines = file($logPath);

foreach ($lines as $line) {
    $data = json_decode($line, true);
    if (isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $call) {
            if ($call['name'] === 'replace_file_content') {
                if (isset($call['args']['TargetFile']) && strpos($call['args']['TargetFile'], 'create.blade.php') !== false) {
                    if (isset($call['args']['ReplacementContent'])) {
                        $len = strlen($call['args']['ReplacementContent']);
                        if ($len > 10000) {
                            file_put_contents('best_chunk.txt', $call['args']['ReplacementContent']);
                            echo "Found chunk: $len\n";
                        }
                    }
                }
            }
        }
    }
}
