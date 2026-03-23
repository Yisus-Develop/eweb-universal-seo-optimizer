<?php
/**
 * Análisis de Alt Text en Imágenes - Mars Challenge
 * Escanea la biblioteca de medios para encontrar imágenes sin texto alternativo
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

echo "=== ANÁLISIS DE IMÁGENES (ALT TEXT) ===\n";
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

// Obtener Media
echo "Obteniendo biblioteca de medios...\n";
$all_media = array();
$page = 1;

do {
    $data = make_request($site_url . "/wp-json/wp/v2/media?per_page=100&page=$page&_fields=id,title,alt_text,source_url,media_details", $auth_header);
    if (!empty($data) && is_array($data)) {
        $all_media = array_merge($all_media, $data);
        $page++;
        echo "   Página " . ($page-1) . " cargada (" . count($data) . " items)...\r";
    } else {
        break;
    }
} while (count($data) == 100);

echo "\nTotal imágenes encontradas: " . count($all_media) . "\n\n";

$missing_alt = array();

foreach ($all_media as $media) {
    // Verificar si tiene alt text
    if (empty($media['alt_text'])) {
        $filename = basename($media['source_url']);
        $title = $media['title']['rendered'];
        
        $missing_alt[] = array(
            'id' => $media['id'],
            'filename' => $filename,
            'title' => $title,
            'url' => $media['source_url']
        );
    }
}

echo "=== RESULTADOS ===\n";
echo "Imágenes con Alt Text: " . (count($all_media) - count($missing_alt)) . "\n";
echo "Imágenes SIN Alt Text: " . count($missing_alt) . "\n\n";

if (!empty($missing_alt)) {
    echo "Primeros 10 casos sin Alt Text:\n";
    for ($i = 0; $i < min(10, count($missing_alt)); $i++) {
        echo "- ID {$missing_alt[$i]['id']}: {$missing_alt[$i]['filename']}\n";
    }
}

// Guardar reporte
file_put_contents('missing_alt_text_report.json', json_encode(array(
    'total' => count($all_media),
    'missing_count' => count($missing_alt),
    'items' => $missing_alt
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\nReporte guardado en missing_alt_text_report.json\n";
