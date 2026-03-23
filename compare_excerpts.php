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

echo "--- ES Version ---\n";
$res_es = wp_api_request('wp/v2/proyectos_destacados/6317');
if ($res_es['code'] === 200) {
    echo "Excerpt ES: " . $res_es['body']['excerpt']['rendered'] . "\n";
}

echo "\n--- PT Version ---\n";
// Let's find the translation ID
// Usually WPML doesn't expose translation links directly in standard REST unless configured.
// But we can check if it has a pt-pt version if we know the ID or slug if it's translate slug too.
// Or we can list pt-pt posts.
$res_pt = wp_api_request('wp/v2/proyectos_destacados?lang=pt-pt&slug=m-l-c-s-mars-life-care-system'); // typical but might vary
if ($res_pt['code'] === 200 && !empty($res_pt['body'])) {
    echo "Excerpt PT: " . $res_pt['body'][0]['excerpt']['rendered'] . "\n";
} else {
    echo "Could not find PT version by slug.\n";
    // Try listing all PT-PT to see if they exist
    $list_pt = wp_api_request('wp/v2/proyectos_destacados?lang=pt-pt');
    echo "Total PT posts found: " . (is_array($list_pt['body']) ? count($list_pt['body']) : 0) . "\n";
}
