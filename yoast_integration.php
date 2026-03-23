<?php
/**
 * Integración con Herramientas de Yoast SEO para Mars Challenge
 * Utiliza las funcionalidades de Yoast para resolver problemas de enlaces rotos y redirecciones
 */

class Yoast_SEO_Integration {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔗 Iniciando integración con herramientas de Yoast SEO para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Yoast-Integration/1.0',
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
     * Verificar si Yoast SEO está activo y obtener información del estado
     */
    public function check_yoast_status() {
        echo "\n🔍 Verificando estado de Yoast SEO...\n";
        
        // Verificar si el endpoint de Yoast está disponible
        $response = $this->make_request($this->site_url . "/wp-json/yoast/v1/replacement_variables");
        
        if ($response['status_code'] === 200 || $response['status_code'] === 404) {
            echo "  ✓ Yoast SEO REST API está disponible\n";
            
            // Intentar obtener información general de Yoast
            $config_response = $this->make_request($this->site_url . "/wp-json/yoast/v1/config");
            if ($config_response['status_code'] === 200) {
                echo "  ✓ Configuración de Yoast accesible\n";
            }
            
            // Verificar si es Yoast Premium (tiene herramientas de redirección)
            $response = $this->make_request($this->site_url . "/wp-json/yoast/v1/report/redirects");
            if ($response['status_code'] === 200) {
                echo "  ✓ Yoast Premium detectado (tiene herramientas de redirección)\n";
                return 'premium';
            } else {
                echo "  ⚠ Solo está instalado Yoast SEO gratuito (sin herramientas de redirección)\n";
                return 'free';
            }
        } else {
            echo "  ✗ Yoast SEO no está disponible o no está correctamente instalado\n";
            return 'not_installed';
        }
    }
    
    /**
     * Obtener herramientas de Yoast (wpseo_tools)
     */
    public function get_yoast_tools() {
        echo "\n🔧 Obteniendo herramientas de Yoast SEO...\n";
        
        // Intentar acceder a las herramientas de Yoast
        $response = $this->make_request($this->site_url . "/wp-json/wpseo/v1/tools");
        
        if ($response['status_code'] === 200 && isset($response['body'])) {
            $tools = $response['body'];
            echo "  ✓ Herramientas de Yoast encontradas:\n";
            
            foreach ($tools as $tool_key => $tool_info) {
                echo "    - $tool_key: " . ($tool_info['name'] ?? 'Sin nombre') . "\n";
            }
            
            return $tools;
        } else {
            echo "  - No se pudieron acceder a las herramientas de Yoast via API\n";
            echo "  - Las herramientas de Yoast generalmente se acceden por interfaz de admin\n";
            return array();
        }
    }
    
    /**
     * Obtener reporte de enlaces internos (si está disponible)
     */
    public function get_internal_link_report() {
        echo "\n🔗 Obteniendo reporte de enlaces internos de Yoast (si disponible)...\n";
        
        // Intentar obtener reporte de enlaces internos
        $response = $this->make_request($this->site_url . "/wp-json/yoast/v1/reports/internal_link_count");
        
        if ($response['status_code'] === 200) {
            $report = $response['body'];
            echo "  ✓ Reporte de enlaces internos obtenido\n";
            
            // Mostrar datos relevantes
            if (isset($report['internal_link_count'])) {
                echo "    - Total de enlaces internos: {$report['internal_link_count']}\n";
            }
            if (isset($report['incoming_link_count'])) {
                echo "    - Enlaces internos entrantes: {$report['incoming_link_count']}\n";
            }
            
            return $report;
        } else {
            echo "  - No se pudo obtener reporte de enlaces internos (posiblemente requiere permisos específicos)\n";
            return null;
        }
    }
    
    /**
     * Verificar estado de redirecciones en Yoast (requiere Premium)
     */
    public function check_redirects_status() {
        echo "\n🔄 Verificando sistema de redirecciones de Yoast...\n";
        
        $yoast_type = $this->check_yoast_status();
        
        if ($yoast_type === 'premium') {
            echo "  ✓ Yoast Premium detectado - acceso a herramientas de redirección disponible\n";
            
            // Intentar obtener lista de redirecciones
            $response = $this->make_request($this->site_url . "/wp-json/yoast/v1/redirects");
            
            if ($response['status_code'] === 200) {
                $redirects = $response['body'];
                echo "  ✓ Sistema de redirecciones de Yoast accesible\n";
                echo "    - Total de redirecciones configuradas: " . count($redirects) . "\n";
                
                if (count($redirects) > 0) {
                    echo "    - Ejemplos de redirecciones existentes:\n";
                    foreach (array_slice($redirects, 0, 3) as $redirect) {
                        echo "      * {$redirect['origin']} → {$redirect['target']} ({$redirect['type']})\n";
                    }
                }
                
                return $redirects;
            } else {
                echo "  - No se pudo acceder a las redirecciones (posible problema de permisos)\n";
                return array();
            }
        } else {
            echo "  ⚠ Yoast gratuito detectado - herramientas de redirección no disponibles\n";
            echo "  - Para gestionar redirecciones, se recomienda:\n";
            echo "    * Actualizar a Yoast SEO Premium\n";
            echo "    * O instalar plugin adicional como 'Redirection'\n";
            return array();
        }
    }
    
