<?php
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';

function wp_api_request($endpoint) {
    global $site_url, $username, $app_password;
    $url = $site_url . '/wp-json/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $app_password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($response, true)];
}

echo "--- 1. Listing all Custom Post Types ---\n";
$types = wp_api_request('wp/v2/types');
if ($types['code'] === 200) {
    foreach ($types['body'] as $slug => $type) {
        if (!$type['hierarchical'] || $slug !== 'page' && $slug !== 'post') {
            echo " - Slug: $slug | Name: " . $type['name'] . " | REST Base: " . ($type['rest_base'] ?? $slug) . "\n";
        }
    }
}

echo "\n--- 2. Attempting to discover ACF groups via all known endpoints ---\n";
$acf_endpoints = [
    'acf/v3/field-groups',
    'acf/v3/field-group',
    'wp/v2/acf-field-group',
    'wp/v2/acf'
];

foreach ($acf_endpoints as $endpoint) {
    echo "Checking $endpoint... ";
    $res = wp_api_request($endpoint);
    if ($res['code'] === 200) {
        echo "✓ FOUND! (" . count($res['body']) . " items)\n";
        file_put_contents("projects/marschallenge-seo/discovery_" . str_replace('/', '_', $endpoint) . ".json", json_encode($res['body'], JSON_PRETTY_PRINT));
    } else {
        echo "✗ " . $res['code'] . "\n";
    }
}

echo "\n--- 3. Fetching the specific post that is 'broken' ---\n";
// The slug is m-l-c-s-mars-life-care-system. Let's find its ID and CPT.
// Based on the URL, the CPT rest base is likely 'proyectos_destacados'
$search_broken = wp_api_request('wp/v2/proyectos_destacados?slug=m-l-c-s-mars-life-care-system');
if ($search_broken['code'] === 200 && !empty($search_broken['body'])) {
    $post = $search_broken['body'][0];
    echo "✓ Found Broken Post! ID: " . $post['id'] . "\n";
    echo "Keys available in post: " . implode(', ', array_keys($post)) . "\n";
    if (isset($post['acf'])) {
        echo "✓ ACF DATA EXPOSED DIRECTLY! Saving...\n";
        file_put_contents('projects/marschallenge-seo/broken_post_acf.json', json_encode($post['acf'], JSON_PRETTY_PRINT));
    }
} else {
    echo "✗ Could not find post via /wp/v2/proyectos_destacados. Code: " . $search_broken['code'] . "\n";
}
