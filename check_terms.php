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

echo "--- Checking Terms for Post 6317 ---\n";
// Taxonomy endpoints: /wp/v2/nombre_pais, /wp/v2/ano, /wp/v2/country
$taxonomies = ['nombre_pais', 'ano', 'country'];
foreach ($taxonomies as $tax) {
    echo "Taxonomy $tax: ";
    $res = wp_api_request('wp/v2/' . $tax . '?post=6317');
    if ($res['code'] === 200) {
        $names = array_map(fn($t) => $t['name'], $res['body']);
        echo implode(', ', $names) . "\n";
    } else {
        echo "Error " . $res['code'] . "\n";
    }
}

echo "\n--- Checking Post Type Settings for Proyectos Destacados ---\n";
$res_type = wp_api_request('wp/v2/types/proyectos_destacados');
if ($res_type['code'] === 200) {
    echo "Labels:\n";
    print_r($res_type['body']['labels']);
}
