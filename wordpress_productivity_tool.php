<?php
/**
 * HERRAMIENTA DEFINTIVA DE PRODUCTIVIDAD PARA WORDPRESS + RANKMATH + ELEMENTOR
 * Comunicación eficiente: Dime qué necesitas y te doy exactamente eso
 */

class WordPress_Productivity_Tool {

    private $site_config = array();
    private $capabilities = array();

    public function __construct() {
        $this->site_config = array(
            'site_url' => 'https://mars-challenge.com',
            'api_username' => 'wmaster_cs4or9qs',
            'api_app_password' => 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV'
        );
        
        $this->capabilities = array(
            'api_integration' => true,
            'file_generation' => true,
            'analysis_tools' => true,
            'wp_cli_commands' => false,  // Asumiendo que no tienes acceso directo
            'graphql_support' => true    // Puedo generar consultas
        );
    }

    /**
     * MÉTODO PRINCIPAL: Hazme esto
     * Recibe una descripción de lo que necesitas y devuelve la solución exacta
     */
    public function hazme_esto($descripcion_necesidad) {
        echo "🔍 ANALIZANDO: \"$descripcion_necesidad\"\n";
        echo "=========================================\n\n";

        // Detectar tipo de tarea basado en la descripción
        $accion = $this->detectar_tipo_accion($descripcion_necesidad);
        
        switch ($accion) {
            case 'crear_script_actualizacion':
                return $this->generar_script_actualizacion($descripcion_necesidad);
                
            case 'generar_palabras_clave':
                return $this->generar_actualizacion_palabras_clave($descripcion_necesidad);
                
            case 'resolver_conflicto_seo':
                return $this->generar_solucion_conflicto_seo($descripcion_necesidad);
                
            case 'instalar_plugins':
                return $this->generar_comandos_instalacion_plugins($descripcion_necesidad);
                
            case 'analisis_tecnico':
                return $this->generar_analisis_tecnico($descripcion_necesidad);
                
            case 'generar_script_diagnostico':
                return $this->generar_script_diagnostico($descripcion_necesidad);
                
            case 'monitoreo_seo':
                return $this->generar_monitoreo_seo($descripcion_necesidad);
                
            default:
                return $this->respuesta_generica($descripcion_necesidad);
        }
    }

    /**
     * Detectar tipo de acción basado en palabras clave
     */
    private function detectar_tipo_accion($descripcion) {
        $descripcion_lower = strtolower($descripcion);
        
        // Palabras clave para cada tipo de acción
        $acciones = array(
            'crear_script_actualizacion' => array('script', 'actualizar', 'bulk', 'masivo', 'actualización'),
            'generar_palabras_clave' => array('palabra clave', 'keywords', 'focus keyword', 'keyword'),
            'resolver_conflicto_seo' => array('conflicto', 'elementor', 'rank math', 'seo conflict', 'problema seo'),
            'instalar_plugins' => array('instalar', 'plugin', 'wp-graphql', 'graphql', 'agregar', 'añadir'),
            'analisis_tecnico' => array('analizar', 'análisis', 'verificar', 'revisar', 'auditoría', 'técnico'),
            'generar_script_diagnostico' => array('diagnosticar', 'diagnóstico', 'problema', 'error', 'falla'),
            'monitoreo_seo' => array('monitor', 'monitoreo', 'rastrear', 'seguimiento', 'tracking')
        );
        
        foreach ($acciones as $accion => $palabras) {
            foreach ($palabras as $palabra) {
                if (strpos($descripcion_lower, $palabra) !== false) {
                    return $accion;
                }
            }
        }
        
        return 'otro';
    }

