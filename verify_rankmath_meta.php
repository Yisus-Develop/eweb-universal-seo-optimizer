<?php
/**
 * Verificación Final de Metadatos Rank Math
 * Consulta directamente la API de Rank Math para verificar cumplimiento con estándares de Google
 */

// Configuración
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

// Estándares de Google
const TITLE_MIN_LENGTH = 30;
const TITLE_MAX_LENGTH = 60;
const DESC_MIN_LENGTH = 120;
const DESC_MAX_LENGTH = 160;

// Post types a verificar
$post_types = [
    'post',
    'page',
    'instituciones',
    'empresas_aliadas',
    'landing_paises',
    'quienes-sirven',
    'participa',
    'tematicas_y_elemento',
    'logos',
    'testimonios',
    'country_page'
];

function make_request($url, $username, $password) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$username:$password")
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        return null;
    }
    
    return json_decode($response, true);
}

function get_rankmath_meta($site_url, $post_id, $username, $password) {
    $url = "$site_url/wp-json/rankmath/v1/getHead?objectID=$post_id&objectType=post";
    return make_request($url, $username, $password);
}

echo "=== VERIFICACIÓN FINAL RANK MATH ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Estándares Google: Título {" . TITLE_MIN_LENGTH . "-" . TITLE_MAX_LENGTH . "}, Descripción {" . DESC_MIN_LENGTH . "-" . DESC_MAX_LENGTH . "}\n\n";

$all_items = [];
$stats = [
    'total' => 0,
    'ok_title' => 0,
    'ok_desc' => 0,
    'ok_keyword' => 0,
    'fully_compliant' => 0,
    'issues' => [
        'no_title' => 0,
        'title_too_short' => 0,
        'title_too_long' => 0,
        'no_desc' => 0,
        'desc_too_short' => 0,
        'desc_too_long' => 0,
        'no_keyword' => 0
    ],
    'by_type' => []
];

foreach ($post_types as $type) {
    echo "Consultando $type...\n";
    $stats['by_type'][$type] = ['total' => 0, 'compliant' => 0, 'issues' => 0];
    
    $page = 1;
    do {
        $url = "$site_url/wp-json/wp/v2/$type?per_page=100&page=$page&_fields=id,title,link";
        $items = make_request($url, $username, $password);
        
        if (!$items || empty($items)) {
            break;
        }
        
        foreach ($items as $item) {
            $stats['total']++;
            $stats['by_type'][$type]['total']++;
            
            // Obtener metadatos de Rank Math
            $meta = get_rankmath_meta($site_url, $item['id'], $username, $password);
            
            $title = '';
            $description = '';
            $keyword = '';
            $issues = [];
            
            if ($meta && isset($meta['head'])) {
                // Extraer título
                if (preg_match('/<title>(.*?)<\/title>/', $meta['head'], $matches)) {
                    $title = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
                }
                
                // Extraer descripción
                if (preg_match('/<meta name="description" content="(.*?)"/', $meta['head'], $matches)) {
                    $description = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
                }
                
                // Extraer keyword
                if (preg_match('/<meta name="keywords" content="(.*?)"/', $meta['head'], $matches)) {
                    $keyword = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
                }
            }
            
            // Validar título
            $title_length = mb_strlen($title);
            if (empty($title)) {
                $issues[] = 'Sin título SEO';
                $stats['issues']['no_title']++;
            } else if ($title_length < TITLE_MIN_LENGTH) {
                $issues[] = "Título muy corto ({$title_length} chars)";
                $stats['issues']['title_too_short']++;
            } else if ($title_length > TITLE_MAX_LENGTH) {
                $issues[] = "Título muy largo ({$title_length} chars)";
                $stats['issues']['title_too_long']++;
            } else {
                $stats['ok_title']++;
            }
            
            // Validar descripción
            $desc_length = mb_strlen($description);
            if (empty($description)) {
                $issues[] = 'Sin descripción SEO';
                $stats['issues']['no_desc']++;
            } else if ($desc_length < DESC_MIN_LENGTH) {
                $issues[] = "Descripción muy corta ({$desc_length} chars)";
                $stats['issues']['desc_too_short']++;
            } else if ($desc_length > DESC_MAX_LENGTH) {
                $issues[] = "Descripción muy larga ({$desc_length} chars)";
                $stats['issues']['desc_too_long']++;
            } else {
                $stats['ok_desc']++;
            }
            
            // Validar keyword
            if (empty($keyword)) {
                $issues[] = 'Sin keyword';
                $stats['issues']['no_keyword']++;
            } else {
                $stats['ok_keyword']++;
            }
            
            // Contabilizar cumplimiento total
            if (empty($issues)) {
                $stats['fully_compliant']++;
                $stats['by_type'][$type]['compliant']++;
            } else {
                $stats['by_type'][$type]['issues']++;
            }
            
            $all_items[] = [
                'id' => $item['id'],
                'type' => $type,
                'url' => $item['link'],
                'wp_title' => $item['title']['rendered'] ?? '',
                'seo_title' => $title,
                'title_length' => $title_length,
                'description' => $description,
                'desc_length' => $desc_length,
                'keyword' => $keyword,
                'issues' => $issues,
                'compliant' => empty($issues)
            ];
        }
        
        $page++;
        usleep(300000); // 0.3s entre páginas
        
    } while (!empty($items) && count($items) === 100);
}

