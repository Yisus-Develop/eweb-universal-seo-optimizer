<?php
/**
 * Script para probar la API de Rank Math con Headless CMS habilitado
 * Basado en la documentación oficial de Rank Math
 */

class RankMath_Headless_API_Check {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando verificación de API de Rank Math con Headless CMS para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'RankMath-Headless-API-Check/1.0',
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
     * Verificar si el endpoint específico de Rank Math está disponible
     */
    public function check_rankmath_endpoints() {
        echo "\n🔍 VERIFICANDO ENDPOINTS ESPECÍFICOS DE RANK MATH\n";
        echo "===============================================\n";

        $endpoints_to_test = array(
            '/wp-json/rankmath/v1/meta',
            '/wp-json/rankmath/v1/settings',
            '/wp-json/rankmath/v1/analytics',
            '/wp-json/rankmath/v1/general',
            '/wp-json/rank-math/v1/meta', // Posible variación en la nomenclatura
            '/wp-json/rank-math/v1/settings'
        );

        $available_endpoints = array();

        foreach ($endpoints_to_test as $endpoint) {
            $response = $this->make_request($this->site_url . $endpoint);
            $status = $response['status_code'];
            
            echo "Probando $endpoint: ";
            if ($status === 200) {
                echo "✓ Disponible\n";
                $available_endpoints[] = $endpoint;
                if (isset($response['body'])) {
                    echo "  - Respuesta: " . json_encode($response['body']) . "\n";
                }
            } elseif ($status === 404) {
                echo "✗ No encontrado\n";
            } elseif ($status === 401) {
                echo "✗ No autorizado (posible configuración necesaria)\n";
            } else {
                echo "⚠️  Otro código: $status\n";
            }
        }

        return $available_endpoints;
    }

    /**
     * Probar obtener metadata específica de Rank Math para un post
     */
    public function test_get_rankmath_meta($post_id = 10) {
        echo "\n🧪 PROBANDO OBTENCIÓN DE METADATA DE RANK MATH - ID: $post_id\n";
        echo "==========================================================\n";

        $test_endpoints = array(
            $this->site_url . "/wp-json/rankmath/v1/meta/$post_id",
            $this->site_url . "/wp-json/rankmath/v1/meta/?post_id=$post_id",
            $this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit&_fields=meta,rank_math_data",
            $this->site_url . "/wp-json/wp/v2/posts/$post_id?context=edit&_fields=meta,rank_math_data"
        );

        foreach ($test_endpoints as $endpoint) {
            echo "Probando: $endpoint\n";
            $response = $this->make_request($endpoint);
            
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                echo "✓ Éxito\n";
                echo "Respuesta: " . json_encode($response['body']) . "\n\n";
                
                // Buscar campos específicos de Rank Math
                $this->analyze_rankmath_fields($response['body']);
                return true;
            } else {
                echo "✗ Fallo (código: {$response['status_code']})\n\n";
            }
        }

