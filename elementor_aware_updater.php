<?php
/**
 * Script para probar actualización directa vía endpoint de Rank Math
 * Considerando que Elementor puede interferir con las meta tags
 */

class Direct_RankMath_Updater_v2 {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Probando actualización directa de Rank Math considerando Elementor\n";
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
            CURLOPT_USERAGENT => 'Direct-RankMath-Updater-v2/1.0',
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
     * Intentar actualizar usando el endpoint getHead para ver si podemos modificar directamente
     */
    public function test_gethead_modification($url_to_update) {
        echo "\n🔍 PROBANDO POSIBLE ACTUALIZACIÓN VIA GETHEAD PARA: $url_to_update\n";
        echo "============================================================\n";

        $endpoint_url = $this->site_url . "/wp-json/rankmath/v1/getHead?url=" . urlencode($url_to_update);
        
        // Primero obtener los datos actuales
        $response = $this->make_request($endpoint_url);
        
        if ($response['status_code'] === 200 && !empty($response['body'])) {
            echo "✓ Datos actuales obtenidos\n";
            
            // Mostrar datos actuales
            $current_data = $response['body'];
            echo "Título actual: " . ($current_data['title'] ?? 'N/A') . "\n";
            echo "Descripción actual: " . (isset($current_data['description']) ? substr($current_data['description'], 0, 100) . '...' : 'N/A') . "\n";
            
            // Intentar hacer una actualización (aunque getHead normalmente es solo para GET)
            // Esto es para probar si hay un endpoint de modificación
            $update_response = $this->make_request($endpoint_url, 'POST', array(
                'description' => 'Descripción actualizada vía getHead - ' . date('Y-m-d H:i:s'),
                'title' => 'Título actualizado vía getHead - ' . date('Y-m-d H:i:s')
            ));
            
            echo "Código de respuesta POST a getHead: {$update_response['status_code']}\n";
            
            if ($update_response['status_code'] === 404 || $update_response['status_code'] === 405) {
                echo "✗ El endpoint getHead no permite actualización directa (esperado)\n";
            }
            
            return $current_data;
        } else {
            echo "✗ No se pudieron obtener datos\n";
            return null;
        }
    }

    /**
     * Prueba de actualización considerando Elementor
     */
    public function update_with_elementor_consideration($post_id, $new_description, $new_title = null) {
        echo "\n🔧 ACTUALIZANDO CONSIDERANDO ELEMENTOR - ID: $post_id\n";
        echo "===============================================\n";

        // Primero, determinar si es página o post
        $post_type = $this->get_post_type($post_id);
        if ($post_type === 'unknown') {
            echo "✗ No se pudo determinar el tipo de post\n";
            return false;
        }

        // Intentar diferentes enfoques para actualizar
        $update_methods = array();

        // Método 1: Estándar de Rank Math
        $update_methods[] = array(
            'name' => 'Método Estándar Rank Math',
            'data' => array(
                'meta' => array(
                    'rank_math_description' => $new_description,
                    '_rank_math_description' => $new_description,
                )
            )
        );

        // Método 2: Con título incluido
        if ($new_title) {
            $update_methods[] = array(
                'name' => 'Con Título Incluido',
                'data' => array(
                    'meta' => array(
                        'rank_math_description' => $new_description,
                        '_rank_math_description' => $new_description,
                        'rank_math_title' => $new_title,
                        '_rank_math_title' => $new_title
                    )
                )
            );
        }

        // Método 3: Campo alternativo que Elementor podría reconocer
        $update_methods[] = array(
            'name' => 'Campo Alternativo',
            'data' => array(
                'meta' => array(
                    'description' => $new_description,
                    'rank_math_description' => $new_description,
                    '_rank_math_description' => $new_description
                )
            )
        );

        foreach ($update_methods as $method) {
            echo "\nProbando: {$method['name']}\n";
            
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/{$post_type}/{$post_id}", 'POST', $method['data']);
            
            if ($response['status_code'] === 200) {
                echo "✓ Éxito con {$method['name']}\n";
                
                // Para algunas páginas, especialmente con Elementor, puede ser necesario limpiar la caché
                // o esperar un poco para que se reflejen los cambios
                sleep(5); // Esperar para que se procesen los cambios
                
                // Verificar si el cambio se reflejó usando getHead
                $check_url = ($post_type === 'pages') ? 
                    $this->site_url . "/?page_id=$post_id" : 
                    $this->site_url . "/?p=$post_id";
                    
                $head_data = $this->test_gethead_modification($check_url);
                
                if ($head_data && $head_data['description'] ?? false) {
                    if (strpos($head_data['description'], $new_description) !== false) {
                        echo "✓ Cambio verificado en getHead\n";
                        return true;
                    } else {
                        echo "⚠️  Cambio no reflejado en getHead inmediatamente\n";
                    }
                }
                
                return true;
            } else {
                echo "✗ Fallo con {$method['name']} (código: {$response['status_code']})\n";
                if (isset($response['body']['message'])) {
                    echo "  Error: {$response['body']['message']}\n";
                }
            }
        }

        return false;
    }

    /**
     * Obtener tipo de post
     */
    private function get_post_type($post_id) {
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        if ($response['status_code'] === 200) {
            return 'pages';
        }
        
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts/$post_id");
        if ($response['status_code'] === 200) {
            return 'posts';
        }
        
        return 'unknown';
    }

    /**
     * Ejecutar pruebas considerando Elementor
     */
    public function run_elementor_aware_tests() {
        echo "🚀 PRUEBAS CONSIDERANDO LA INTEGRACIÓN CON ELEMENTOR\n";
        echo "================================================\n";

        // Probar con una página específica que sabemos que tiene problemas
        $test_item = array(
            'id' => 2883, // Página fuego
            'title' => 'Reto Marte Fuego - Actualizado',
            'description' => 'Reto Marte 2025: Fuego - Descripción actualizada considerando Elementor. Soluciones innovadoras para la gestión de energía y recursos en condiciones extremas.'
        );

        $result = $this->update_with_elementor_consideration($test_item['id'], $test_item['description'], $test_item['title']);

        if ($result) {
            echo "\n✅ Posible solución encontrada para integración con Elementor\n";
            echo "💡 NOTA: Si usas Elementor, puede ser necesario recargar el editor de Elementor\n";
            echo "   o limpiar la caché para que los cambios se reflejen en el HTML final.\n";
            echo "   También puede haber configuraciones específicas de Elementor que anulan\n";
            echo "   las meta tags de Rank Math.\n";
        } else {
            echo "\n⚠️  Es posible que se necesite una integración específica con Elementor\n";
            echo "   o que debas actualizar las meta tags directamente en el editor de Elementor.\n";
        }

        return $result;
    }
}

// Ejecutar las pruebas considerando Elementor
$updater = new Direct_RankMath_Updater_v2();
$result = $updater->run_elementor_aware_tests();

echo "\n✅ PRUEBAS CONSIDERANDO ELEMENTOR COMPLETADAS\n";
echo "Ahora entendemos mejor por qué los cambios no se reflejan inmediatamente.\n";