<?php
/**
 * Verificación via Rank Math getHead - Mars Challenge
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

$url_to_check = 'https://mars-challenge.com/agua/';

echo "=== VERIFICANDO GETHEAD PARA $url_to_check ===\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/rankmath/v1/getHead?url=' . urlencode($url_to_check),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array($auth_header),
    CURLOPT_TIMEOUT => 30
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n";
// echo "Response: $response\n\n";

if ($http_code == 200) {
    $data = json_decode($response, true);
    if (isset($data['head'])) {
        echo "Head content retrieved.\n";
        
        // Buscar título
        if (preg_match('/<title>(.*?)<\/title>/', $data['head'], $matches)) {
            echo "Title found: " . $matches[1] . "\n";
        }
        
        // Buscar descripción
        if (preg_match('/<meta name="description" content="(.*?)"/', $data['head'], $matches)) {
            echo "Description found: " . $matches[1] . "\n";
        }
        
        // Buscar keywords (si existen)
        if (preg_match('/<meta name="keywords" content="(.*?)"/', $data['head'], $matches)) {
            echo "Keywords found: " . $matches[1] . "\n";
        } else {
            echo "Keywords meta tag not found (Normal for Focus Keywords, they are internal)\n";
        }
    }
} else {
    echo "Error retrieving head.\n";
}
