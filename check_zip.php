<?php
// Script para verificar el contenido del archivo ZIP del plugin
$zipFile = 'C:/Users/jesus/AI-Vault/projects/marschallenge-seo/dist/eweb-universal-seo-optimizer.zip';

if (!file_exists($zipFile)) {
    echo "ERROR: El archivo ZIP no existe en: $zipFile\n";
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    echo "Contenido del archivo ZIP: $zipFile\n";
    echo "----------------------------------------\n";
    
    for($i = 0; $i < $zip->numFiles; $i++) {
        $filename = $zip->getNameIndex($i);
        $stat = $zip->statIndex($i);
        $size = $stat['size'];
        
        echo sprintf("%-50s %8d bytes\n", $filename, $size);
    }
    
    echo "----------------------------------------\n";
    echo "Total de archivos: " . $zip->numFiles . "\n";
    
    $zip->close();
} else {
    echo "ERROR: No se pudo abrir el archivo ZIP\n";
}
?>