    /**
     * Generar un script de actualización específica
     */
    private function generar_script_actualizacion($descripcion) {
        preg_match('/(\d+)\s*(?:página|pagina|posts?|entradas?)/i', $descripcion, $matches);
        $cantidad = isset($matches[1]) ? $matches[1] : 'múltiples';

        $script_template = "<?php
/**
 * Script de actualización {$cantidad} para Mars Challenge
 * Generado específicamente para: $descripcion
 */

class Bulk_Updater {
    private \$site_url = 'https://mars-challenge.com';
    private \$username = 'wmaster_cs4or9qs';
    private \$app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private \$auth_header;

    public function __construct() {
        \$this->auth_header = 'Authorization: Basic ' . base64_encode(\$this->username . ':' . str_replace(' ', '', \$this->app_password));
    }

    private function make_request(\$url, \$method = 'GET', \$data = null) {
        \$ch = curl_init();
        curl_setopt_array(\$ch, array(
            CURLOPT_URL => \$url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(\$this->auth_header, 'Content-Type: application/json'),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Bulk-Updater/1.0'
        ));
        
        if (\$method === 'POST' && \$data) {
            curl_setopt(\$ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode(\$data));
        }
        
        \$response = curl_exec(\$ch);
        \$http_code = curl_getinfo(\$ch, CURLINFO_HTTP_CODE);
        curl_close(\$ch);
        
        return array('status_code' => \$http_code, 'body' => \$response ? json_decode(\$response, true) : null);
    }

    public function update_posts(\$post_ids, \$updates) {
        \$results = array('success' => 0, 'failed' => 0);
        
        foreach (\$post_ids as \$id) {
            \$post_type = \$this->get_post_type(\$id);
            if (\$post_type === 'unknown') continue;
            
            \$response = \$this->make_request(
                \$this->site_url . \"/wp-json/wp/v2/\$post_type/\$id\", 
                'POST', 
                array('meta' => \$updates)
            );
            
            if (\$response['status_code'] === 200) {
                \$results['success']++;
            } else {
                \$results['failed']++;
            }
            
            sleep(1); // Evitar sobrecarga
        }
        
        return \$results;
    }
    
    private function get_post_type(\$post_id) {
        \$response = \$this->make_request(\$this->site_url . \"/wp-json/wp/v2/pages/\$post_id\");
        if (\$response['status_code'] === 200) return 'pages';
        
        \$response = \$this->make_request(\$this->site_url . \"/wp-json/wp/v2/posts/\$post_id\");
        return \$response['status_code'] === 200 ? 'posts' : 'unknown';
    }
}

// Ejemplo de uso
\$updater = new Bulk_Updater();
\$post_ids = [10, 27, 37]; // IDs específicos
\$updates = array(
    'rank_math_description' => 'Descripción actualizada',
    'rank_math_title' => 'Título SEO actualizado',
    'rank_math_focus_keyword' => 'palabra clave principal'
);

\$results = \$updater->update_posts(\$post_ids, \$updates);
echo 'Actualizados: ' . \$results['success'] . ', Fallidos: ' . \$results['failed'];
";

        return array(
            'type' => 'script_php',
            'content' => $script_template,
            'instructions' => 'Guarda este archivo PHP y ejecútalo en tu servidor para actualizar ' . $cantidad . ' según tus necesidades'
        );
    }

    /**
     * Generar actualización de palabras clave
     */
    private function generar_actualizacion_palabras_clave($descripcion) {
        $respuesta = "## ACTUALIZACIÓN DE PALABRAS CLAVE PARA RANK MATH\n\n";
        $respuesta .= "### Campos disponibles:\n";
        $respuesta .= "- `'rank_math_focus_keyword'` - Palabra clave principal\n";
        $respuesta .= "- `'rank_math_seo_score'` - Puntuación SEO (automática)\n";
        $respuesta .= "- `'rank_math_internal_links_count'` - Contador de enlaces internos\n\n";
        
        $respuesta .= "### Ejemplo de actualización:\n";
        $respuesta .= "```\n";
        $respuesta .= "POST /wp-json/wp/v2/pages/PAGE_ID\n";
        $respuesta .= "{\n";
        $respuesta .= "    \"meta\": {\n";
        $respuesta .= "        \"rank_math_focus_keyword\": \"tu palabra clave principal\",\n";
        $respuesta .= "        \"rank_math_description\": \"Descripción optimizada para la palabra clave\",\n";
        $respuesta .= "        \"rank_math_title\": \"Título optimizado para la palabra clave\"\n";
        $respuesta .= "    }\n";
        $respuesta .= "}\n";
        $respuesta .= "```\n\n";
        
        $respuesta .= "### WP CLI Comandos:\n";
        $respuesta .= "- `wp post meta update PAGE_ID rank_math_focus_keyword 'palabra-clave'`\n";
        $respuesta .= "- `wp post meta update PAGE_ID rank_math_focus_keyword 'marte,solución,mars-challenge'` (múltiples palabras)\n\n";
        
        $respuesta .= "### Script de ejemplo para múltiples páginas:\n";
        $respuesta .= "```bash\n";
        $respuesta .= "# Actualizar palabra clave para páginas específicas\n";
        $respuesta .= "for page_id in 10 27 37 1521; do\n";
        $respuesta .= "    wp post meta update \$page_id rank_math_focus_keyword 'mars challenge'\n";
        $respuesta .= "    wp post meta update \$page_id rank_math_description 'Descripción optimizada para: mars challenge'\n";
        $respuesta .= "done\n";
        $respuesta .= "```\n";

        return array(
            'type' => 'instructions',
            'content' => $respuesta,
            'instructions' => 'Sigue estas instrucciones para actualizar palabras clave en tus páginas'
        );
    }

    /**
     * Generar solución para conflicto SEO
     */
    private function generar_solucion_conflicto_seo($descripcion) {
        $respuesta = "## SOLUCIÓN PARA CONFLICTO ELEMENTOR - RANK MATH\n\n";
        
        $respuesta .= "### Diagnóstico del problema:\n";
        $respuesta .= "Elementor está sobrescribiendo las meta tags que Rank Math intenta aplicar.\n\n";
        
        $respuesta .= "### Solución paso a paso:\n";
        $respuesta .= "1. **Verificar prioridad de plugins**:\n";
        $respuesta .= "   - En Rank Math > General Settings > Advanced\n";
        $respuesta .= "   - Asegurarse que Rank Math tenga alta precedencia\n\n";
        
        $respuesta .= "2. **Desactivar meta tags de Elementor**:\n";
        $respuesta .= "   - Elementor > Settings > Advanced\n";
        $respuesta .= "   - Desmarcar 'Enable Document Elements' donde afecte SEO\n\n";
        
        $respuesta .= "3. **Actualización coordinada**:\n";
        $respuesta .= "   ```php\n";
        $respuesta .= "   // Actualizar ambos sistemas al mismo tiempo\n";
        $respuesta .= "   'meta' => array(\n";
        $respuesta .= "       'rank_math_description' => 'Descripción',\n";
        $respuesta .= "       'rank_math_title' => 'Título',\n";
        $respuesta .= "       'elementor_page_settings' => json_encode([ // Si es necesario\n";
        $respuesta .= "           'post_title_tag' => 'h1',\n";
        $respuesta .= "           'meta_description' => 'Descripción'\n";
        $respuesta .= "       ])\n";
        $respuesta .= "   )\n";
        $respuesta .= "   ```\n\n";
        
        $respuesta .= "4. **Limpiar caché después de cada actualización**:\n";
        $respuesta .= "   - Elementor Tools > Clear Cache\n";
        $respuesta .= "   - Cualquier plugin de caché que estés usando\n\n";
        
        $respuesta .= "### Comando para verificar conflictos:\n";
        $respuesta .= "```bash\n";
        $respuesta .= "# Verificar qué meta tags están activas\n";
        $respuesta .= "wp post meta list PAGE_ID --keys=rank_math_description,elementor_page_settings,_elementor_page_settings\n";
        $respuesta .= "```\n";

        return array(
            'type' => 'solution',
            'content' => $respuesta,
            'instructions' => 'Sigue esta solución para resolver el conflicto entre Elementor y Rank Math'
        );
    }

    /**
     * Generar comandos de instalación de plugins
     */
    private function generar_comandos_instalacion_plugins($descripcion) {
        preg_match_all('/([a-zA-Z0-9_-]+)/', strtolower($descripcion), $matches);
        $plugins = array_intersect($matches[0], ['wp-graphql', 'graphql', 'rank-math', 'elementor', 'seo', 'cache']);
        
        $respuesta = "## COMANDOS PARA INSTALAR PLUGINS\n\n";
        
        if (in_array('wp-graphql', $plugins) || in_array('graphql', $plugins)) {
            $respuesta .= "### WP GraphQL:\n";
            $respuesta .= "```bash\n";
            $respuesta .= "wp plugin install wp-graphql --activate\n";
            $respuesta .= "wp plugin install wp-graphql-acf --activate  # Si usas ACF Fields\n";
            $respuesta .= "wp plugin install wp-graphql-gutenberg --activate  # Soporte para Gutenberg\n";
            $respuesta .= "```\n\n";
        }
        
        if (in_array('rank-math', $plugins)) {
            $respuesta .= "### Rank Math Pro (si tienes licencia):\n";
            $respuesta .= "```bash\n";
            $respuesta .= "# Si tienes archivo ZIP de Rank Math Pro\n";
            $respuesta .= "wp plugin install /path/to/rank-math-pro.zip --activate\n";
            $respuesta .= "# O actualizar desde WP Admin si ya está instalado\n";
            $respuesta .= "```\n\n";
        }
        
        if (in_array('seo', $plugins)) {
            $respuesta .= "### Otros plugins SEO útiles:\n";
            $respuesta .= "```bash\n";
            $respuesta .= "wp plugin install yoast-seo --activate  # Como backup\n";
            $respuesta .= "wp plugin install schema-and-structured-data-for-wp --activate\n";
            $respuesta .= "wp plugin install broken-link-checker --activate\n";
            $respuesta .= "```\n\n";
        }
        
        if (in_array('cache', $plugins)) {
            $respuesta .= "### Plugins de Caché (útiles para rendimiento):\n";
            $respuesta .= "```bash\n";
            $respuesta .= "wp plugin install wp-rocket --activate\n";
            $respuesta .= "# O alternativas\n";
            $respuesta .= "wp plugin install w3-total-cache --activate\n";
            $respuesta .= "wp plugin install wp-super-cache --activate\n";
            $respuesta .= "```\n\n";
        }
        
        if (empty($plugins)) {
            $respuesta .= "### Comandos generales útiles:\n";
            $respuesta .= "```bash\n";
            $respuesta .= "wp plugin list --status=active  # Ver plugins activos\n";
            $respuesta .= "wp plugin search seo  # Buscar plugins relacionados\n";
            $respuesta .= "wp plugin install slug-del-plugin --activate\n";
            $respuesta .= "wp plugin update --all  # Actualizar todos los plugins\n";
            $respuesta .= "```\n";
        }

        return array(
            'type' => 'commands',
            'content' => $respuesta,
            'instructions' => 'Usa estos comandos WP CLI para instalar los plugins que necesitas'
        );
    }

    /**
     * Generar análisis técnico
     */
    private function generar_analisis_tecnico($descripcion) {
        $respuesta = "## ANÁLISIS TÉCNICO ESPECÍFICO\n\n";
        
        $respuesta .= "### Información del sitio actual:\n";
        $respuesta .= "- URL: " . $this->site_config['site_url'] . "\n";
        $respuesta .= "- API disponible: Sí (Application Passwords)\n";
        $respuesta .= "- Plugins activos detectados: Rank Math, Elementor\n";
        $respuesta .= "- Problemas identificados: Conflicto SEO Elementor-Rank Math\n\n";
        
        $respuesta .= "### Recomendaciones técnicas:\n";
        $respuesta .= "1. **Verificar versiones**:\n";
        $respuesta .= "   ```bash\n";
        $respuesta .= "   wp plugin get rank-math --field=version\n";
        $respuesta .= "   wp plugin get elementor --field=version\n";
        $respuesta .= "   wp core version\n";
        $respuesta .= "   ```\n\n";
        
        $respuesta .= "2. **Configuraciones óptimas**:\n";
        $respuesta .= "   - Rank Math: Activar 'Headless CMS Support' si es necesario\n";
        $respuesta .= "   - Elementor: Minimizar generación de meta tags duplicados\n";
        $respuesta .= "   - Caché: Asegurar que se regenere después de cambios SEO\n\n";
        
        $respuesta .= "3. **Script de diagnóstico completo**:\n";
        $respuesta .= "   ```php\n";
        $respuesta .= "   // Verificar estado actual de SEO en páginas críticas\n";
        $respuesta .= "   \$critical_pages = [10, 27, 37, 1521, 2883];\n";
        $respuesta .= "   foreach (\$critical_pages as \$pid) {\n";
        $respuesta .= "       \$rm_desc = get_post_meta(\$pid, 'rank_math_description', true);\n";
        $respuesta .= "       \$rm_title = get_post_meta(\$pid, 'rank_math_title', true);\n";
        $respuesta .= "       \$rm_kw = get_post_meta(\$pid, 'rank_math_focus_keyword', true);\n";
        $respuesta .= "       \$elementor_settings = get_post_meta(\$pid, '_elementor_page_settings', true);\n";
        $respuesta .= "       // Analizar y reportar conflictos\n";
        $respuesta .= "   }\n";
        $respuesta .= "   ```\n\n";
        
        $respuesta .= "4. **Monitoreo continuo**:\n";
        $respuesta .= "   - Usar Google Search Console para rastrear mejoras\n";
        $respuesta .= "   - Monitorizar Core Web Vitals después de cambios\n";
        $respuesta .= "   - Verificar índices en Google periódicamente\n";

        return array(
            'type' => 'analysis',
            'content' => $respuesta,
            'instructions' => 'Este análisis técnico te ayudará a entender y optimizar tu configuración actual'
        );
    }

    /**
     * Generar script de diagnóstico
     */
    private function generar_script_diagnostico($descripcion) {
        $script = "<?php
/**
 * SCRIPT DE DIAGNÓSTICO COMPLETO SEO - ELEMENTOR - RANK MATH
 * Generado para: $descripcion
 */

class SeoConflictDiagnostician {
    private \$checks = array();
    
    public function run_full_diagnosis() {
        echo \"\\n=== DIAGNÓSTICO COMPLETO SEO ===\\n\\n\";
        
        \$this->check_wordpress_info();
        \$this->check_rankmath_active();
        \$this->check_elementor_active();
        \$this->check_conflicting_meta_tags();
        \$this->check_seo_settings();
        \$this->check_critical_pages();
        
        \$this->generate_report();
    }
    
    private function check_wordpress_info() {
        echo \"[INFO] WordPress Info:\\n\";
        echo \"  - Version: \" . get_bloginfo('version') . \"\\n\";
        echo \"  - Site URL: \" . get_site_url() . \"\\n\";
        echo \"  - Home URL: \" . get_home_url() . \"\\n\\n\";
    }
    
    private function check_rankmath_active() {
        \$active = defined('RANK_MATH_VERSION');
        echo \"[RANK MATH] Status: \" . (\$active ? \"ACTIVE (v\" . RANK_MATH_VERSION . \")\" : \"NOT ACTIVE\") . \"\\n\\n\";
        \$this->checks['rankmath_active'] = \$active;
    }
    
    private function check_elementor_active() {
        \$active = defined('ELEMENTOR_VERSION');
        echo \"[ELEMENTOR] Status: \" . (\$active ? \"ACTIVE (v\" . ELEMENTOR_VERSION . \")\" : \"NOT ACTIVE\") . \"\\n\\n\";
        \$this->checks['elementor_active'] = \$active;
    }
    
    private function check_conflicting_meta_tags(\$post_ids = [10, 27, 37, 1521, 2883]) {
        echo \"[SEO CONFLICTS] Checking for conflicting tags:\\n\";
        
        foreach (\$post_ids as \$pid) {
            \$post = get_post(\$pid);
            if (!\$post) continue;
            
            echo \"  Page ID \$pid (\" . \$post->post_name . \"):\\n\";
            
            // Check Rank Math tags
            \$rm_desc = get_post_meta(\$pid, 'rank_math_description', true);
            \$rm_title = get_post_meta(\$pid, 'rank_math_title', true);
            \$rm_kw = get_post_meta(\$pid, 'rank_math_focus_keyword', true);
            
            // Check Elementor settings
            \$elementor_settings = get_post_meta(\$pid, '_elementor_page_settings', true);
            
            echo \"    - Rank Math Desc: \" . (!empty(\$rm_desc) ? \"SET\" : \"MISSING\") . \"\\n\";
            echo \"    - Rank Math Title: \" . (!empty(\$rm_title) ? \"SET\" : \"MISSING\") . \"\\n\";
            echo \"    - Rank Math KW: \" . (!empty(\$rm_kw) ? \$rm_kw : \"MISSING\") . \"\\n\";
            echo \"    - Elementor Settings: \" . (!empty(\$elementor_settings) ? \"SET\" : \"MISSING\") . \"\\n\\n\";
        }
    }
    
    private function check_seo_settings() {
        echo \"[SEO SETTINGS] Current Configuration:\\n\";
        
        \$rankmath_options = get_option('rank_math_options');
        if (\$rankmath_options && is_array(\$rankmath_options)) {
            \$headless = isset(\$rankmath_options['general']['headless_support']) ? \$rankmath_options['general']['headless_support'] : 'disabled';
            echo \"  - Rank Math Headless Support: \" . (\$headless ? \"ENABLED\" : \"DISABLED\") . \"\\n\";
        }
        
        echo \"\\n\";
    }
    
    private function check_critical_pages() {
        echo \"[CRITICAL PAGES] Status Report:\\n\";
        
        \$critical_urls = [
            '/' => 'Homepage',
            '/fuego/' => 'Fuego Section',
            '/registro/' => 'Registration Page',
            '/sobre/mars-challenge/' => 'About Page'
        ];
        
        foreach (\$critical_urls as \$url => \$name) {
            \$has_desc = \$this->page_has_seo_description(\$url);
            echo \"  - \$name (\$url): \" . (\$has_desc ? \"SEO OK\" : \"NEEDS ATTENTION\") . \"\\n\";
        }
        
        echo \"\\n\";
    }
    
    private function page_has_seo_description(\$relative_url) {
        // Logic to check if page has proper SEO description
        \$full_url = get_site_url() . \$relative_url;
        \$post_id = url_to_postid(\$full_url);
        
        if (\$post_id) {
            \$rm_desc = get_post_meta(\$post_id, 'rank_math_description', true);
            return !empty(\$rm_desc);
        }
        
        return false;
    }
    
    private function generate_report() {
        echo \"\\n=== DIAGNÓSTICO COMPLETADO ===\\n\";
        echo \"Recomendaciones:\\n\";
        echo \"- Resolver conflictos identificados entre Elementor y Rank Math\\n\";
        echo \"- Actualizar páginas críticas que necesiten descripciones\\n\";
        echo \"- Verificar configuración de Headless CMS Support\\n\";
        echo \"- Limpiar caché después de aplicar cambios\\n\";
    }
}

// Ejecutar diagnóstico
\$diagnostician = new SeoConflictDiagnostician();
\$diagnostician->run_full_diagnosis();
";

        return array(
            'type' => 'diagnostic_script',
            'content' => $script,
            'instructions' => 'Guarda este script como PHP y ejecútalo en tu servidor para obtener un diagnóstico completo'
        );
    }

    /**
     * Generar monitoreo SEO
     */
    private function generar_monitoreo_seo($descripcion) {
        $respuesta = "## MONITOREO SEO CONTINUO\n\n";
        
        $respuesta .= "### Scripts para seguimiento:\n";
        $respuesta .= "```php\n";
        $respuesta .= "// Script para monitorizar posiciones SEO\n";
        $respuesta .= "\$monitoreo = [\n";
        $respuesta .= "    'fecha' => date('Y-m-d H:i:s'),\n";
        $respuesta .= "    'pagina_inicio' => [\n";
        $respuesta .= "        'titulo' => get_post_meta(10, 'rank_math_title', true),\n";
        $respuesta .= "        'descripcion' => get_post_meta(10, 'rank_math_description', true),\n";
        $respuesta .= "        'palabra_clave' => get_post_meta(10, 'rank_math_focus_keyword', true)\n";
        $respuesta .= "    ],\n";
        $respuesta .= "    'estado_cache' => wp_using_ext_object_cache()  // Verificar sistema de caché\n";
        $respuesta .= "];\n";
        $respuesta .= "print_r(\$monitoreo);\n";
        $respuesta .= "```\n\n";
        
        $respuesta .= "### Comandos útiles para monitoreo:\n";
        $respuesta .= "```bash\n";
        $respuesta .= "# Verificar estado de todos los posts/pages\n";
        $respuesta .= "wp post list --post_type=page,post --format=csv --fields=ID,post_title,post_status | wc -l\n";
        $respuesta .= "\n";
        $respuesta .= "# Buscar posts sin descripción de Rank Math\n";
        $respuesta .= "wp post list --post_type=page --meta_key=rank_math_description --meta_compare=NOT EXISTS --format=ids\n";
        $respuesta .= "\n";
        $respuesta .= "# Contar cuántos tienen descripciones\n";
        $respuesta .= "wp eval \"echo count(get_posts(array('post_type'=>'page', 'meta_query'=>array(array('key'=>'rank_math_description','compare'=>'EXISTS')))));\"\n";
        $respuesta .= "```\n\n";
        
        $respuesta .= "### Recomendaciones de monitoreo:\n";
        $respuesta .= "1. Establecer puntos de control semanales\n";
        $respuesta .= "2. Usar Google Search Console para monitorear índices\n";
        $respuesta .= "3. Verificar Core Web Vitals periódicamente\n";
        $respuesta .= "4. Configurar alertas para páginas con errores 404\n";

        return array(
            'type' => 'monitoring',
            'content' => $respuesta,
            'instructions' => 'Usa estos métodos para monitorear continuamente tu estado SEO'
        );
    }

    /**
     * Respuesta genérica cuando no se reconoce el tipo de tarea
     */
    private function respuesta_generica($descripcion) {
        $respuesta = "## TAREA NO ESPECIFICADA CLARAMENTE\n\n";
        $respuesta .= "Descripción recibida: \"$descripcion\"\n\n";
        $respuesta .= "### Por favor, sé más específico. Ejemplos de buenas instrucciones:\n";
        $respuesta .= "- \"Hazme un script para actualizar las palabras clave en 20 páginas\"\n";
        $respuesta .= "- \"Genera comandos para instalar WP GraphQL y conectarlo con Rank Math\"\n";
        $respuesta .= "- \"Resuelve el conflicto entre Elementor y Rank Math que hace que no se muestren las meta descriptions\"\n";
        $respuesta .= "- \"Crea un script de diagnóstico que encuentre todas las páginas sin metadescripción\"\n\n";
        
        $respuesta .= "### Capacidad actual de la herramienta:\n";
        $respuesta .= "- Scripts PHP para actualizaciones masivas\n";
        $respuesta .= "- Comandos WP CLI para gestión de plugins y contenido\n";
        $respuesta .= "- Scripts de diagnóstico técnico\n";
        $respuesta .= "- Soluciones específicas para conflictos SEO\n";
        $respuesta .= "- Consultas GraphQL (si se implementa)\n\n";
        
        $respuesta .= "Vuelve a formular tu solicitud de forma específica y te ayudaré exactamente con lo que necesitas.";

        return array(
            'type' => 'clarification',
            'content' => $respuesta,
            'instructions' => 'Por favor reformula tu solicitud de forma más específica'
        );
    }
}

// Ejemplo de uso:
echo "🔧 HERRAMIENTA DE PRODUCTIVIDAD WORDPRESS ACTIVADA\n";
echo "==================================================\n\n";

$tool = new WordPress_Productivity_Tool();

// Mostrar cómo usarla
echo "USO: \$tool->hazme_esto(\"descripción_de_lo_que_necesitas\");\n\n";

echo "EJEMPLOS DE USO:\n";
echo "- Actualizar palabras clave: \"Actualizar palabras clave en 5 páginas específicas\"\n";
echo "- Resolver conflicto: \"Resolver conflicto entre Elementor y Rank Math con metadescripciones\"\n";
echo "- Instalar plugins: \"Instalar WP GraphQL y configurarlo para trabajar con Rank Math\"\n";
echo "- Análisis: \"Hacer un análisis técnico completo de mi sitio\"\n\n";

echo "La herramienta detectará automáticamente qué necesitas y te dará la solución exacta.\n\n";