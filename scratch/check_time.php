<?php
$logDir = 'C:/Users/nguye/.gemini/antigravity-ide/brain';
$convDirs = glob($logDir . '/*');
$list = [];
foreach ($convDirs as $cDir) {
    $fullLog = $cDir . '/.system_generated/logs/transcript_full.jsonl';
    if (file_exists($fullLog)) {
        $list[] = [
            'id' => basename($cDir),
            'time' => date('Y-m-d H:i:s', filemtime($fullLog))
        ];
    }
}
usort($list, fn($a, $b) => strcmp($b['time'], $a['time']));
foreach (array_slice($list, 0, 10) as $item) {
    echo "{$item['time']} : {$item['id']}\n";
}
