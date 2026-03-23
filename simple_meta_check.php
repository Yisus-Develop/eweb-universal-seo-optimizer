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

echo "--- Testing Page 69 --- \n";
$res = wp_api_request('wp/v2/pages/69?context=edit');
echo "Code: " . $res['code'] . "\n";
if ($res['code'] === 200) {
    echo "Keys: " . implode(', ', array_keys($res['body'])) . "\n";
    if (isset($res['body']['meta'])) {
        echo "Meta Keys: " . implode(', ', array_keys($res['body']['meta'])) . "\n";
    }
}

echo "\n--- Testing Logo 5747 --- \n";
$res2 = wp_api_request('wp/v2/logos/5747?context=edit');
echo "Code: " . $res2['code'] . "\n";
if ($res2['code'] === 200) {
    echo "Keys: " . implode(', ', array_keys($res2['body'])) . "\n";
    if (isset($res2['body']['meta'])) {
        echo "Meta Keys: " . implode(', ', array_keys($res2['body']['meta'])) . "\n";
    }
}
