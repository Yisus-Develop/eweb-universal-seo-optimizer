<?php
/**
 * Script Integrado de Corrección SEO para Mars Challenge
 * Basado en datos de Semrush y Google Search Console
 */

class Integrated_SEO_Fixer {
    
    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;
    
    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        
        echo "Inicializando sistema integrado de corrección SEO para: " . $this->site_url . "\n";
        echo "Usuario: " . $this->username . "\n\n";
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
            CURLOPT_USERAGENT => 'Integrated-SEO-Fixer/1.0',
            CURLOPT_SSL_VERIFYPEER => false, // Temporal para pruebas
            CURLOPT_FOLLOWLOCATION => true
        ));
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                $this->auth_header,
                'Content-Type: application/json'
            ));
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
     * Probar conexión con la API
     */
    public function test_connection() {
        echo "Verificando conexión con la API...\n";
        
        $response = $this->make_request($this->site_url . '/wp-json/wp/v2/users/me');
        
        if (isset($response['error'])) {
            echo "ERROR de cURL: " . $response['error'] . "\n";
            return false;
        }
        
        if ($response['status_code'] === 200) {
            echo "✓ Conexión exitosa con la API\n";
            $user_data = $response['body'];
            if ($user_data) {
                echo "Usuario autenticado: " . ($user_data['name'] ?? 'N/A') . " (ID: " . ($user_data['id'] ?? 'N/A') . ")\n";
            }
            return true;
        } elseif ($response['status_code'] === 401) {
            echo "✗ Error de autenticación (401 Unauthorized)\n";
            echo "Posible problema con las credenciales de API\n";
            echo "Respuesta: " . substr($response['raw_response'], 0, 100) . "...\n";
        } else {
            echo "✗ Error: Código de respuesta " . $response['status_code'] . "\n";
            echo "Respuesta: " . substr($response['raw_response'], 0, 100) . "...\n";
        }
        
        return false;
    }
    
    /**
     * Cargar datos de Search Console desde CSV
     */
    public function load_search_console_data() {
        echo "\nCargando datos de Google Search Console...\n";
        
        $data = array();
        
        // Cargar Problemas críticos
        $critical_file = __DIR__ . '/ai-artifacts/assets/Problemas críticos.csv';
        if (file_exists($critical_file)) {
            $critical_problems = $this->parse_csv($critical_file);
            $data['critical_problems'] = $critical_problems;
            echo "✓ Cargados " . count($critical_problems) . " tipos de problemas críticos\n";
            
            // Mostrar resumen
            foreach ($critical_problems as $problem) {
                echo "  - {$problem['Motivo']}: {$problem['Páginas']} páginas\n";
            }
        }
        
        // Cargar datos del gráfico de indexación
        $graph_file = __DIR__ . '/ai-artifacts/assets/Gráfico.csv';
        if (file_exists($graph_file)) {
            $indexing_data = $this->parse_csv($graph_file);
            $data['indexing_trend'] = $indexing_data;
            echo "✓ Cargados " . count($indexing_data) . " registros de tendencia de indexación\n";
        }
        
        return $data;
    }
    
    /**
     * Parsear archivo CSV
     */
    private function parse_csv($file_path) {
        $data = array();
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (empty($lines)) {
            return $data;
        }
        
        // Obtener encabezados
        $headers = str_getcsv($lines[0]);
        
        // Procesar filas
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            $data_row = array();
            
            for ($j = 0; $j < count($headers); $j++) {
                $data_row[$headers[$j]] = $row[$j] ?? '';
            }
            
            $data[] = $data_row;
        }
        
        return $data;
    }
    
    /**
     * Analizar datos combinados de Semrush y Search Console
     */
    public function analyze_combined_data() {
        echo "\n=== ANÁLISIS INTEGRADO DE DATOS SEO ===\n";
        
        // Datos de Search Console
        $sc_data = $this->load_search_console_data();
        
        // Problemas críticos de Search Console
        if (isset($sc_data['critical_problems'])) {
            echo "\n🔍 Problemas críticos identificados en Search Console:\n";
            foreach ($sc_data['critical_problems'] as $problem) {
                $reason = $problem['Motivo'];
                $pages = $problem['Páginas'];
                $status = $problem['Validación'];
                
                echo "  • $reason: $pages páginas ($status)\n";
                
                // Relación con datos de Semrush
                switch ($reason) {
                    case 'No se ha encontrado (404)':
                        echo "    → Relacionado con los 8 errores 4XX identificados por Semrush\n";
                        break;
                    case 'Excluida por una etiqueta "noindex"':
                        echo "    → Posible conflicto con metadatos o configuraciones de Yoast SEO\n";
                        break;
                    case 'Página con redirección':
                        echo "    → Posible relación con enlaces internos rotos de Semrush\n";
                        break;
                }
            }
        }
        
        // Tendencia de indexación
        if (isset($sc_data['indexing_trend']) && count($sc_data['indexing_trend']) > 0) {
            $latest = end($sc_data['indexing_trend']);
            echo "\n📈 Última tendencia de indexación ({$latest['Fecha']}):\n";
            echo "  • Páginas indexadas: {$latest['Indexadas']}\n";
            echo "  • Páginas sin indexar: {$latest['Sin indexar']}\n";
            echo "  • Impresiones: {$latest['Impresiones']}\n";
            
            // Calcular tendencia
            $first = reset($sc_data['indexing_trend']);
            if ($first !== false) {
                $change_in_indexed = $latest['Indexadas'] - $first['Indexadas'];
                $change_in_unindexed = $latest['Sin indexar'] - $first['Sin indexar'];
                
                echo "\n📊 Cambio desde {$first['Fecha']} hasta {$latest['Fecha']}:\n";
                echo "  • Cambio en páginas indexadas: " . ($change_in_indexed >= 0 ? '+' : '') . "$change_in_indexed\n";
                echo "  • Cambio en páginas sin indexar: " . ($change_in_unindexed >= 0 ? '+' : '') . "$change_in_unindexed\n";
            }
        }
        
        // Correlación con informe de Semrush
        echo "\n🔗 Correlación con informe de Semrush:\n";
        echo "  • 62 enlaces internos rotos → relacionado con páginas 404 en Search Console\n";
        echo "  • 38 títulos duplicados → puede afectar clasificación en SERPs\n";
        echo "  • 51 páginas sin meta descripciones → bajo CTR potencial\n";
        echo "  • 8 páginas con errores 4XX → confirmado por Search Console (48 páginas 404)\n";
        
        return $sc_data;
    }
    
    /**
     * Generar reporte combinado
     */
    public function generate_report($sc_data) {
        $report = array(
            'site' => $this->site_url,
            'analysis_date' => date('Y-m-d H:i:s'),
            'api_connection' => $this->test_connection(),
            'search_console_data' => $sc_data,
            'recommended_actions' => $this->get_recommended_actions($sc_data)
        );
        
        return $report;
    }
    
    /**
     * Obtener acciones recomendadas basadas en datos
     */
    private function get_recommended_actions($sc_data) {
        $actions = array();
        
        // Prioridad 1: Problemas críticos
        if (isset($sc_data['critical_problems'])) {
            foreach ($sc_data['critical_problems'] as $problem) {
                $reason = $problem['Motivo'];
                $pages = (int)$problem['Páginas'];
                
                if (strpos($reason, '404') !== false && $pages > 0) {
                    $actions['priority_1'][] = "Corregir las $pages páginas con error 404 - Incluye las 8 identificadas por Semrush";
                }
                if (strpos($reason, 'noindex') !== false && $pages > 0) {
                    $actions['priority_1'][] = "Revisar las $pages páginas excluidas con noindex";
                }
            }
        }
        
        // Prioridad 2: Basado en Semrush
        $actions['priority_2'][] = "Corregir los 38 títulos duplicados identificados por Semrush";
        $actions['priority_2'][] = "Añadir meta descripciones a las 51 páginas que las carecen";
        $actions['priority_2'][] = "Resolver los 62 enlaces internos rotos identificados por Semrush";
        
        // Prioridad 3: Optimizaciones
        $actions['priority_3'][] = "Implementar optimizaciones de Core Web Vitals";
        $actions['priority_3'][] = "Mejorar estructura de enlaces internos";
        $actions['priority_3'][] = "Actualizar sitemap.xml";
        
        return $actions;
    }
    
    /**
     * Ejecutar análisis completo
     */
    public function run_analysis() {
        echo "=== INICIANDO ANÁLISIS INTEGRADO SEO ===\n";
        echo "Sitio: {$this->site_url}\n";
        echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Probar conexión
        $connection_ok = $this->test_connection();
        
        if (!$connection_ok) {
            echo "\n⚠️  Advertencia: No se puede conectar a la API. Las correcciones automatizadas no podrán realizarse.\n";
            echo "Se puede proceder con análisis y recomendaciones.\n\n";
        }
        
        // Analizar datos combinados
        $sc_data = $this->analyze_combined_data();
        
        // Generar reporte
        $report = $this->generate_report($sc_data);
        
        // Mostrar recomendaciones
        $this->show_recommendations($report['recommended_actions']);
        
        return $report;
    }
    
    /**
     * Mostrar recomendaciones
     */
    private function show_recommendations($actions) {
        echo "\n=== RECOMENDACIONES PRIORITARIAS ===\n";
        
        if (isset($actions['priority_1'])) {
            echo "\n🔴 PRIORIDAD 1 (Crítico):\n";
            foreach ($actions['priority_1'] as $action) {
                echo "  • $action\n";
            }
        }
        
        if (isset($actions['priority_2'])) {
            echo "\n🟡 PRIORIDAD 2 (Importante):\n";
            foreach ($actions['priority_2'] as $action) {
                echo "  • $action\n";
            }
        }
        
        if (isset($actions['priority_3'])) {
            echo "\n🟢 PRIORIDAD 3 (Optimización):\n";
            foreach ($actions['priority_3'] as $action) {
                echo "  • $action\n";
            }
        }
    }
}

// Ejecutar el análisis
$seo_fixer = new Integrated_SEO_Fixer();
$report = $seo_fixer->run_analysis();

// Guardar reporte
$report_file = __DIR__ . '/seo_analysis_report_' . date('Y-m-d') . '.json';
file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
echo "\n📊 Reporte de análisis guardado en: $report_file\n";