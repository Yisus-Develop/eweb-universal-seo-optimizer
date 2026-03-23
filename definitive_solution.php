<?php
/**
 * SOLUCIÓN DEFINITIVA: ACTUALIZACIÓN COORDINADA ELEMENTOR + RANK MATH PARA MARS CHALLENGE
 * Incorporando comandos específicos de Elementor CLI y solución al conflicto identificado
 */

class Definitive_Elementor_RankMath_Solution {

    private $site_config = array();

    public function __construct() {
        $this->site_config = array(
            'site_url' => 'https://mars-challenge.com',
            'api_username' => 'wmaster_cs4or9qs',
            'api_app_password' => 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV'
        );
    }

    /**
     * Generar solución completa paso a paso
     */
    public function generate_complete_solution() {
        echo "🎯 SOLUCIÓN DEFINITIVA: ELEMENTOR + RANK MATH MARS CHALLENGE\n";
        echo "=========================================================\n\n";

        // Paso 1: Diagnóstico inicial
        echo "🔍 PASO 1: DIAGNÓSTICO INICIAL\n";
        echo "------------------------------\n";
        echo "Comandos para diagnosticar el estado actual:\n\n";
        
        $diagnostic_commands = array(
            "Verificar versión de Elementor y plugins activos:",
            "  wp plugin list | grep -i elementor",
            "  wp plugin list | grep -i rank",
            "",
            "Obtener configuración actual de Elementor:",
            "  wp option get elementor_settings",
            "",
            "Verificar configuración de Rank Math:",
            "  wp option get rank_math_options",
            "",
            "Listar páginas que usan Elementor:",
            "  wp post list --post_type=page --meta_key='_elementor_edit_mode' --format=ids",
            "",
            "Buscar páginas sin descripción de Rank Math:",
            "  wp post list --post_type=page --meta_key='rank_math_description' --meta_compare=NOT EXISTS --format=ids"
        );
        
        foreach ($diagnostic_commands as $cmd) {
            echo $cmd . "\n";
        }
        echo "\n";

        // Paso 2: Configuración de prioridad
        echo "⚙️  PASO 2: CONFIGURACIÓN DE PRIORIDAD\n";
        echo "------------------------------------\n";
        echo "Configurar Elementor para que no sobrescriba Rank Math:\n\n";
        
        $config_commands = array(
            "Deshabilitar meta tags generadas por Elementor:",
            "  wp option patch update elementor_settings seo_open_graph_enabled disabled",
            "  wp option patch update elementor_settings seo_twitter_cards_enabled disabled",
            "",
            "Configurar prioridad de plugins de SEO:",
            "  # Si Rank Math tiene campo para prioridad",
            "  wp option patch add elementor_settings seo_override_priority low",
            "",
            "Registrar el conflicto para futuras referencias:",
            "  wp eval 'error_log(\"Conflicto Elementor-RankMath registrado el \" . date(\"Y-m-d H:i:s\"));'"
        );
        
        foreach ($config_commands as $cmd) {
            echo $cmd . "\n";
        }
        echo "\n";

        // Paso 3: Script combinado de actualización
        echo "🔄 PASO 3: SCRIPT COMBINADO DE ACTUALIZACIÓN\n";
        echo "-------------------------------------------\n";
        echo "Script que actualiza ambos sistemas coordinadamente:\n\n";

        $combined_script = '<?php
/**
 * Script de actualización combinada Elementor + Rank Math
 * Actualiza ambos sistemas para asegurar que los cambios se reflejen
 */

function update_seo_both_systems($post_id, $seo_data) {
    global $wpdb;
    
    echo "Actualizando SEO para post ID: $post_id\\n";
    
    // PASO 1: Actualizar Rank Math (sistema principal)
    $rank_math_updates = array(
        "rank_math_description" => $seo_data["description"],
        "_rank_math_description" => $seo_data["description"],
        "rank_math_title" => $seo_data["title"],
        "_rank_math_title" => $seo_data["title"],
        "rank_math_focus_keyword" => $seo_data["focus_keyword"]
    );
    
    foreach ($rank_math_updates as $meta_key => $meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }
    
    // PASO 2: Actualizar también los valores en la configuración de Elementor
    $elementor_settings = get_post_meta($post_id, "_elementor_page_settings", true);
    if (!is_array($elementor_settings)) {
        $elementor_settings = array();
    }
    
    // Asegurar que Elementor respete los valores de Rank Math
    $elementor_settings["meta_description"] = $seo_data["description"];
    $elementor_settings["meta_title"] = $seo_data["title"];
    
    update_post_meta($post_id, "_elementor_page_settings", $elementor_settings);
    
    // PASO 3: Forzar regeneración de página
    // Esto asegura que Elementor use la nueva configuración
    $post = get_post($post_id);
    $post->post_modified = current_time("mysql");
    $post->post_modified_gmt = current_time("mysql", 1);
    wp_update_post($post);
    
    echo "✓ SEO actualizado para ID $post_id\\n";
    return true;
}

// Ejemplo de uso
$updates = array(
    "title" => "Título optimizado para SEO",
    "description" => "Descripción optimizada para SEO y palabras clave específicas",
    "focus_keyword" => "palabra clave principal"
);

$page_ids = [10, 27, 37, 1521, 2883]; // IDs de páginas a actualizar

foreach ($page_ids as $id) {
    update_seo_both_systems($id, $updates);
    sleep(1); // Pequeña pausa entre actualizaciones
}

echo "\\nTodas las actualizaciones completadas.\\n";
';

        echo highlight_string($combined_script, true) . "\n\n";

        // Paso 4: Limpieza y verificación
        echo "🧹 PASO 4: LIMPIEZA Y VERIFICACIÓN\n";
        echo "---------------------------------\n";
        echo "Comandos para limpiar caché y verificar cambios:\n\n";
        
        $cleanup_commands = array(
            "Limpiar caché de Elementor:",
            "  wp elementor clear-cache",
            "",
            "Limpiar cualquier otra caché (si tienes plugin de caché):",
            "  wp cache flush  # Si usas WP Rocket, W3TC, etc.",
            "",
            "Verificar que los cambios se hayan aplicado:",
            "  wp post meta get 10 rank_math_description",
            "  wp post meta get 10 _elementor_page_settings | grep -i description",
            "",
            "Recargar las páginas afectadas para forzar regeneración:",
            "  curl -s https://mars-challenge.com/ >/dev/null 2>&1",
            "  curl -s https://mars-challenge.com/fuego/ >/dev/null 2>&1"
        );
        
        foreach ($cleanup_commands as $cmd) {
            echo $cmd . "\n";
        }
        echo "\n";

        // Paso 5: Solución permanente
        echo "🔒 PASO 5: SOLUCIÓN PERMANENTE\n";
        echo "-------------------------------\n";
        echo "Para evitar que el problema vuelva:\n\n";
        
        $permanent_solution = array(
            "Opción A: Configuración de Elementor (recomendada):",
            "  - Ir a Elementor > Settings > Advanced",
            "  - Desactivar 'Enable Document Elements' para secciones que no necesiten SEO personalizado",
            "  - Verificar que no haya duplicación de meta tags",
            "",
            "Opción B: Workflow específico:",
            "  - Cuando actualices SEO, usa el script combinado",
            "  - Siempre actualiza ambos sistemas (Rank Math + Elementor settings)",
            "  - Limpia caché después de cada actualización importante",
            "",
            "Opción C: Revisión de theme:",
            "  - Verificar que el theme no esté agregando meta tags adicionales",
            "  - Asegurar que el theme sea compatible con Rank Math"
        );
        
        foreach ($permanent_solution as $solution) {
            echo $solution . "\n";
        }
        echo "\n";

        // Resumen
        echo "✅ RESUMEN DE LA SOLUCIÓN:\n";
        echo "-------------------------\n";
        echo "1. Diagnosticar estado actual de ambos sistemas\n";
        echo "2. Configurar prioridad para que Rank Math tenga precedencia\n";
        echo "3. Usar el script combinado para futuras actualizaciones\n";
        echo "4. Limpiar caché después de cada actualización\n";
        echo "5. Implementar solución permanente para evitar el conflicto futuro\n\n";

        echo "Con esta solución, los cambios que actualizas via API deberían reflejarse correctamente\n";
        echo "en el HTML final de tus páginas, ya que ambos sistemas (Elementor y Rank Math) estarán\n";
        echo "sincronizados y funcionando en conjunto en lugar de en conflicto.\n\n";
    }

