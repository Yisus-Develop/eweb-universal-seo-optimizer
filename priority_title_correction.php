<?php
/**
 * Script de Corrección Prioritaria de Títulos Duplicados para Mars Challenge
 * Basado en análisis previo de títulos duplicados que pudieron ser pasados por alto
 */

class Priority_Title_Correction {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando corrección prioritaria de títulos duplicados para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Priority-Title-Correction/1.0',
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
     * Actualizar título en Rank Math
     */
    private function update_rankmath_title($post_id, $new_title, $post_type) {
        $update_data = array(
            'meta' => array(
                'rank_math_title' => $new_title
            )
        );

        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }

    /**
     * Actualizar título normal (fallback)
     */
    private function update_normal_title($post_id, $new_title, $post_type) {
        $update_data = array(
            'title' => array('raw' => $new_title, 'rendered' => $new_title)
        );

        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }

    /**
     * Obtener el tipo correcto de un post/id
     */
    private function get_post_type($post_id) {
        // Intentar con páginas primero
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
        if ($response['status_code'] === 200) {
            return 'pages';
        }
        
        // Si no es página, intentar con posts
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts/$post_id?context=edit");
        if ($response['status_code'] === 200) {
            return 'posts';
        }
        
        // Si no se encuentra, devolver 'unknown'
        return 'unknown';
    }

    /**
     * Corregir títulos duplicados identificados
     */
    public function correct_priority_titles() {
        echo "\n🔧 Corrigiendo títulos duplicados prioritarios...\n";

        // Esta función tomaría la información de los análisis previos
        // y corregiría específicamente los títulos duplicados que pudieron ser pasados por alto
        
        // Primero, necesitamos obtener todos los contenidos
        $all_content = $this->get_all_content();
        
        // Encontrar títulos duplicados
        $duplicates = $this->find_duplicate_titles($all_content);
        
        if (empty($duplicates)) {
            echo "✅ No se encontraron títulos duplicados que requieran corrección\n";
            return 0;
        }

        echo "\n📝 Títulos duplicados encontrados para corregir:\n";
        $titles_fixed = 0;
        
        foreach ($duplicates as $title => $items) {
            if (count($items) > 1) {
                echo "\nTítulo duplicado: '$title' (".count($items)." veces)\n";
                
                // Dejar el primero sin cambiar y actualizar los demás
                for ($i = 1; $i < count($items); $i++) {  // Empezamos desde 1, dejando el primero
                    $item = $items[$i];
                    $original_title = $title;
                    $new_title = $this->generate_unique_title($original_title, $i, $item);
                    
                    $post_type = $this->get_post_type($item['id']);
                    if ($post_type !== 'unknown') {
                        // Intentar actualizar primero en Rank Math
                        $success = $this->update_rankmath_title($item['id'], $new_title, $post_type);
                        
                        // Si falla, intentar con el título normal
                        if (!$success) {
                            $success = $this->update_normal_title($item['id'], $new_title, $post_type);
                        }
                        
                        if ($success) {
                            echo "  ✓ ID {$item['id']}: '$new_title'\n";
                            $titles_fixed++;
                        } else {
                            echo "  ✗ ID {$item['id']}: Error al actualizar\n";
                        }
                    } else {
                        echo "  ? ID {$item['id']}: No se pudo determinar el tipo\n";
                    }
                    
                    sleep(1); // Evitar demasiadas solicitudes rápidas
                }
            }
        }

        echo "\n✅ Corregidos $titles_fixed títulos duplicados\n";
        return $titles_fixed;
    }

    /**
     * Obtener todo el contenido
     */
    private function get_all_content() {
        echo "🔄 Obteniendo contenido para análisis de duplicados...\n";

        $content = array('pages' => array(), 'posts' => array());

        // Obtener páginas
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $content['pages'] = array_merge($content['pages'], $new_pages);
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
                $content['posts'] = array_merge($content['posts'], $new_posts);
                $post_num++;
                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        return array_merge($content['pages'], $content['posts']);
    }

    /**
     * Buscar títulos duplicados
     */
    private function find_duplicate_titles($all_content) {
        $title_map = array();

        foreach ($all_content as $item) {
            $title = isset($item['title']['rendered']) ? $item['title']['rendered'] : 'Título desconocido';
            $id = $item['id'];
            $type = $item['type'] ?? 'unknown';
            $url = $item['link'] ?? '';

            if (!isset($title_map[$title])) {
                $title_map[$title] = array();
            }

            $title_map[$title][] = array(
                'id' => $id,
                'type' => $type,
                'url' => $url,
                'original_title' => $title
            );
        }

        // Filtrar solo títulos que aparecen más de una vez
        $duplicates = array();
        foreach ($title_map as $title => $items) {
            if (count($items) > 1) {
                $duplicates[$title] = $items;
            }
        }

        return $duplicates;
    }

    /**
     * Generar título único
     */
    private function generate_unique_title($original_title, $index, $item) {
        // Intentar crear un título único que sea descriptivo
        $extension = " - Parte " . ($index + 1);
        
        // Si es una noticia o artículo, intentar usar la fecha
        if (isset($item['date'])) {
            $date = new DateTime($item['date']);
            $extension = " ({$date->format('d/m/Y')})";
        }
        
        // Si el título original ya contiene un patrón de número, usar un enfoque diferente
        if (preg_match('/\b(Parte|Part|Section|Sección)\s+\d+\b/i', $original_title)) {
            $extension = " - Revisión " . ($index + 1);
        }
        
        // Asegurar que no exceda el límite razonable para títulos
        $new_title = $original_title . $extension;
        
        if (strlen($new_title) > 60) {
            // Si es demasiado largo, usar un enfoque más corto
            $truncated_original = substr($original_title, 0, 50 - strlen($extension)) . '...';
            $new_title = $truncated_original . $extension;
        }
        
        return $new_title;
    }

    /**
     * Ejecutar corrección prioritaria
     */
    public function run_priority_correction() {
        echo "🚀 INICIANDO CORRECCIÓN PRIORITARIA DE TÍTULOS DUPLICADOS\n";
        echo "=======================================================\n";

        $titles_fixed = $this->correct_priority_titles();

        echo "\n🎯 RESUMEN DE CORRECCIÓN PRIORITARIA:\n";
        echo "   • Títulos duplicados corregidos: $titles_fixed\n";

        // Siguiente paso
        echo "\n⏭️  PRÓXIMOS PASOS:\n";
        echo "   - Verificar en Rank Math que los cambios se hayan aplicado correctamente\n";
        echo "   - Revisar manualmente cualquier título que no se haya actualizado\n";
        echo "   - Verificar el impacto en SEO de los títulos actualizados\n";

        return array('titles_fixed' => $titles_fixed);
    }
}

// Ejecutar la corrección prioritaria
$correction = new Priority_Title_Correction();
$result = $correction->run_priority_correction();

echo "\n✅ CORRECCIÓN PRIORITARIA DE TÍTULOS FINALIZADA\n";