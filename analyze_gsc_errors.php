<?php
/**
 * Análisis de Errores GSC y Generador de Redirecciones
 * Lee el CSV de Search Console y genera un archivo de importación para Rank Math
 */

$csv_file = 'ai-artifacts/assets/Tablav2.csv';
$site_url = 'https://mars-challenge.com';

echo "=== ANÁLISIS DE ERRORES GSC ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

if (!file_exists($csv_file)) {
    die("Error: No se encuentra el archivo $csv_file\n");
}

// Leer CSV
$lines = file($csv_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$urls = array();

// Saltar cabecera
array_shift($lines);

foreach ($lines as $line) {
    $parts = str_getcsv($line);
    if (isset($parts[0])) {
        $urls[] = $parts[0];
    }
}

echo "URLs encontradas en reporte GSC: " . count($urls) . "\n\n";

// Verificar estado actual
echo "Verificando estado actual de URLs...\n";
$redirects = array();
$still_broken = array();

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // No seguir redirecciones para ver el estado real
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "[$code] $url\n";
    
    if ($code == 404) {
        // Lógica de redirección inteligente
        $path = parse_url($url, PHP_URL_PATH);
        $target = '';
        
        // Reglas
        if (strpos($path, '/noticia/') !== false) {
            // Redirigir noticias antiguas a la sección de prensa o home
            $target = $site_url . '/sobre/prensa/';
        } elseif (strpos($path, '/colombia/') !== false) {
            // Redirigir subdirectorios de países a registro de países o home
            $target = $site_url . '/participar/registro-de-paises/';
        } elseif (strpos($path, '/author/') !== false) {
            // Autores al home
            $target = $site_url . '/';
        } elseif (strpos($path, 'hello-world') !== false) {
            // Hello world al home
            $target = $site_url . '/';
        } elseif (strpos($path, 'wp-admin') !== false) {
            // Admin links ignorar
            continue;
        } else {
            // Default: Home
            $target = $site_url . '/';
        }
        
        $redirects[] = array(
            'source' => $url,
            'target' => $target,
            'code' => 301
        );
        $still_broken[] = $url;
    }
}

echo "\n=== RESUMEN ===\n";
echo "URLs rotas (404) confirmadas: " . count($still_broken) . "\n";
echo "Redirecciones generadas: " . count($redirects) . "\n\n";

// Generar CSV para Rank Math
// Formato Rank Math: Source URL, Destination URL, Type (301/302/410)
if (!empty($redirects)) {
    $rm_csv = "Source URL,Destination URL,Type\n";
    foreach ($redirects as $r) {
        // Rank Math prefiere rutas relativas para source si es el mismo dominio, pero absolutas funcionan
        $source_path = parse_url($r['source'], PHP_URL_PATH);
        $rm_csv .= "{$source_path},{$r['target']},301\n";
    }
    
    file_put_contents('rankmath_redirects_import.csv', $rm_csv);
    echo "Archivo de importación generado: rankmath_redirects_import.csv\n";
    echo "Instrucciones: Importar este archivo en Rank Math > Redirections > Import/Export\n";
}
