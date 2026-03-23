<?php
/**
 * Actualización masiva de metadescripciones para Mars Challenge
 * Genera y actualiza descripciones SEO-optimizadas vía WordPress REST API
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

// Configuración
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ACTUALIZACIÓN MASIVA DE METADESCRIPCIONES ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Cargar el reporte de verificación
$report_file = 'seo_verification_report_2025-11-28.json';
if (!file_exists($report_file)) {
    die("Error: No se encontró el archivo $report_file\n");
}

$report = json_decode(file_get_contents($report_file), true);
$missing_items = $report['missing_items'];

echo "Total de páginas a actualizar: " . count($missing_items) . "\n\n";

// Plantillas de metadescripciones SEO-optimizadas
$meta_templates = array(
    'default' => 'Descubre %s en Mars Challenge. Únete al movimiento global de innovación dual-planeta para crear soluciones sostenibles para la Tierra y Marte.',
    'registro' => 'Regístrate en Mars Challenge y participa en el reto global de innovación. %s para formar parte del cambio.',
    'sobre' => 'Conoce más sobre %s en Mars Challenge. Innovación, educación y sostenibilidad para un futuro mejor en la Tierra y Marte.',
    'hackimpacto' => '%s - HackImpacto Mars Challenge. Participa en el hackathon global de innovación dual-planeta y gana premios internacionales.',
    'empresas' => '%s para empresas en Mars Challenge. Responsabilidad social corporativa e innovación sostenible.',
    'participar' => '%s en Mars Challenge. Únete al reto global de innovación y sostenibilidad para jóvenes y educadores.',
    'seasons' => 'Explora %s de Mars Challenge. Retos temáticos de innovación: Agua, Fuego, Genesis y más.',
);

// Función para generar metadescripción
function generate_meta_description($title, $url) {
    global $meta_templates;
    
    $title_lower = mb_strtolower($title);
    
    // Detectar tipo de página
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
    
    // Asegurar que no exceda 160 caracteres
    if (strlen($description) > 160) {
        $description = substr($description, 0, 157) . '...';
    }
    
    return $description;
}

// Función para actualizar metadescripción vía API
function update_meta_description($post_id, $description, $auth_header, $site_url) {
    $endpoint = $site_url . '/wp-json/wp/v2/posts/' . $post_id;
    
    // Intentar actualizar usando el campo meta de Rank Math
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

// Procesar cada página
$success_count = 0;
$error_count = 0;
$results = array();

foreach ($missing_items as $index => $item) {
    $num = $index + 1;
    echo "[$num/" . count($missing_items) . "] Procesando: {$item['title']}\n";
    
    // Generar metadescripción
    $description = generate_meta_description($item['title'], $item['url']);
    echo "   Descripción: $description\n";
    
    // Intentar actualizar
    $result = update_meta_description($item['id'], $description, $auth_header, $site_url);
    
    if ($result['code'] == 200) {
        echo "   ✓ Actualizado exitosamente\n\n";
        $success_count++;
        $results[] = array(
            'id' => $item['id'],
            'title' => $item['title'],
            'description' => $description,
            'status' => 'success'
        );
    } else {
        echo "   ✗ Error (Código: {$result['code']})\n";
        if (isset($result['response']['message'])) {
            echo "   Mensaje: {$result['response']['message']}\n";
        }
        echo "\n";
        $error_count++;
        $results[] = array(
            'id' => $item['id'],
            'title' => $item['title'],
            'description' => $description,
            'status' => 'error',
            'error_code' => $result['code'],
            'error_message' => $result['response']['message'] ?? 'Unknown error'
        );
    }
    
    // Pausa para no sobrecargar el servidor
    usleep(500000); // 0.5 segundos
}

// Guardar resultados
$results_file = 'meta_update_results_' . date('Y-m-d_His') . '.json';
file_put_contents($results_file, json_encode(array(
    'timestamp' => date('Y-m-d H:i:s'),
    'total_processed' => count($missing_items),
    'success' => $success_count,
    'errors' => $error_count,
    'results' => $results
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== RESUMEN FINAL ===\n";
echo "Total procesado: " . count($missing_items) . "\n";
echo "✓ Exitosos: $success_count\n";
echo "✗ Errores: $error_count\n";
echo "\nResultados guardados en: $results_file\n";

if ($error_count > 0) {
    echo "\nNOTA: Si hubo errores, puede ser porque:\n";
    echo "1. Rank Math no permite actualizar el campo 'rank_math_description' vía API\n";
    echo "2. Se necesita un plugin adicional o acceso directo a la base de datos\n";
    echo "3. Los permisos del usuario no son suficientes\n\n";
    echo "Alternativa: Usar el archivo CSV generado para actualización manual\n";
}
