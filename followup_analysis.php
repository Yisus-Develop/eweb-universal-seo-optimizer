<?php
/**
 * Herramienta de Seguimiento - Problemas Críticos de Mars Challenge
 * Basado en los hallazgos de Search Console y Semrush
 */

class MarsChallenge_FollowUp {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando herramienta de seguimiento para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'FollowUp-Tool/1.0',
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
     * Revisar y corregir el problema con la página ID 21
     */
    public function fix_page_id_21_error() {
        echo "\n🔧 Revisando el error con la página ID 21...\n";
        
        // Obtener información sobre la página específica
        $page_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/21");
        
        if ($page_response['status_code'] !== 200) {
            echo "✗ Error al obtener la página ID 21: {$page_response['status_code']}\n";
            echo "  Raw response: " . substr($page_response['raw_response'], 0, 200) . "...\n";
            return false;
        }
        
        $page_data = $page_response['body'];
        echo "✓ Página ID 21 obtenida exitosamente\n";
        echo "  Título actual: {$page_data['title']['rendered']}\n";
        echo "  Estado: {$page_data['status']}\n";
        echo "  Tipo: {$page_data['type']}\n";
        
        // Verificar si es parte del grupo de títulos duplicados 'Empresas'
        echo "\n  Buscando páginas con título similar 'Empresas'...\n";
        
        $all_pages_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=100");
        if ($all_pages_response['status_code'] === 200) {
            $duplicates = array();
            foreach ($all_pages_response['body'] as $page) {
                if ($page['title']['rendered'] === 'Empresas' && $page['id'] != 21) {
                    $duplicates[] = array('id' => $page['id'], 'title' => $page['title']['rendered']);
                }
            }
            
            if (!empty($duplicates)) {
                echo "  ✓ Se encontraron " . count($duplicates) . " páginas adicionales con título 'Empresas':\n";
                foreach ($duplicates as $dup) {
                    echo "    - ID {$dup['id']}: {$dup['title']}\n";
                }
                
                // Intentar corregir el título de la página ID 21
                echo "\n  🔄 Intentando corregir el título de la página ID 21...\n";
                
                $new_title = 'Empresas - Particular';
                $update_data = array('title' => $new_title);
                $update_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/21", 'POST', $update_data);
                
                if ($update_response['status_code'] === 200) {
                    echo "  ✓ Título de página ID 21 actualizado a: '$new_title'\n";
                    return true;
                } else {
                    echo "  ✗ Error al actualizar título de página ID 21: {$update_response['status_code']}\n";
                    echo "    Detalle: " . $update_response['raw_response'] . "\n";
                    return false;
                }
            } else {
                echo "  - No se encontraron otras páginas con título 'Empresas'\n";
            }
        }
        
        return false;
    }
    
    /**
     * Analizar datos de Search Console para identificar URLs 404
     */
    public function analyze_404_urls() {
        echo "\n🔍 Analizando URLs con problemas 404 (de Search Console)...\n";
        
        // Cargar los datos de Search Console
        $critical_problems_file = __DIR__ . '/ai-artifacts/assets/Problemas críticos.csv';
        
        if (!file_exists($critical_problems_file)) {
            echo "✗ No se encontró el archivo de problemas críticos\n";
            return array();
        }
        
        $lines = file($critical_problems_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $headers = str_getcsv($lines[0]);
        
        $error_404_entries = array();
        
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            $data_row = array();
            
            for ($j = 0; $j < count($headers); $j++) {
                $data_row[$headers[$j]] = $row[$j] ?? '';
            }
            
            if ($data_row['Motivo'] === 'No se ha encontrado (404)') {
                $error_404_entries[] = $data_row;
            }
        }
        
        echo "  ✓ Identificados " . count($error_404_entries) . " tipos de problemas 404 en Search Console\n";
        
        // Mostrar detalles
        foreach ($error_404_entries as $entry) {
            echo "    - Páginas: {$entry['Páginas']} | Validación: {$entry['Validación']}\n";
        }
        
        // Las URLs específicas no están en el CSV, pero sabemos hay 48 páginas con error 404
        echo "  - Total de páginas identificadas con error 404: 48 (según Search Console)\n";
        
        return $error_404_entries;
    }
    
