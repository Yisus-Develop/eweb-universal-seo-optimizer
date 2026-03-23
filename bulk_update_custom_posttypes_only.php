<?php
/**
 * Actualización masiva SOLO para Custom Post Types - Mars Challenge
 * Excluye 'post' y 'page' que ya fueron actualizados anteriormente
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN CUSTOM POST TYPES ÚNICAMENTE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Custom post types a procesar (excluimos 'post' y 'page')
$target_post_types = array(
    'instituciones',
    'empresas_aliadas',
    'landing_paises',
    'quienes-sirven',
    'participa',
    'tematicas_y_elemento',
    'logos',
    'testimonios',
    'country_page'
);

// Cargar análisis
$analysis_file = 'seo_titles_analysis.json';
if (!file_exists($analysis_file)) {
    die("Error: No se encuentra $analysis_file. Ejecuta analyze_seo_titles.php primero.\n");
}

$data = json_decode(file_get_contents($analysis_file), true);

// Filtrar SOLO custom post types
$items_to_process = array_filter($data['details'], function($item) use ($target_post_types) {
    return in_array($item['type'], $target_post_types);
});

echo "Total en análisis: " . count($data['details']) . "\n";
echo "Custom post types a procesar: " . count($items_to_process) . "\n\n";

// Mostrar desglose por tipo
$by_type = array();
foreach ($items_to_process as $item) {
    $type = $item['type'];
    if (!isset($by_type[$type])) $by_type[$type] = 0;
    $by_type[$type]++;
}

echo "Desglose por tipo:\n";
foreach ($by_type as $type => $count) {
    echo "  - $type: $count items\n";
}
echo "\n";

// Función para actualizar usando Rank Math API
function update_rankmath_meta($post_id, $title, $description, $keyword, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/rankmath/v1/updateMeta';
    
    $payload = array(
        'objectID' => $post_id,
        'objectType' => 'post', // Rank Math acepta 'post' genéricamente
        'meta' => array(
            'rank_math_title' => $title,
            'rank_math_description' => $description,
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

// Generador de títulos
function generate_seo_title($original_title, $url, $type) {
    $title = trim(str_replace('&#8211;', '-', $original_title));
    
    // Por tipo de post type
    switch ($type) {
        case 'instituciones':
            return "$title | Instituciones Mars Challenge";
        case 'empresas_aliadas':
            return "$title | Empresas Aliadas Mars Challenge";
        case 'landing_paises':
            return "Mars Challenge $title | Innovación Dual-Planeta";
        case 'quienes-sirven':
            return "$title | Quiénes Sirven - Mars Challenge";
        case 'participa':
            return "$title | Participa en Mars Challenge";
        case 'tematicas_y_elemento':
            return "$title | Temáticas Mars Challenge";
        case 'logos':
            return "$title | Mars Challenge";
        case 'testimonios':
            return "$title | Testimonios Mars Challenge";
        case 'country_page':
            return "Mars Challenge $title | País Participante";
        default:
            return "$title | Mars Challenge";
    }
}

// Generador de descripciones
function generate_description($title, $type) {
    switch ($type) {
        case 'instituciones':
            return "Conoce $title, institución participante en Mars Challenge. Red global de innovación dual-planeta para soluciones sostenibles.";
        case 'empresas_aliadas':
            return "$title - Empresa aliada de Mars Challenge. Colaboración corporativa en innovación espacial y sostenibilidad terrestre.";
        case 'landing_paises':
            return "Mars Challenge en $title. Únete al movimiento global de innovación dual-planeta. Participa en el reto educativo más importante.";
        case 'quienes-sirven':
            return "Descubre $title en Mars Challenge. Información sobre participantes, beneficios y cómo formar parte del movimiento global.";
        case 'participa':
            return "Participa: $title en Mars Challenge. Únete al reto global de innovación y sostenibilidad para jóvenes y educadores.";
        case 'tematicas_y_elemento':
            return "Explora $title - Temática Mars Challenge. Retos de innovación dual-planeta: soluciones para la Tierra y Marte.";
        case 'logos':
            return "$title - Mars Challenge";
        case 'testimonios':
            return "Testimonio: $title. Experiencias reales de participantes en Mars Challenge, el reto global de innovación espacial.";
        case 'country_page':
            return "Mars Challenge $title - Página oficial del país. Descubre cómo participar en el reto global de innovación dual-planeta.";
        default:
            return "Descubre $title en Mars Challenge. Movimiento global de innovación dual-planeta para soluciones sostenibles.";
    }
}

// Generador de keywords
function generate_keyword($title, $type) {
    $base = strtolower($title);
    return "$base, mars challenge, " . str_replace('_', ' ', $type);
}

// Procesar
$success = 0;
$errors = 0;
$results = array();

foreach ($items_to_process as $index => $item) {
    $num = $index + 1;
    
    $new_title = generate_seo_title($item['wp_title'], $item['url'], $item['type']);
    $new_description = generate_description($item['wp_title'], $item['type']);
    $new_keyword = generate_keyword($item['wp_title'], $item['type']);
    
    echo "[$num/" . count($items_to_process) . "] ID: {$item['id']} ({$item['type']})\n";
    echo "   Original: {$item['wp_title']}\n";
    echo "   Título:   $new_title\n";
    echo "   Desc:     " . substr($new_description, 0, 60) . "...\n";
    
    // Actualizar
    $result = update_rankmath_meta(
        $item['id'], 
        $new_title, 
        $new_description, 
        $new_keyword,
        $auth_header, 
        $site_url
    );
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado\n\n";
        $success++;
        $results[] = array(
            'id' => $item['id'],
            'type' => $item['type'],
            'old_title' => $item['wp_title'],
            'new_title' => $new_title,
            'new_description' => $new_description,
            'new_keyword' => $new_keyword,
            'status' => 'success'
        );
    } else {
        echo "   ✗ Error {$result['code']}\n";
        if (isset($result['response']['message'])) {
            echo "     {$result['response']['message']}\n";
        }
        echo "\n";
        $errors++;
        $results[] = array(
            'id' => $item['id'],
            'type' => $item['type'],
            'status' => 'error',
            'code' => $result['code'],
            'message' => $result['response']['message'] ?? 'Unknown error'
        );
    }
    
    usleep(500000); // 0.5s delay
}

// Guardar resultados
$results_file = 'custom_posttypes_update_results_' . date('Y-m-d_His') . '.json';
file_put_contents($results_file, 
    json_encode(array(
        'timestamp' => date('Y-m-d H:i:s'),
        'target_types' => $target_post_types,
        'processed' => count($items_to_process),
        'success' => $success,
        'errors' => $errors,
        'by_type' => $by_type,
        'results' => $results
    ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN FINAL ===\n";
echo "Custom post types procesados: " . count($items_to_process) . "\n";
echo "✓ Exitosos: $success\n";
echo "✗ Errores: $errors\n";
echo "\nResultados guardados en: $results_file\n";
