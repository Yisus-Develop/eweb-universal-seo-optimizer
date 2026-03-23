<?php
/**
 * Análisis completo HTML - Todas las páginas y custom post types
 * Verificación de cumplimiento con estándares Google (Title 30-60, Desc 120-160)
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

const TITLE_MIN = 30;
const TITLE_MAX = 60;
const DESC_MIN = 120;
const DESC_MAX = 160;

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
    
    return ($http_code === 200) ? json_decode($response, true) : null;
}

function fetch_html($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($http_code === 200) ? $html : null;
}

function extract_meta($html) {
    $meta = ['title' => '', 'description' => '', 'keywords' => ''];
    
    if (preg_match('/<title>([^<]+)<\/title>/i', $html, $m)) {
        $meta['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
    }
    
    if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
        $meta['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    
    if (preg_match('/<meta\s+name=["\']keywords["\']\s+content=["\'](.*?)["\']/i', $html, $m)) {
        $meta['keywords'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }
    
    return $meta;
}

echo "=== ANÁLISIS COMPLETO GOOGLE SEO ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Estándares: Título [" . TITLE_MIN . "-" . TITLE_MAX . "], Descripción [" . DESC_MIN . "-" . DESC_MAX . "]\n\n";

$all_items = [];
$stats = [
    'total' => 0,
    'compliant' => 0,
    'issues' => [
        'title_missing' => 0,
        'title_short' => 0,
        'title_long' => 0,
        'desc_missing' => 0,
        'desc_short' => 0,
        'desc_long' => 0,
        'keywords_missing' => 0
    ],
    'by_type' => []
];

foreach ($post_types as $type) {
    echo "Procesando $type...\n";
    $stats['by_type'][$type] = ['total' => 0, 'ok' => 0, 'issues' => 0];
    
    $page = 1;
    do {
        $url = "$site_url/wp-json/wp/v2/$type?per_page=100&page=$page&_fields=id,title,link";
        $items = make_request($url, $username, $password);
        
        if (!$items || empty($items)) break;
        
        foreach ($items as $item) {
            $stats['total']++;
            $stats['by_type'][$type]['total']++;
            
            // Obtener HTML
            $html = fetch_html($item['link']);
            if (!$html) {
                echo "  ⚠ Error obteniendo HTML para ID {$item['id']}\n";
                continue;
            }
            
            $meta = extract_meta($html);
            $issues = [];
            
            // Validar título
            $title_len = mb_strlen($meta['title']);
            if (empty($meta['title'])) {
                $issues[] = 'Sin título';
                $stats['issues']['title_missing']++;
            } else if ($title_len < TITLE_MIN) {
                $issues[] = "Título corto ({$title_len})";
                $stats['issues']['title_short']++;
            } else if ($title_len > TITLE_MAX) {
                $issues[] = "Título largo ({$title_len})";
                $stats['issues']['title_long']++;
            }
            
            // Validar descripción
            $desc_len = mb_strlen($meta['description']);
            if (empty($meta['description'])) {
                $issues[] = 'Sin descripción';
                $stats['issues']['desc_missing']++;
            } else if ($desc_len < DESC_MIN) {
                $issues[] = "Desc corta ({$desc_len})";
                $stats['issues']['desc_short']++;
            } else if ($desc_len > DESC_MAX) {
                $issues[] = "Desc larga ({$desc_len})";
                $stats['issues']['desc_long']++;
            }
            
            // Validar keywords
            if (empty($meta['keywords'])) {
                $issues[] = 'Sin keywords';
                $stats['issues']['keywords_missing']++;
            }
            
            $is_compliant = empty($issues);
            if ($is_compliant) {
                $stats['compliant']++;
                $stats['by_type'][$type]['ok']++;
            } else {
                $stats['by_type'][$type]['issues']++;
            }
            
            $all_items[] = [
                'id' => $item['id'],
                'type' => $type,
                'url' => $item['link'],
                'title' => $meta['title'],
                'title_length' => $title_len,
                'description' => $meta['description'],
                'desc_length' => $desc_len,
                'keywords' => $meta['keywords'],
                'issues' => $issues,
                'compliant' => $is_compliant
            ];
            
            usleep(200000); // 0.2s delay entre requests
        }
        
        $page++;
    } while (!empty($items) && count($items) === 100);
}

// Resumen
echo "\n" . str_repeat("=", 60) . "\n";
echo "RESUMEN GENERAL\n";
echo str_repeat("=", 60) . "\n";
echo sprintf("Total elementos:        %3d\n", $stats['total']);
echo sprintf("✓ Totalmente OK:        %3d (%5.1f%%)\n", 
    $stats['compliant'], 
    ($stats['compliant']/$stats['total'])*100
);
echo sprintf("✗ Con problemas:        %3d (%5.1f%%)\n", 
    $stats['total'] - $stats['compliant'],
    (($stats['total'] - $stats['compliant'])/$stats['total'])*100
);

echo "\n" . str_repeat("-", 60) . "\n";
echo "PROBLEMAS DETECTADOS\n";
echo str_repeat("-", 60) . "\n";
echo sprintf("Título faltante:        %3d\n", $stats['issues']['title_missing']);
echo sprintf("Título muy corto (<30): %3d\n", $stats['issues']['title_short']);
echo sprintf("Título muy largo (>60): %3d\n", $stats['issues']['title_long']);
echo sprintf("Desc faltante:          %3d\n", $stats['issues']['desc_missing']);
echo sprintf("Desc muy corta (<120):  %3d\n", $stats['issues']['desc_short']);
echo sprintf("Desc muy larga (>160):  %3d\n", $stats['issues']['desc_long']);
echo sprintf("Keywords faltantes:     %3d\n", $stats['issues']['keywords_missing']);

echo "\n" . str_repeat("-", 60) . "\n";
echo "CUMPLIMIENTO POR TIPO\n";
echo str_repeat("-", 60) . "\n";
foreach ($stats['by_type'] as $type => $data) {
    if ($data['total'] > 0) {
        $pct = ($data['ok']/$data['total'])*100;
        echo sprintf("%-25s: %3d items | ✓ %3d OK (%5.1f%%) | ✗ %3d\n", 
            $type, $data['total'], $data['ok'], $pct, $data['issues']
        );
    }
}

// Guardar reporte
$report = [
    'generated_at' => date('Y-m-d H:i:s'),
    'standards' => [
        'title_range' => [TITLE_MIN, TITLE_MAX],
        'desc_range' => [DESC_MIN, DESC_MAX]
    ],
    'summary' => $stats,
    'items' => $all_items
];

$filename = 'google_seo_compliance_' . date('Y-m-d_His') . '.json';
file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n✓ Reporte guardado: $filename\n";

// Mostrar items con problemas
$items_with_issues = array_filter($all_items, fn($i) => !$i['compliant']);
if (!empty($items_with_issues)) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "ITEMS CON PROBLEMAS (primeros 15)\n";
    echo str_repeat("=", 60) . "\n";
    
    $count = 0;
    foreach ($items_with_issues as $item) {
        if ($count >= 15) break;
        
        echo "\n[{$item['type']}] ID: {$item['id']}\n";
        echo "URL: {$item['url']}\n";
        echo "Título: {$item['title']} [{$item['title_length']} chars]\n";
        echo "Desc: " . substr($item['description'], 0, 80) . "... [{$item['desc_length']} chars]\n";
        echo "Problemas: " . implode(', ', $item['issues']) . "\n";
        
        $count++;
    }
    
    if (count($items_with_issues) > 15) {
        echo "\n... y " . (count($items_with_issues) - 15) . " items más.\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "ANÁLISIS COMPLETADO\n";
echo str_repeat("=", 60) . "\n";
