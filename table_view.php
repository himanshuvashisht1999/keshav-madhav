<?php
$logPath = 'C:/Users/ADMIN/.gemini/antigravity/brain/ac854de0-384a-45a7-a6b3-110fc595e6fe/.system_generated/logs/transcript.jsonl';
$lines = file($logPath);

$html = "<html><body>";

foreach (array_reverse($lines) as $line) {
    if (strpos($line, '<table class="table') !== false || strpos($line, 'table-responsive') !== false) {
        $data = json_decode($line, true);
        if (!$data || !isset($data['tool_calls'])) continue;
        
        foreach ($data['tool_calls'] as $call) {
            $args = $call['args'] ?? [];
            if (!isset($args['TargetFile'])) continue;
            
            if (strpos($args['TargetFile'], 'create.blade.php') !== false) {
                if (isset($args['ReplacementContent'])) {
                    $html .= "<h3>Replacement</h3><pre>" . htmlspecialchars(substr($args['ReplacementContent'], 0, 3000)) . "...</pre>";
                }
                if (isset($args['ReplacementChunks'])) {
                    $chunks = is_string($args['ReplacementChunks']) ? json_decode($args['ReplacementChunks'], true) : $args['ReplacementChunks'];
                    if (is_array($chunks)) {
                        foreach ($chunks as $chunk) {
                            $html .= "<h3>Chunk Replacement</h3><pre>" . htmlspecialchars(substr($chunk['ReplacementContent'] ?? '', 0, 3000)) . "...</pre>";
                        }
                    }
                }
            }
        }
    }
}

$html .= "</body></html>";
file_put_contents('table_view.html', $html);
echo "Written to table_view.html\n";
