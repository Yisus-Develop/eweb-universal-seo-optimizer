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

echo "--- Checking Excerpts for Proyectos Destacados ---\n";
// Let's check the post we were looking at before (M.L.C.S)
$res = wp_api_request('wp/v2/proyectos_destacados?slug=m-l-c-s-mars-life-care-system');
if ($res['code'] === 200 && !empty($res['body'])) {
    $post = $res['body'][0];
    echo "Post ID: " . $post['id'] . "\n";
    echo "Excerpt (Raw): " . ($post['excerpt']['raw'] ?? 'NOT FOUND') . "\n";
    echo "Excerpt (Rendered): " . ($post['excerpt']['rendered'] ?? 'NOT FOUND') . "\n";
} else {
    echo "Could not find post or API error: " . $res['code'] . "\n";
}

echo "\n--- Checking available fields for Proyectos Destacados CPT ---\n";
$type_res = wp_api_request('wp/v2/types/proyectos_destacados');
if ($type_res['code'] === 200) {
    echo "Supports: " . implode(', ', array_keys($type_res['body']['supports'] ?? [])) . "\n";
}
