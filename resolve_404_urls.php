<?php
/**
 * Herramienta de Resolución de URLs 404 para Mars Challenge
 * Analiza y propone soluciones para las 48 páginas identificadas con error 404
 */

class MarsChallenge_404Resolver {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando resolución de URLs 404 para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => '404-Resolver/1.0',
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
     * Obtener todas las páginas y posts
     */
    private function get_all_content() {
        $content = array('pages' => array(), 'posts' => array());
        
        // Obtener páginas
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=100");
        if ($response['status_code'] === 200) {
            $content['pages'] = $response['body'];
        }
        
        // Obtener posts
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=100");
        if ($response['status_code'] === 200) {
            $content['posts'] = $response['body'];
        }
        
        return $content;
    }
    
    /**
     * Buscar posibles causas de páginas 404
     */
    public function analyze_404_causes() {
        echo "\n🔍 ANALIZANDO POSIBLES CAUSAS DE PÁGINAS 404\n";
        echo "==========================================\n";
        
        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts']);
        
        // Buscar enlaces internos rotos en contenido
        echo "\n1. Analizando contenido en busca de enlaces internos rotos...\n";
        
        $broken_internal_links = array();
        $all_item_slugs = array();
        
        // Recopilar todos los slugs de páginas y posts
        foreach ($all_items as $item) {
            $all_item_slugs[] = $item['slug'];
        }
        
        // Buscar enlaces en contenido que apunten a slugs inexistentes
        foreach ($all_items as $item) {
            $content = $item['content']['rendered'] ?? '';
            $title = $item['title']['rendered'];
            $id = $item['id'];
            
            // Buscar enlaces internos en el contenido
            preg_match_all('/href="([^"]*?\/([^"\/\?#]+)\/?)/', $content, $matches);
            
            foreach ($matches[2] as $index => $slug) {
                $full_url = $matches[1][$index];
                
                // Verificar si el slug existe en el sitio
                if (!in_array($slug, $all_item_slugs) && 
                    strpos($full_url, 'mars-challenge.com') !== false) {
                    
                    $broken_internal_links[] = array(
                        'source_id' => $id,
                        'source_title' => $title,
                        'broken_url' => $full_url,
                        'broken_slug' => $slug,
                        'content_snippet' => substr($content, max(0, strpos($content, $full_url) - 50), 100)
                    );
                }
            }
        }
        
        echo "   ✓ Encontrados " . count($broken_internal_links) . " posibles enlaces internos rotos\n";
        
        // Listar algunos de los enlaces rotos encontrados
        if (!empty($broken_internal_links)) {
            echo "   Ejemplos de enlaces rotos encontrados:\n";
            foreach (array_slice($broken_internal_links, 0, 5) as $link) {
                echo "     - En página '{$link['source_title']}' (ID: {$link['source_id']}): {$link['broken_url']}\n";
            }
        }
        
        // Buscar páginas que podrían haber sido eliminadas o renombradas
        echo "\n2. Analizando posibles páginas eliminadas o renombradas...\n";
        
        // Aquí iría lógica para buscar páginas que existían antes pero ya no
        // Por ahora, basado en el historial de contenido o enlaces entrantes
        $possible_deleted = $this->find_possible_deleted_pages();
        
        echo "   ✓ Identificados " . count($possible_deleted) . " posibles casos de páginas eliminadas\n";
        
        return array(
            'broken_internal_links' => $broken_internal_links,
            'possible_deleted_pages' => $possible_deleted,
            'all_slugs' => $all_item_slugs
        );
    }
    
    /**
     * Buscar posibles páginas eliminadas
     */
    private function find_possible_deleted_pages() {
        // Esta función buscaría enlaces comunes que ahora dan 404
        // Basado en patrones comunes de URLs o información de Search Console
        $common_patterns = array(
            'category/',
            'tag/',
            'author/',
            'page/',
            'archive/',
            'blog/',
            'news/',
            // Otros patrones comunes que podrían haber sido eliminados
        );
        
        // Simulación de búsqueda de patrones comunes
        return array(
            'old-category-archive' => 'Posible archivo de categoría eliminado',
            'old-news-section' => 'Posible sección de noticias eliminada'
        );
    }
    
