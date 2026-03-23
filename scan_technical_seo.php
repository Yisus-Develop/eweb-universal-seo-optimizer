<?php
/**
 * Escaneo Técnico SEO - Mars Challenge
 * Detecta páginas con 'noindex' y verifica enlaces rotos (404)
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ESCANEO TÉCNICO SEO ===\n";
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

// 1. Obtener todas las páginas y posts
echo "1. Obteniendo lista de contenidos...\n";
$all_items = array();

// Posts
$page = 1;
do {
    $data = make_request($site_url . "/wp-json/wp/v2/posts?per_page=100&page=$page&_fields=id,title,link,meta,type,status", $auth_header);
    if (!empty($data) && is_array($data)) {
        $all_items = array_merge($all_items, $data);
        $page++;
    } else {
        break;
    }
} while (count($data) == 100);

// Pages
$page = 1;
do {
    $data = make_request($site_url . "/wp-json/wp/v2/pages?per_page=100&page=$page&_fields=id,title,link,meta,type,status", $auth_header);
    if (!empty($data) && is_array($data)) {
        $all_items = array_merge($all_items, $data);
        $page++;
    } else {
        break;
    }
} while (count($data) == 100);

echo "   Total elementos encontrados: " . count($all_items) . "\n\n";

// 2. Analizar Noindex
echo "2. Analizando etiquetas 'noindex'...\n";
$noindex_items = array();

foreach ($all_items as $item) {
    $robots_meta = isset($item['meta']['rank_math_robots']) ? $item['meta']['rank_math_robots'] : array();
    
    // Si rank_math_robots es un string, convertirlo a array (a veces pasa)
    if (is_string($robots_meta)) {
        $robots_meta = explode(',', $robots_meta);
    }
    
    if (in_array('noindex', $robots_meta)) {
        $noindex_items[] = array(
            'id' => $item['id'],
            'title' => $item['title']['rendered'],
            'url' => $item['link'],
            'type' => $item['type']
        );
        echo "   [NOINDEX] {$item['title']['rendered']} ({$item['link']})\n";
    }
}

if (empty($noindex_items)) {
    echo "   ¡Excelente! No se encontraron páginas con 'noindex'.\n";
} else {
    echo "   Se encontraron " . count($noindex_items) . " páginas con 'noindex'.\n";
}
echo "\n";

// 3. Verificar 404s (Simulado verificando si el link es accesible)
// Nota: Un escaneo real de 404s internos requiere un crawler completo. 
// Aquí verificaremos si las URLs de los posts/páginas listados son accesibles.
echo "3. Verificando accesibilidad de URLs (Status Code)...\n";
$broken_urls = array();

// Solo verificamos una muestra o todos si son pocos, para no tardar tanto
$count = 0;
foreach ($all_items as $item) {
    $url = $item['link'];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($code >= 400) {
        $broken_urls[] = array(
            'id' => $item['id'],
            'title' => $item['title']['rendered'],
            'url' => $url,
            'code' => $code
        );
        echo "   [ERROR $code] {$url}\n";
    }
    
    $count++;
    if ($count % 10 == 0) echo "   Procesados $count/" . count($all_items) . "...\r";
}
echo "\n";

if (empty($broken_urls)) {
    echo "   ¡Excelente! Todas las URLs principales responden correctamente.\n";
} else {
    echo "   Se encontraron " . count($broken_urls) . " URLs con error.\n";
}

// Guardar reporte
$report = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'noindex_items' => $noindex_items,
    'broken_urls' => $broken_urls
);

file_put_contents('technical_seo_report.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\nReporte guardado en technical_seo_report.json\n";
