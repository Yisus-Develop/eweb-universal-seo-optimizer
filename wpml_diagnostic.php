<?php
/**
 * Diagnostic script for mars-challenge.com WPML issues
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';

function wp_api_request($endpoint, $method = 'GET', $body = null) {
    global $site_url, $username, $app_password;
    
    $url = $site_url . '/wp-json/' . ltrim($endpoint, '/');
    $auth = base64_encode($username . ':' . $app_password);
    
    $ch = curl_init($url);
    $headers = [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($body) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $http_code,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

echo "--- Testing connection to $site_url ---\n";
// Try to get site name without auth first to see if API is alive
$site_info = wp_api_request('');
if ($site_info['code'] === 200) {
    echo "✓ API is reachable. Site Name: " . ($site_info['body']['name'] ?? 'Unknown') . "\n";
} else {
    echo "✗ API root unreachable. Code: " . $site_info['code'] . "\n";
    echo "Response: " . substr($site_info['raw'], 0, 500) . "\n";
}

echo "\n--- Testing Auth with /wp/v2/posts ---\n";
$posts = wp_api_request('wp/v2/posts?per_page=1');
if ($posts['code'] === 200) {
    echo "✓ Auth successful! Fetched 1 post.\n";
} else {
    echo "✗ Auth failed on /posts. Code: " . $posts['code'] . "\n";
    echo "Response: " . substr($posts['raw'], 0, 500) . "\n";
}

echo "\n--- Testing Auth with /wp/v2/users/me (original failed one) ---\n";
$me = wp_api_request('wp/v2/users/me');
if ($me['code'] === 200) {
    echo "✓ Connected as: " . $me['body']['name'] . " (ID: " . $me['body']['id'] . ")\n";
} else {
    echo "✗ /users/me failed. Code: " . $me['code'] . "\n";
}

echo "\n--- Checking available endpoints ---\n";
$root = wp_api_request('');
$namespaces = $root['body']['namespaces'] ?? [];
if (in_array('wpml/v1', $namespaces)) {
    echo "✓ WPML REST API detected\n";
} else {
    echo "! WPML REST API not found in namespaces\n";
}

echo "\n--- Fetching a single item from 'logos' CPT ---\n";
$logos = wp_api_request('wp/v2/logos?per_page=1&_fields=id,title,type');
if ($logos['code'] === 200) {
    echo "✓ Successfully fetched 1 logo item: " . $logos['body'][0]['title']['rendered'] . "\n";
} else {
    echo "✗ Failed to fetch logos. Code: " . $logos['code'] . "\n";
    echo "Response: " . substr($logos['raw'], 0, 500) . "\n";
}

$results = [];

echo "\n--- Listing ALL Namespaces ---\n";
$root = wp_api_request('');
if ($root['code'] === 200) {
    echo "Total Namespaces: " . count($root['body']['namespaces'] ?? []) . "\n";
    print_r($root['body']['namespaces'] ?? []);
}

echo "\n--- Searching for ACF Field Groups (REST API) ---\n";
// ACF PRO exposes field groups here sometimes
$groups = wp_api_request('wp/v2/acf-field-group');
if ($groups['code'] === 200) {
    echo "✓ Found " . count($groups['body']) . " ACF Field Groups.\n";
    foreach ($groups['body'] as $g) {
        echo " - " . $g['title']['rendered'] . " (ID: " . $g['id'] . ")\n";
    }
    file_put_contents('projects/marschallenge-seo/acf_field_groups.json', json_encode($groups['body'], JSON_PRETTY_PRINT));
} else {
    echo "✗ Failed to fetch field groups via wp/v2/acf-field-group (Code: " . $groups['code'] . ")\n";
}

echo "\n--- Fetching ALL Metadata for Page 69 (context=edit) ---\n";
$p69_meta = wp_api_request('wp/v2/pages/69?context=edit');
if ($p69_meta['code'] === 200 && isset($p69_meta['body']['meta'])) {
    echo "✓ Meta fields found. Saving to page_69_meta.json...\n";
    file_put_contents('projects/marschallenge-seo/page_69_meta.json', json_encode($p69_meta['body']['meta'], JSON_PRETTY_PRINT));
    
    // Search for repeater-like patterns in meta keys (e.g. name, name_0_subname)
    $keys = array_keys($p69_meta['body']['meta']);
    $repeaters = [];
    foreach ($keys as $k) {
        if (preg_match('/^(.+)_\d+_(.+)$/', $k, $matches)) {
            $repeaters[$matches[1]][] = $matches[2];
        }
    }
    if (!empty($repeaters)) {
        echo "Potential Repeaters found in meta:\n";
        foreach ($repeaters as $rep => $subs) {
            echo " - $rep (Subfields: " . implode(', ', array_unique($subs)) . ")\n";
        }
    }
}