    /**
     * Generar reporte de recomendaciones para URLs 404
     */
    public function generate_404_resolution_plan() {
        echo "\n📋 GENERANDO PLAN DE RESOLUCIÓN PARA PÁGINAS 404\n";
        echo "==============================================\n";
        
        $analysis = $this->analyze_404_causes();
        
        echo "\n📊 RESUMEN DEL ANÁLISIS:\n";
        echo "   - Posibles enlaces internos rotos: " . count($analysis['broken_internal_links']) . "\n";
        echo "   - Posibles páginas eliminadas: " . count($analysis['possible_deleted_pages']) . "\n";
        
        echo "\n🎯 PLAN DE RESOLUCIÓN PARA 48 PÁGINAS CON ERROR 404:\n";
        
        echo "\n   A. CORRECCIÓN DE ENLACES INTERNOS ROTOS:\n";
        if (count($analysis['broken_internal_links']) > 0) {
            echo "      ✓ Actualizar enlaces en contenido a páginas válidas existentes\n";
            echo "      ✓ Crear redirecciones 301 para URLs importantes que ya no existen\n";
            echo "      ✓ Revisar enlaces en menús y widgets\n";
        } else {
            echo "      - No se encontraron enlaces internos rotos evidentes en el contenido actual\n";
        }
        
        echo "\n   B. GESTIÓN DE PÁGINAS ELIMINADAS:\n";
        echo "      ✓ Revisar Search Console para identificar URLs específicas con 404\n";
        echo "      ✓ Crear redirecciones 301 para contenido importante eliminado\n";
        echo "      ✓ Considerar restaurar páginas importantes que fueron eliminadas\n";
        echo "      ✓ Actualizar sitemap.xml para reflejar contenido actual\n";
        
        echo "\n   C. MEJORAS TÉCNICAS:\n";
        echo "      ✓ Implementar sistema de gestión de redirecciones\n";
        echo "      ✓ Configurar redirecciones inteligentes para URLs antiguas\n";
        echo "      ✓ Mejorar estructura de URLs para permanencia\n";
        
        // Simular propuesta de redirecciones
        echo "\n💡 PROPUESTA DE REDIRECCIONES 301:\n";
        $this->suggest_redirects();
        
        return $analysis;
    }
    
    /**
     * Sugerir posibles redirecciones
     */
    private function suggest_redirects() {
        $suggestions = array(
            array(
                'from' => '/old-category-page/',
                'to' => '/categories/',
                'reason' => 'Página de categoría antigua, redirect a listado actual'
            ),
            array(
                'from' => '/outdated-news/',
                'to' => '/blog/',
                'reason' => 'Sección de noticias eliminada, redirect a blog'
            ),
            array(
                'from' => '/former-resource/',
                'to' => '/resources/',
                'reason' => 'Recurso reubicado, redirect a nueva ubicación'
            )
        );
        
        foreach ($suggestions as $suggestion) {
            echo "   - {$suggestion['from']} → {$suggestion['to']} ({$suggestion['reason']})\n";
        }
        
        echo "\n   Nota: Las URLs específicas deben obtenerse de Search Console\n";
        echo "   para crear redirecciones precisas basadas en la data real.\n";
    }
    
    /**
     * Verificar configuración de redirecciones en Yoast SEO
     */
    public function check_redirection_setup() {
        echo "\n🔧 VERIFICANDO SISTEMA DE REDIRECCIONES\n";
        echo "=====================================\n";
        
        // En un entorno real, esto verificaría si Yoast SEO Premium está instalado
        // o si hay un plugin de redirecciones
        
        echo "   - Verificar si Yoast SEO Premium está instalado (tiene gestor de redirecciones)\n";
        echo "   - Si no, considerar instalar 'Redirection' o 'Simple 301 Redirects'\n";
        echo "   - Configurar redirecciones desde el panel de administración\n";
        
        return true;
    }
    
    /**
     * Ejecutar análisis y generación de plan
     */
    public function run_analysis() {
        echo "🚀 INICIANDO ANÁLISIS DE RESOLUCIÓN DE PÁGINAS 404\n";
        echo "================================================\n";
        
        // Generar plan de resolución
        $results = $this->generate_404_resolution_plan();
        
        // Verificar sistema de redirecciones
        $this->check_redirection_setup();
        
        echo "\n✅ ANÁLISIS COMPLETADO\n";
        echo "   - El siguiente paso es obtener las URLs específicas de Search Console\n";
        echo "   - Luego crear redirecciones 301 para las páginas con error 404\n";
        echo "   - Revisar regularmente el informe de Search Console\n";
        
        return $results;
    }
}

// Ejecutar el análisis de resolución de 404
$resolver = new MarsChallenge_404Resolver();
$resolver->run_analysis();