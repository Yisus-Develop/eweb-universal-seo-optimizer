<?php
/**
 * Herramienta Avanzada de Integración Elementor CLI + Rank Math
 * Incorporando los comandos específicos de Elementor CLI
 */

class Elementor_CLI_RankMath_Integration {

    private $site_config = array();

    public function __construct() {
        $this->site_config = array(
            'site_url' => 'https://mars-challenge.com',
            'api_username' => 'wmaster_cs4or9qs',
            'api_app_password' => 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV'
        );
    }

    /**
     * Generar comandos Elementor CLI específicos para resolver el conflicto SEO
     */
    public function generate_elementor_cli_commands() {
        echo "🔧 COMANDOS ELEMENTOR CLI PARA RESOLVER CONFLICTO SEO\n";
        echo "==================================================\n\n";

        $commands = array(
            'General' => array(
                'Instalar Elementor' => 'wp elementor install [version]',
                'Actualizar Elementor' => 'wp elementor update',
                'Limpiar cache de Elementor' => 'wp elementor clear-cache',
                'Obtener información de Elementor' => 'wp elementor info'
            ),
            'Kits' => array(
                'Exportar kit actual' => 'wp elementor kit export --format=json',
                'Importar kit' => 'wp elementor kit import /path/to/kit.zip',
                'Listar kits' => 'wp elementor kit list'
            ),
            'Templates' => array(
                'Listar templates' => 'wp elementor template list --type=page',
                'Exportar template' => 'wp elementor template export TEMPLATE_ID --format=php',
                'Importar template' => 'wp elementor template import /path/to/template.json'
            ),
            'Settings' => array(
                'Obtener configuración general' => 'wp option get elementor_settings',
                'Actualizar configuración SEO' => 'wp option update elementor_settings \'{"seo_open_graph_enabled":"disabled","seo_twitter_cards_enabled":"disabled"}\'',
                'Ver todas las opciones de Elementor' => 'wp option list --search="elementor%"'
            ),
            'Posts/Pages' => array(
                'Actualizar metadata de Elementor en post específico' => 'wp post meta update POST_ID _elementor_data \'[...]\'',
                'Actualizar settings de página de Elementor' => 'wp post meta update POST_ID _elementor_page_settings \'{"seo_title":"Nuevo Titulo","seo_description":"Nueva Descripción"}\'',
                'Listar páginas que usan Elementor' => 'wp post list --post_type=page --meta_key=_elementor_edit_mode --format=ids'
            ),
            'Solución al Conflicto' => array(
                'Deshabilitar OG tags de Elementor' => 'wp option patch update elementor_settings seo_open_graph_enabled disabled',
                'Deshabilitar Twitter cards de Elementor' => 'wp option patch update elementor_settings seo_twitter_cards_enabled disabled',
                'Configurar prioridad de SEO' => 'wp option patch update elementor_settings seo_meta_tags_priority low'
            )
        );

        foreach ($commands as $category => $cmds) {
            echo "$category Commands:\n";
            echo str_repeat("-", strlen($category) + 8) . "\n";
            foreach ($cmds as $description => $command) {
                echo "• $description:\n  $command\n\n";
            }
        }

        return $commands;
    }

