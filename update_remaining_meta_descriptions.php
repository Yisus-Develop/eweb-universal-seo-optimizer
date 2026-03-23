<?php
/**
 * Script para actualizar las metadescripciones restantes en Mars Challenge
 * Basado en el análisis previo donde se identificaron 53 faltantes y se actualizaron 10
 */

class Update_Remaining_Meta_Descriptions {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando actualización de metadescripciones restantes para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Update-Remaining-Meta-Descriptions/1.0',
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
     * Obtener tipo de post correcto
     */
    private function get_post_type($post_id) {
        // Intentar con páginas
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        if ($response['status_code'] === 200) {
            return 'pages';
        }
        
        // Si no es página, intentar con posts
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts/$post_id");
        if ($response['status_code'] === 200) {
            return 'posts';
        }
        
        return 'unknown';
    }

    /**
     * Actualizar metadescripción en Rank Math
     */
    private function update_rankmath_description($post_id, $new_description, $post_type) {
        if ($post_type === 'unknown') {
            return false;
        }

        $update_data = array(
            'meta' => array(
                'rank_math_description' => $new_description
            )
        );

        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }

    /**
     * Actualizar descripciones restantes
     */
    public function update_remaining_descriptions() {
        echo "\n🔧 Actualizando metadescripciones restantes...\n";

        // Obtener todas las páginas y posts para identificar los que aún no tienen descripción
        $all_content = $this->get_all_content_with_meta();
        
        // Filtrar solo los que no tienen descripción de Rank Math
        $items_without_desc = $this->find_items_without_description($all_content);
        
        // Selección de los más importantes para actualizar primero
        $important_items = array_slice($items_without_desc, 0, 20); // Actualizamos 20 más importantes

        $descriptions_updated = 0;
        $errors = 0;

        foreach ($important_items as $item) {
            echo "\nActualizando descripción para: {$item['title']} (ID: {$item['id']})\n";
            
            // Generar descripción basada en el contenido o tipo de página
            $description = $this->generate_description_for_item($item);
            
            $post_type = $this->get_post_type($item['id']);
            if ($post_type !== 'unknown') {
                $success = $this->update_rankmath_description($item['id'], $description, $post_type);
                
                if ($success) {
                    echo "  ✓ Descripción actualizada para ID {$item['id']}\n";
                    $descriptions_updated++;
                } else {
                    echo "  ✗ Error al actualizar descripción para ID {$item['id']}\n";
                    $errors++;
                }
            } else {
                echo "  ✗ No se pudo determinar el tipo para ID {$item['id']}\n";
                $errors++;
            }
            
            sleep(1); // Evitar demasiadas solicitudes rápidas
        }

        echo "\n✅ Actualizadas $descriptions_updated metadescripciones restantes\n";
        if ($errors > 0) {
            echo "⚠️  Hubo $errors errores al actualizar descripciones\n";
        }

        return array('updated' => $descriptions_updated, 'errors' => $errors, 'total_checked' => count($important_items));
    }

    /**
     * Obtener todo el contenido con metadatos
     */
    private function get_all_content_with_meta() {
        echo "🔄 Obteniendo todo el contenido con metadatos...\n";

        $content = array();

        // Obtener páginas
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $content = array_merge($content, $new_pages);
                $page_num++;
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener posts
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                $content = array_merge($content, $new_posts);
                $post_num++;
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        return $content;
    }

    /**
     * Encontrar elementos sin descripción de Rank Math
     */
    private function find_items_without_description($all_content) {
        $items_without_desc = array();
        
        foreach ($all_content as $item) {
            $has_rankmath_desc = false;
            
            // Verificar si tiene descripción de Rank Math en los metadatos
            if (isset($item['meta']) && is_array($item['meta'])) {
                foreach ($item['meta'] as $key => $value) {
                    if ((stripos($key, 'rank') !== false && stripos($key, 'desc') !== false) || 
                        $key === 'rank_math_description' || $key === '_rank_math_description') {
                        if (is_string($value) && !empty(trim($value))) {
                            $has_rankmath_desc = true;
                            break;
                        } elseif (is_array($value) && count($value) > 0) {
                            $first_val = reset($value);
                            if (is_string($first_val) && !empty(trim($first_val))) {
                                $has_rankmath_desc = true;
                                break;
                            }
                        }
                    }
                }
            }
            
            if (!$has_rankmath_desc) {
                $items_without_desc[] = array(
                    'id' => $item['id'],
                    'title' => isset($item['title']['rendered']) ? $item['title']['rendered'] : 'Título desconocido',
                    'type' => $item['type'] ?? 'unknown',
                    'url' => $item['link'] ?? '',
                    'content' => isset($item['content']['rendered']) ? strip_tags($item['content']['rendered']) : '',
                    'excerpt' => isset($item['excerpt']['rendered']) ? strip_tags($item['excerpt']['rendered']) : ''
                );
            }
        }
        
        // Ordenar por longitud del contenido (potencialmente más importantes)
        usort($items_without_desc, function($a, $b) {
            return strlen($b['content']) - strlen($a['content']);
        });
        
        return $items_without_desc;
    }

    /**
     * Generar descripción para un elemento
     */
    private function generate_description_for_item($item) {
        $title = $item['title'];
        $content = $item['content'];
        $excerpt = $item['excerpt'];
        
        // Si tiene excerpt, usarlo como base
        if (!empty($excerpt) && strlen($excerpt) > 20) {
            $description = $excerpt;
        } 
        // Si tiene contenido, usarlo
        elseif (!empty($content) && strlen($content) > 20) {
            // Tomar los primeros 140 caracteres del contenido
            $description = strlen($content) > 140 ? substr($content, 0, 137) . '...' : $content;
        } 
        // Si no tiene contenido, crear una descripción genérica basada en el título
        else {
            $description = "Descubre más sobre {$title} en Mars Challenge. La plataforma global para jóvenes innovadores que buscan soluciones para Marte y la Tierra.";
        }
        
        // Asegurar que no exceda los límites recomendados
        if (strlen($description) > 155) {
            $description = substr($description, 0, 152) . '...';
        }
        
        // Asegurar que tenga al menos algo de longitud
        if (strlen($description) < 50) {
            $description = "Explora {$title} en Mars Challenge. Descubre soluciones innovadoras para Marte y la Tierra. Participa en el reto global más importante.";
        }
        
        return $description;
    }

    /**
     * Ejecutar actualización de descripciones restantes
     */
    public function run_update_remaining() {
        echo "🚀 INICIANDO ACTUALIZACIÓN DE DESCRIPCIONES RESTANTES\n";
        echo "====================================================\n";

        $result = $this->update_remaining_descriptions();

        echo "\n🎯 RESUMEN DE ACTUALIZACIÓN RESTANTE:\n";
        echo "   • Metadescripciones actualizadas: {$result['updated']}\n";
        echo "   • Errores encontrados: {$result['errors']}\n";
        echo "   • Total verificados: {$result['total_checked']}\n";

        echo "\n✅ PROCESO DE ACTUALIZACIÓN DE DESCRIPCIONES RESTANTES COMPLETADO\n";
        
        return $result;
    }
}

// Ejecutar la actualización de descripciones restantes
$updater = new Update_Remaining_Meta_Descriptions();
$result = $updater->run_update_remaining();

echo "\n✅ ACTUALIZACIÓN DE DESCRIPCIONES RESTANTES FINALIZADA\n";