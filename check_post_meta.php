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

echo "--- POST META FOR 6317 ---\n";
$res = wp_api_request('wp/v2/proyectos_destacados/6317?context=edit');
if ($res['code'] === 200) {
    echo "EXCERPT: " . ($res['body']['excerpt']['raw'] ?? 'N/A') . "\n";
    echo "\nMETA KEYS:\n";
    print_r(array_keys($res['body']['meta'] ?? []));
} else {
    echo "Error: " . $res['code'] . "\n";
}