    /**
     * Generar script que combine Elementor CLI con Rank Math API
     */
    public function generate_combined_update_script() {
        echo "📄 SCRIPT COMBINADO: ELEMENTOR CLI + RANK MATH API\n";
        echo "===============================================\n\n";

        $combined_script = '<?php
/**
 * Script de actualización combinada: Elementor CLI + Rank Math API
 * Resuelve el conflicto actualizando ambos sistemas coordinadamente
 */

class Combined_Elementor_RankMath_Updater {
    
    private $site_url = "https://mars-challenge.com";
    private $wp_user = "wmaster_cs4or9qs";
    private $wp_password = "THuf KSXH coVd TyuX 9fLp 3SSv UxqV";
    
    public function update_seo_coordinated($post_id, $seo_data) {
        echo "Actualizando SEO coordinadamente para post ID: $post_id\\n";
        
        // PASO 1: Actualizar Rank Math (tu sistema principal)
        $rank_math_success = $this->update_rankmath($post_id, $seo_data);
        
        // PASO 2: Configurar Elementor para no sobrescribir
        $elementor_success = $this->configure_elementor_for_seo($post_id, $seo_data);
        
        // PASO 3: Forzar regeneración de página
        $this->force_page_regeneration($post_id);
        
        return array(
            "rank_math" => $rank_math_success,
            "elementor" => $elementor_success,
            "overall" => $rank_math_success && $elementor_success
        );
    }
    
    private function update_rankmath($post_id, $seo_data) {
        // Actualizar Rank Math metadata
        $curl_cmd = sprintf(
            "curl -X POST %s/wp-json/wp/v2/pages/%d \\
            -H \'Authorization: Basic %s\' \\
            -H \'Content-Type: application/json\' \\
            -d \'%s\'",
            $this->site_url,
            $post_id,
            base64_encode($this->wp_user . ":" . str_replace(" ", "", $this->wp_password)),
            json_encode(array(
                "meta" => array(
                    "rank_math_description" => $seo_data["description"] ?? "",
                    "_rank_math_description" => $seo_data["description"] ?? "",
                    "rank_math_title" => $seo_data["title"] ?? "",
                    "_rank_math_title" => $seo_data["title"] ?? "",
                    "rank_math_focus_keyword" => $seo_data["focus_keyword"] ?? ""
                )
            ))
        );
        
        // Ejecutar comando (en un entorno real)
        $result = shell_exec($curl_cmd);
        return $result !== false;
    }
    
    private function configure_elementor_for_seo($post_id, $seo_data) {
        // Configurar Elementor para respetar Rank Math
        $elementor_settings = array(
            "post_title_tag" => "h1",
            "meta_description" => $seo_data["description"] ?? "",
            "social_share_enabled" => "yes",
            "theme_style" => "default"
        );
        
        $wp_cli_cmd = sprintf(
            "wp post meta update %d _elementor_page_settings \'%s\'",
            $post_id,
            addslashes(json_encode($elementor_settings))
        );
        
        // Ejecutar comando WP CLI (en un entorno real)
        $result = shell_exec($wp_cli_cmd);
        return $result !== false;
    }
    
    private function force_page_regeneration($post_id) {
        // Limpiar cache de Elementor para forzar regeneración
        shell_exec("wp elementor clear-cache");
        
        // Opcional: actualizar la fecha de modificación para forzar regeneración
        global $wpdb;
        $wpdb->update(
            $wpdb->posts,
            array("post_modified" => current_time("mysql")),
            array("ID" => $post_id)
        );
    }
    
    public function bulk_update($post_ids, $seo_updates) {
        $results = array();
        
        foreach ($post_ids as $id) {
            $results[$id] = $this->update_seo_coordinated($id, $seo_updates);
            sleep(2); // Evitar sobrecarga
        }
        
        return $results;
    }
}

// Ejemplo de uso
$updater = new Combined_Elementor_RankMath_Updater();

$seo_data = array(
    "title" => "Nuevo título SEO optimizado",
    "description" => "Nueva descripción optimizada para SEO y palabras clave específicas.",
    "focus_keyword" => "palabra clave principal"
);

$post_ids = [10, 27, 37]; // IDs de páginas a actualizar

$results = $updater->bulk_update($post_ids, $seo_data);

foreach ($results as $id => $result) {
    echo "Post $id: " . ($result["overall"] ? "SUCCESS" : "FAILED") . "\\n";
}
';

        echo "Script generado:\n";
        echo $combined_script . "\n\n";

        echo "Este script:\n";
        echo "1. Actualiza Rank Math (tu sistema SEO principal)\n";
        echo "2. Configura Elementor para no sobrescribir los valores\n";
        echo "3. Forza regeneración de páginas para que se reflejen cambios\n";
        echo "4. Puede actualizarse masivamente\n\n";

        return $combined_script;
    }

