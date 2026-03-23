<?php
/**
 * Script de Corrección Automatizada de Problemas SEO para Mars Challenge
 * Basado en el informe de Semrush
 * Version: cURL para ejecución fuera de WordPress
 */

class MarsChallenge_SEO_Fixer {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    private $site_data = array();
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->app_password);
        
        echo "Inicializando sistema de corrección SEO para: " . $this->site_url . "\n";
        echo "Usuario: " . $this->username . "\n\n";
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
            CURLOPT_USERAGENT => 'MarsChallenge-SEO-Fixer/1.0'
        ));
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return array('error' => $error);
        }
        
        return array(
            'status_code' => $http_code,
            'body' => $response ? json_decode($response, true) : null
        );
    }
    
    /**
     * Conectar y verificar acceso a la API
     */
    public function test_connection() {
        echo "Verificando conexión con la API...\n";
        
        $response = $this->make_request($this->site_url . '/wp-json/wp/v2/users/me');
        
        if (isset($response['error'])) {
            echo "ERROR: " . $response['error'] . "\n";
            return false;
        }
        
        if ($response['status_code'] === 200) {
            echo "✓ Conexión exitosa con la API\n";
            return true;
        } else {
            echo "ERROR: Código de respuesta: " . $response['status_code'] . "\n";
            if ($response['status_code'] === 401) {
                echo "Posible problema de autenticación\n";
            }
            return false;
        }
    }
    
    /**
     * Obtener todas las páginas y posts
     */
    public function get_all_content() {
        echo "Obteniendo todas las páginas y posts...\n";
        
        $all_content = array();
        
        // Obtener páginas
        $pages_response = $this->make_request($this->site_url . '/wp-json/wp/v2/pages?per_page=100');
        
        if (!isset($pages_response['error']) && $pages_response['status_code'] === 200) {
            $all_content['pages'] = $pages_response['body'];
            echo "✓ Obtenidas " . count($pages_response['body']) . " páginas\n";
        } else {
            echo "⚠ No se pudieron obtener las páginas\n";
            $all_content['pages'] = array();
        }
        
        // Obtener posts
        $posts_response = $this->make_request($this->site_url . '/wp-json/wp/v2/posts?per_page=100');
        
        if (!isset($posts_response['error']) && $posts_response['status_code'] === 200) {
            $all_content['posts'] = $posts_response['body'];
            echo "✓ Obtenidos " . count($posts_response['body']) . " posts\n";
        } else {
            echo "⚠ No se pudieron obtener los posts\n";
            $all_content['posts'] = array();
        }
        
        return $all_content;
    }
    
    /**
     * Detectar y corregir títulos duplicados
     */
    public function fix_duplicate_titles() {
        echo "\nDetectando títulos duplicados...\n";
        
        $all_content = $this->get_all_content();
        $all_items = array_merge($all_content['pages'], $all_content['posts']);
        
        // Contar títulos duplicados (tanto títulos normales como de Yoast)
        $title_count = array();
        $yoast_title_count = array();
        
        foreach ($all_items as $item) {
            // Título normal
            $title = $item['title']['rendered'] ?? '';
            if (!empty($title)) {
                if (isset($title_count[$title])) {
                    $title_count[$title]['count']++;
                    $title_count[$title]['items'][] = array(
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'original_title' => $title
                    );
                } else {
                    $title_count[$title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $item['id'],
                            'type' => $item['type'],
                            'original_title' => $title
                        ))
                    );
                }
            }
            
            // Título de Yoast si existe
            $yoast_title = $item['meta']['_yoast_wpseo_title'] ?? '';
            if (!empty($yoast_title) && $yoast_title !== $title) {
                if (isset($yoast_title_count[$yoast_title])) {
                    $yoast_title_count[$yoast_title]['count']++;
                    $yoast_title_count[$yoast_title]['items'][] = array(
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'original_title' => $yoast_title
                    );
                } else {
                    $yoast_title_count[$yoast_title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $item['id'],
                            'type' => $item['type'],
                            'original_title' => $yoast_title
                        ))
                    );
                }
            }
        }
        
        $duplicates_fixed = 0;
        
        // Corregir títulos duplicados normales
        foreach ($title_count as $title => $info) {
            if ($info['count'] > 1) {
                echo "Título duplicado encontrado: '$title' ({$info['count']} veces)\n";
                
                // Dejar el primero y actualizar los demás
                foreach ($info['items'] as $index => $item_info) {
                    if ($index > 0) { // No actualizar el primero
                        $new_title = $title . ' - Part ' . ($index + 1);
                        
                        $update_result = $this->update_post_title($item_info['id'], $item_info['type'], $new_title);
                        if ($update_result) {
                            echo "  - Actualizado ID {$item_info['id']}: '$new_title'\n";
                            $duplicates_fixed++;
                        } else {
                            echo "  - ERROR al actualizar ID {$item_info['id']}\n";
                        }
                    }
                }
            }
        }
        
        // Corregir títulos duplicados de Yoast
        foreach ($yoast_title_count as $title => $info) {
            if ($info['count'] > 1) {
                echo "Título Yoast duplicado encontrado: '$title' ({$info['count']} veces)\n";
                
                foreach ($info['items'] as $index => $item_info) {
                    if ($index > 0) { // No actualizar el primero
                        $new_title = $title . ' - Part ' . ($index + 1);
                        
                        $update_result = $this->update_yoast_title($item_info['id'], $new_title);
                        if ($update_result) {
                            echo "  - Actualizado Yoast ID {$item_info['id']}: '$new_title'\n";
                            $duplicates_fixed++;
                        } else {
                            echo "  - ERROR al actualizar Yoast ID {$item_info['id']}\n";
                        }
                    }
                }
            }
        }
        
        echo "✓ Corregidos $duplicates_fixed títulos duplicados\n";
        return $duplicates_fixed;
    }
    
    /**
     * Actualizar título normal de un post/página
     */
    private function update_post_title($post_id, $post_type, $new_title) {
        $update_data = array(
            'title' => $new_title
        );
        
        $url = $this->site_url . "/wp-json/wp/v2/$post_type/$post_id";
        $response = $this->make_request($url, 'POST', $update_data);
        
        return isset($response['status_code']) && $response['status_code'] === 200;
    }
    
    /**
     * Actualizar título de Yoast
     */
    private function update_yoast_title($post_id, $new_title) {
        $update_data = array(
            'meta' => array(
                '_yoast_wpseo_title' => $new_title
            )
        );
        
        $post_type = $this->get_post_type($post_id);
        $url = $this->site_url . "/wp-json/wp/v2/$post_type/$post_id";
        $response = $this->make_request($url, 'POST', $update_data);
        
        return isset($response['status_code']) && $response['status_code'] === 200;
    }
    
    /**
     * Obtener tipo de post (pages o posts) basado en ID
     */
    private function get_post_type($post_id) {
        $page_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        if (isset($page_response['status_code']) && $page_response['status_code'] === 200) {
            return 'pages';
        }
        
        return 'posts';
    }
    
    /**
     * Añadir meta descripciones faltantes
     */
    public function add_missing_descriptions() {
        echo "\nAñadiendo meta descripciones faltantes...\n";
        
        $all_content = $this->get_all_content();
        $all_items = array_merge($all_content['pages'], $all_content['posts']);
        
        $descriptions_added = 0;
        
        foreach ($all_items as $item) {
            // Verificar si tiene meta descripción de Yoast
            $yoast_desc = $item['meta']['_yoast_wpseo_metadesc'] ?? '';
            
            if (empty($yoast_desc)) {
                // Crear descripción a partir del contenido si está disponible
                $content = isset($item['content']['rendered']) ? wp_strip_all_tags($item['content']['rendered']) : '';
                $excerpt = isset($item['excerpt']['rendered']) ? $item['excerpt']['rendered'] : '';
                
                if (!empty($excerpt)) {
                    $description = $excerpt;
                } else {
                    // Extraer primeros 150 caracteres del contenido
                    $description = strlen($content) > 150 ? substr($content, 0, 147) . '...' : $content;
                }
                
                // Limitar longitud
                if (strlen($description) > 160) {
                    $description = substr($description, 0, 157) . '...';
                }
                
                // Asegurar que no esté vacía
                if (empty(trim($description))) {
                    $description = 'Explore Mars with Mars Challenge - Your ultimate resource for Mars exploration, missions, and space education.';
                }
                
                // Actualizar la meta descripción de Yoast
                $update_result = $this->update_yoast_description($item['id'], $description);
                if ($update_result) {
                    echo "  - Añadida descripción a ID {$item['id']}: '" . substr($description, 0, 50) . "...'\n";
                    $descriptions_added++;
                } else {
                    echo "  - ERROR al añadir descripción a ID {$item['id']}\n";
                }
            }
        }
        
        echo "✓ Añadidas $descriptions_added meta descripciones\n";
        return $descriptions_added;
    }
    
    /**
     * Actualizar meta descripción de Yoast
     */
    private function update_yoast_description($post_id, $new_description) {
        $update_data = array(
            'meta' => array(
                '_yoast_wpseo_metadesc' => $new_description
            )
        );
        
        $post_type = $this->get_post_type($post_id);
        $url = $this->site_url . "/wp-json/wp/v2/$post_type/$post_id";
        $response = $this->make_request($url, 'POST', $update_data);
        
        return isset($response['status_code']) && $response['status_code'] === 200;
    }
    
    /**
     * Detectar posibles enlaces internos rotos (método básico)
     */
    public function detect_broken_internal_links() {
        echo "\nDetectando posibles enlaces internos rotos...\n";
        
        $all_content = $this->get_all_content();
        $all_items = array_merge($all_content['pages'], $all_content['posts']);
        
        $internal_links = array();
        
        foreach ($all_items as $item) {
            $content = $item['content']['rendered'] ?? '';
            
            // Extraer enlaces internos
            preg_match_all('/href="(https?:\/\/[^"]*?\.space[^"]*?)"/', $content, $matches);
            
            if (!empty($matches[1])) {
                foreach ($matches[1] as $link) {
                    if (strpos($link, 'mars-challenge.com') !== false) {
                        $internal_links[] = array(
                            'source_id' => $item['id'],
                            'source_title' => $item['title']['rendered'],
                            'link_url' => $link,
                            'item_type' => $item['type']
                        );
                    }
                }
            }
        }
        
        // Filtrar enlaces únicos
        $unique_links = array();
        foreach ($internal_links as $link) {
            $key = $link['link_url'];
            if (!isset($unique_links[$key])) {
                $unique_links[$key] = $link;
            }
        }
        
        echo "✓ Detectados " . count($unique_links) . " enlaces internos únicos para verificar\n";
        
        // Aquí iría la lógica para verificar si los enlaces están rotos
        // Por ahora solo mostramos los enlaces encontrados
        $broken_links = array();
        foreach ($unique_links as $link) {
            // En una implementación completa, aquí haríamos una verificación HTTP
            // Por ahora registramos que encontramos el enlace
            $broken_links[] = $link;
        }
        
        return $broken_links;
    }
    
    /**
     * Ejecutar todas las correcciones
     */
    public function run_all_fixes() {
        echo "=== INICIANDO CORRECCIONES SEO AUTOMATIZADAS ===\n\n";
        
        if (!$this->test_connection()) {
            echo "\nNo se puede continuar sin conexión a la API\n";
            return false;
        }
        
        $stats = array();
        
        // Corregir títulos duplicados
        $stats['titles_fixed'] = $this->fix_duplicate_titles();
        
        // Añadir meta descripciones faltantes
        $stats['descriptions_added'] = $this->add_missing_descriptions();
        
        // Detectar enlaces rotos (no se pueden corregir sin más análisis)
        $broken_links = $this->detect_broken_internal_links();
        $stats['broken_links_detected'] = count($broken_links);
        
        echo "\n=== RESUMEN DE CORRECCIONES ===\n";
        echo "Títulos duplicados corregidos: {$stats['titles_fixed']}\n";
        echo "Meta descripciones añadidas: {$stats['descriptions_added']}\n";
        echo "Enlaces internos detectados: {$stats['broken_links_detected']}\n";
        
        echo "\n✅ Proceso de correcciones automatizadas completado\n";
        echo "⚠️  Nota: La corrección de enlaces rotos requiere análisis manual o más verificación\n";
        
        return $stats;
    }
}

// Ejecutar las correcciones
$seo_fixer = new MarsChallenge_SEO_Fixer();
$seo_fixer->run_all_fixes();

?>