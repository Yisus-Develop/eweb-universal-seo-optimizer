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

echo "--- Searching for 'opciones' or 'seleccion' in ACF data of Post 6317 ---\n";
$res = wp_api_request('wp/v2/proyectos_destacados/6317');
if ($res['code'] === 200) {
    $acf = $res['body']['acf'] ?? [];
    echo "Found ACF keys:\n";
    print_r(array_keys($acf));
    
    foreach ($acf as $key => $val) {
        if (is_array($val)) {
            echo "Subkeys for '$key': " . implode(', ', array_keys($val)) . "\n";
            foreach ($val as $sk => $sv) {
                if (is_array($sv)) {
                    // Could be a repeater or another group
                    if (isset($sv[0]) && is_array($sv[0])) {
                         echo "  '$sk' is a repeater with subkeys: " . implode(', ', array_keys($sv[0])) . "\n";
                    } else {
                         echo "  '$sk' is an array/group with subkeys: " . implode(', ', array_keys($sv)) . "\n";
                    }
                }
            }
        }
    }
} else {
    echo "Error: " . $res['code'] . "\n";
}
