<?php
/**
 * Verificación Completa de Yoast Premium y Gestión de Sitemap/Noindex
 * Identifica herramientas de Yoast Premium y páginas con noindex
 */

class Yoast_Premium_Full_Check {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando verificación completa de Yoast Premium para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Yoast-Full-Check/1.0',
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
     * Verificar todas las posibles rutas de la API de Yoast
     */
    public function check_all_yoast_endpoints() {
        echo "\n🔍 VERIFICANDO TODOS LOS ENDPOINTS DE YOAST...\n";
        
        $endpoints_to_check = array(
            '/wp-json/yoast/v1/',
            '/wp-json/wpseo/v1/',
            '/wp-json/yoast/v1/config',
            '/wp-json/yoast/v1/replacement_variables',
            '/wp-json/yoast/v1/report/indexables',
            '/wp-json/yoast/v1/redirects', // Premium
            '/wp-json/yoast/v1/import_info', // Premium
        );
        
        $yoast_info = array();
        
        foreach ($endpoints_to_check as $endpoint) {
            $response = $this->make_request($this->site_url . $endpoint);
            $status = $response['status_code'];
            
            if ($status === 200 || $status === 404 || $status === 401) {
                $yoast_info[$endpoint] = array(
                    'status' => $status,
                    'accessible' => $status !== 404,
                    'data' => $response['body']
                );
                
                if ($status === 200) {
                    echo "  ✓ Accesible: $endpoint (Status: $status)\n";
                } elseif ($status === 401) {
                    echo "  ⚠ Accesible pero requiere permisos: $endpoint (Status: $status)\n";
                } elseif ($status === 404) {
                    echo "  - No encontrado: $endpoint (Status: $status)\n";
                }
            }
        }
        
        return $yoast_info;
    }
    
    /**
     * Verificar si Yoast Premium está realmente instalado
     */
    public function check_premium_status() {
        echo "\n🔐 VERIFICANDO ESTADO DE YOAST PREMIUM...\n";
        
        // Intentar acceder a funcionalidades exclusivas de Premium
        $premium_endpoints = array(
            'redirects' => '/wp-json/yoast/v1/redirects',
            'import_info' => '/wp-json/yoast/v1/import_info',
            'configuration_wizard' => '/wp-json/yoast/v1/configuration',
        );
        
        $premium_features = array();
        
        foreach ($premium_endpoints as $feature => $endpoint) {
            $response = $this->make_request($this->site_url . $endpoint);
            
            if ($response['status_code'] === 200) {
                $premium_features[$feature] = array(
                    'available' => true,
                    'status' => $response['status_code']
                );
                echo "  ✓ $feature: Disponible (Premium)\n";
            } elseif ($response['status_code'] === 404) {
                $premium_features[$feature] = array(
                    'available' => false,
                    'status' => $response['status_code']
                );
                echo "  - $feature: No disponible (Free version)\n";
            } else {
                $premium_features[$feature] = array(
                    'available' => null,
                    'status' => $response['status_code'],
                    'error' => true
                );
                echo "  ? $feature: Error (Status: {$response['status_code']})\n";
            }
        }
        
        // Determinar si es realmente Premium
        $has_premium = count(array_filter($premium_features, function($f) { 
            return $f['available'] === true; 
        })) > 0;
        
        echo "\n  RESULTADO: " . ($has_premium ? "✓ YOAST PREMIUM INSTALADO" : "✗ SOLO YOAST FREE") . "\n";
        
        return array(
            'is_premium' => $has_premium,
            'features' => $premium_features
        );
    }
    
