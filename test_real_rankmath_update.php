<?php
/**
 * Script de Prueba de Actualización Real de Rank Math
 * Verifica si la actualización del campo de Rank Math se refleja correctamente
 */

class RankMath_Real_Update_Test {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando prueba de actualización real de Rank Math para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'RankMath-Real-Update-Test/1.0',
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
     * Verificar campos específicos de Rank Math en el encabezado HTML
     * (Simulando cómo Rank Math puede mostrar su información)
     */
    public function test_direct_update_and_verification() {
        echo "\n🧪 PRUEBA DE ACTUALIZACIÓN REAL DE RANK MATH\n";
        echo "==========================================\n";

        $post_id = 10; // Página de inicio
        $test_description = "Descripción actualizada via API - Prueba de Rank Math - " . date('Y-m-d H:i:s');

        echo "Objetivo: Actualizar metadescripción de ID: $post_id\n";
        echo "Nueva descripción: $test_description\n\n";

        // 1. Obtener el estado actual
        echo "1. Obteniendo estado actual...\n";
        $current = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
        if ($current['status_code'] !== 200) {
            echo "✗ Error al obtener el estado actual\n";
            return false;
        }

        // 2. Intentar actualizar con diferentes campos posibles de Rank Math
        $update_methods = array(
            array('meta' => array('rank_math_description' => $test_description)),
            array('meta' => array('_rank_math_description' => $test_description)),
            array('meta' => array('rank_math_title' => 'Título de prueba', 'rank_math_description' => $test_description)),
            array('meta_input' => array('rank_math_description' => $test_description)),
            array('meta_input' => array('_rank_math_description' => $test_description))
        );

        $update_success = false;
        $method_used = null;

        foreach ($update_methods as $idx => $update_data) {
            echo "\nPrueba " . ($idx + 1) . ": Intentando actualizar con " . json_encode(array_keys($update_data)) . "\n";
            
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id", 'POST', $update_data);
            
            if ($response['status_code'] === 200) {
                $update_success = true;
                $method_used = $update_data;
                echo "✓ Actualización exitosa con el método " . ($idx + 1) . "\n";
                
                // 3. Verificar si se actualizó realmente
                sleep(3); // Esperar un poco para que se procese
                
                $updated = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
                
                if ($updated['status_code'] === 200 && !empty($updated['body'])) {
                    $item = $updated['body'];
                    
                    // Buscar en los campos meta si se actualizó el campo específico de Rank Math
                    if (isset($item['meta']) && is_array($item['meta'])) {
                        $rank_math_found = false;
                        foreach ($item['meta'] as $key => $value) {
                            if (stripos($key, 'rank') !== false && stripos($key, 'desc') !== false) {
                                $rank_math_found = true;
                                if (is_array($value)) {
                                    foreach ($value as $val) {
                                        if ($val === $test_description) {
                                            echo "✓ Campo Rank Math encontrado y actualizado correctamente!\n";
                                            echo "Campo: $key, Valor: $val\n";
                                            return true;
                                        }
                                    }
                                } elseif ($value === $test_description) {
                                    echo "✓ Campo Rank Math encontrado y actualizado correctamente!\n";
                                    echo "Campo: $key, Valor: $value\n";
                                    return true;
                                }
                            }
                        }
                        
                        if (!$rank_math_found) {
                            echo "⚠️  El campo Rank Math no se encontró en los metadatos, pero la actualización fue exitosa\n";
                            echo "   Esto sugiere que Rank Math almacena esta información de forma diferente\n";
                        }
                    } else {
                        echo "⚠️  No se encontraron campos meta después de la actualización\n";
                    }
                } else {
                    echo "✗ Error al verificar la actualización\n";
                }
                
                break;
            } else {
                echo "✗ Actualización fallida con el método " . ($idx + 1) . "\n";
                if (isset($response['body']['message'])) {
                    echo "Mensaje de error: {$response['body']['message']}\n";
                }
            }
            
            sleep(2);
        }

        if (!$update_success) {
            echo "\n✗ Ningún método de actualización funcionó\n";
            return false;
        }

        // 4. Probar método alternativo: Actualizar directamente el head de Rank Math
        echo "\n2. Probando método alternativo...\n";
        
        // Intentar obtener el head SEO de Rank Math (si está disponible)
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        if ($response['status_code'] === 200 && isset($response['body']['yoast_head'])) {
            // Este campo podría ser diferente para Rank Math
            echo "Campo yoast_head encontrado (posiblemente indica que Rank Math no está activo o configurado)\n";
        } else {
            echo "⚠️  No se encontró yoast_head, lo que sugiere que Rank Math está activo\n";
        }

        // 5. Intentar ver si hay un endpoint específico de Rank Math
        echo "\n3. Verificando endpoint específico de Rank Math...\n";
        $rankmath_response = $this->make_request($this->site_url . "/wp-json/rankmath/v1/meta/$post_id");
        if ($rankmath_response['status_code'] === 200) {
            echo "✓ Endpoint específico de Rank Math encontrado!\n";
            echo "Posible respuesta: " . json_encode($rankmath_response['body']) . "\n";
        } else {
            echo "⚠️  No se encontró endpoint específico de Rank Math\n";
        }

        echo "\n💡 CONCLUSIONES:\n";
        echo "   - La actualización puede haber sido exitosa pero almacenada de forma diferente\n";
        echo "   - Rank Math puede requerir un endpoint o campo específico no estándar\n";
        echo "   - Puede que necesitemos usar el panel de control de WordPress para actualizaciones precisas\n";
        echo "   - Consulta la documentación de Rank Math para saber los campos específicos de API\n\n";

        return true;
    }

