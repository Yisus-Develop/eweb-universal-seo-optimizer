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

$post_id = 6317; // M.L.C.S
echo "--- Dumping Meta for Post $post_id ---\n";
$res = wp_api_request("wp/v2/proyectos_destacados/$post_id?context=edit");
if ($res['code'] === 200) {
    echo "Found ACF keys in 'acf' object:\n";
    print_r(array_keys($res['body']['acf']));
    
    // Check if it's a field group mapping issue
    file_put_contents('post_6317_full_edit.json', json_encode($res['body'], JSON_PRETTY_PRINT));
} else {
    echo "Error " . $res['code'] . "\n";
}

echo "\n--- Checking Translation Status ---\n";
// Check WPML status if available
$res_wpml = wp_api_request("wpml/v1/posts/$post_id");
if ($res_wpml['code'] === 200) {
    print_r($res_wpml['body']);
} else {
    echo "WPML endpoint error or not found (" . $res_wpml['code'] . ")\n";
}
