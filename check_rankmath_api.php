<?php
/**
 * Script de Verificación de Campos de Rank Math API
 * Determina los campos correctos para actualizar metadescripciones via API
 */

class RankMath_API_Check {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando verificación de campos de Rank Math API para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'RankMath-API-Check/1.0',
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
     * Obtener un elemento y examinar sus campos meta
     */
    public function inspect_meta_fields($post_id) {
        echo "\n🔍 Inspeccionando campos meta para ID: $post_id\n";

        // Intentar con página
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
        $is_page = $response['status_code'] === 200;
        $type = 'page';
        
        if (!$is_page) {
            // Intentar con post
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts/$post_id?context=edit");
            $is_post = $response['status_code'] === 200;
            $type = 'post';
        }
        
        if ($response['status_code'] === 200 && !empty($response['body'])) {
            $item = $response['body'];
            
            echo "Tipo: $type\n";
            echo "Título: " . (isset($item['title']['rendered']) ? $item['title']['rendered'] : 'N/A') . "\n";
            
            if (isset($item['meta']) && is_array($item['meta'])) {
                echo "\n Campos 'meta' encontrados:\n";
                foreach ($item['meta'] as $key => $value) {
                    if (is_array($value)) {
                        echo "  - $key: [array con " . count($value) . " elementos]\n";
                        foreach ($value as $idx => $val) {
                            if (is_string($val)) {
                                echo "    [$idx]: '" . substr($val, 0, 100) . (strlen($val) > 100 ? '...' : '') . "'\n";
                            } else {
                                echo "    [$idx]: " . gettype($val) . "\n";
                            }
                        }
                    } else {
                        if (is_string($value)) {
                            echo "  - $key: '" . substr($value, 0, 100) . (strlen($value) > 100 ? '...' : '') . "'\n";
                        } else {
                            echo "  - $key: " . gettype($value) . "\n";
                        }
                    }
                }
            } else {
                echo "\nNo se encontraron campos 'meta'\n";
                
                // Verificar si hay otros campos posibles relacionados con SEO
                $possible_seo_fields = array(
                    'yoast_head', 'yoast_meta', 'rank_math_data', 'seo_meta', 'seo_description',
                    'description', 'excerpt', 'meta_desc', 'meta_description'
                );
                
                echo "\nVerificando otros campos posibles:\n";
                foreach ($possible_seo_fields as $field) {
                    if (isset($item[$field])) {
                        echo "  - $field: encontrado\n";
                        if (is_string($item[$field])) {
                            echo "    Contenido: '" . substr($item[$field], 0, 100) . (strlen($item[$field]) > 100 ? '...' : '') . "'\n";
                        }
                    }
                }
            }
            
            return $item;
        } else {
            echo "✗ No se pudo obtener información para ID $post_id\n";
            return null;
        }
    }

    /**
     * Probar diferentes campos para actualizar metadescripción
     */
    public function test_update_fields($post_id) {
        echo "\n🧪 Probando diferentes campos para actualización de Rank Math - ID: $post_id\n";

        $test_description = "Descripción de prueba para Rank Math - " . date('Y-m-d H:i:s');

        // Diferentes posibles campos de Rank Math
        $rank_math_fields = array(
            'rank_math_description',
            '_rank_math_description',
            'meta' => array('rank_math_description' => $test_description),
            'meta' => array('_rank_math_description' => $test_description),
            'rank_math' => array('description' => $test_description),
            'meta_input' => array('rank_math_description' => $test_description),
            'meta_input' => array('_rank_math_description' => $test_description)
        );

        $responses = array();

        // Intentar diferentes formatos de actualización
        $update_data_variants = array(
            array('meta' => array('rank_math_description' => $test_description)),
            array('meta' => array('_rank_math_description' => $test_description)),
            array('meta_input' => array('rank_math_description' => $test_description)),
            array('meta_input' => array('_rank_math_description' => $test_description)),
            array('rank_math_description' => $test_description),
            array('_rank_math_description' => $test_description)
        );

        foreach ($update_data_variants as $idx => $update_data) {
            echo "\nPrueba " . ($idx + 1) . ": Intentando con formato: " . json_encode(array_keys($update_data)) . "\n";
            
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/" . ($this->is_page($post_id) ? 'pages' : 'posts') . "/$post_id", 'POST', $update_data);
            
            $responses[] = array(
                'attempt' => $idx + 1,
                'data' => $update_data,
                'status_code' => $response['status_code'],
                'response' => $response['body']
            );
            
            echo "  Código de estado: {$response['status_code']}\n";
            
            if ($response['status_code'] === 200) {
                echo "  ✓ Actualización exitosa\n";
                break;
            } else {
                echo "  ✗ Error en la actualización\n";
                if (isset($response['body']['message'])) {
                    echo "  Mensaje: {$response['body']['message']}\n";
                }
            }
            
            sleep(2); // Esperar entre intentos
        }

        return $responses;
    }

    /**
     * Verificar si una ID es página o post
     */
    private function is_page($post_id) {
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        return $response['status_code'] === 200;
    }

    /**
     * Ejecutar verificación completa
     */
    public function run_check() {
        echo "🚀 INICIANDO VERIFICACIÓN DE CAMPOS RANK MATH API\n";
        echo "================================================\n";

        // Verificar un par de elementos diferentes
        $test_ids = array(10, 27); // Inicio y Mars Challenge
        
        foreach ($test_ids as $id) {
            echo "\n" . str_repeat("=", 60) . "\n";
            $this->inspect_meta_fields($id);
        }

        echo "\n" . str_repeat("=", 60) . "\n";
        echo "PRUEBAS DE ACTUALIZACIÓN:\n";
        $responses = $this->test_update_fields(10); // Probar con la página de inicio
        
        echo "\n📋 RESUMEN DE PRUEBAS DE ACTUALIZACIÓN:\n";
        foreach ($responses as $response) {
            echo "  Prueba {$response['attempt']}: Código {$response['status_code']}\n";
        }

        echo "\n🔍 RECOMENDACIONES:\n";
        echo "   1. Verifica que el plugin Rank Math esté activo y actualizado\n";
        echo "   2. Revisa en el panel de Rank Math si hay una API específica\n";
        echo "   3. Consulta la documentación de la API de Rank Math para ver los campos correctos\n";
        echo "   4. Considera usar WP CLI con Rank Math si está disponible\n";
        echo "   5. Opcionalmente, se puede usar el panel de control de WordPress manualmente\n";

        return $responses;
    }
}

// Ejecutar la verificación
$checker = new RankMath_API_Check();
$checker->run_check();

echo "\n✅ VERIFICACIÓN DE CAMPOS RANK MATH API COMPLETADA\n";