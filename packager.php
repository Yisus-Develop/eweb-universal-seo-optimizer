#!/usr/bin/env php
<?php
/**
 * Script para empaquetar el plugin EWEB Universal SEO Optimizer
 * Crea un archivo ZIP listo para instalar en WordPress
 */

// Directorio base del proyecto
$base_dir = dirname(__DIR__, 2) . '/projects/marschallenge-seo/src';
$output_dir = dirname(__DIR__, 2) . '/projects/marschallenge-seo/dist';

// Crear directorio de salida si no existe
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0755, true);
}

// Archivos que van en el plugin
$plugin_files = [
    'eweb-universal-seo-optimizer.php',
    'eweb-universal-config-handler.php',
    'eweb-seo-config-template.php',
    'readme.txt'
];

// Crear directorio temporal para el plugin
$temp_plugin_dir = $output_dir . '/eweb-universal-seo-optimizer';
if (!file_exists($temp_plugin_dir)) {
    mkdir($temp_plugin_dir, 0755, true);
}

// Copiar archivos al directorio temporal
foreach ($plugin_files as $file) {
    $source = $base_dir . '/' . $file;
    $dest = $temp_plugin_dir . '/' . $file;
    
    if (file_exists($source)) {
        copy($source, $dest);
        echo "Copiado: $file\n";
    } else {
        echo "Advertencia: No se encontró $file\n";
    }
}

// Crear el archivo ZIP
$zip_filename = $output_dir . '/eweb-universal-seo-optimizer.zip';
$zip = new ZipArchive();
if ($zip->open($zip_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Agregar archivos al ZIP
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temp_plugin_dir)
    );
    
    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $relative_path = substr($file->getPathname(), strlen($temp_plugin_dir) + 1);
            $zip->addFile($file->getPathname(), 'eweb-universal-seo-optimizer/' . $relative_path);
        }
    }
    
    $zip->close();
    echo "\nPlugin empaquetado exitosamente en: $zip_filename\n";
    echo "Tamaño del archivo: " . formatBytes(filesize($zip_filename)) . "\n";
} else {
    echo "Error: No se pudo crear el archivo ZIP\n";
}

// Eliminar directorio temporal
removeDirectory($temp_plugin_dir);

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

function removeDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

echo "\nInstrucciones para instalar el plugin:\n";
echo "1. Accede al panel de administración de WordPress\n";
echo "2. Ve a Plugins > Añadir nuevo > Subir plugin\n";
echo "3. Selecciona el archivo: $zip_filename\n";
echo "4. Haz clic en 'Instalar ahora'\n";
echo "5. Activa el plugin desde Plugins > EWEB SEO\n";
?>