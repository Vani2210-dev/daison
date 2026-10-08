<?php
$logDir = 'C:/Users/nguye/.gemini/antigravity-ide/brain';
$convDirs = glob($logDir . '/*');

foreach ($convDirs as $cDir) {
    if (basename($cDir) === '7da9caed-4bd5-40e4-baef-e41e867560f7') continue;
    $fullLog = $cDir . '/.system_generated/logs/transcript_full.jsonl';
    if (file_exists($fullLog)) {
        $fp = fopen($fullLog, 'r');
        $lineNum = 0;
        while (($line = fgets($fp)) !== false) {
            $lineNum++;
            if (strpos($line, '_picking.tsx') !== false && strpos($line, 'write_to_file') !== false) {
                echo "Found write_to_file in $cDir at line $lineNum\n";
            }
        }
        fclose($fp);
    }
}
