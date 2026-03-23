<?php
// Probar la funcionalidad específica de diagnóstico

require_once 'wordpress_productivity_tool.php';

echo "🔍 PRUEBA ESPECÍFICA: GENERAR SCRIPT DE DIAGNÓSTICO\n";
echo "=================================================\n\n";

$tool = new WordPress_Productivity_Tool();

// Probar específicamente el diagnóstico
$resultado = $tool->hazme_esto("Crear un script de diagnóstico que encuentre todas las páginas sin metadescripción de Rank Math");

echo "RESULTADO DE DIAGNÓSTICO:\n";
echo "TIPO: " . $resultado['type'] . "\n";
echo "INSTRUCCIONES: " . $resultado['instructions'] . "\n";
echo "\nCONTENIDO DEL SCRIPT:\n";
echo $resultado['content'] . "\n\n";

echo "✅ PRUEBA DE DIAGNÓSTICO COMPLETADA\n";