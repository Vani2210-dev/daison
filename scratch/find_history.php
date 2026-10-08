<?php
$historyDir = "C:/Users/nguye/AppData/Roaming/Antigravity IDE/User/History";
$dirs = glob($historyDir . '/*');

foreach ($dirs as $dir) {
    $entriesFile = $dir . '/entries.json';
    if (file_exists($entriesFile)) {
        $content = file_get_contents($entriesFile);
        if (strpos($content, '_picking.tsx') !== false) {
            echo "Found in $entriesFile:\n";
            echo $content . "\n";
            $files = glob($dir . '/*');
            foreach ($files as $f) {
                echo "  " . basename($f) . " (" . filesize($f) . " bytes)\n";
            }
        }
    }
}
