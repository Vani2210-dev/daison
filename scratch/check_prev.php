<?php
$log = 'C:/Users/nguye/.gemini/antigravity-ide/brain/d0fcd939-0f9f-462b-909a-36bfa93fd4a9/.system_generated/logs/transcript_full.jsonl';
$fp = fopen($log, 'r');
$lineNum = 0;
while (($line = fgets($fp)) !== false) {
    $lineNum++;
    if (strpos($line, '_picking.tsx') !== false) {
        $data = json_decode($line, true);
        $toolCalls = $data['tool_calls'] ?? [];
        foreach ($toolCalls as $tc) {
            echo "Line $lineNum: " . ($tc['name'] ?? '') . "\n";
        }
    }
}
fclose($fp);
