<?php
/**
 * Actualización masiva de Títulos SEO - Mars Challenge
 * Genera y aplica títulos optimizados usando Rank Math API
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN DE TÍTULOS SEO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Cargar análisis previo
$analysis_file = 'seo_titles_analysis.json';
if (!file_exists($analysis_file)) {
    die("Error: No se encuentra $analysis_file. Ejecuta analyze_seo_titles.php primero.\n");
}

$data = json_decode(file_get_contents($analysis_file), true);
$items_to_process = $data['details'];

echo "Elementos a procesar: " . count($items_to_process) . "\n\n";

// Función para actualizar usando Rank Math API
function update_rankmath_title($post_id, $title, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/rankmath/v1/updateMeta';
    
    $payload = array(
        'objectID' => $post_id,
        'objectType' => 'post', // Forzamos 'post' para evitar error 403 en páginas
        'meta' => array(
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

// Generador de Títulos Inteligente
function generate_seo_title($item) {
    $original_title = $item['wp_title'];
    $url = $item['url'];
    
    // Limpieza básica
    $title = trim(str_replace('&#8211;', '-', $original_title));
    
    // Reglas específicas por URL o contenido
    if ($title == 'Inicio' || $url == 'https://mars-challenge.com/') {
        return 'Mars Challenge 2026 | Innovación Dual-Planeta Tierra y Marte';
    }
    
    // Categorías principales
    if (strpos($url, '/registro') !== false || stripos($title, 'registro') !== false) {
        return "$title | Mars Challenge - Únete al Reto Global";
    }
    
    if (strpos($url, '/hackimpacto/') !== false) {
        return "$title | HackImpacto Mars Challenge - Hackathon Global";
    }
    
    if (strpos($url, '/empresas/') !== false) {
        return "$title | Mars Challenge - Innovación Corporativa y CSR";
    }
    
    if (strpos($url, '/participar/') !== false) {
        return "$title | Participa en Mars Challenge - Reto Educativo";
    }
    
    if (strpos($url, '/sobre/') !== false) {
        return "$title | Sobre Mars Challenge - Misión y Visión";
    }
    
    if (strpos($url, '/quien-sirve/') !== false) {
        return "$title | Comunidad Mars Challenge - Impacto Global";
    }
    
    // Elementos (Agua, Fuego, etc)
    $elements = ['Agua', 'Fuego', 'Genesis', 'Aire', 'Tierra', 'Conciencia', 'Tecnología', 'Equilibrio'];
    if (in_array($title, $elements)) {
        return "Reto Marte: $title | Mars Challenge - Soluciones Sostenibles";
    }
    
    // Casos específicos
    if (stripos($title, 'voices') !== false) {
        return "Voices | Mars Challenge - Voces del Futuro";
    }
    
    if (stripos($title, 'seasons') !== false) {
        return "Seasons | Mars Challenge - Temporadas del Reto";
    }
    
    // Default con branding
    // Si el título ya es largo (>40), solo agregar branding corto
    if (mb_strlen($title) > 40) {
        return "$title | Mars Challenge";
    }
    
    return "$title | Mars Challenge - Innovación Espacial";
}

// Procesar
$success = 0;
$errors = 0;
$results = array();

foreach ($items_to_process as $index => $item) {
    $num = $index + 1;
    $new_title = generate_seo_title($item);
    
    echo "[$num/" . count($items_to_process) . "] ID: {$item['id']}\n";
    echo "   Actual: {$item['wp_title']}\n";
    echo "   Nuevo:  $new_title\n";
    
    // Actualizar
    $result = update_rankmath_title($item['id'], $new_title, $auth_header, $site_url);
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado\n\n";
        $success++;
        $results[] = array(
            'id' => $item['id'],
            'old_title' => $item['wp_title'],
            'new_title' => $new_title,
            'status' => 'success'
        );
    } else {
        echo "   ✗ Error {$result['code']}\n";
        if (isset($result['response']['message'])) echo "     {$result['response']['message']}\n";
        echo "\n";
        $errors++;
        $results[] = array(
            'id' => $item['id'],
            'status' => 'error', 
            'code' => $result['code']
        );
    }
    
    usleep(300000); // 0.3s
}

// Guardar resultados
file_put_contents('title_update_results_' . date('Y-m-d_His') . '.json', 
    json_encode(array(
        'timestamp' => date('Y-m-d H:i:s'),
        'processed' => count($items_to_process),
        'success' => $success,
        'errors' => $errors,
        'results' => $results
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN DE ACTUALIZACIÓN ===\n";
echo "Procesados: " . count($items_to_process) . "\n";
echo "✓ Exitosos: $success\n";
echo "✗ Errores: $errors\n";
