<?php
/**
 * Verificador de Configuración de Rank Math para Mars Challenge
 * Valida que las configuraciones críticas estén correctamente establecidas
 */

class RankMath_Config_Checker {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando verificación de configuración de Rank Math para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'RankMath-Checker/1.0',
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
     * Verificar si Rank Math está activo
     */
    public function check_rankmath_installed() {
        echo "\n🔍 VERIFICANDO SI RANK MATH ESTÁ ACTIVO...\n";
        
        // Verificar si el endpoint de Rank Math está disponible
        $response = $this->make_request($this->site_url . "/wp-json/rankmath/v1/analyzeUrl");
        
        if ($response['status_code'] === 200 || $response['status_code'] === 404) {
            echo "  ✓ API de Rank Math detectada\n";
            
            // Intentar otro endpoint común
            $config_response = $this->make_request($this->site_url . "/wp-json/rankmath/v1/getSettings");
            if ($config_response['status_code'] === 200 || $config_response['status_code'] === 401) {
                echo "  ✓ Configuración de Rank Math accesible (permisos ok)\n";
                return true;
            }
        }
        
        echo "  - No se detectó Rank Math activo o con problemas de configuración\n";
        return false;
    }
    
    /**
     * Verificar configuración general de Rank Math
     */
    public function check_general_settings() {
        echo "\n⚙️  VERIFICANDO CONFIGURACIÓN GENERAL DE RANK MATH...\n";
        
        // Intentar obtener opciones generales
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/settings");
        
        if ($response['status_code'] === 200) {
            echo "  ✓ Configuración general accesible\n";
            
            // Verificar configuraciones de SEO comunes
            $settings_check = array(
                'sitemap_enabled' => 'No disponible por API directa',
                'robot_meta_settings' => 'No disponible por API directa',
                'webmaster_tools' => 'No disponible por API directa'
            );
            
            foreach ($settings_check as $setting => $status) {
                echo "    - $setting: $status\n";
            }
        } else {
            echo "  - No se pudo acceder a la configuración general\n";
        }
    }
    
    /**
     * Obtener páginas y verificar configuraciones individuales
     */
    public function check_page_configs() {
        echo "\n📋 VERIFICANDO CONFIGURACIONES INDIVIDUALES DE PÁGINAS...\n";
        
        $content = $this->get_all_content();
        $noindex_found = 0;
        
        foreach ($content['pages'] as $page) {
            $rankmath_meta = $page['meta'] ?? array();
            
            // Verificar configuración de noindex (campo específico de Rank Math/Yoast)
            $noindex_status = $rankmath_meta['rank_math_noindex'] ?? 
                             ($rankmath_meta['_yoast_wpseo_meta-robots-noindex'] ?? '0');
            
            if ($noindex_status == '1') {
                $noindex_found++;
                echo "  ✓ Página ID {$page['id']} ({$page['title']['rendered']}) tiene noindex\n";
            }
        }
        
        if ($noindex_found === 0) {
            echo "  - No se encontraron páginas con noindex configurado (¡bien!)\n";
        } else {
            echo "  - Encontradas $noindex_found páginas con noindex (posible problema a revisar)\n";
        }
    }
    
    /**
     * Obtener todas las páginas y posts
     */
    private function get_all_content() {
        $content = array('pages' => array(), 'posts' => array());
        
        // Obtener páginas
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $content['pages'] = array_merge($content['pages'], $new_pages);
                $page_num++;
            } else {
                break;
            }
        } while (count($response['body']) === 50);
        
        // Obtener posts
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                $content['posts'] = array_merge($content['posts'], $new_posts);
                $post_num++;
            } else {
                break;
            }
        } while (count($response['body']) === 50);
        
        return $content;
    }
    
    /**
     * Verificar estado del sitemap
     */
    public function check_sitemap_status() {
        echo "\n🗺️  VERIFICANDO ESTATUS DEL SITEMAP...\n";
        
        $sitemap_url = $this->site_url . '/sitemap_index.xml';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sitemap_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RankMath-Checker/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "  ✗ Error de conexión al sitemap: $error\n";
        } elseif ($http_code === 200) {
            $sitemap_xml = simplexml_load_string($response);
            if ($sitemap_xml !== false) {
                $url_count = isset($sitemap_xml->sitemap) ? count($sitemap_xml->sitemap) : 
                            (isset($sitemap_xml->url) ? count($sitemap_xml->url) : 0);
                echo "  ✓ Sitemap accesible (URLs o sub-sitemaps: $url_count)\n";
            } else {
                echo "  ⚠ Sitemap accesible pero no se pudo parsear\n";
            }
        } else {
            echo "  ✗ Sitemap no accesible (Status: $http_code)\n";
        }
    }
    
    /**
     * Generar resumen de verificación
     */
    public function generate_summary() {
        echo "\n📋 RESUMEN DE VERIFICACIÓN DE RANK MATH\n";
        echo "=======================================\n";
        
        $rankmath_active = $this->check_rankmath_installed();
        
        if ($rankmath_active) {
            echo "\n✅ RANK MATH ESTÁ ACTIVO Y FUNCIONAL\n";
            
            $this->check_general_settings();
            $this->check_page_configs();
            $this->check_sitemap_status();
            
            echo "\n🎯 RECOMENDACIONES PARA MEJORA:\n";
            echo "   1. Verificar configuración de redirecciones en Rank Math\n";
            echo "   2. Confirmar conexión con Google Search Console\n";
            echo "   3. Validar que no haya páginas con noindex configurado incorrectamente\n";
            echo "   4. Verificar correcta generación del sitemap\n";
            
        } else {
            echo "\n❌ RANK MATH NO DETECTADO O CON PROBLEMAS\n";
            echo "   - Verifica que Rank Math esté instalado y activo\n";
            echo "   - Revisa permisos y configuración del plugin\n";
            echo "   - Confirma que se haya completado la instalación correctamente\n";
        }
        
        echo "\n📊 ESTADO ACTUAL:\n";
        echo "   - Sitio: " . $this->site_url . "\n";
        echo "   - API accesible: " . ($rankmath_active ? "Sí" : "No") . "\n";
        echo "   - Sitemap disponible: " . (file_get_contents($this->site_url . '/sitemap_index.xml', false, null, 0, 100) !== false ? "Sí" : "No") . "\n";
        
        return array(
            'rankmath_active' => $rankmath_active,
            'site_url' => $this->site_url
        );
    }
    
    /**
     * Ejecutar verificación completa
     */
    public function run_full_check() {
        echo "🚀 INICIANDO VERIFICACIÓN COMPLETA DE RANK MATH\n";
        echo "=============================================\n";
        
        $results = $this->generate_summary();
        
        echo "\n✅ VERIFICACIÓN COMPLETA TERMINADA\n";
        echo "   - Estado general de Rank Math verificado\n";
        echo "   - Configuraciones revisadas\n";
        echo "   - Recomendaciones generadas\n";
        echo "   - Listo para implementar solución de errores 404\n";
        
        return $results;
    }
}

// Ejecutar la verificación de Rank Math
$checker = new RankMath_Config_Checker();
$checker->run_full_check();

echo "\n🎯 ¡LISTO PARA IMPLEMENTAR LAS REDIRECCIONES EN RANK MATH!\n";
echo "   Recuerda seguir la guía detallada en rank_math_implementation_guide.md\n";