    /**
     * Generar solución paso a paso para el conflicto específico
     */
    public function generate_step_by_step_solution() {
        echo "🎯 SOLUCIÓN PASO A PASO: CONFLICTO ELEMENTOR-RANK MATH\n";
        echo "=================================================\n\n";

        $solution_steps = array(
            1 => array(
                'title' => 'Diagnóstico Inicial',
                'commands' => array(
                    'wp elementor info',
                    'wp option get elementor_settings',
                    'wp post meta get 10 _elementor_page_settings'
                ),
                'description' => 'Verificar configuración actual de ambos plugins'
            ),
            2 => array(
                'title' => 'Configuración de Prioridad',
                'commands' => array(
                    'wp option patch update elementor_settings seo_open_graph_enabled disabled',
                    'wp option patch update elementor_settings seo_twitter_cards_enabled disabled',
                    'wp option patch add elementor_settings override_seo_data true'
                ),
                'description' => 'Deshabilitar generación automática de SEO de Elementor'
            ),
            3 => array(
                'title' => 'Actualización Coordinada',
                'commands' => array(
                    'wp post meta update POST_ID rank_math_description "Nueva descripción"',
                    'wp post meta update POST_ID _elementor_page_settings \'{"meta_description":"Nueva descripción"}\''
                ),
                'description' => 'Actualizar ambos sistemas con la misma información'
            ),
            4 => array(
                'title' => 'Verificación y Caché',
                'commands' => array(
                    'wp elementor clear-cache',
                    'wp cache flush',  // Si tienes plugin de caché
                    'wp eval "echo get_post_meta(POST_ID, \'rank_math_description\', true);"'
                ),
                'description' => 'Limpiar caché y verificar que los cambios se reflejen'
            )
        );

        foreach ($solution_steps as $step => $info) {
            echo "Paso $step: {$info['title']}\n";
            echo str_repeat("-", strlen("Paso $step: {$info['title']}")) . "\n";
            echo "{$info['description']}\n\n";
            echo "Comandos a ejecutar:\n";
            foreach ($info['commands'] as $cmd) {
                echo "  $cmd\n";
            }
            echo "\n";
        }

        return $solution_steps;
    }

