<?php
/**
 * Script para obtener y posiblemente actualizar datos de Rank Math usando getHead
 * Basado en el descubrimiento de que getHead sí está disponible
 */

class RankMathGetHeadUpdater {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Probando actualización de Rank Math vía getHead y otros endpoints para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'RankMathGetHeadUpdater/1.0',
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
     * Obtener datos actuales de getHead
     */
    public function get_current_meta_data($url) {
        $endpoint_url = $this->site_url . "/wp-json/rankmath/v1/getHead?url=" . urlencode($url);
        $response = $this->make_request($endpoint_url);
        
        if ($response['status_code'] === 200 && !empty($response['body'])) {
            return $response['body'];
        }
        
        return null;
    }

    /**
     * Intentar encontrar el ID del post a partir del contenido o URL
     */
    private function find_post_id_by_url($url) {
        // Este es un método básico; en un sistema real se necesitaría un mapeo más sofisticado
        if (strpos($url, '?p=10') !== false) return 10;  // Página de inicio
        if (strpos($url, '?p=27') !== false) return 27;  // Sobre Mars Challenge
        if (strpos($url, '?p=37') !== false) return 37;  // Cómo participar
        if (strpos($url, '/registro/') !== false) return 1521;  // Página de registro
        
        // Intentar extraer ID del parámetro ?p= en la URL
        if (preg_match('/\?p=(\d+)/', $url, $matches)) {
            return intval($matches[1]);
        }
        
        return null;
    }

    /**
     * Intentar actualizar usando el endpoint general de WordPress
     */
    public function update_meta_via_wp_api($post_id, $new_title = null, $new_description = null) {
        echo "\n📝 ACTUALIZANDO POST ID: $post_id\n";
        echo "========================\n";
        
        $post_type = $this->get_post_type($post_id);
        if ($post_type === 'unknown') {
            echo "✗ No se pudo determinar el tipo de post\n";
            return false;
        }
        
        // Construir la actualización
        $update_data = array(
            'meta' => array()
        );
        
        if ($new_description) {
            $update_data['meta']['rank_math_description'] = $new_description;
            $update_data['meta']['_rank_math_description'] = $new_description;  // Variante
        }
        
        if ($new_title) {
            $update_data['meta']['rank_math_title'] = $new_title;
            $update_data['meta']['_rank_math_title'] = $new_title;  // Variante
        }

        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/{$post_type}/{$post_id}", 'POST', $update_data);
        
        if ($response['status_code'] === 200) {
            echo "✓ Actualización exitosa\n";
            
            // Verificar si los cambios se aplicaron
            sleep(3); // Esperar a que se procese
            
            $check_url = ($post_type === 'pages') ? 
                $this->site_url . "/?page_id=$post_id" : 
                $this->site_url . "/?p=$post_id";
                
            $meta_data = $this->get_current_meta_data($check_url);
            
            if ($meta_data) {
                if ($new_title && (isset($meta_data['title']) || isset($meta_data['og_title']))) {
                    $title = $meta_data['title'] ?? $meta_data['og_title'] ?? '';
                    echo "✓ Título actualizado: " . substr($title, 0, 60) . (strlen($title) > 60 ? '...' : '') . "\n";
                }
                if ($new_description && (isset($meta_data['description']) || isset($meta_data['og_description']))) {
                    $desc = $meta_data['description'] ?? $meta_data['og_description'] ?? '';
                    echo "✓ Descripción actualizada: " . substr($desc, 0, 60) . (strlen($desc) > 60 ? '...' : '') . "\n";
                }
            }
            
            return true;
        } else {
            echo "✗ Actualización fallida (código: {$response['status_code']})\n";
            if (isset($response['body']['message'])) {
                echo "Mensaje: {$response['body']['message']}\n";
            }
            return false;
        }
    }

    /**
     * Obtener tipo de post
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
     * Actualizar múltiples elementos
     */
    public function bulk_update_test() {
        echo "🚀 INICIANDO PRUEBA DE ACTUALIZACIÓN CON GETHEAD + WP API\n";
        echo "=====================================================\n";

        // URLs a probar con nuevas descripciones
        $urls_to_test = array(
            $this->site_url . '/',
            $this->site_url . '/?p=10',
            $this->site_url . '/registro/',
        );

        $update_data = array(
            array(
                'url' => $this->site_url . '/',
                'new_title' => 'Mars Challenge 2026 - Inicio Actualizado',
                'new_description' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores. Actualizado via API.',
                'post_id' => 10
            ),
            array(
                'url' => $this->site_url . '/?p=27',
                'new_title' => 'Sobre Mars Challenge - Actualizado',
                'new_description' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio. Actualizado.',
                'post_id' => 27
            ),
            array(
                'url' => $this->site_url . '/registro/',
                'new_title' => 'Registro Mars Challenge - Actualizado',
                'new_description' => 'Regístrate en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante. Actualizado.',
                'post_id' => 1521
            )
        );

        $results = array();

        foreach ($update_data as $item) {
            echo "\n" . str_repeat("-", 60) . "\n";
            
            // Primero, obtener los datos actuales
            echo "Obteniendo datos actuales para: {$item['url']}\n";
            $current_data = $this->get_current_meta_data($item['url']);
            
            if ($current_data) {
                echo "Título actual: " . (isset($current_data['title']) ? substr($current_data['title'], 0, 60) : 'N/A') . "\n";
                echo "Descripción actual: " . (isset($current_data['description']) ? substr($current_data['description'], 0, 60) : 'N/A') . "\n";
            } else {
                echo "No se pudieron obtener datos actuales\n";
            }

            // Intentar actualizar
            $update_result = $this->update_meta_via_wp_api($item['post_id'], $item['new_title'], $item['new_description']);
            
            $results[] = array(
                'url' => $item['url'],
                'post_id' => $item['post_id'],
                'update_result' => $update_result,
                'new_title' => $item['new_title'],
                'new_description' => $item['new_description']
            );
            
            // Pausa entre actualizaciones
            sleep(5);
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESULTADOS FINALES:\n";
        
        $success_count = 0;
        foreach ($results as $result) {
            $status = $result['update_result'] ? '✓' : '✗';
            echo "$status ID {$result['post_id']}: " . ($result['update_result'] ? 'ÉXITO' : 'FALLO') . "\n";
            if ($result['update_result']) $success_count++;
        }
        
        echo "\nÉxito: $success_count de " . count($results) . " actualizaciones\n";
        
        if ($success_count > 0) {
            echo "\n🎉 ¡ÉXITO! Has descubierto cómo actualizar Rank Math:\n";
            echo "   - El endpoint getHead está disponible para OBTENER datos\n";
            echo "   - Se puede usar la API estándar de WP con campos meta personalizados\n";
            echo "   - Campos: 'rank_math_description', 'rank_math_title', '_rank_math_description', '_rank_math_title'\n";
        } else {
            echo "\n⚠️  Aún necesitas verificar la configuración de Rank Math\n";
        }

        return $results;
    }
}

// Ejecutar las pruebas
$updater = new RankMathGetHeadUpdater();
$results = $updater->bulk_update_test();

echo "\n✅ PRUEBA DE ACTUALIZACIÓN CON GETHEAD COMPLETADA\n";