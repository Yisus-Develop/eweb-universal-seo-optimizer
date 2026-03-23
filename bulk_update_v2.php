<?php
/**
 * Actualización masiva de metadescripciones V2 - Mars Challenge
 * Versión mejorada que detecta automáticamente si es post o página
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN V2 - AUTO-DETECCIÓN POST/PAGE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Cargar resultados anteriores para saber cuáles fallaron
$prev_results = json_decode(file_get_contents('meta_update_results_2025-11-28_143603.json'), true);
$failed_items = array_filter($prev_results['results'], function($item) {
    return $item['status'] === 'error';
});

echo "Elementos fallidos a procesar: " . count($failed_items) . "\n\n";

// Función para detectar tipo de contenido
function get_content_type($post_id, $auth_header, $site_url) {
    // Intentar como post primero
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $site_url . '/wp-json/wp/v2/posts/' . $post_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array($auth_header),
        CURLOPT_TIMEOUT => 10
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return 'posts';
    }
    
    // Si no es post, intentar como página
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $site_url . '/wp-json/wp/v2/pages/' . $post_id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array($auth_header),
        CURLOPT_TIMEOUT => 10
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        return 'pages';
    }
    
    return null;
}

// Función para actualizar
function update_meta($post_id, $description, $type, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/wp/v2/' . $type . '/' . $post_id;
    
    $data = json_encode(array(
        'meta' => array(
            'rank_math_description' => $description
        )
    ));
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            $auth_header,
            'Content-Type: application/json'
        ),
        CURLOPT_TIMEOUT => 30
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'code' => $http_code,
        'response' => json_decode($response, true)
    );
}

// Procesar elementos fallidos
$success = 0;
$errors = 0;
$results = array();

foreach ($failed_items as $index => $item) {
    $num = $index + 1;
    echo "[$num/" . count($failed_items) . "] {$item['title']}\n";
    
    // Detectar tipo
    echo "   Detectando tipo...";
    $type = get_content_type($item['id'], $auth_header, $site_url);
    
    if (!$type) {
        echo " ✗ No se pudo determinar\n\n";
        $errors++;
        continue;
    }
    
    echo " $type\n";
    
    // Actualizar
    $result = update_meta($item['id'], $item['description'], $type, $auth_header, $site_url);
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado\n\n";
        $success++;
        $results[] = array(
            'id' => $item['id'],
            'title' => $item['title'],
            'type' => $type,
            'status' => 'success'
        );
    } else {
        echo "   ✗ Error {$result['code']}\n\n";
        $errors++;
        $results[] = array(
            'id' => $item['id'],
            'title' => $item['title'],
            'type' => $type,
            'status' => 'error',
            'code' => $result['code']
        );
    }
    
    usleep(500000);
}

// Guardar
file_put_contents('meta_update_v2_results_' . date('Y-m-d_His') . '.json', 
    json_encode(array(
        'timestamp' => date('Y-m-d H:i:s'),
        'processed' => count($failed_items),
        'success' => $success,
        'errors' => $errors,
        'results' => $results
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN ===\n";
echo "Procesados: " . count($failed_items) . "\n";
echo "✓ Exitosos: $success\n";
echo "✗ Errores: $errors\n";
