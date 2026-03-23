<?php
/**
 * Debug Keywords - Verificar si las keywords se guardaron en la base de datos
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

$test_ids = [4961, 5476, 4255, 648];

echo "=== DEBUG KEYWORDS ===\n\n";

foreach ($test_ids as $id) {
    echo "ID: $id\n";
    
    // 1. Obtener post meta via REST API
    $url = "$site_url/wp-json/wp/v2/posts/$id";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$username:$password")
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        
        // Verificar meta fields
        if (isset($data['meta'])) {
            echo "Meta fields disponibles: " . implode(', ', array_keys($data['meta'])) . "\n";
            
            if (isset($data['meta']['rank_math_focus_keyword'])) {
                echo "✓ rank_math_focus_keyword: " . $data['meta']['rank_math_focus_keyword'] . "\n";
            } else {
                echo "✗ rank_math_focus_keyword NO encontrado\n";
            }
        }
    }
    
    echo "\n";
    usleep(300000);
}

echo "\n=== INVESTIGACIÓN ===\n";
echo "Rank Math típicamente NO renderiza meta keywords en HTML por defecto.\n";
echo "Google ha ignorado meta keywords desde 2009.\n\n";
echo "Las keywords en Rank Math se usan para:\n";
echo "1. Análisis SEO interno (content score)\n";
echo "2. Sugerencias de optimización\n";
echo "3. Tracking de focus keywords\n\n";
echo "NO se renderizan como <meta name=\"keywords\"> en el HTML.\n";
echo "Esto es CORRECTO y sigue mejores prácticas SEO actuales.\n";
