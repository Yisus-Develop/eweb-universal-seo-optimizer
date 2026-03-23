<?php
/**
 * Script de actualización directa de Rank Math que intenta usar el campo correcto
 * Basado en la información de que Headless CMS no está completamente configurado
 */

class Direct_RankMath_Updater {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando actualización directa de Rank Math para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Direct-RankMath-Updater/1.0',
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
     * Intentar actualizar Rank Math usando todos los campos posibles conocidos
     */
    public function update_rankmath_fields($post_id, $title = null, $description = null) {
        echo "\n📝 ACTUALIZANDO RANK MATH - ID: $post_id\n";
        echo "========================================\n";

        $update_attempts = array();
        
        // Lista de posibles campos de Rank Math
        $possible_fields = array(
            'rank_math_description',
            '_rank_math_description', 
            'rank_math_title',
            '_rank_math_title',
            'rank_math_data' => array(
                'title' => $title,
                'description' => $description
            ),
            'meta' => array(
                'rank_math_description' => $description,
                'rank_math_title' => $title
            ),
            'meta_input' => array(
                'rank_math_description' => $description,
                'rank_math_title' => $title
            )
        );

        if ($description) {
            echo "Nueva descripción: $description\n";
        }
        if ($title) {
            echo "Nuevo título: $title\n";
        }

        // Intentar diferentes combinaciones
        $update_data_variants = array(
            // Método 1: Directo en meta
            array(
                'meta' => array(
                    'rank_math_description' => $description,
                    'rank_math_title' => $title
                )
            ),
            // Método 2: Con guión bajo
            array(
                'meta' => array(
                    '_rank_math_description' => $description,
                    '_rank_math_title' => $title
                )
            ),
            // Método 3: meta_input
            array(
                'meta_input' => array(
                    'rank_math_description' => $description,
                    'rank_math_title' => $title
                )
            ),
            // Método 4: meta_input con guión bajo
            array(
                'meta_input' => array(
                    '_rank_math_description' => $description,
                    '_rank_math_title' => $title
                )
            ),
            // Método 5: Sólo descripción
            array(
                'meta' => array(
                    'rank_math_description' => $description
                )
            ),
            // Método 6: Sólo título
            array(
                'meta' => array(
                    'rank_math_title' => $title
                )
            ),
            // Método 7: Combinación de campos posibles
            array(
                'meta' => array(
                    'rank_math_description' => $description,
                    '_rank_math_description' => $description,
                    'rank_math_title' => $title,
                    '_rank_math_title' => $title
                )
            )
        );

        $success = false;
        $successful_method = null;
        
        foreach ($update_data_variants as $idx => $update_data) {
            echo "\nIntento " . ($idx + 1) . ": ";
            
            // Solo actualizar campos que existen
            $clean_update_data = $this->clean_update_data($update_data, $title, $description);
            
            if (empty($clean_update_data)) {
                echo "Omitido (no hay datos válidos)\n";
                continue;
            }
            
            $method_name = $this->get_method_name($idx);
            echo $method_name . "\n";
            
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id", 'POST', $clean_update_data);
            
            $update_attempts[] = array(
                'attempt' => $idx + 1,
                'method' => $method_name,
                'status' => $response['status_code'],
                'data' => $clean_update_data
            );
            
            if ($response['status_code'] === 200) {
                $success = true;
                $successful_method = $method_name;
                echo "✓ ¡Éxito!\n";
                
                // Verificar que se actualizó correctamente
                sleep(2); // Esperar un poco para que se procese
                
                // Intentar verificar si se guardó el campo
                $verification = $this->verify_update($post_id, $title, $description);
                if ($verification) {
                    echo "✓ Verificación exitosa - Campos actualizados correctamente\n";
                    return array('success' => true, 'method' => $successful_method, 'attempts' => $update_attempts);
                } else {
                    echo "⚠️  Actualización devolvió éxito pero no se verificó el campo\n";
                }
                break;
            } else {
                echo "✗ Fallo (código: {$response['status_code']})\n";
                if (isset($response['body']['message'])) {
                    echo "  Mensaje: {$response['body']['message']}\n";
                }
            }
            
            sleep(2); // Esperar entre intentos
        }
        
        if (!$success) {
            echo "\n❌ No se pudo actualizar usando ninguno de los métodos estándar.\n";
            echo "⚠️  Es posible que necesites habilitar completamente el soporte Headless CMS de Rank Math.\n";
        }

        return array('success' => $success, 'method' => $successful_method, 'attempts' => $update_attempts);
    }

