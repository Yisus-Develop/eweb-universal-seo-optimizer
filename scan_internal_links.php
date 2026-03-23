<?php
/**
 * Escaneo Profundo de Enlaces Internos - Mars Challenge
 * Analiza el contenido HTML de cada página para encontrar enlaces rotos
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ESCANEO DE ENLACES INTERNOS ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Función para hacer peticiones API
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

// Función para verificar URL (HEAD request)
function check_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    // User agent para evitar bloqueos
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code;
}

// 1. Obtener contenido
echo "1. Obteniendo contenido de páginas...\n";
$all_items = array();

// Pages only (donde suelen estar los links estructurales)
$page = 1;
do {
    $data = make_request($site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page&_fields=id,title,link,content", $auth_header);
    if (!empty($data) && is_array($data)) {
        $all_items = array_merge($all_items, $data);
        $page++;
    } else {
        break;
    }
} while (count($data) == 50);

echo "   Analizando " . count($all_items) . " páginas...\n\n";

$broken_links = array();
$checked_urls = array(); // Cache para no verificar la misma URL mil veces

foreach ($all_items as $index => $item) {
    $content = $item['content']['rendered'];
    $source_url = $item['link'];
    $title = $item['title']['rendered'];
    
    // Extraer hrefs
    preg_match_all('/href="([^"]+)"/', $content, $matches);
    $links = $matches[1];
    
    // Filtrar solo enlaces internos
    $internal_links = array_filter($links, function($link) use ($site_url) {
        return strpos($link, $site_url) !== false && strpos($link, 'wp-json') === false && strpos($link, 'wp-admin') === false;
    });
    
    if (empty($internal_links)) continue;
    
    echo "[$index] $title: " . count($internal_links) . " enlaces internos\n";
    
    foreach ($internal_links as $link) {
        // Normalizar URL (quitar anchors)
        $link_clean = explode('#', $link)[0];
        if (empty($link_clean) || $link_clean == $site_url . '/') continue;
        
        if (!isset($checked_urls[$link_clean])) {
            $code = check_url($link_clean);
            $checked_urls[$link_clean] = $code;
            usleep(100000); // 0.1s pausa
        } else {
            $code = $checked_urls[$link_clean];
        }
        
        if ($code >= 400) {
            echo "   ✗ ROTO ($code): $link_clean\n";
            $broken_links[] = array(
                'source_page' => $title,
                'source_url' => $source_url,
                'broken_link' => $link_clean,
                'status_code' => $code
            );
        }
    }
}

echo "\n=== RESUMEN DE ENLACES ROTOS ===\n";
if (empty($broken_links)) {
    echo "¡Increíble! No se encontraron enlaces internos rotos en el contenido.\n";
} else {
    echo "Se encontraron " . count($broken_links) . " enlaces rotos.\n";
    foreach ($broken_links as $broken) {
        echo "- {$broken['broken_link']} (en {$broken['source_page']})\n";
    }
}

// Guardar reporte
file_put_contents('broken_links_report.json', json_encode($broken_links, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
