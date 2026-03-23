<?php
/**
 * Actualización masiva de Palabras Clave (Focus Keywords) - Mars Challenge
 * Genera y aplica palabras clave usando Rank Math API
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN DE FOCUS KEYWORDS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Cargar resultados de la actualización de títulos para tener la base más reciente
$title_results_file = 'title_update_results_' . date('Y-m-d') . '*.json'; // Buscar el archivo de hoy
$files = glob($title_results_file);
if (empty($files)) {
    // Si no encuentra el de hoy, intentar usar el análisis original
    $analysis_file = 'seo_titles_analysis.json';
    if (!file_exists($analysis_file)) {
        die("Error: No se encuentran datos de origen.\n");
    }
    $data = json_decode(file_get_contents($analysis_file), true);
    $items_to_process = $data['details'];
} else {
    // Usar el más reciente
    $latest_file = end($files);
    echo "Usando datos de: $latest_file\n";
    $data = json_decode(file_get_contents($latest_file), true);
    $items_to_process = $data['results']; // La estructura es diferente en el archivo de resultados
}

echo "Elementos a procesar: " . count($items_to_process) . "\n\n";

// Función para actualizar usando Rank Math API
function update_rankmath_keyword($post_id, $keyword, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/rankmath/v1/updateMeta';
    
    $payload = array(
        'objectID' => $post_id,
        'objectType' => 'post',
        'meta' => array(
            'rank_math_focus_keyword' => $keyword
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

// Generador de Keywords Inteligente
function generate_focus_keyword($item) {
    // Intentar obtener título, manejando diferentes estructuras de datos
    $title = isset($item['new_title']) ? $item['new_title'] : (isset($item['wp_title']) ? $item['wp_title'] : '');
    
    // Limpiar título de branding para obtener la keyword principal
    $clean_title = explode('|', $title)[0];
    $clean_title = trim($clean_title);
    
    // Reglas específicas
    if (stripos($clean_title, 'Inicio') !== false || stripos($clean_title, 'Mars Challenge 2026') !== false) {
        return 'Mars Challenge, innovación espacial, reto educativo';
    }
    
    if (stripos($clean_title, 'Registro') !== false) {
        return 'registro Mars Challenge, inscripción reto marte';
    }
    
    if (stripos($clean_title, 'Agua') !== false) {
        return 'reto agua marte, gestión agua espacio';
    }
    
    if (stripos($clean_title, 'Fuego') !== false) {
        return 'reto fuego marte, energía espacio';
    }
    
    if (stripos($clean_title, 'HackImpacto') !== false) {
        return 'HackImpacto, hackathon espacial, hackathon innovación';
    }
    
    if (stripos($clean_title, 'Empresas') !== false) {
        return 'empresas Mars Challenge, CSR espacio, patrocinio innovación';
    }
    
    // Por defecto, usar el título limpio en minúsculas
    return mb_strtolower($clean_title) . ', mars challenge';
}

// Procesar
$success = 0;
$errors = 0;
$results = array();

foreach ($items_to_process as $index => $item) {
    $num = $index + 1;
    
    // Asegurar que tenemos el ID
    $id = isset($item['id']) ? $item['id'] : 0;
    if (!$id) continue;

    $keyword = generate_focus_keyword($item);
    
    echo "[$num/" . count($items_to_process) . "] ID: $id\n";
    echo "   Keyword: $keyword\n";
    
    // Actualizar
    $result = update_rankmath_keyword($id, $keyword, $auth_header, $site_url);
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado\n\n";
        $success++;
        $results[] = array(
            'id' => $id,
            'keyword' => $keyword,
            'status' => 'success'
        );
    } else {
        echo "   ✗ Error {$result['code']}\n";
        if (isset($result['response']['message'])) echo "     {$result['response']['message']}\n";
        echo "\n";
        $errors++;
        $results[] = array(
            'id' => $id,
            'status' => 'error', 
            'code' => $result['code']
        );
    }
    
    usleep(300000); // 0.3s
}

// Guardar resultados
file_put_contents('keyword_update_results_' . date('Y-m-d_His') . '.json', 
    json_encode(array(
        'timestamp' => date('Y-m-d H:i:s'),
        'processed' => count($items_to_process),
        'success' => $success,
        'errors' => $errors,
        'results' => $results
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN DE KEYWORDS ===\n";
echo "Procesados: " . count($items_to_process) . "\n";
echo "✓ Exitosos: $success\n";
echo "✗ Errores: $errors\n";