    /**
     * Limpiar datos de actualización
     */
    private function clean_update_data($update_data, $title, $description) {
        $cleaned = array();
        
        if (isset($update_data['meta'])) {
            $cleaned_meta = array();
            foreach ($update_data['meta'] as $key => $value) {
                if ($key === 'rank_math_title' || $key === '_rank_math_title') {
                    if ($title) $cleaned_meta[$key] = $title;
                } elseif ($key === 'rank_math_description' || $key === '_rank_math_description') {
                    if ($description) $cleaned_meta[$key] = $description;
                }
            }
            if (!empty($cleaned_meta)) {
                $cleaned['meta'] = $cleaned_meta;
            }
        }
        
        if (isset($update_data['meta_input'])) {
            $cleaned_input = array();
            foreach ($update_data['meta_input'] as $key => $value) {
                if ($key === 'rank_math_title' || $key === '_rank_math_title') {
                    if ($title) $cleaned_input[$key] = $title;
                } elseif ($key === 'rank_math_description' || $key === '_rank_math_description') {
                    if ($description) $cleaned_input[$key] = $description;
                }
            }
            if (!empty($cleaned_input)) {
                $cleaned['meta_input'] = $cleaned_input;
            }
        }
        
        return $cleaned;
    }

    /**
     * Obtener nombre del método para identificarlo
     */
    private function get_method_name($idx) {
        $names = array(
            'Directo en meta',
            'Con guión bajo',
            'Meta input',
            'Meta input con guión bajo',
            'Solo descripción',
            'Solo título',
            'Combinación de campos'
        );
        
        return isset($names[$idx]) ? $names[$idx] : 'Método #' . ($idx + 1);
    }

    /**
     * Verificar que se haya actualizado correctamente
     */
    private function verify_update($post_id, $title, $description) {
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id?context=edit");
        
        if ($response['status_code'] === 200 && isset($response['body']['meta'])) {
            $meta = $response['body']['meta'];
            
            // Buscar si se actualizó la descripción
            if ($description) {
                foreach ($meta as $key => $value) {
                    if ((stripos($key, 'rank') !== false && stripos($key, 'desc') !== false) || $key === 'rank_math_description' || $key === '_rank_math_description') {
                        if (is_string($value) && strpos($value, $description) !== false) {
                            return true;
                        } elseif (is_array($value)) {
                            foreach ($value as $v) {
                                if (is_string($v) && strpos($v, $description) !== false) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
            
            // Buscar si se actualizó el título
            if ($title) {
                foreach ($meta as $key => $value) {
                    if ((stripos($key, 'rank') !== false && stripos($key, 'title') !== false) || $key === 'rank_math_title' || $key === '_rank_math_title') {
                        if (is_string($value) && strpos($value, $title) !== false) {
                            return true;
                        } elseif (is_array($value)) {
                            foreach ($value as $v) {
                                if (is_string($v) && strpos($v, $title) !== false) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return false;
    }

    /**
     * Actualizar múltiples elementos
     */
    public function bulk_update() {
        echo "🚀 INICIANDO ACTUALIZACIÓN MASIVA DE RANK MATH\n";
        echo "==============================================\n";

        // Datos para actualizar (ejemplos)
        $items_to_update = array(
            array(
                'id' => 10,
                'title' => 'Mars Challenge 2026 - Inicio',
                'description' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores.'
            ),
            array(
                'id' => 27,
                'title' => 'Sobre Mars Challenge',
                'description' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio.'
            ),
            array(
                'id' => 37,
                'title' => 'Cómo participar en Mars Challenge',
                'description' => 'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. ¡Únete al reto!'
            )
        );

        $results = array();
        
        foreach ($items_to_update as $item) {
            echo "\n" . str_repeat("-", 50) . "\n";
            $result = $this->update_rankmath_fields($item['id'], $item['title'], $item['description']);
            $result['item'] = $item;
            $results[] = $result;
            
            echo "Resultado para ID {$item['id']}: " . ($result['success'] ? '✓ ÉXITO' : '✗ FALLO') . "\n";
        }

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 RESUMEN DE ACTUALIZACIONES:\n";
        
        $successful = 0;
        foreach ($results as $result) {
            if ($result['success']) {
                $successful++;
                echo "✓ ID {$result['item']['id']}: Actualizado con método '{$result['method']}'\n";
            } else {
                echo "✗ ID {$result['item']['id']}: No se pudo actualizar\n";
            }
        }
        
        echo "\nÉxito: $successful de " . count($results) . " actualizaciones\n";
        
        if ($successful === 0) {
            echo "\n⚠️  Ninguna actualización fue exitosa. Esto sugiere que:\n";
            echo "   1. El soporte Headless CMS de Rank Math no está habilitado\n";
            echo "   2. Los campos específicos de Rank Math requieren un método diferente\n";
            echo "   3. Es posible que necesites usar el panel de control de WordPress\n\n";
            
            echo "💡 RECOMENDACIÓN:\n";
            echo "   - Habilita el soporte Headless CMS en la configuración de Rank Math\n";
            echo "   - O usa WP CLI con comandos específicos de Rank Math\n";
            echo "   - O actualiza manualmente a través del panel de control de WordPress\n";
        }

        return $results;
    }
}

// Ejecutar la actualización directa
$updater = new Direct_RankMath_Updater();
$results = $updater->bulk_update();

echo "\n✅ ACTUALIZACIÓN DIRECTA DE RANK MATH COMPLETADA\n";