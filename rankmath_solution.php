<?php
/**
 * Script de Solución para Actualización de Metadescripciones en Rank Math
 * Basado en todos los hallazgos anteriores
 */

class RankMath_Solution {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Preparando solución para actualización de Rank Math en: " . $this->site_url . "\n";
    }

    /**
     * Hacer una petición HTTP usando cURL
     */
    private function make_request($url, $method = 'GET', $data = null) {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                $this->auth_header,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'RankMath-Solution/1.0',
            CURLOPT_FOLLOWLOCATION => true
        ));

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array('error' => $error, 'status_code' => 0);
        }

        return array(
            'status_code' => $http_code,
            'body' => $response ? json_decode($response, true) : null,
            'raw_response' => $response
        );
    }

    /**
     * Crear script PHP para actualización masiva que puede ejecutarse directamente en el servidor WordPress
     */
    public function generate_wordpress_plugin_update_script() {
        echo "\n📝 GENERANDO SCRIPT PARA ACTUALIZACIÓN DIRECTA EN WORDPRESS\n";
        echo "========================================================\n";

        $script_content = '<?php
/**
 * Script para actualizar metadescripciones de Rank Math directamente en WordPress
 * Este script debe ejecutarse en el contexto de WordPress (como un plugin temporal o snippet)
 */

// Asegurarse de que se está ejecutando en el contexto de WordPress
if (!defined(\'ABSPATH\')) {
    exit;
}

/**
 * Actualizar metadescripciones de Rank Math para múltiples posts/páginas
 */
function actualizar_descripciones_rankmath_masivo($descripciones_data) {
    $resultados = array(
        \'exitosos\' => 0,
        \'fallidos\' => 0,
        \'detalles\' => array()
    );
    
    foreach ($descripciones_data as $item) {
        $post_id = $item[\'id\'];
        $descripcion = $item[\'descripcion\'];
        
        // Actualizar el campo de descripción de Rank Math
        $update_result = update_post_meta($post_id, \'rank_math_description\', $descripcion);
        
        // También actualizar el título si se proporciona
        if (isset($item[\'titulo\'])) {
            $update_title = update_post_meta($post_id, \'rank_math_title\', $item[\'titulo\']);
        }
        
        if ($update_result) {
            $resultados[\'exitosos\']++;
            $resultados[\'detalles\'][] = array(
                \'id\' => $post_id,
                \'titulo\' => get_the_title($post_id),
                \'estado\' => \'exitoso\',
                \'descripcion\' => $descripcion
            );
        } else {
            $resultados[\'fallidos\']++;
            $resultados[\'detalles\'][] = array(
                \'id\' => $post_id,
                \'titulo\' => get_the_title($post_id),
                \'estado\' => \'fallido\',
                \'error\' => \'No se pudo actualizar\'
            );
        }
        
        // Pequeña pausa para no sobrecargar el sistema
        usleep(100000); // 0.1 segundos
    }
    
    return $resultados;
}

// Datos de ejemplo a actualizar (reemplaza con tus datos reales)
$descripciones_para_actualizar = array(
    array(
        \'id\' => 10,
        \'titulo\' => \'Mars Challenge 2026\',
        \'descripcion\' => \'¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores.\'
    ),
    array(
        \'id\' => 27,
        \'titulo\' => \'Sobre Mars Challenge\',
        \'descripcion\' => \'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio.\'
    ),
    array(
        \'id\' => 37,
        \'titulo\' => \'Cómo participar\',
        \'descripcion\' => \'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. ¡Únete al reto!\'
    ),
    // Agrega más entradas según sea necesario
);

// Ejecutar la actualización
$resultados = actualizar_descripciones_rankmath_masivo($descripciones_para_actualizar);

// Mostrar resultados
echo "<h2>Resultados de la actualización de Rank Math</h2>";
echo "<p>Exitosos: " . $resultados[\'exitosos\'] . "</p>";
echo "<p>Fallidos: " . $resultados[\'fallidos\'] . "</p>";

if (!empty($resultados[\'detalles\'])) {
    echo "<ul>";
    foreach ($resultados[\'detalles\'] as $detalle) {
        echo "<li>ID " . $detalle[\'id\'] . " (" . $detalle[\'titulo\'] . "): " . $detalle[\'estado\'] . "</li>";
    }
    echo "</ul>";
}

// Opcional: Limpiar el script después de usarlo
// wp_delete_attachment(); // No borrar esta línea en producción
';

        // Guardar el script en un archivo
        $script_file = 'rankmath_bulk_update.php';
        file_put_contents($script_file, $script_content);
        
        echo "✓ Script generado: $script_file\n";
        echo "✓ Este script debe copiarse a un plugin temporal o snippet de WordPress\n";
        echo "✓ para ejecutarse en el contexto de WordPress con acceso a las funciones de Rank Math\n\n";

        return $script_file;
    }

    /**
     * Crear archivo CSV con las descripciones pendientes para actualización manual
     */
    public function generate_csv_for_manual_update() {
        echo "\n📊 GENERANDO CSV PARA ACTUALIZACIÓN MANUAL\n";
        echo "==========================================\n";

        // Datos de ejemplo basados en nuestro análisis previo
        $pending_updates = array(
            array('ID' => 1200, 'Título' => 'Jóvenes puertorriqueños rumbo a Madrid', 'Descripción sugerida' => 'Descubre cómo estudiantes puertorriqueños están representando a su país en el Mars Challenge rumbo a Madrid. Innovación y talento joven hacia Marte.'),
            array('ID' => 1197, 'Título' => 'Reto Marte en Costa Rica', 'Descripción sugerida' => 'Más de 150 universitarios costarricenses resuelven retos sobre el agua en Marte. Conoce cómo se desarrolló el Mars Challenge en Costa Rica.'),
            array('ID' => 1193, 'Título' => 'Final Reto Marte 2024 — Madrid', 'Descripción sugerida' => 'Estudiantes de 6 países presentan soluciones para la gestión del agua en Marte y en la Tierra. Resultados de la final del Mars Challenge 2024 en Madrid.'),
            array('ID' => 10, 'Título' => 'Inicio', 'Descripción sugerida' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores.'),
            array('ID' => 1521, 'Título' => 'Registro', 'Descripción sugerida' => 'Regístrate en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante.'),
            array('ID' => 27, 'Título' => 'Mars Challenge', 'Descripción sugerida' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio.'),
            array('ID' => 37, 'Título' => 'Cómo participar', 'Descripción sugerida' => 'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. ¡Únete al reto!'),
            array('ID' => 2883, 'Título' => 'Fuego', 'Descripción sugerida' => 'Reto Marte 2025: Fuego - Soluciones innovadoras para la gestión de energía y recursos en condiciones extremas. ¿Tienes lo que se necesita?'),
            array('ID' => 57, 'Título' => 'Fases del reto', 'Descripción sugerida' => 'Conoce las fases del Mars Challenge: del registro a la acción, del prototipo al impacto real. Sigue la estructura que guía a los participantes al éxito.'),
            array('ID' => 39, 'Título' => 'Convocatoria actual — 2026 Tierra', 'Descripción sugerida' => 'Mars Challenge 2026: Imagina la Tierra como un Marte en formación. Evita que llegue ese día. Participa en el reto que transformará el futuro del planeta.')
        );

        $csv_content = "ID,Título,Descripción sugerida\n";
        foreach ($pending_updates as $item) {
            $csv_content .= '"' . $item['ID'] . '","' . str_replace('"', '""', $item['Título']) . '","' . str_replace('"', '""', $item['Descripción sugerida']) . "\"\n";
        }

        $csv_file = 'pending_descriptions_rankmath.csv';
        file_put_contents($csv_file, $csv_content);
        
        echo "✓ CSV generado: $csv_file\n";
        echo "✓ Este archivo puede usarse para actualización manual en el panel de WordPress\n\n";

        return $csv_file;
    }

    /**
     * Generar instrucciones para WP CLI
     */
    public function generate_wp_cli_instructions() {
        echo "\n🔧 GENERANDO INSTRUCCIONES PARA WP CLI\n";
        echo "=====================================\n";

        $instructions = "
# Instrucciones para actualizar Rank Math usando WP CLI

## 1. Instalar el comando de Rank Math (si está disponible):
wp package install rank-math/wp-cli

## 2. Si no está disponible, usar wp post update con campos meta:
wp post meta update 10 rank_math_description 'Tu descripción aquí'
wp post meta update 10 rank_math_title 'Tu título aquí'

## 3. Actualización masiva desde un script:
# Ejemplo de script bash para actualización masiva
for id in 10 27 37 1521; do
  wp post meta update \$id rank_math_description 'Descripción para ID \$id'
done

## 4. Verificar actualizaciones:
wp post meta get 10 rank_math_description

# Para páginas en lugar de posts, usar 'wp post' igualmente ya que WordPress
# trata páginas como un tipo especial de post
        ";

        $instructions_file = 'wp_cli_instructions.txt';
        file_put_contents($instructions_file, $instructions);
        
        echo "✓ Instrucciones WP CLI generadas: $instructions_file\n";
        echo "✓ Estas instrucciones pueden usarse si tienes acceso a WP CLI\n\n";

        return $instructions_file;
    }

    /**
     * Ejecutar solución completa
     */
    public function run_solution() {
        echo "🚀 GENERANDO SOLUCIONES PARA ACTUALIZACIÓN DE RANK MATH\n";
        echo "=====================================================\n";

        $script_file = $this->generate_wordpress_plugin_update_script();
        $csv_file = $this->generate_csv_for_manual_update();
        $wp_cli_instructions = $this->generate_wp_cli_instructions();

        echo "\n📋 RESUMEN DE SOLUCIONES GENERADAS:\n";
        echo "   1. $script_file - Para ejecución directa en WordPress\n";
        echo "   2. $csv_file - Para actualización manual en panel de control\n";
        echo "   3. $wp_cli_instructions - Para uso con WP CLI si está disponible\n";

        echo "\n🎯 MÉTODO RECOMENDADO:\n";
        echo "   1. Opción A (Más efectiva): Usar WP CLI si tienes acceso al servidor\n";
        echo "   2. Opción B (Garantizada): Actualizar manualmente vía panel de control de WordPress\n";
        echo "   3. Opción C (Desarrollador): Usar el script PHP en un plugin temporal\n";

        echo "\n💡 NOTA IMPORTANTE:\n";
        echo "   Las actualizaciones no se pudieron hacer directamente a través de la API estándar\n";
        echo "   de WordPress porque Rank Math almacena su información de forma diferente.\n";
        echo "   Estas soluciones permiten actualizar las metadescripciones correctamente.\n";

        return array(
            'script_file' => $script_file,
            'csv_file' => $csv_file,
            'wp_cli_instructions' => $wp_cli_instructions
        );
    }
}

// Ejecutar la solución
$solution = new RankMath_Solution();
$results = $solution->run_solution();

echo "\n✅ SOLUCIÓN COMPLETA GENERADA\n";