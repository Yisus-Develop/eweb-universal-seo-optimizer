<?php
// Quick fix para los 2 títulos cortos
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

function update_meta($id, $title, $site_url, $username, $password) {
    $url = "$site_url/wp-json/rankmath/v1/updateMeta";
    $payload = [
        'objectID' => $id,
        'objectType' => 'post',
        'meta' => ['rank_math_title' => $title]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$username:$password"),
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $code;
}

echo "Corrigiendo 2 títulos cortos...\n\n";

$code = update_meta(1338, 'ESA Space Agency | Mars Challenge', $site_url, $username, $password);
echo "ESA (1338): " . ($code === 200 ? '✓' : '✗') . " Code $code - " . mb_strlen('ESA Space Agency | Mars Challenge') . " chars\n";

$code = update_meta(860, 'AEC Asociación | Mars Challenge', $site_url, $username, $password);
echo "AEC (860): " . ($code === 200 ? '✓' : '✗') . " Code $code - " . mb_strlen('AEC Asociación | Mars Challenge') . " chars\n";

echo "\n✓ Completado\n";
