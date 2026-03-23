<?php
/**
 * Análisis de Títulos SEO - Mars Challenge
 * Obtiene los títulos actuales (WP y Rank Math) para análisis
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ANÁLISIS DE TÍTULOS SEO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Función para hacer peticiones
function make_request($url, $auth_header) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array($auth_header),
        CURLOPT_TIMEOUT => 30
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Obtener Posts, Páginas y Custom Post Types
$all_items = array();

// Post types a consultar
$post_types = array(
    'posts',
    'pages',
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

foreach ($post_types as $post_type) {
    echo "Consultando $post_type...\n";
    $page = 1;
    do {
        $data = make_request($site_url . "/wp-json/wp/v2/$post_type?per_page=100&page=$page&_fields=id,title,link,meta,type", $auth_header);
        if (!empty($data) && is_array($data)) {
            $all_items = array_merge($all_items, $data);
            echo "  - Página $page: " . count($data) . " items\n";
            $page++;
            usleep(500000); // 0.5s delay entre requests
        } else {
            break;
        }
    } while (count($data) == 100);
}

echo "Total elementos analizados: " . count($all_items) . "\n\n";

$analysis = array();
$issues = array(
    'missing_rm_title' => 0,
    'too_short' => 0, // < 30 chars
    'too_long' => 0,  // > 60 chars
    'duplicate' => 0,
    'default_format' => 0 // Titles that look like just "Page Name" without branding
);

$titles_seen = array();

foreach ($all_items as $item) {
    $wp_title = $item['title']['rendered'];
    $rm_title = $item['meta']['rank_math_title'] ?? '';
    
    // Si no hay título RM explícito, Rank Math usa el título WP + Separador + Sitio por defecto
    // Pero para el análisis, queremos saber si se ha optimizado manualmente
    
    $effective_title = !empty($rm_title) ? $rm_title : $wp_title;
    $length = mb_strlen($effective_title);
    
    $item_issues = array();
    
    if (empty($rm_title)) {
        $item_issues[] = 'No tiene título Rank Math personalizado';
        $issues['missing_rm_title']++;
    }
    
    if ($length < 30) {
        $item_issues[] = "Muy corto ($length caracteres)";
        $issues['too_short']++;
    } elseif ($length > 60) {
        $item_issues[] = "Muy largo ($length caracteres)";
        $issues['too_long']++;
    }
    
    if (isset($titles_seen[$effective_title])) {
        $item_issues[] = "Duplicado con ID " . $titles_seen[$effective_title];
        $issues['duplicate']++;
    }
    $titles_seen[$effective_title] = $item['id'];
    
    // Detectar formato por defecto (solo nombre de página)
    if ($effective_title == $wp_title && strpos($effective_title, 'Mars Challenge') === false) {
        $item_issues[] = 'Posible falta de branding (Mars Challenge)';
        $issues['default_format']++;
    }

    $analysis[] = array(
        'id' => $item['id'],
        'type' => $item['type'],
        'url' => $item['link'],
        'wp_title' => $wp_title,
        'rm_title' => $rm_title,
        'length' => $length,
        'issues' => $item_issues
    );
}

// Guardar reporte
file_put_contents('seo_titles_analysis.json', json_encode(array(
    'summary' => $issues,
    'details' => $analysis
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "=== RESUMEN DE PROBLEMAS ===\n";
echo "Sin título Rank Math personalizado: {$issues['missing_rm_title']}\n";
echo "Muy cortos (<30): {$issues['too_short']}\n";
echo "Muy largos (>60): {$issues['too_long']}\n";
echo "Duplicados: {$issues['duplicate']}\n";
echo "Posible falta de branding: {$issues['default_format']}\n\n";

echo "Detalles guardados en seo_titles_analysis.json\n";
