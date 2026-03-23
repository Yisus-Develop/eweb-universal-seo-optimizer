<?php
/**
 * Estrategia ampliada de integración con WordPress, Elementor y APIs
 * Incorporando los nuevos recursos descubiertos
 */

class Extended_Integration_Strategy {

    public function generate_strategy() {
        echo "🎯 ESTRATEGIA AMPLIADA DE INTEGRACIÓN\n";
        echo "=====================================\n\n";

        echo "1. INTEGRACIÓN CON ELEMENTOR CLI\n";
        echo "-------------------------------\n";
        echo "Comandos útiles de Elementor CLI:\n";
        echo "• wp elementor install [version] - Instalar Elementor\n";
        echo "• wp elementor update - Actualizar Elementor\n";
        echo "• wp elementor kit import <file> - Importar kit de Elementor\n";
        echo "• wp elementor kit export <kit-id> - Exportar kit de Elementor\n";
        echo "• wp elementor clear-cache - Limpiar caché de Elementor\n\n";

        echo "2. INTEGRACIÓN AVANZADA CON WORDPRESS REST API\n";
        echo "---------------------------------------------\n";
        echo "Endpoints adicionales que podemos usar:\n";
        echo "• /wp-json/wp/v2/posts - Gestionar posts\n";
        echo "• /wp-json/wp/v2/pages - Gestionar páginas\n";
        echo "• /wp-json/wp/v2/media - Gestionar media\n";
        echo "• /wp-json/wp/v2/users - Gestionar usuarios\n";
        echo "• /wp-json/wp/v2/settings - Gestionar configuraciones\n\n";

        echo "3. AUTENTICACIÓN CON APPLICATION PASSWORDS\n";
        echo "------------------------------------------\n";
        echo "Ya tienes Application Passwords funcionando (como usamos en tus scripts).\n";
        echo "Podemos acceder a:\n";
        echo "• Todo el CRUD de contenidos (crear, leer, actualizar, borrar)\n";
        echo "• Gestionar taxonomías, categorías, tags\n";
        echo "• Actualizar campos personalizados (metadata)\n";
        echo "• Gestionar menús y widgets\n\n";

        echo "4. NUEVOS CAMPOS DE RANK MATH POTENCIALMENTE ACCESIBLES\n";
        echo "-----------------------------------------------------\n";
        echo "Agregando a nuestros campos exitosos:\n";
        echo "• rank_math_focus_keyword - Palabra clave principal\n";
        echo "• rank_math_facebook_title - Título para Facebook\n";
        echo "• rank_math_facebook_description - Descripción para Facebook\n";
        echo "• rank_math_twitter_title - Título para Twitter\n";
        echo "• rank_math_twitter_description - Descripción para Twitter\n";
        echo "• rank_math_schema_type - Tipo de schema markup\n";
        echo "• rank_math_primary_category - Categoría primaria para SEO\n\n";

        echo "5. POSIBLE INTEGRACIÓN CON ELEMENTOR METADATA\n";
        echo "---------------------------------------------\n";
        echo "Campos de Elementor que podrían afectar SEO:\n";
        echo "• elementor_page_settings - Configuración de página de Elementor\n";
        echo "• _elementor_edit_mode - Modo de edición\n";
        echo "• _elementor_template_type - Tipo de template\n";
        echo "• _elementor_data - Datos de layout (podría contener SEO info)\n\n";

        echo "6. PLAN DE ACCIÓN INTEGRADO\n";
        echo "---------------------------\n";
        
        $action_plan = array(
            "Fase 1 - Análisis Profundo" => array(
                "Verificar qué campos de Elementor afectan el SEO",
                "Mapear relación entre Rank Math y Elementor settings",
                "Identificar conflicto exacto que causa la sobreescritura"
            ),
            "Fase 2 - Solución Técnica" => array(
                "Actualizar campos de Rank Math via API (ya probado)",
                "Configurar Elementor para que respete Rank Math",
                "Limpiar y regenerar caché de ambas herramientas"
            ),
            "Fase 3 - Automatización" => array(
                "Script para actualizar palabras clave por página",
                "Script para actualizar todos los campos SEO de Rank Math",
                "Script para verificar que ambas herramientas estén sincronizadas"
            ),
            "Fase 4 - Monitoreo" => array(
                "Verificar que cambios se reflejen en HTML final",
                "Monitorear índices de Google Search Console",
                "Revisar Core Web Vitals después de cambios"
            )
        );

        foreach ($action_plan as $phase => $tasks) {
            echo "$phase:\n";
            foreach ($tasks as $task) {
                echo "  • $task\n";
            }
            echo "\n";
        }

        echo "7. COMANDOS WP CLI ESPECÍFICOS PARA TU CASO\n";
        echo "------------------------------------------\n";
        
        $specific_commands = array(
            "Actualizar metadata de Rank Math" => array(
                "wp post meta update POST_ID rank_math_description 'Nueva descripción'",
                "wp post meta update POST_ID rank_math_focus_keyword 'palabra-clave'",
                "wp post meta update POST_ID rank_math_title 'Nuevo título SEO'"
            ),
            "Gestionar Elementor settings" => array(
                "wp post meta update POST_ID _elementor_page_settings --format=json",
                "wp post meta update POST_ID elementor_page_settings '{\"meta_viewport\":\"viewport\"}'"
            ),
            "Operaciones masivas" => array(
                "wp post list --post_type=page --format=ids | xargs -I {} wp post meta update {} rank_math_description 'Descripción común'",
                "wp db query \"SELECT ID FROM wp_posts WHERE post_type='page' AND ID NOT IN (SELECT post_id FROM wp_postmeta WHERE meta_key='rank_math_description') LIMIT 50\""
            )
        );

        foreach ($specific_commands as $category => $commands) {
            echo "$category:\n";
            foreach ($commands as $command) {
                echo "  $command\n";
            }
            echo "\n";
        }

        echo "8. SCRIPT PARA DIAGNOSTICAR CONFLICTO ELEMENTOR-RANKMATH\n";
        echo "-----------------------------------------------------\n";
        
        $diagnostic_script = '
#!/usr/bin/env php
<?php
/**
 * Script para diagnosticar conflicto Elementor-RankMath
 * Identifica páginas donde Elementor sobrescribe Rank Math
 */

function diagnose_conflict($post_id) {
    // Obtener ambos conjuntos de metadatos
    $rank_math_meta = array(
        "description" => get_post_meta($post_id, "rank_math_description", true),
        "title" => get_post_meta($post_id, "rank_math_title", true),
        "focus_keyword" => get_post_meta($post_id, "rank_math_focus_keyword", true)
    );
    
    $elementor_settings = get_post_meta($post_id, "_elementor_page_settings", true);
    
    $post_content = get_post($post_id)->post_content;
    
    // Verificar si Elementor está activo en esta página
    $has_elementor = has_shortcode($post_content, "elementor") || 
                     get_post_meta($post_id, "_elementor_edit_mode", true) === "builder";
    
    return array(
        "post_id" => $post_id,
        "rank_math_data" => $rank_math_meta,
        "elementor_settings" => $elementor_settings,
        "uses_elementor" => $has_elementor
    );
}

// Ejecutar diagnóstico
$post_ids = [10, 27, 37, 1521, 2883]; // IDs que ya sabemos que tienen problemas
foreach ($post_ids as $id) {
    $diagnosis = diagnose_conflict($id);
    print_r($diagnosis);
}
?>
        ';
        
        echo "Script de diagnóstico para copiar y ejecutar:\n";
        echo $diagnostic_script . "\n";

        echo "9. CONCLUSIÓN: ¿QUÉ PUEDO HACER AHORA POR TI?\n";
        echo "--------------------------------------------\n";
        echo "✓ Crear scripts WP CLI para actualización masiva\n";
        echo "✓ Crear scripts específicos para resolver conflicto Elementor-RankMath\n";
        echo "✓ Crear comandos para instalar y configurar plugins adicionales\n";
        echo "✓ Crear herramientas para monitorear y verificar cambios\n";
        echo "✓ Crear consultas GraphQL avanzadas si instalas WPGraphQL\n\n";

        echo "CUANDO DIGAS \"AHORA HAZME ESTO\" - PODRÉ:\n";
        echo "• Crear el script exacto para tu necesidad específica\n";
        echo "• Generar comandos WP CLI para ejecutar en tu servidor\n";
        echo "• Crear consultas API específicas\n";
        echo "• Hacer análisis técnicos profundos\n\n";
    }
}

$strategy = new Extended_Integration_Strategy();
$strategy->generate_strategy();

echo "✅ ESTRATEGIA AMPLIADA COMPLETADA\n";
echo "Ahora puedo ayudarte con mucho más detalle y precisión.\n";