<?php
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';

function wp_api_request($endpoint, $method = 'GET', $data = null) {
    global $site_url, $username, $app_password;
    $url = $site_url . '/wp-json/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $app_password);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($response, true)];
}

// 1. Get all Proyectos Destacados
echo "Fetching all 'proyectos_destacados'...\n";
$res_proy = wp_api_request('wp/v2/proyectos_destacados?per_page=100');
$proyectos = ($res_proy['code'] === 200) ? $res_proy['body'] : [];

// 2. Get all Pages (Optional, but good to have)
echo "Fetching all 'pages'...\n";
$res_pages = wp_api_request('wp/v2/pages?per_page=100');
$pages = ($res_pages['code'] === 200) ? $res_pages['body'] : [];

$all_items = array_merge(
    array_map(fn($i) => ['id' => $i['id'], 'type' => 'proyectos_destacados'], $proyectos),
    array_map(fn($i) => ['id' => $i['id'], 'type' => 'pages'], $pages)
);

echo "Total items to 'touch': " . count($all_items) . "\n";

foreach ($all_items as $item) {
    echo "Touching " . $item['type'] . " ID: " . $item['id'] . "... ";
    
    // A simple POST update with no data change triggers the 'save_post' hook
    // We send an empty object to minimize payload but trigger the save.
    $update = wp_api_request('wp/v2/' . $item['type'] . '/' . $item['id'], 'POST', ['status' => 'publish']);
    
    if ($update['code'] === 200) {
        echo "✓ Success\n";
    } else {
        echo "✗ Failed (" . $update['code'] . ")\n";
    }
}

echo "\nDone! WPML should now detect the new ACF fields for these posts.\n";
echo "Go to WPML > Translation Management to send them to translation in bulk.\n";