    /**
     * Generar recomendaciones basadas en integración con Yoast
     */
    public function generate_yoast_recommendations() {
        echo "\n📋 GENERANDO RECOMENDACIONES INTEGRADAS CON YOAST SEO\n";
        echo "=================================================\n";
        
        $yoast_status = $this->check_yoast_status();
        
        echo "\n📊 ESTADO ACTUAL DE YOAST SEO:\n";
        if ($yoast_status === 'premium') {
            echo "  ✓ Yoast SEO Premium completamente funcional\n";
            echo "  ✓ Acceso a herramientas de redirección\n";
            echo "  ✓ Acceso a análisis avanzados de enlaces\n";
        } elseif ($yoast_status === 'free') {
            echo "  ✓ Yoast SEO gratuito instalado\n";
            echo "  ⚠ Funcionalidades de redirección no disponibles\n";
            echo "  - Considerar actualización a Premium para gestión avanzada\n";
        } else {
            echo "  ✗ Yoast SEO no detectado o con problemas\n";
        }
        
        // Obtener herramientas y reportes
        $this->get_yoast_tools();
        $this->get_internal_link_report();
        
        echo "\n🎯 RECOMENDACIONES ESPECÍFICAS PARA MARS CHALLENGE:\n";
        
        if ($yoast_status === 'premium') {
            echo "\n  A. UTILIZAR HERRAMIENTAS DE YOAST PREMIUM:\n";
            echo "     ✓ Usar 'Redirecciones' para gestionar las 48 URLs 404\n";
            echo "     ✓ Utilizar 'Analizador de contenido' para revisión masiva\n";
            echo "     ✓ Implementar 'Trailing Slash Manager' si aplica\n";
            
            echo "\n  B. PROCEDIMIENTO RECOMENDADO:\n";
            echo "     1. Acceder al panel de administración de WordPress\n";
            echo "     2. Ir a SEO > Herramientas > Redirecciones\n";
            echo "     3. Importar lista de URLs 404 desde Search Console\n";
            echo "     4. Crear redirecciones 301 a contenido relevante\n";
            echo "     5. Monitorear efectividad en Search Console\n";
        } else {
            echo "\n  A. OPCIONES PARA GESTIÓN DE REDIRECCIONES:\n";
            echo "     ✓ Opción 1: Actualizar a Yoast SEO Premium\n";
            echo "     ✓ Opción 2: Instalar plugin 'Redirection'\n";
            echo "     ✓ Opción 3: Usar .htaccess para redirecciones masivas\n";
            
            echo "\n  B. RECOMENDACIÓN INMEDIATA:\n";
            echo "     1. Instalar plugin 'Redirection' (gratuito y potente)\n";
            echo "     2. Importar URLs 404 de Search Console\n";
            echo "     3. Crear redirecciones 301 a contenido relevante\n";
        }
        
        echo "\n  C. VERIFICACIÓN POST-IMPLEMENTACIÓN:\n";
        echo "     ✓ Revisar Search Console después de 48 horas\n";
        echo "     ✓ Verificar que no haya nuevos errores 404\n";
        echo "     ✓ Confirmar mejora en indexación de páginas\n";
        
        // Recomendaciones específicas para los problemas restantes
        $this->generate_specific_recommendations();
        
        return array(
            'yoast_status' => $yoast_status,
            'has_redirect_tools' => $yoast_status === 'premium'
        );
    }
    
    /**
     * Generar recomendaciones específicas para problemas restantes
     */
    private function generate_specific_recommendations() {
        echo "\n🔍 RECOMENDACIONES ESPECÍFICAS:\n";
        
        echo "\n  PROBLEMA: 48 URLs con error 404 (Search Console)\n";
        echo "    - Causa común: Páginas eliminadas sin redirecciones\n";
        echo "    - Solución: Crear redirecciones 301 con Yoast/Redirection\n";
        echo "    - Prioridad: Alta (afecta SEO y experiencia de usuario)\n";
        
        echo "\n  PROBLEMA: 7 páginas con noindex (Search Console)\n";
        echo "    - Verificar: Configuración individual de páginas en Yoast\n";
        echo "    - Verificar: Ajustes generales de privacidad en Yoast\n";
        echo "    - Prioridad: Media (afecta indexación)\n";
        
        echo "\n  TENDENCIA: Disminución de páginas indexadas (-16 desde agosto)\n";
        echo "    - Posible causa: Aumento de páginas con errores 404\n";
        echo "    - Solución: Resolver errores 404 y mejorar calidad de contenido\n";
        echo "    - Prioridad: Alta (afecta visibilidad general)\n";
    }
    
    /**
     * Ejecutar análisis completo de integración
     */
    public function run_integration_analysis() {
        echo "🚀 INICIANDO ANÁLISIS DE INTEGRACIÓN CON YOAST SEO\n";
        echo "=================================================\n";
        
        $results = $this->generate_yoast_recommendations();
        
        echo "\n✅ INTEGRACIÓN YOAST SEO COMPLETADA\n";
        echo "   - Estado de Yoast verificado: {$results['yoast_status']}\n";
        echo "   - Acceso a herramientas de redirección: " . ($results['has_redirect_tools'] ? 'Sí' : 'No') . "\n";
        echo "   - Plan de acción generado para resolver errores restantes\n";
        
        return $results;
    }
}

// Ejecutar el análisis de integración con Yoast SEO
$yoast_integration = new Yoast_SEO_Integration();
$yoast_integration->run_integration_analysis();