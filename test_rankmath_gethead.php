<?php
/**
 * Script para probar el endpoint getHead de la API de Rank Math
 * Basado en la información adicional proporcionada
 */

class TestRankMathGetHead {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Probando endpoint getHead de Rank Math API para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'TestRankMathGetHead/1.0',
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
     * Probar el endpoint getHead
     */
    public function test_getHead_endpoint() {
        echo "\n🔧 PROBANDO ENDPOINT getHead DE RANK MATH\n";
        echo "========================================\n";

        // Probar con diferentes páginas
        $test_pages = array(
            'inicio' => $this->site_url . '/',
            'pagina10' => $this->site_url . '/?p=10',
            'registro' => $this->site_url . '/registro/',
            'reto-marte' => $this->site_url . '/reto-marte-en-costa-rica/'
        );

        $results = array();

        foreach ($test_pages as $page_name => $page_url) {
            echo "\nProbando getHead para $page_name: $page_url\n";
            
            $endpoint_url = $this->site_url . "/wp-json/rankmath/v1/getHead?url=" . urlencode($page_url);
            $response = $this->make_request($endpoint_url);
            
            $result = array(
                'page' => $page_name,
                'url' => $page_url,
                'endpoint' => $endpoint_url,
                'status_code' => $response['status_code'],
                'has_data' => !empty($response['body'])
            );
            
            $results[] = $result;
            
            if ($response['status_code'] === 200) {
                echo "✓ Éxito (código 200)\n";
                if (!empty($response['body'])) {
                    echo "✓ Datos recibidos\n";
                    
                    // Mostrar algunas claves principales si existen
                    $data = $response['body'];
                    if (isset($data['title'])) {
                        echo "  Título: " . $data['title'] . "\n";
                    }
                    if (isset($data['description'])) {
                        echo "  Descripción: " . substr($data['description'], 0, 100) . (strlen($data['description']) > 100 ? '...' : '') . "\n";
                    }
                    if (isset($data['og_title'])) {
                        echo "  OG Título: " . $data['og_title'] . "\n";
                    }
                    if (isset($data['og_description'])) {
                        echo "  OG Descripción: " . substr($data['og_description'], 0, 100) . (strlen($data['og_description']) > 100 ? '...' : '') . "\n";
                    }
                }
            } elseif ($response['status_code'] === 404) {
                echo "✗ Endpoint no encontrado (código 404)\n";
                echo "   El soporte Headless CMS probablemente no está habilitado\n";
            } elseif ($response['status_code'] === 401) {
                echo "✗ No autorizado (código 401)\n";
            } else {
                echo "✗ Otro error (código: {$response['status_code']})\n";
            }
        }

        return $results;
    }

    /**
     * Probar si hay otros endpoints disponibles
     */
    public function test_other_endpoints() {
        echo "\n🔍 PROBANDO OTROS ENDPOINTS POSIBLES DE RANK MATH\n";
        echo "===============================================\n";

        $other_endpoints = array(
            '/wp-json/rankmath/v1/',
            '/wp-json/rankmath/v1/meta/',
            '/wp-json/rankmath/v1/settings/',
            '/wp-json/rankmath/v1/analytics/',
            '/wp-json/rankmath/v1/getHead/',
            '/wp-json/rank-math/v1/getHead/'  // Posible variación
        );

        foreach ($other_endpoints as $endpoint) {
            $url = $this->site_url . $endpoint;
            $response = $this->make_request($url);
            
            echo "Probando $endpoint: ";
            if ($response['status_code'] === 200) {
                echo "✓ Disponible\n";
                if (isset($response['body']) && is_array($response['body'])) {
                    echo "  - Posibles métodos disponibles: " . implode(', ', array_keys($response['body'])) . "\n";
                }
            } elseif ($response['status_code'] === 404) {
                echo "✗ No encontrado\n";
            } elseif ($response['status_code'] === 401) {
                echo "✗ No autorizado\n";
            } else {
                echo "✗ Otro código: {$response['status_code']}\n";
            }
        }
    }

    /**
     * Ejecutar pruebas completas
     */
    public function run_tests() {
        echo "🚀 INICIANDO PRUEBAS COMPLETAS DE RANK MATH API\n";
        echo "============================================\n";

        $getHead_results = $this->test_getHead_endpoint();
        $this->test_other_endpoints();

        echo "\n📊 RESUMEN DE PRUEBAS:\n";
        $successful_getHead = 0;
        foreach ($getHead_results as $result) {
            if ($result['status_code'] === 200) {
                $successful_getHead++;
            }
        }
        
        if ($successful_getHead === 0) {
            echo "❌ No se encontraron endpoints de Rank Math funcionando.\n";
            echo "   Esto confirma que el soporte Headless CMS no está habilitado.\n\n";
            
            echo "🔧 INSTRUCCIONES PARA HABILITAR SOPORTE HEADLESS CMS:\n";
            echo "   1. Accede al panel de administración de WordPress\n";
            echo "   2. Ve a Rank Math > General Settings > Advanced\n";
            echo "   3. Busca la opción 'Headless CMS Support' y habilítala\n";
            echo "   4. Guarda los cambios\n";
            echo "   5. Los endpoints como /wp-json/rankmath/v1/getHead?url=URL deberían funcionar\n\n";
            
            echo "💡 NOTA: Una vez habilitado, podrás usar los endpoints:\n";
            echo "   - GET /wp-json/rankmath/v1/getHead?url=URL - Para obtener meta tags\n";
            echo "   - POST /wp-json/rankmath/v1/meta/{id} - Para actualizar meta tags\n";
        } else {
            echo "✓ Se encontraron {$successful_getHead} endpoints funcionando correctamente.\n";
            echo "   Puedes usar los endpoints de Rank Math para obtener y actualizar meta tags.\n";
        }

        return array(
            'getHead_results' => $getHead_results
        );
    }
}

// Ejecutar las pruebas
$tester = new TestRankMathGetHead();
$results = $tester->run_tests();

echo "\n✅ PRUEBAS DE RANK MATH API COMPLETADAS\n";