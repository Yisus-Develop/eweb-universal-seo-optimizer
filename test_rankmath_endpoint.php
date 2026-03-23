<?php
/**
 * Test Rank Math Specific API Endpoint
 * Tries to update metadata using the dedicated Rank Math REST API
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== TESTING RANK MATH API ENDPOINT ===\n";
echo "Endpoint: /wp-json/rankmath/v1/updateMeta\n\n";

// Test with "Agua" page (ID 2898)
$post_id = 2898;
$test_description = "Explora Agua de Mars Challenge. Retos temáticos de innovación: Agua, Fuego, Genesis y más. (Updated via RM API)";

$payload = array(
    'objectID' => $post_id,
    'objectType' => 'post', // Rank Math might use 'post' for pages too in this context
    'meta' => array(
        'rank_math_description' => $test_description,
        'rank_math_title' => 'Agua - Mars Challenge'
    )
);

echo "Payload:\n" . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/rankmath/v1/updateMeta',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        $auth_header,
        'Content-Type: application/json'
    ),
    CURLOPT_TIMEOUT => 30
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "Response Code: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}
echo "Response Body:\n$response\n";

// Also try with 'page' as objectType just in case
echo "\n--- Retrying with objectType: page ---\n";
$payload['objectType'] = 'page';
$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/rankmath/v1/updateMeta',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => array(
        $auth_header,
        'Content-Type: application/json'
    ),
    CURLOPT_TIMEOUT => 30
));
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Response Code: $http_code\n";
echo "Response Body:\n$response\n";
