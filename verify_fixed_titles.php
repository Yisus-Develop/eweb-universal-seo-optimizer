<?php
$urls = [
    'ESA' => 'https://mars-challenge.com/logos/esa/',
    'AEC' => 'https://mars-challenge.com/logos/aec/'
];

echo "=== VERIFICACIÓN TÍTULOS CORREGIDOS ===\n\n";

foreach ($urls as $name => $url) {
    $html = file_get_contents($url);
    if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
        $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
        $len = mb_strlen($title);
        $status = ($len >= 30 && $len <= 60) ? '✓' : '✗';
        echo "$name:\n";
        echo "  Title: $title\n";
        echo "  Length: $len chars $status\n\n";
    }
}
