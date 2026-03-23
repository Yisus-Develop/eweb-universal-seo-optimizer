<?php
/**
 * Detect REST base / post type for a list of IDs (read-only)
 * Usage: php bulk_detect_posttypes.php [report.json]
 */

$report_file = $argv[1] ?? 'seo_verification_report_2025-11-28.json';
if (!file_exists($report_file)) {
    echo "Report file not found: $report_file\n";
    exit(2);
}

$report = json_decode(file_get_contents($report_file), true);
if (!$report) {
    echo "Failed to parse report JSON: $report_file\n";
    exit(3);
}

$site = $report['site'] ?? 'https://mars-challenge.com';
$ids = [];
foreach ($report['missing_items'] as $item) {
    $ids[] = $item['id'];
}

echo "Using site: $site\n";
echo "IDs to detect: " . count($ids) . "\n";

// Get available types from WP REST API
$types_endpoint = rtrim($site, '/') . '/wp-json/wp/v2/types';
echo "Fetching REST types from: $types_endpoint\n";

function http_get($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'AI-Vault-Detect/1.0'
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return ['code' => $code, 'body' => $body, 'error' => $err];
}

$resp = http_get($types_endpoint);
if ($resp['code'] !== 200) {
    echo "Warning: could not fetch types list (HTTP {$resp['code']}). Will fall back to common bases (posts,pages).\n";
    $rest_bases = ['posts', 'pages'];
} else {
    $types = json_decode($resp['body'], true);
    $rest_bases = [];
    foreach ($types as $type_name => $type_info) {
        if (!empty($type_info['rest_base'])) $rest_bases[] = $type_info['rest_base'];
    }
    // Ensure posts/pages present
    if (!in_array('posts', $rest_bases)) $rest_bases[] = 'posts';
    if (!in_array('pages', $rest_bases)) $rest_bases[] = 'pages';
}

echo "REST bases to try: " . implode(', ', $rest_bases) . "\n\n";

$mapping = [];

foreach ($ids as $id) {
    echo "Detecting ID $id...\n";
    $found = [];
    foreach ($rest_bases as $base) {
        $url = rtrim($site, '/') . '/wp-json/wp/v2/' . $base . '/' . $id;
        $r = http_get($url);
        echo "  Trying $base -> HTTP {$r['code']}\n";
        if ($r['code'] === 200) {
            // attempt to parse body for type/name
            $body = json_decode($r['body'], true);
            $found[] = [
                'rest_base' => $base,
                'http_code' => $r['code'],
                'title' => $body['title']['rendered'] ?? null,
                'link' => $body['link'] ?? null
            ];
            // do not break: record all matches
        }
        // small delay to be polite
        usleep(200000);
    }

    if (empty($found)) {
        $mapping[$id] = ['found' => false, 'candidates' => []];
        echo "  No REST base returned 200 for ID $id\n\n";
    } else {
        $mapping[$id] = ['found' => true, 'candidates' => $found];
        echo "  Found candidates for $id: " . count($found) . "\n\n";
    }
}

$out_file = 'posttype_mapping_' . date('Y-m-d_His') . '.json';
file_put_contents($out_file, json_encode(['site' => $site, 'mapping' => $mapping], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Mapping saved to $out_file\n";
echo "Done. Review the JSON file and tell me if you want to proceed with dry-run or apply updates.\n";

?>