        return false;
    }

    /**
     * Analizar posibles campos de Rank Math en la respuesta
     */
    private function analyze_rankmath_fields($data) {
        echo "🔍 ANALIZANDO CAMPOS DE RANK MATH EN LA RESPUESTA:\n";
        
        if (is_array($data) || is_object($data)) {
            $this->search_rankmath_fields_recursive($data, '');
        } else {
            echo "  - Datos no son array u objeto\n";
        }
    }

    /**
     * Buscar recursivamente campos de Rank Math
     */
    private function search_rankmath_fields_recursive($data, $path) {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $current_path = $path ? "$path.$key" : $key;
                
                if (is_string($key) && (stripos($key, 'rank') !== false || stripos($key, 'math') !== false || stripos($key, 'seo') !== false)) {
                    echo "  Encontrado campo relacionado: $current_path = ";
                    if (is_string($value)) {
                        echo "'$value'\n";
                    } else {
                        echo gettype($value) . "\n";
                    }
                }
                
                if (is_array($value) || is_object($value)) {
                    $this->search_rankmath_fields_recursive($value, $current_path);
                }
            }
        }
    }

    /**
     * Probar la actualización usando el nuevo conocimiento
     */
    public function test_update_with_headless_support($post_id = 10) {
        echo "\n🔧 PROBANDO ACTUALIZACIÓN CON SOPORTE HEADLESS CMS\n";
        echo "===============================================\n";

        $test_description = "Descripción de prueba con soporte Headless CMS - " . date('Y-m-d H:i:s');
        
        // Primero intentar con el formato sugerido en la documentación
        $update_data = array(
            'meta' => array(
                'rank_math_description' => $test_description,
                'rank_math_title' => 'Título de prueba para ID ' . $post_id
            )
        );

        echo "Intentando actualizar página $post_id con el formato estándar...\n";
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id", 'POST', $update_data);
        
        if ($response['status_code'] === 200) {
            echo "✓ Actualización exitosa (código 200)\n";
            
            // Esperar un poco y verificar
            sleep(2);
            
            // Intentar obtener la metadata actualizada
            $check_response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
            if ($check_response['status_code'] === 200 && isset($check_response['body'])) {
                $page_data = $check_response['body'];
                
                // Verificar si la descripción está en los campos meta
                if (isset($page_data['meta']) && is_array($page_data['meta'])) {
                    foreach ($page_data['meta'] as $key => $value) {
                        if (stripos($key, 'rank') !== false && is_string($value) && strpos($value, 'Headless CMS') !== false) {
                            echo "✓ Descripción actualizada encontrada en campo: $key\n";
                            echo "  Valor: $value\n";
                            return true;
                        }
                    }
                    echo "⚠️  Actualización confirmada pero no se encontró el campo específico de Rank Math\n";
                    echo "   Esto puede indicar que se requiere un campo o endpoint diferente\n";
                    return true;
                }
            }
        } else {
            echo "✗ Actualización fallida (código: {$response['status_code']})\n";
            if (isset($response['body']['message'])) {
                echo "Mensaje: {$response['body']['message']}\n";
            }
        }

        echo "\n💡 INTENTANDO ENDPOINT ESPECÍFICO DE RANK MATH (si está disponible)\n";
        
        // Intentar con endpoint específico si está disponible
        $rankmath_update_response = $this->make_request($this->site_url . "/wp-json/rankmath/v1/meta/$post_id", 'POST', array(
            'description' => $test_description,
            'title' => 'Título de prueba para ID ' . $post_id
        ));
        
        if ($rankmath_update_response['status_code'] === 200) {
            echo "✓ Actualización exitosa vía endpoint específico de Rank Math\n";
            return true;
        } else {
            echo "✗ Endpoint específico no disponible o no soporta POST (código: {$rankmath_update_response['status_code']})\n";
        }

        return false;
    }

    /**
     * Probar el campo 'yoast_head' o similar que pueda contener datos de Rank Math
     */
    public function test_extended_fields($post_id = 10) {
        echo "\n🔍 PROBANDO CAMPOS EXTENDIDOS DE RANK MATH\n";
        echo "========================================\n";

        $extended_endpoints = array(
            $this->site_url . "/wp-json/wp/v2/pages/$post_id?_embed",
            $this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit",
            $this->site_url . "/wp/v2/pages/$post_id?_fields=all"
        );

        foreach ($extended_endpoints as $endpoint) {
            echo "Probando: $endpoint\n";
            $response = $this->make_request($endpoint);
            
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $data = $response['body'];
                
                // Buscar posibles campos de SEO
                $seo_related_keys = array();
                $this->find_seo_related_fields($data, $seo_related_keys, '');
                
                if (!empty($seo_related_keys)) {
                    echo " Campos relacionados con SEO encontrados:\n";
                    foreach ($seo_related_keys as $key => $value) {
                        echo "  - $key: ";
                        if (is_string($value)) {
                            echo "'$value'\n";
                        } else {
                            echo gettype($value) . "\n";
                        }
                    }
                    echo "\n";
                } else {
                    echo "  No se encontraron campos relacionados con SEO\n\n";
                }
                return;
            }
        }
    }

    /**
     * Buscar campos relacionados con SEO de forma recursiva
     */
    private function find_seo_related_fields($data, &$results, $path) {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $current_path = $path ? "$path.$key" : $key;
                
                if (is_string($key)) {
                    $lower_key = strtolower($key);
                    if (preg_match('/(seo|meta|rank|math|description|title|yoast|head|og|twitter|schema|structured|data)/i', $key)) {
                        $results[$current_path] = is_string($value) ? $value : gettype($value);
                    }
                }
                
                if (is_array($value) || is_object($value)) {
                    $this->find_seo_related_fields($value, $results, $current_path);
                }
            }
        }
    }

    /**
     * Ejecutar verificación completa
     */
    public function run_verification() {
        echo "🚀 INICIANDO VERIFICACIÓN CON SOPORTE HEADLESS CMS\n";
        echo "================================================\n";

        $endpoints = $this->check_rankmath_endpoints();
        
        if (!empty($endpoints)) {
            echo "\n✅ Se encontraron endpoints de Rank Math disponibles!\n";
            foreach ($endpoints as $endpoint) {
                echo "  - $endpoint\n";
            }
        } else {
            echo "\n⚠️  No se encontraron endpoints específicos de Rank Math.\n";
            echo "   Esto podría significar que:\n";
            echo "   1. El soporte Headless CMS aún no está completamente habilitado\n";
            echo "   2. Se necesita una configuración específica\n";
            echo "   3. El endpoint sigue un patrón diferente\n";
        }

        $this->test_get_rankmath_meta(10);
        $this->test_extended_fields(10);
        $this->test_update_with_headless_support(10);

        echo "\n🎯 RESUMEN DE VERIFICACIÓN:\n";
        echo "   - Verificar en la configuración de Rank Math que 'Headless CMS support' esté habilitado\n";
        echo "   - Si los endpoints específicos no están disponibles, contactar al administrador\n";
        echo "   - Probar actualizaciones pequeñas primero para verificar el funcionamiento\n\n";

        return array(
            'endpoints' => $endpoints,
            'verification_completed' => true
        );
    }
}

// Ejecutar la verificación
$checker = new RankMath_Headless_API_Check();
$checker->run_verification();

echo "\n✅ VERIFICACIÓN CON SOPORTE HEADLESS CMS COMPLETADA\n";