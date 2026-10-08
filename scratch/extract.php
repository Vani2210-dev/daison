<?php
$log = 'C:/Users/nguye/.gemini/antigravity-ide/brain/d0fcd939-0f9f-462b-909a-36bfa93fd4a9/.system_generated/logs/transcript_full.jsonl';
$fp = fopen($log, 'r');
$lineNum = 0;
while (($line = fgets($fp)) !== false) {
    $lineNum++;
    if ($lineNum == 2092 || $lineNum == 2093 || $lineNum == 2094 || $lineNum == 2095) {
        $data = json_decode($line, true);
        echo "Line $lineNum: " . ($data['type'] ?? '') . " " . ($data['name'] ?? '') . " len=" . strlen($line) . "\n";
    }
}
fclose($fp);