    /**
     * Generar script específico para actualizar las páginas restantes
     */
    public function generate_remaining_pages_script() {
        echo "📄 SCRIPT PARA ACTUALIZAR PÁGINAS RESTANTES\n";
        echo "----------------------------------------\n\n";

        $script = '<?php
/**
 * Script específico para actualizar las páginas restantes de Mars Challenge
 * Basado en la solución coordinada Elementor + Rank Math
 */

// Datos de ejemplo para actualización
$seo_updates = array(
    array(
        "id" => 10,
        "title" => "Mars Challenge 2026 - Inicio",
        "description" => "¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores que buscan soluciones para Marte y la Tierra.",
        "keywords" => "mars challenge, innovación, jóvenes, espacio, marte"
    ),
    array(
        "id" => 27,
        "title" => "Sobre Mars Challenge",
        "description" => "Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio que transforma el futuro.",
        "keywords" => "sobre mars challenge, historia, iniciativa, soluciones"
    ),
    array(
        "id" => 37,
        "title" => "Cómo participar en Mars Challenge",
        "description" => "Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante para jóvenes innovadores.",
        "keywords" => "cómo participar, reto global, jóvenes innovadores, participación"
    )
    // Añadir más según sea necesario
);

function update_page_seo_coordinated($page_data) {
    $post_id = $page_data["id"];
    
    echo "Actualizando página ID: $post_id\\n";
    
    // Actualizar Rank Math
    update_post_meta($post_id, "rank_math_title", $page_data["title"]);
    update_post_meta($post_id, "_rank_math_title", $page_data["title"]);
    update_post_meta($post_id, "rank_math_description", $page_data["description"]);
    update_post_meta($post_id, "_rank_math_description", $page_data["description"]);
    update_post_meta($post_id, "rank_math_focus_keyword", $page_data["keywords"]);
    
    // Actualizar configuración de Elementor para consistencia
    $elementor_settings = get_post_meta($post_id, "_elementor_page_settings", true);
    if (!is_array($elementor_settings)) {
        $elementor_settings = array();
    }
    
    $elementor_settings["meta_title"] = $page_data["title"];
    $elementor_settings["meta_description"] = $page_data["description"];
    
    update_post_meta($post_id, "_elementor_page_settings", $elementor_settings);
    
    echo "✓ Página $post_id actualizada\\n";
}

// Ejecutar actualizaciones
foreach ($seo_updates as $page_data) {
    update_page_seo_coordinated($page_data);
    sleep(2); // Evitar sobrecarga
}

echo "\\n✓ Todas las páginas han sido actualizadas.\\n";
echo "Ahora ejecuta: wp elementor clear-cache\\n";
echo "Y verifica las páginas en el navegador.\\n";
';

        echo highlight_string($script, true) . "\n\n";
    }