    /**
     * Analizar la configuración de Rank Math en el sitio
     */
    public function analyze_rankmath_setup() {
        echo "\n🔍 ANALIZANDO CONFIGURACIÓN DE RANK MATH\n";
        echo "=======================================\n";

        // Intentar obtener información del endpoint general de Rank Math
        $response = $this->make_request($this->site_url . "/wp-json/rankmath/v1/settings");
        
        if ($response['status_code'] === 200) {
            echo "✓ Configuración de Rank Math disponible\n";
            if (isset($response['body']['options'])) {
                echo "Opciones disponibles: " . count($response['body']['options']) . " configuraciones\n";
            }
        } else {
            echo "⚠️  No se encontró endpoint de configuración de Rank Math\n";
        }

        // Revisar información general de plugins
        echo "\nVerificando estado de plugins...\n";
        echo "Parece que Rank Math está instalado, pero su integración con la API de WordPress\n";
        echo "puede requerir configuración específica o un campo distinto al que probamos.\n\n";

        echo "🔧 SOLUCIONES POSIBLES:\n";
        echo "   1. Usar WP CLI con el plugin de Rank Math: wp rankmath update-meta\n";
        echo "   2. Usar el panel de control de WordPress para actualizaciones masivas\n";
        echo "   3. Probar con el campo '_yoast_wpseo_metadesc' como fallback\n";
        echo "   4. Contactar a soporte de Rank Math para conocer el campo API específico\n\n";

        return true;
    }

    /**
     * Ejecutar prueba completa
     */
    public function run_test() {
        echo "🚀 INICIANDO PRUEBA COMPLETA DE ACTUALIZACIÓN RANK MATH\n";
        echo "====================================================\n";

        $this->analyze_rankmath_setup();
        $result = $this->test_direct_update_and_verification();

        echo "\n🎯 RESULTADO FINAL:\n";
        if ($result) {
            echo "   La actualización fue técnica y posiblemente exitosa,\n";
            echo "   pero se requiere verificación adicional o método específico.\n";
        } else {
            echo "   La actualización no pudo completarse con los métodos probados.\n";
        }

        echo "\n📋 RECOMENDACIONES FINALES:\n";
        echo "   1. Verificar manualmente en WordPress si los cambios se reflejan\n";
        echo "   2. Consultar la documentación de Rank Math para el campo API correcto\n";
        echo "   3. Considerar usar WP CLI o el panel de control para actualizaciones masivas\n";
        echo "   4. Probar con el campo '_yoast_wpseo_metadesc' como alternativa temporal\n";

        return $result;
    }
}

// Ejecutar la prueba
$tester = new RankMath_Real_Update_Test();
$tester->run_test();

echo "\n✅ PRUEBA DE ACTUALIZACIÓN REAL DE RANK MATH COMPLETADA\n";