    /**
     * Obtener todas las páginas y revisar configuración de noindex
     */
    public function find_noindex_pages() {
        echo "\n🕵️  BUSCANDO PÁGINAS CON CONFIGURACIÓN NOINDEX...\n";
        
        // Obtener todas las páginas y posts
        $all_pages = array();
        $page_num = 1;
        
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $all_pages = array_merge($all_pages, $new_pages);
                
                foreach ($new_pages as $page) {
                    $yoast_meta = $page['meta'] ?? array();
                    $noindex_status = $yoast_meta['_yoast_wpseo_meta-robots-noindex'] ?? '0';
                    
                    if ($noindex_status == '1') {
                        echo "  ✓ Página ID {$page['id']}: {$page['title']['rendered']} - tiene noindex\n";
                    }
                }
                
                $page_num++;
                sleep(1); // Evitar demasiadas solicitudes
            } else {
                break;
            }
        } while (count($response['body']) === 50);
        
        // Obtener posts también
        $all_posts = array();
        $post_num = 1;
        
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                $all_posts = array_merge($all_posts, $new_posts);
                
                foreach ($new_posts as $post) {
                    $yoast_meta = $post['meta'] ?? array();
                    $noindex_status = $yoast_meta['_yoast_wpseo_meta-robots-noindex'] ?? '0';
                    
                    if ($noindex_status == '1') {
                        echo "  ✓ Post ID {$post['id']}: {$post['title']['rendered']} - tiene noindex\n";
                    }
                }
                
                $post_num++;
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);
        
        // También revisar configuración general que podría causar noindex
        $yoast_options_response = $this->make_request($this->site_url . "/wp-json/wp/v2/settings");
        if ($yoast_options_response['status_code'] === 200) {
            // Esto requiere acceso a opciones específicas de Yoast, que normalmente no están expuestas
            echo "  - Revisando configuración general de Yoast (requiere acceso directo a DB)\n";
        }
        
        return array(
            'pages' => $all_pages,
            'posts' => $all_posts
        );
    }
    
    /**
     * Analizar el sitemap.xml
     */
    public function analyze_sitemap() {
        echo "\n📄 ANALIZANDO SITEMAP.XML...\n";
        
        $sitemap_url = $this->site_url . '/sitemap_index.xml';
        if (file_exists($this->site_url . '/sitemap.xml')) {
            $sitemap_url = $this->site_url . '/sitemap.xml';
        }
        
        // Intentar obtener el sitemap
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sitemap_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Sitemap-Analyzer/1.0');
        
        $sitemap_content = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo "  ✗ Error al obtener sitemap: $error\n";
            return array('error' => $error);
        }
        
        if ($http_code !== 200) {
            echo "  ✗ Sitemap no accesible (Status: $http_code)\n";
            return array('error' => "Status code: $http_code");
        }
        
        // Analizar el contenido del sitemap
        $sitemap_xml = simplexml_load_string($sitemap_content);
        
        if ($sitemap_xml === false) {
            echo "  ✗ No se pudo parsear el sitemap XML\n";
            return array('error' => 'XML parsing failed');
        }
        
        // Contar URLs en el sitemap
        $sitemap_urls = array();
        
        if (isset($sitemap_xml->sitemap)) {
            // Es un sitemap index
            echo "  ✓ Sitemap index encontrado\n";
            echo "  - Sub-sitemaps: " . count($sitemap_xml->sitemap) . "\n";
            
            foreach ($sitemap_xml->sitemap as $sub_sitemap) {
                echo "    * " . (string)$sub_sitemap->loc . "\n";
            }
        } elseif (isset($sitemap_xml->url)) {
            // Es un sitemap normal
            echo "  ✓ Sitemap normal encontrado\n";
            $url_count = count($sitemap_xml->url);
            echo "  - Total URLs en sitemap: $url_count\n";
            
            // Mostrar algunas URLs
            $count = 0;
            foreach ($sitemap_xml->url as $url) {
                if ($count < 10) { // Mostrar primeras 10
                    echo "    * " . (string)$url->loc . "\n";
                    $sitemap_urls[] = (string)$url->loc;
                }
                $count++;
            }
        }
        
        return array(
            'status' => 'success',
            'url_count' => isset($sitemap_xml->url) ? count($sitemap_xml->url) : 0,
            'sitemap_urls' => $sitemap_urls,
            'content' => $sitemap_content
        );
    }
    
    /**
     * Comparar Yoast vs Rank Math
     */
    public function compare_seo_plugins() {
        echo "\n⚖️  COMPARACIÓN YOAST vs RANK MATH\n";
        echo "================================\n";
        
        echo "\n📊 Características Clave:\n";
        
        $comparison = array(
            'Yoast SEO' => array(
                'Gratuito' => 'Sí, con funciones básicas completas',
                'Premium' => 'Sí, con redirecciones, configuración avanzada',
                'Facilidad de uso' => 'Muy buena, interfaz clara',
                'Redirecciones' => 'Solo en Premium',
                'Análisis de contenido' => 'Excelente',
                'Schema markup' => 'Bueno, pero limitado',
                'Soporte' => 'Muy bueno',
                'Reputación' => 'Líder del mercado'
            ),
            'Rank Math' => array(
                'Gratuito' => 'Sí, con más funciones que Yoast Free',
                'Premium' => 'Sí, similar a Yoast Premium',
                'Facilidad de uso' => 'Buena, más intuitivo para algunos',
                'Redirecciones' => 'Sí en versión gratuita',
                'Análisis de contenido' => 'Muy bueno',
                'Schema markup' => 'Excelente, más opciones',
                'Soporte' => 'Bueno',
                'Reputación' => 'Muy buena, creciendo rápidamente'
            )
        );
        
        foreach ($comparison as $plugin => $features) {
            echo "\n{$plugin}:\n";
            foreach ($features as $feature => $value) {
                echo "  • $feature: $value\n";
            }
        }
        
        echo "\n🎯 Recomendación para Mars Challenge:\n";
        echo "   Si actualmente tienes Yoast Free y necesitas redirecciones:\n";
        echo "   1. Opción A: Actualizar a Yoast Premium (solución integrada)\n";
        echo "   2. Opción B: Cambiar a Rank Math Free (gratuito con redirecciones)\n";
        echo "   3. Opción C: Mantener Yoast Free + Plugin 'Redirection' adicional\n";
        
        echo "\n   Para tu caso específico (48 errores 404):\n";
        echo "   - Rank Math Free: Mejor opción GRATUITA (tiene redirecciones)\n";
        echo "   - Yoast Premium: Mejor opción PAGA (todo en uno, más estable)\n";
        
        return $comparison;
    }
    
    /**
     * Generar reporte completo
     */
    public function generate_full_report() {
        echo "\n📄 GENERANDO REPORTE COMPLETO...\n";
        echo "================================\n";
        
        // Verificar estado de Yoast
        $yoast_status = $this->check_premium_status();
        $endpoints = $this->check_all_yoast_endpoints();
        
        echo "\n🔍 RESULTADO FINAL DE YOAST:\n";
        echo "   Estado: " . ($yoast_status['is_premium'] ? "PREMIUM" : "FREE") . "\n";
        
        // Buscar páginas con noindex
        echo "\n📋 PÁGINAS CON NOINDEX:\n";
        $this->find_noindex_pages();
        
        // Analizar sitemap
        echo "\n🗺️  SITEMAP ANALYSIS:\n";
        $sitemap_analysis = $this->analyze_sitemap();
        
        // Comparación de plugins
        $this->compare_seo_plugins();
        
        // Recomendaciones finales
        $this->generate_final_recommendations($yoast_status['is_premium']);
        
        return array(
            'yoast_status' => $yoast_status,
            'endpoints' => $endpoints,
            'sitemap' => $sitemap_analysis
        );
    }
    
    /**
     * Generar recomendaciones finales
     */
    private function generate_final_recommendations($is_premium) {
        echo "\n🎯 RECOMENDACIONES FINALES:\n";
        echo "==========================\n";
        
        if ($is_premium) {
            echo "\n✅ YOAST PREMIUM DETECTADO:\n";
            echo "   1. Utiliza la herramienta de Redirecciones de Yoast (SEO > Herramientas > Redirecciones)\n";
            echo "   2. Importa las 48 URLs 404 desde Search Console\n";
            echo "   3. Crea redirecciones 301 a contenido relevante\n";
            echo "   4. Revisa páginas con noindex en la configuración de cada página\n";
        } else {
            echo "\n🔄 OPCIONES PARA YOAST FREE:\n";
            echo "\n   OPCIÓN 1: Actualizar a Yoast Premium (RECOMENDADO)\n";
            echo "     - Ventajas: Todo integrado, más estable, soporte oficial\n";
            echo "     - Desventajas: Costo anual\n";
            echo "\n   OPCIÓN 2: Cambiar a Rank Math (ALTERNATIVA GRATUITA)\n";
            echo "     - Ventajas: Gratuito con redirecciones, más opciones de schema\n";
            echo "     - Desventajas: Cambiar todo el setup SEO\n";
            echo "     - Mejor opción si no deseas pagar\n";
            echo "\n   OPCIÓN 3: Mantener Yoast Free + Plugin Redirection\n";
            echo "     - Ventajas: Mínimo cambio, gratuito\n";
            echo "     - Desventajas: 2 plugins en lugar de 1\n";
        }
        
        echo "\n📋 PASOS ESPECÍFICOS PARA RESOLVER 48 ERRORES 404:\n";
        echo "   1. Obtén la lista específica de URLs 404 desde Google Search Console\n";
        echo "   2. Determina la mejor página de destino para cada URL (contenido similar)\n";
        echo "   3. Implementa las redirecciones usando la opción que elijas arriba\n";
        echo "   4. Verifica en Search Console después de 48 horas\n";
        
        echo "\n📊 MONITOREO POST-IMPLEMENTACIÓN:\n";
        echo "   - Revisa Search Console semanalmente las primeras 2 semanas\n";
        echo "   - Verifica que no aparezcan nuevos errores 404\n";
        echo "   - Monitorea la tendencia de páginas indexadas\n";
    }
    
    /**
     * Ejecutar análisis completo
     */
    public function run_complete_analysis() {
        echo "🚀 INICIANDO ANÁLISIS COMPLETO YOAST/SITEMAP/NOINDEX\n";
        echo "==================================================\n";
        
        $results = $this->generate_full_report();
        
        echo "\n✅ ANÁLISIS COMPLETO FINALIZADO\n";
        echo "   - Estado de Yoast verificado\n";
        echo "   - Páginas con noindex identificadas\n";
        echo "   - Sitemap analizado\n";
        echo "   - Comparación de plugins realizada\n";
        echo "   - Recomendaciones específicas generadas\n";
        
        return $results;
    }
}

// Ejecutar el análisis completo
$full_check = new Yoast_Premium_Full_Check();
$full_check->run_complete_analysis();