echo "\n=== RESUMEN GENERAL ===\n";
echo "Total elementos: {$stats['total']}\n";
echo "✓ Totalmente compatibles: {$stats['fully_compliant']} (" . round(($stats['fully_compliant']/$stats['total'])*100, 1) . "%)\n";
echo "✓ Títulos OK: {$stats['ok_title']}\n";
echo "✓ Descripciones OK: {$stats['ok_desc']}\n";
echo "✓ Keywords OK: {$stats['ok_keyword']}\n\n";

echo "=== PROBLEMAS DETECTADOS ===\n";
echo "Sin título SEO: {$stats['issues']['no_title']}\n";
echo "Título muy corto: {$stats['issues']['title_too_short']}\n";
echo "Título muy largo: {$stats['issues']['title_too_long']}\n";
echo "Sin descripción: {$stats['issues']['no_desc']}\n";
echo "Descripción muy corta: {$stats['issues']['desc_too_short']}\n";
echo "Descripción muy larga: {$stats['issues']['desc_too_long']}\n";
echo "Sin keyword: {$stats['issues']['no_keyword']}\n\n";

echo "=== CUMPLIMIENTO POR TIPO ===\n";
foreach ($stats['by_type'] as $type => $data) {
    if ($data['total'] > 0) {
        $pct = round(($data['compliant']/$data['total'])*100, 1);
        echo sprintf("%-25s: %3d items | ✓ %3d OK (%5.1f%%) | ✗ %3d problemas\n", 
            $type, $data['total'], $data['compliant'], $pct, $data['issues']);
    }
}

// Guardar reporte detallado
$report = [
    'generated_at' => date('Y-m-d H:i:s'),
    'standards' => [
        'title_min' => TITLE_MIN_LENGTH,
        'title_max' => TITLE_MAX_LENGTH,
        'desc_min' => DESC_MIN_LENGTH,
        'desc_max' => DESC_MAX_LENGTH
    ],
    'summary' => $stats,
    'items' => $all_items
];

$filename = 'rankmath_verification_' . date('Y-m-d_His') . '.json';
file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n✓ Reporte detallado guardado en: $filename\n";

// Mostrar items con problemas si existen
$items_with_issues = array_filter($all_items, fn($item) => !$item['compliant']);
if (!empty($items_with_issues)) {
    echo "\n=== ITEMS CON PROBLEMAS (primeros 10) ===\n";
    $count = 0;
    foreach ($items_with_issues as $item) {
        if ($count >= 10) break;
        echo "\n[{$item['type']}] ID: {$item['id']}\n";
        echo "  URL: {$item['url']}\n";
        echo "  Título: " . ($item['seo_title'] ?: '(vacío)') . " [{$item['title_length']} chars]\n";
        echo "  Descripción: " . substr($item['description'], 0, 80) . "... [{$item['desc_length']} chars]\n";
        echo "  Problemas: " . implode(', ', $item['issues']) . "\n";
        $count++;
    }
    
    if (count($items_with_issues) > 10) {
        echo "\n... y " . (count($items_with_issues) - 10) . " items más con problemas.\n";
    }
}

echo "\n=== VERIFICACIÓN COMPLETADA ===\n";
