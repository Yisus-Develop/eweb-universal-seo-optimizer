<?php
/**
 * Script de Corrección Automatizada de Problemas SEO para Mars Challenge
 * Basado en el informe de Semrush
 */

class MarsChallenge_SEO_Fixer {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $headers;
    
    public function __construct() {
        $this->headers = array(
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->app_password),
            'Content-Type' => 'application/json'
        );
        
        echo "Inicializando sistema de corrección SEO para: " . $this->site_url . "\n";
        echo "Usuario: " . $this->username . "\n\n";
    }
    
    /**
     * Conectar y verificar acceso a la API
     */
    public function test_connection() {
        echo "Verificando conexión con la API...\n";
        
        $response = wp_remote_get($this->site_url . '/wp-json/wp/v2/users/me', array(
            'headers' => $this->headers
        ));
        
        if (is_wp_error($response)) {
            echo "ERROR: No se pudo conectar con la API\n";
            echo "Mensaje: " . $response->get_error_message() . "\n";
            return false;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200) {
            echo "✓ Conexión exitosa con la API\n\n";
            return true;
        } else {
            echo "ERROR: Código de respuesta: " . $status_code . "\n";
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
        $pages_response = wp_remote_get($this->site_url . '/wp-json/wp/v2/pages?per_page=100', array(
            'headers' => $this->headers
        ));
        
        if (!is_wp_error($pages_response)) {
            $pages = json_decode(wp_remote_retrieve_body($pages_response), true);
            $all_content['pages'] = $pages;
            echo "✓ Obtenidas " . count($pages) . " páginas\n";
        }
        
        // Obtener posts
        $posts_response = wp_remote_get($this->site_url . '/wp-json/wp/v2/posts?per_page=100', array(
            'headers' => $this->headers
        ));
        
        if (!is_wp_error($posts_response)) {
            $posts = json_decode(wp_remote_retrieve_body($posts_response), true);
            $all_content['posts'] = $posts;
            echo "✓ Obtenidos " . count($posts) . " posts\n";
        }
        
        return $all_content;
    }
    
    /**
     * Detectar y corregir títulos duplicados
     */
    public function fix_duplicate_titles() {
        echo "\nDetectando títulos duplicados...\n";
        
        $all_content = $this->get_all_content();
        $all_items = array_merge($all_content['pages'] ?? array(), $all_content['posts'] ?? array());
        
        // Contar títulos duplicados (tanto títulos normales como de Yoast)
        $title_count = array();
        $yoast_title_count = array();
        
        foreach ($all_items as $item) {
            // Título normal
            $title = $item['title']['rendered'];
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
                        
                        $update_result = $this->update_post_title($item_info['id'], $new_title);
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
    private function update_post_title($post_id, $new_title) {
        $update_data = array(
            'title' => $new_title
        );
        
        $response = wp_remote_post($this->site_url . "/wp-json/wp/v2/{$this->get_post_type($post_id)}/$post_id", array(
            'headers' => $this->headers,
            'body' => json_encode($update_data),
            'method' => 'POST'
        ));
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
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
        
        $response = wp_remote_post($this->site_url . "/wp-json/wp/v2/{$this->get_post_type($post_id)}/$post_id", array(
            'headers' => $this->headers,
            'body' => json_encode($update_data),
            'method' => 'POST'
        ));
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
    
    /**
     * Obtener tipo de post (pages o posts) basado en ID
     */
    private function get_post_type($post_id) {
        $response = wp_remote_get($this->site_url . "/wp-json/wp/v2/pages/$post_id", array(
            'headers' => $this->headers
        ));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
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
        $all_items = array_merge($all_content['pages'] ?? array(), $all_content['posts'] ?? array());
        
        $descriptions_added = 0;
        
        foreach ($all_items as $item) {
            // Verificar si tiene meta descripción de Yoast
            $yoast_desc = $item['meta']['_yoast_wpseo_metadesc'] ?? '';
            
            if (empty($yoast_desc)) {
                // Crear descripción a partir del contenido
                $content = wp_strip_all_tags($item['content']['rendered']);
                $excerpt = $item['excerpt']['rendered'] ?? '';
                
                if (!empty($excerpt)) {
                    $description = $excerpt;
                } else {
                    $description = wp_trim_words($content, 30, '...');
                }
                
                // Limitar longitud
                if (strlen($description) > 160) {
                    $description = substr($description, 0, 157) . '...';
                }
                
                // Asegurar que no esté vacía
                if (empty($description)) {
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
        
        $response = wp_remote_post($this->site_url . "/wp-json/wp/v2/{$this->get_post_type($post_id)}/$post_id", array(
            'headers' => $this->headers,
            'body' => json_encode($update_data),
            'method' => 'POST'
        ));
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
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
        
        echo "\n=== RESUMEN DE CORRECCIONES ===\n";
        echo "Títulos duplicados corregidos: {$stats['titles_fixed']}\n";
        echo "Meta descripciones añadidas: {$stats['descriptions_added']}\n";
        
        // Aquí irían las otras correcciones (enlaces rotos, errores 4XX, etc.)
        echo "\nSiguiente paso: Corrección de enlaces internos rotos y errores 4XX\n";
        
        return $stats;
    }
}

// Ejecutar las correcciones
$seo_fixer = new MarsChallenge_SEO_Fixer();
$seo_fixer->run_all_fixes();

?>

if (is_admin()) { require_once plugin_dir_path(__FILE__) . "includes/class-eweb-github-updater.php"; new EWEB_GitHub_Updater(__FILE__, "Yisus-Develop", "eweb-universal-seo-optimizer"); }