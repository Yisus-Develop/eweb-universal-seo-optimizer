<?php
/**
 * Verificación del estado actual de optimizaciones SEO en Mars Challenge
 * Compara el estado actual con el reporte de noviembre 2025
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao'; // Keeping spaces as-is

// Configuración de autenticación
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== VERIFICACIÓN SEO - MARS CHALLENGE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Sitio: $site_url\n\n";

// Función auxiliar para hacer peticiones a la API
function make_api_request($url, $auth_header) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array($auth_header),
        CURLOPT_TIMEOUT => 30
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'code' => $http_code,
        'data' => json_decode($response, true)
    );
}

// 1. Verificar autenticación
echo "1. Verificando autenticación...\n";
$auth_test = make_api_request($site_url . '/wp-json/wp/v2/users/me', $auth_header);
if ($auth_test['code'] == 200) {
    echo "   ✓ Autenticación exitosa\n";
    echo "   Usuario: " . ($auth_test['data']['name'] ?? 'N/A') . "\n\n";
} else {
    echo "   ✗ Error de autenticación (Código: {$auth_test['code']})\n";
    exit(1);
}

// 2. Obtener todos los posts y páginas
echo "2. Obteniendo contenido del sitio...\n";
$all_content = array();
$page = 1;
$per_page = 100;

do {
    $response = make_api_request(
        $site_url . "/wp-json/wp/v2/posts?per_page=$per_page&page=$page&_fields=id,title,link,meta",
        $auth_header
    );
    
    if ($response['code'] == 200 && !empty($response['data'])) {
        $all_content = array_merge($all_content, $response['data']);
        $page++;
    } else {
        break;
    }
} while (count($response['data']) == $per_page);

// También obtener páginas
$page = 1;
do {
    $response = make_api_request(
        $site_url . "/wp-json/wp/v2/pages?per_page=$per_page&page=$page&_fields=id,title,link,meta",
        $auth_header
    );
    
    if ($response['code'] == 200 && !empty($response['data'])) {
        $all_content = array_merge($all_content, $response['data']);
        $page++;
    } else {
        break;
    }
} while (count($response['data']) == $per_page);

echo "   Total de elementos encontrados: " . count($all_content) . "\n\n";

// 3. Analizar metadescripciones
echo "3. Analizando metadescripciones...\n";
$missing_descriptions = array();
$has_descriptions = 0;

foreach ($all_content as $item) {
    $meta = $item['meta'] ?? array();
    $description = $meta['rank_math_description'] ?? '';
    
    if (empty($description)) {
        $missing_descriptions[] = array(
            'id' => $item['id'],
            'title' => $item['title']['rendered'] ?? 'Sin título',
            'url' => $item['link'] ?? 'N/A'
        );
    } else {
        $has_descriptions++;
    }
}

echo "   ✓ Con metadescripción: $has_descriptions\n";
echo "   ✗ Sin metadescripción: " . count($missing_descriptions) . "\n\n";

// 4. Comparar con el reporte anterior (53 faltantes)
$previous_missing = 53;
$current_missing = count($missing_descriptions);
$improvement = $previous_missing - $current_missing;

echo "4. Comparación con reporte anterior (2025-11-07):\n";
echo "   Faltantes anteriores: $previous_missing\n";
echo "   Faltantes actuales: $current_missing\n";

if ($improvement > 0) {
    echo "   ✓ MEJORA: Se agregaron $improvement metadescripciones\n";
} elseif ($improvement < 0) {
    echo "   ✗ RETROCESO: Faltan " . abs($improvement) . " más que antes\n";
} else {
    echo "   = SIN CAMBIOS: Mismo número de faltantes\n";
}
echo "\n";

// 5. Mostrar elementos sin descripción (top 10)
if (count($missing_descriptions) > 0) {
    echo "5. Elementos sin metadescripción (primeros 10):\n";
    foreach (array_slice($missing_descriptions, 0, 10) as $item) {
        echo "   - " . $item['title'] . "\n";
        echo "     URL: " . $item['url'] . "\n";
    }
    echo "\n";
}

// 6. Generar reporte JSON
$report = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'site' => $site_url,
    'total_items' => count($all_content),
    'with_description' => $has_descriptions,
    'missing_description' => $current_missing,
    'previous_missing' => $previous_missing,
    'improvement' => $improvement,
    'missing_items' => $missing_descriptions
);

$report_file = 'seo_verification_report_' . date('Y-m-d') . '.json';
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "6. Reporte guardado en: $report_file\n\n";

// 7. Resumen final
echo "=== RESUMEN ===\n";
if ($current_missing == 0) {
    echo "✓ ¡EXCELENTE! Todas las páginas tienen metadescripciones\n";
} elseif ($improvement > 0) {
    echo "✓ PROGRESO: Se han agregado $improvement metadescripciones desde el último reporte\n";
    echo "  Aún faltan $current_missing por completar\n";
} else {
    echo "✗ NO SE HAN APLICADO LAS OPTIMIZACIONES\n";
    echo "  Todavía faltan $current_missing metadescripciones\n";
}

echo "\nPróximos pasos recomendados:\n";
if ($current_missing > 0) {
    echo "- Revisar el archivo CSV: pending_descriptions_rankmath.csv\n";
    echo "- Actualizar manualmente desde el panel de WordPress\n";
    echo "- O ejecutar: rankmath_bulk_update.php (requiere acceso al servidor)\n";
}