    /**
     * Buscar páginas con meta noindex
     */
    public function find_noindex_pages() {
        echo "\n🔍 Buscando páginas con meta noindex...\n";
        
        $content = $this->get_all_content();
        $noindex_pages = array();
        
        foreach ($content['pages'] as $page) {
            // Verificar metadatos de Yoast/SEO que puedan causar noindex
            $yoast_meta = $page['meta'] ?? array();
            $yoast_noindex = $yoast_meta['_yoast_wpseo_meta-robots-noindex'] ?? '0';
            
            if ($yoast_noindex == '1') {
                $noindex_pages[] = array(
                    'id' => $page['id'],
                    'title' => $page['title']['rendered'],
                    'url' => $page['link'],
                    'reason' => 'Yoast meta-robots-noindex'
                );
            }
        }
        
        echo "  ✓ Encontradas " . count($noindex_pages) . " páginas con configuración de noindex\n";
        
        foreach ($noindex_pages as $page) {
            echo "    - ID {$page['id']}: {$page['title']}\n";
        }
        
        if (empty($noindex_pages)) {
            echo "  - No se encontraron páginas configuradas con noindex en metadatos de Yoast\n";
            echo "  - Los 7 problemas identificados por Search Console podrían estar relacionados con:\n";
            echo "    * Etiquetas HTML noindex\n";
            echo "    * Ficheros robots.txt\n";
            echo "    * Configuración general de privacidad de WordPress\n";
        }
        
        return $noindex_pages;
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
     * Verificar estado de indexación de páginas
     */
    public function check_indexing_status() {
        echo "\n🔍 Verificando estado de indexación...\n";
        
        // Del CSV de Search Console:
        // "Rastreada: actualmente sin indexar" - 40 páginas
        // Esto indica que Google ha rastreado estas páginas pero no las ha indexado
        
        $content = $this->get_all_content();
        
        // Calcular estadísticas básicas
        $total_content = count($content['pages']) + count($content['posts']);
        $published_pages = array_filter($content['pages'], function($page) {
            return $page['status'] === 'publish';
        });
        
        $published_posts = array_filter($content['posts'], function($post) {
            return $post['status'] === 'publish';
        });
        
        echo "  - Total de páginas/posts: $total_content\n";
        echo "  - Páginas publicadas: " . count($published_pages) . "\n";
        echo "  - Posts publicados: " . count($published_posts) . "\n";
        
        // Mostrar tendencia preocupante de Search Console
        echo "\n📈 Tendencia preocupante identificada:\n";
        echo "  - Páginas indexadas: -16 desde agosto\n";
        echo "  - Páginas sin indexar: +21 desde agosto\n";
        
        return array(
            'total_content' => $total_content,
            'published_pages' => count($published_pages),
            'published_posts' => count($published_posts)
        );
    }
    
    /**
     * Ejecutar análisis de seguimiento completo
     */
    public function run_follow_up_analysis() {
        echo "🚀 INICIANDO ANÁLISIS DE SEGUIMIENTO\n";
        echo "==================================\n";
        
        $results = array();
        
        // 1. Corregir error específico con página ID 21
        echo "1. Analizando error específico con página ID 21:\n";
        $results['page_21_fixed'] = $this->fix_page_id_21_error();
        
        // 2. Analizar URLs con error 404
        echo "\n2. Analizando URLs con error 404:\n";
        $results['error_404_analysis'] = $this->analyze_404_urls();
        
        // 3. Buscar páginas con noindex
        echo "\n3. Buscando páginas con meta noindex:\n";
        $results['noindex_pages'] = $this->find_noindex_pages();
        
        // 4. Verificar estado de indexación
        echo "\n4. Verificando estado general de indexación:\n";
        $results['indexing_status'] = $this->check_indexing_status();
        
        // Recomendaciones
        echo "\n📋 RECOMENDACIONES DE SEGUIMIENTO:\n";
        echo "  1. Revisar las 48 páginas con error 404 vía inspección de URL en Search Console\n";
        echo "  2. Verificar las 7 páginas identificadas con noindex (pueden ser configuración externa)\n";
        echo "  3. Implementar optimizaciones de Core Web Vitals\n";
        echo "  4. Crear redirecciones 301 para URLs importantes que ya no existen\n";
        echo "  5. Verificar configuración de privacidad del sitio\n";
        
        return $results;
    }
}

// Ejecutar el análisis de seguimiento
$followup = new MarsChallenge_FollowUp();
$followup->run_follow_up_analysis();