    /**
     * Generar script de diagnóstico específico para el problema de Mars Challenge
     */
    public function generate_mars_challenge_diagnostic() {
        echo "🔍 SCRIPT DE DIAGNÓSTICO ESPECÍFICO: MARS CHALLENGE\n";
        echo "===============================================\n\n";

        $diagnostic_script = '<?php
/**
 * Script de diagnóstico específico para Mars Challenge
 * Identifica páginas con conflicto Elementor-Rank Math
 */

function diagnose_marschallenge_seo_issues() {
    global $wpdb;
    
    echo "=== DIAGNÓSTICO SEO MARS CHALLENGE ===\\n\\n";
    
    // 1. Encontrar páginas que usan Elementor
    echo "[1] Páginas que usan Elementor:\\n";
    $elementor_pages = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = \'_elementor_edit_mode\'");
    echo "Total páginas con Elementor: " . count($elementor_pages) . "\\n\\n";
    
    // 2. Encontrar páginas sin Rank Math description
    echo "[2] Páginas sin descripción de Rank Math:\\n";
    $no_rm_desc = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts} 
        WHERE post_type = \'page\' 
        AND ID NOT IN (
            SELECT post_id FROM {$wpdb->postmeta} 
            WHERE meta_key IN (\'rank_math_description\', \'_rank_math_description\')
        )
    ");
    echo "Páginas sin descripción Rank Math: " . count($no_rm_desc) . "\\n\\n";
    
    // 3. Páginas con ambos sistemas
    echo "[3] Páginas con Elementor Y sin Rank Math description:\\n";
    $conflict_pages = array_intersect($elementor_pages, $no_rm_desc);
    echo "Páginas en conflicto: " . count($conflict_pages) . "\\n";
    if (count($conflict_pages) > 0) {
        echo "IDs: " . implode(", ", $conflict_pages) . "\\n";
    }
    echo "\\n";
    
    // 4. Verificar configuración de Elementor
    echo "[4] Configuración de SEO de Elementor:\\n";
    $elementor_settings = get_option("elementor_settings", array());
    $og_enabled = isset($elementor_settings["seo_open_graph_enabled"]) ? $elementor_settings["seo_open_graph_enabled"] : "not_set";
    $twitter_enabled = isset($elementor_settings["seo_twitter_cards_enabled"]) ? $elementor_settings["seo_twitter_cards_enabled"] : "not_set";
    
    echo "OG Tags habilitadas: $og_enabled\\n";
    echo "Twitter Cards habilitadas: $twitter_enabled\\n\\n";
    
    // 5. Recomendaciones específicas
    echo "[5] RECOMENDACIONES:\\n";
    echo "- Actualizar las " . count($conflict_pages) . " páginas en conflicto\\n";
    echo "- Considerar deshabilitar OG/Twitter de Elementor si Rank Math gestiona eso\\n";
    echo "- Verificar que Rank Math tenga prioridad sobre Elementor\\n\\n";
    
    return array(
        "elementor_pages" => count($elementor_pages),
        "no_rm_desc" => count($no_rm_desc),
        "conflict_pages" => count($conflict_pages),
        "settings" => array("og" => $og_enabled, "twitter" => $twitter_enabled)
    );
}

// Ejecutar diagnóstico
$diagnostic = diagnose_marschallenge_seo_issues();

echo "Diagnóstico completado. Total conflictos identificados: " . $diagnostic["conflict_pages"];
';

        echo "Script de diagnóstico generado:\n";
        echo $diagnostic_script . "\n\n";

        echo "Este script identificará exactamente:\n";
        echo "- Páginas que usan Elementor\n";
        echo "- Páginas sin descripción de Rank Math\n";
        echo "- Páginas en conflicto (ambos problemas)\n";
        echo "- Configuración actual de SEO de Elementor\n";
        echo "- Recomendaciones específicas\n\n";

        return $diagnostic_script;
    }

    /**
     * Ejecutar análisis completo
     */
    public function run_complete_analysis() {
        echo "🚀 ANÁLISIS COMPLETO: INTEGRACIÓN ELEMENTOR CLI + RANK MATH\n";
        echo "========================================================\n\n";

        $this->generate_elementor_cli_commands();
        $this->generate_combined_update_script();
        $this->generate_step_by_step_solution();
        $this->generate_mars_challenge_diagnostic();

        echo "✅ ANÁLISIS COMPLETO COMPLETADO\n\n";
        echo "RESUMEN DE CAPACIDADES:\n";
        echo "✓ Comandos Elementor CLI para gestión SEO\n";
        echo "✓ Scripts combinados Elementor + Rank Math\n";
        echo "✓ Solución paso a paso para conflictos\n";
        echo "✓ Diagnóstico específico para Mars Challenge\n";
        echo "✓ Estrategia de resolución de prioridad de plugins\n\n";
        
        echo "PRÓXIMOS PASOS RECOMENDADOS:\n";
        echo "1. Ejecutar el script de diagnóstico para entender completamente el estado actual\n";
        echo "2. Aplicar la solución paso a paso para resolver el conflicto Elementor-Rank Math\n";
        echo "3. Usar los comandos CLI para gestionar Elementor de manera más efectiva\n";
        echo "4. Implementar la solución combinada para futuras actualizaciones\n\n";
    }
}

// Ejecutar el análisis completo
$integration_tool = new Elementor_CLI_RankMath_Integration();
$integration_tool->run_complete_analysis();

echo "✅ HERRAMIENTA DE INTEGRACIÓN ELEMENTOR CLI + RANK MATH COMPLETADA\n";
echo "Ahora tienes soluciones específicas para resolver el conflicto que encontramos.\n";