    /**
     * Ejecutar solución completa
     */
    public function execute_solution() {
        $this->generate_complete_solution();
        $this->generate_remaining_pages_script();
        
        echo "🎉 SOLUCIÓN DEFINITIVA COMPLETA\n";
        echo "==============================\n\n";
        
        echo "Has obtenido:\n";
        echo "✓ Pasos específicos para resolver el conflicto Elementor-Rank Math\n";
        echo "✓ Script combinado para actualizaciones coordinadas\n";
        echo "✓ Comandos de limpieza y verificación\n";
        echo "✓ Soluciones permanentes para evitar futuros conflictos\n";
        echo "✓ Script específico para actualizar tus páginas restantes\n\n";
        
        echo "LA DIFERENCIA CLAVE:\n";
        echo "Antes, actualizabas Rank Math pero Elementor sobrescribía los valores.\n";
        echo "Ahora, actualizas AMBOS sistemas coordinadamente, asegurando que los\n";
        echo "cambios se reflejen correctamente en el HTML final de tus páginas.\n\n";
        
        echo "SIGUIENTES PASOS:\n";
        echo "1. Ejecuta los comandos de diagnóstico para entender tu estado actual\n";
        echo "2. Aplica la configuración de prioridad\n";
        echo "3. Usa el script combinado para actualizar tus páginas\n";
        echo "4. Limpia la caché después de cada actualización\n";
        echo "5. Verifica que los cambios se reflejen en tus páginas\n\n";
    }
}

// Ejecutar la solución definitiva
$solution = new Definitive_Elementor_RankMath_Solution();
$solution->execute_solution();

echo "✅ SOLUCIÓN DEFINITIVA COMPLETADA\n";
echo "Ahora puedes resolver el conflicto de visualización que habías notado.\n";