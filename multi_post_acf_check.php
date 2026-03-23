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

$res = wp_api_request('wp/v2/proyectos_destacados?per_page=10');
if ($res['code'] === 200) {
    $all_keys = [];
    foreach ($res['body'] as $post) {
        $acf = $post['acf'] ?? [];
        foreach (array_keys($acf) as $k) $all_keys[] = $k;
    }
    $unique_keys = array_unique($all_keys);
    echo "Found unique ACF keys across 10 projects:\n";
    print_r($unique_keys);
}
