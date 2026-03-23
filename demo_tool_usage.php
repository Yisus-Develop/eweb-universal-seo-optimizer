<?php
// Demostrar la funcionalidad de la herramienta con un ejemplo específico

require_once 'wordpress_productivity_tool.php';

echo "🎯 DEMO: USANDO LA HERRAMIENTA PARA UNA TAREA ESPECÍFICA\n";
echo "====================================================\n\n";

$tool = new WordPress_Productivity_Tool();

// Ejemplo: Solicitar actualización de palabras clave
echo "EJEMPLO 1: Actualizar palabras clave en páginas específicas\n";
echo "----------------------------------------------------------\n";

$resultado1 = $tool->hazme_esto("Actualizar las palabras clave en las páginas 10, 27 y 37 con 'mars challenge, innovación, jóvenes talentosos'");

echo "TIPO: " . $resultado1['type'] . "\n";
echo "INSTRUCCIONES: " . $resultado1['instructions'] . "\n";
echo "\nCONTENIDO:\n";
echo $resultado1['content'] . "\n\n";

echo "EJEMPLO 2: Resolver conflicto SEO\n";
echo "--------------------------------\n";

$resultado2 = $tool->hazme_esto("Resolver conflicto entre Elementor y Rank Math que hace que no se muestren las metadescripciones en la página /fuego/");

echo "TIPO: " . $resultado2['type'] . "\n";
echo "INSTRUCCIONES: " . $resultado2['instructions'] . "\n";
echo "\nCONTENIDO:\n";
echo $resultado2['content'] . "\n\n";

echo "EJEMPLO 3: Generar script de diagnóstico\n";
echo "---------------------------------------\n";

$resultado3 = $tool->hazme_esto("Crear un script que diagnoostique todas las páginas sin metadescripción de Rank Math");

echo "TIPO: " . $resultado3['type'] . "\n";
echo "INSTRUCCIONES: " . $resultado3['instructions'] . "\n";
echo "\nCONTENIDO:\n";
echo $resultado3['content'] . "\n\n";

echo "✅ DEMOSTRACIÓN COMPLETADA\n";
echo "La herramienta está lista para ayudarte con tareas específicas.\n";
echo "Simplemente dile: \"Hazme esto\" + tu descripción específica y te dará la solución exacta.\n";