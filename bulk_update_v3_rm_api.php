<?php
/**
 * Actualización masiva de metadescripciones V3 - Mars Challenge
 * Usa el endpoint específico de Rank Math: /wp-json/rankmath/v1/updateMeta
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN V3 - RANK MATH API ENDPOINT ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Cargar resultados anteriores (usamos el V2 que ya detectó los tipos, aunque usaremos 'post' para todos)
$prev_results = json_decode(file_get_contents('meta_update_v2_results_2025-11-28_144336.json'), true);
$items_to_process = $prev_results['results']; // Procesamos todos, ya que el V2 reportó éxito falso

echo "Elementos a procesar: " . count($items_to_process) . "\n\n";

// Función para actualizar usando Rank Math API
function update_rankmath_meta($post_id, $description, $title, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/rankmath/v1/updateMeta';
    
    // IMPORTANTE: Usamos 'post' como objectType para todos, ya que 'page' dio error 403
    // y Rank Math parece aceptar 'post' para actualizar metadata de cualquier ID
    $payload = array(
        'objectID' => $post_id,
        'objectType' => 'post', 
        'meta' => array(
            'rank_math_description' => $description,
            'rank_math_title' => $title
        )
    );
    
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $endpoint,
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
    
    return array(
        'code' => $http_code,
        'response' => json_decode($response, true)
    );
}

// Plantillas de metadescripciones (reutilizadas)
$meta_templates = array(
    'default' => 'Descubre %s en Mars Challenge. Únete al movimiento global de innovación dual-planeta para crear soluciones sostenibles para la Tierra y Marte.',
    'registro' => 'Regístrate en Mars Challenge y participa en el reto global de innovación. %s para formar parte del cambio.',
    'sobre' => 'Conoce más sobre %s en Mars Challenge. Innovación, educación y sostenibilidad para un futuro mejor en la Tierra y Marte.',
    'hackimpacto' => '%s - HackImpacto Mars Challenge. Participa en el hackathon global de innovación dual-planeta y gana premios internacionales.',
    'empresas' => '%s para empresas en Mars Challenge. Responsabilidad social corporativa e innovación sostenible.',
    'participar' => '%s en Mars Challenge. Únete al reto global de innovación y sostenibilidad para jóvenes y educadores.',
    'seasons' => 'Explora %s de Mars Challenge. Retos temáticos de innovación: Agua, Fuego, Genesis y más.',
);

function generate_desc($title, $url) {
    global $meta_templates;
    $title_lower = mb_strtolower($title);
    
    if (strpos($url, '/registro') !== false || strpos($title_lower, 'registro') !== false) {
        $template = $meta_templates['registro'];
    } elseif (strpos($url, '/sobre/') !== false) {
        $template = $meta_templates['sobre'];
    } elseif (strpos($url, '/hackimpacto/') !== false) {
        $template = $meta_templates['hackimpacto'];
    } elseif (strpos($url, '/empresas/') !== false) {
        $template = $meta_templates['empresas'];
    } elseif (strpos($url, '/participar/') !== false) {
        $template = $meta_templates['participar'];
    } elseif (strpos($title_lower, 'agua') !== false || strpos($title_lower, 'fuego') !== false || 
              strpos($title_lower, 'genesis') !== false || strpos($title_lower, 'voices') !== false) {
        $template = $meta_templates['seasons'];
    } else {
        $template = $meta_templates['default'];
    }
    
    $description = sprintf($template, $title);
    if (strlen($description) > 160) $description = substr($description, 0, 157) . '...';
    return $description;
}

// Procesar
$success = 0;
$errors = 0;
$results = array();

// Necesitamos recuperar las URLs originales para generar las descripciones correctamente
// Cargamos el reporte original
$original_report = json_decode(file_get_contents('seo_verification_report_2025-11-28.json'), true);
$url_map = array();
foreach ($original_report['missing_items'] as $item) {
    $url_map[$item['id']] = $item['url'];
}

foreach ($items_to_process as $index => $item) {
    $num = $index + 1;
    echo "[$num/" . count($items_to_process) . "] {$item['title']} (ID: {$item['id']})\n";
    
    $url = $url_map[$item['id']] ?? '';
    $description = generate_desc($item['title'], $url);
    
    // Actualizar
    $result = update_rankmath_meta($item['id'], $description, $item['title'], $auth_header, $site_url);
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado (RM API)\n";
        $success++;
        $results[] = array('id' => $item['id'], 'status' => 'success');
    } else {
        echo "   ✗ Error {$result['code']}\n";
        if (isset($result['response']['message'])) echo "     {$result['response']['message']}\n";
        $errors++;
        $results[] = array('id' => $item['id'], 'status' => 'error', 'code' => $result['code']);
    }
    
    usleep(500000); // 0.5s
}

// Guardar
file_put_contents('meta_update_v3_results_' . date('Y-m-d_His') . '.json', 
    json_encode(array(
        'timestamp' => date('Y-m-d H:i:s'),
        'processed' => count($items_to_process),
        'success' => $success,
        'errors' => $errors,
        'results' => $results
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN V3 ===\n";
echo "Procesados: " . count($items_to_process) . "\n";
echo "✓ Exitosos: $success\n";
echo "✗ Errores: $errors\n";
