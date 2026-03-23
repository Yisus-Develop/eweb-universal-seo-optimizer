<?php
/**
 * Script de Correcciones Prioritarias para Mars Challenge
 * Basado en el análisis integrado de Semrush y Search Console
 */

class Priority_SEO_Fixes {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando correcciones prioritarias para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Priority-SEO-Fixes/1.0',
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
        echo "🔄 Obteniendo páginas y posts...\n";
        
        $content = array('pages' => array(), 'posts' => array());
        
        // Obtener páginas
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $content['pages'] = array_merge($content['pages'], $new_pages);
                echo "   Obtenidas " . count($new_pages) . " páginas (página $page_num)\n";
                $page_num++;
                
                // Evitar demasiadas solicitudes rápidas
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50); // Si hay 50 resultados, puede haber más
        
        // Obtener posts
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                $content['posts'] = array_merge($content['posts'], $new_posts);
                echo "   Obtenidos " . count($new_posts) . " posts (página $post_num)\n";
                $post_num++;
                
                // Evitar demasiadas solicitudes rápidas
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);
        
        echo "✅ Total - Páginas: " . count($content['pages']) . ", Posts: " . count($content['posts']) . "\n";
        return $content;
    }
    
    /**
     * Corregir títulos duplicados
     */
    public function fix_duplicate_titles() {
        echo "\n🔧 Corrigiendo títulos duplicados...\n";
        
        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts']);
        
        // Contar títulos duplicados
        $title_count = array();
        $yoast_title_count = array();
        
        foreach ($all_items as $item) {
            // Contar títulos normales
            $normal_title = $item['title']['rendered'];
            if (isset($title_count[$normal_title])) {
                $title_count[$normal_title]['count']++;
                $title_count[$normal_title]['items'][] = array(
                    'id' => $item['id'],
                    'type' => $item['type'],
                    'title' => $normal_title
                );
            } else {
                $title_count[$normal_title] = array(
                    'count' => 1,
                    'items' => array(array(
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'title' => $normal_title
                    ))
                );
            }
            
            // Contar títulos de Yoast
            $yoast_title = $item['meta']['_yoast_wpseo_title'] ?? '';
            if (!empty($yoast_title) && $yoast_title !== $normal_title) {
                if (isset($yoast_title_count[$yoast_title])) {
                    $yoast_title_count[$yoast_title]['count']++;
                    $yoast_title_count[$yoast_title]['items'][] = array(
                        'id' => $item['id'],
                        'type' => $item['type'],
                        'title' => $yoast_title
                    );
                } else {
                    $yoast_title_count[$yoast_title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $item['id'],
                            'type' => $item['type'],
                            'title' => $yoast_title
                        ))
                    );
                }
            }
        }
        
        $titles_fixed = 0;
        
        // Corregir títulos normales duplicados
        foreach ($title_count as $title => $info) {
            if ($info['count'] > 1) {
                echo "   Duplicado encontrado: '$title' ({$info['count']} veces)\n";
                
                foreach ($info['items'] as $index => $item_info) {
                    if ($index > 0) { // Dejar el primero sin cambiar
                        $new_title = $title . ' - Part ' . ($index + 1);
                        
                        $result = $this->update_post_title($item_info['id'], $item_info['type'], $new_title);
                        if ($result) {
                            echo "     ✓ Actualizado ID {$item_info['id']}: '$new_title'\n";
                            $titles_fixed++;
                        } else {
                            echo "     ✗ Error al actualizar ID {$item_info['id']}\n";
                        }
                        sleep(1); // Evitar demasiadas solicitudes rápidas
                    }
                }
            }
        }
        
        // Corregir títulos de Yoast duplicados
        foreach ($yoast_title_count as $title => $info) {
            if ($info['count'] > 1) {
                echo "   Duplicado de Yoast encontrado: '$title' ({$info['count']} veces)\n";
                
                foreach ($info['items'] as $index => $item_info) {
                    if ($index > 0) {
                        $new_title = $title . ' - Part ' . ($index + 1);
                        
                        $result = $this->update_yoast_title($item_info['id'], $new_title);
                        if ($result) {
                            echo "     ✓ Actualizado Yoast ID {$item_info['id']}: '$new_title'\n";
                            $titles_fixed++;
                        } else {
                            echo "     ✗ Error al actualizar Yoast ID {$item_info['id']}\n";
                        }
                        sleep(1);
                    }
                }
            }
        }
        
        echo "✅ Corregidos $titles_fixed títulos duplicados\n";
        return $titles_fixed;
    }
    
    /**
     * Actualizar título normal
     */
    private function update_post_title($post_id, $post_type, $new_title) {
        $update_data = array('title' => $new_title);
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }
    
    /**
     * Actualizar título de Yoast
     */
    private function update_yoast_title($post_id, $new_title) {
        $update_data = array('meta' => array('_yoast_wpseo_title' => $new_title));
        $post_type = $this->get_post_type($post_id);
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }
    
    /**
     * Obtener tipo de post
     */
    private function get_post_type($post_id) {
        $page_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        return $page_response['status_code'] === 200 ? 'pages' : 'posts';
    }
    
    /**
     * Añadir meta descripciones faltantes
     */
    public function add_missing_descriptions() {
        echo "\n📝 Añadiendo meta descripciones faltantes...\n";
        
        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts']);
        
        $descriptions_added = 0;
        
        foreach ($all_items as $item) {
            // Verificar si tiene meta descripción de Yoast
            $yoast_desc = $item['meta']['_yoast_wpseo_metadesc'] ?? '';
            
            if (empty($yoast_desc)) {
                // Crear descripción a partir del contenido
                $content_text = wp_strip_all_tags($item['content']['rendered'] ?? '');
                $excerpt = $item['excerpt']['rendered'] ?? '';
                
                if (!empty($excerpt)) {
                    $description = $excerpt;
                } else {
                    $description = strlen($content_text) > 150 ? substr($content_text, 0, 147) . '...' : $content_text;
                }
                
                // Limitar a 155-160 caracteres
                if (strlen($description) > 160) {
                    $description = substr($description, 0, 157) . '...';
                }
                
                // Asegurar que no esté vacía
                if (empty(trim($description))) {
                    $description = 'Explore Mars with Mars Challenge - Your ultimate resource for Mars exploration, missions, and space education.';
                }
                
                // Añadir marca del sitio
                if (strpos(strtolower($description), 'mars') === false) {
                    $description .= ' - Mars Challenge';
                }
                
                $result = $this->update_yoast_description($item['id'], $description);
                if ($result) {
                    echo "   ✓ Añadida descripción a ID {$item['id']}: '" . substr($description, 0, 60) . "...'\n";
                    $descriptions_added++;
                } else {
                    echo "   ✗ Error al añadir descripción a ID {$item['id']}\n";
                }
                sleep(1); // Evitar demasiadas solicitudes rápidas
            }
        }
        
        echo "✅ Añadidas $descriptions_added meta descripciones\n";
        return $descriptions_added;
    }
    
    /**
     * Actualizar meta descripción de Yoast
     */
    private function update_yoast_description($post_id, $new_description) {
        $update_data = array('meta' => array('_yoast_wpseo_metadesc' => $new_description));
        $post_type = $this->get_post_type($post_id);
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }
    
    /**
     * Generar reporte de estado actual
     */
    public function generate_status_report() {
        echo "\n📋 Generando reporte de estado...\n";
        
        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts']);
        
        $total_items = count($all_items);
        $missing_descriptions = 0;
        $duplicate_titles = 0;
        
        // Contar descripciones faltantes
        foreach ($all_items as $item) {
            $yoast_desc = $item['meta']['_yoast_wpseo_metadesc'] ?? '';
            if (empty($yoast_desc)) {
                $missing_descriptions++;
            }
        }
        
        // Contar títulos duplicados
        $title_count = array();
        foreach ($all_items as $item) {
            $title = $item['title']['rendered'];
            if (isset($title_count[$title])) {
                if ($title_count[$title] === 1) {
                    $duplicate_titles++;  // Contar la primera duplicada
                }
                $title_count[$title]++;
            } else {
                $title_count[$title] = 1;
            }
        }
        
        echo "📊 Reporte de estado actual:\n";
        echo "   Total de páginas/posts: $total_items\n";
        echo "   Páginas con descripciones faltantes: $missing_descriptions\n";
        echo "   Páginas con títulos duplicados: $duplicate_titles\n";
        
        return array(
            'total_items' => $total_items,
            'missing_descriptions' => $missing_descriptions,
            'duplicate_titles' => $duplicate_titles
        );
    }
    
    /**
     * Ejecutar todas las correcciones prioritarias
     */
    public function run_priority_fixes() {
        echo "🚀 INICIANDO CORRECCIONES PRIORITARIAS\n";
        echo "=====================================\n";
        
        // Generar reporte inicial
        echo "1. Estado inicial del sitio:\n";
        $initial_report = $this->generate_status_report();
        
        // Corregir títulos duplicados
        echo "\n2. Corrigiendo títulos duplicados:\n";
        $titles_fixed = $this->fix_duplicate_titles();
        
        // Añadir descripciones faltantes
        echo "\n3. Añadiendo meta descripciones faltantes:\n";
        $descriptions_added = $this->add_missing_descriptions();
        
        // Generar reporte final
        echo "\n4. Estado final del sitio:\n";
        $final_report = $this->generate_status_report();
        
        // Resumen
        echo "\n🎯 RESUMEN DE CORRECCIONES:\n";
        echo "   ✓ Títulos duplicados corregidos: $titles_fixed\n";
        echo "   ✓ Meta descripciones añadidas: $descriptions_added\n";
        echo "   ✓ Páginas con descripciones faltantes reducidas de {$initial_report['missing_descriptions']} a {$final_report['missing_descriptions']}\n";
        echo "   ✓ Páginas con títulos duplicados reducidos de {$initial_report['duplicate_titles']} a {$final_report['duplicate_titles']}\n";
        
        // Siguiente paso
        echo "\n⏭️  PRÓXIMOS PASOS:\n";
        echo "   - Revisar las 48 páginas con error 404 identificadas en Search Console\n";
        echo "   - Verificar las 7 páginas excluidas con noindex\n";
        echo "   - Implementar optimizaciones de Core Web Vitals\n";
        
        // Guardar log de correcciones
        $log_entry = array(
            'date' => date('Y-m-d H:i:s'),
            'titles_fixed' => $titles_fixed,
            'descriptions_added' => $descriptions_added,
            'initial_report' => $initial_report,
            'final_report' => $final_report
        );
        
        $log_file = __DIR__ . '/correction_log_' . date('Y-m-d') . '.json';
        file_put_contents($log_file, json_encode($log_entry, JSON_PRETTY_PRINT));
        echo "\n📋 Registro de correcciones guardado en: $log_file\n";
        
        return $log_entry;
    }
}

// Ejecutar las correcciones prioritarias
$fixer = new Priority_SEO_Fixes();
$fixer->run_priority_fixes();