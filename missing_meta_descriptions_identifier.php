<?php
/**
 * Script de Identificación de Metadescripciones Faltantes en Rank Math
 * para Mars Challenge - Busca descripciones que puedan haber sido pasadas por alto
 */

class Missing_Meta_Descriptions_Identifier {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando identificación de metadescripciones faltantes para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Missing-Meta-Descriptions-Identifier/1.0',
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
     * Obtener todas las páginas y posts con todos los campos disponibles
     */
    private function get_all_content_comprehensive() {
        echo "🔄 Obteniendo contenido completo con todos los campos...\n";

        $content = array(
            'pages' => array(), 
            'posts' => array(), 
            'categorias' => array(), 
            'etiquetas' => array()
        );

        // Obtener páginas con todos los campos
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num&context=edit&_embed");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                
                // Asegurarse que todos los campos estén disponibles
                foreach ($new_pages as &$page) {
                    if (!isset($page['meta'])) {
                        $page['meta'] = array();
                    }
                    $page['content_type'] = 'page';
                }
                
                $content['pages'] = array_merge($content['pages'], $new_pages);
                echo "   Obtenidas " . count($new_pages) . " páginas (página $page_num)\n";
                $page_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener posts con todos los campos
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num&context=edit&_embed");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                
                // Asegurarse que todos los campos estén disponibles
                foreach ($new_posts as &$post) {
                    if (!isset($post['meta'])) {
                        $post['meta'] = array();
                    }
                    $post['content_type'] = 'post';
                }
                
                $content['posts'] = array_merge($content['posts'], $new_posts);
                echo "   Obtenidos " . count($new_posts) . " posts (página $post_num)\n";
                $post_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener categorías
        $cat_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/categories?per_page=50&page=$cat_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_cats = $response['body'];
                
                foreach ($new_cats as &$cat) {
                    if (!isset($cat['meta'])) {
                        $cat['meta'] = array();
                    }
                    $cat['content_type'] = 'category';
                    $cat['title'] = array('rendered' => $cat['name']);
                }
                
                $content['categorias'] = array_merge($content['categorias'], $new_cats);
                echo "   Obtenidas " . count($new_cats) . " categorías (página $cat_num)\n";
                $cat_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener etiquetas
        $tag_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/tags?per_page=50&page=$tag_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_tags = $response['body'];
                
                foreach ($new_tags as &$tag) {
                    if (!isset($tag['meta'])) {
                        $tag['meta'] = array();
                    }
                    $tag['content_type'] = 'tag';
                    $tag['title'] = array('rendered' => $tag['name']);
                }
                
                $content['etiquetas'] = array_merge($content['etiquetas'], $new_tags);
                echo "   Obtenidas " . count($new_tags) . " etiquetas (página $tag_num)\n";
                $tag_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        echo "✅ Total - Páginas: " . count($content['pages']) . 
             ", Posts: " . count($content['posts']) . 
             ", Categorías: " . count($content['categorias']) . 
             ", Etiquetas: " . count($content['etiquetas']) . "\n";
        return $content;
    }

    /**
     * Buscar metadescripciones faltantes en Rank Math
     */
    public function identify_missing_meta_descriptions() {
        echo "\n🔍 Buscando metadescripciones faltantes en Rank Math...\n";

        $content = $this->get_all_content_comprehensive();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

        // Campos posibles para metadescripciones en Rank Math
        $rank_math_desc_fields = array(
            'rank_math_description',
            '_rank_math_description',
            'meta',           // El objeto meta puede contener el campo en algún subnivel
        );

        $missing_descriptions = array(
            'posts' => array(),
            'pages' => array(),
            'categorias' => array(),
            'etiquetas' => array()
        );

        $description_quality = array(
            'too_short' => array(),
            'too_long' => array(),
            'low_quality' => array()
        );

        foreach ($all_items as $item) {
            $id = $item['id'];
            $type = $item['content_type'] ?? $item['type'] ?? 'unknown';
            $title = isset($item['title']['rendered']) ? $item['title']['rendered'] : (isset($item['name']) ? $item['name'] : 'Unknown');
            $url = $item['link'] ?? '';
            $content_type = $item['content_type'] ?? 'unknown';

            // Extraer la descripción de Rank Math
            $rank_math_desc = '';
            
            // Buscar en meta directamente
            if (isset($item['meta']) && is_array($item['meta'])) {
                foreach ($item['meta'] as $key => $value) {
                    if ((stripos($key, 'rank') !== false && stripos($key, 'desc') !== false) || 
                        $key === 'rank_math_description' || $key === '_rank_math_description') {
                        if (is_string($value) && !empty(trim($value))) {
                            $rank_math_desc = $value;
                            break;
                        } elseif (is_array($value) && count($value) > 0) {
                            // Si es un array con un solo valor, usar ese
                            $first_val = reset($value);
                            if (is_string($first_val) && !empty(trim($first_val))) {
                                $rank_math_desc = $first_val;
                                break;
                            }
                        }
                    }
                }
            }

            // Si no hay descripción de Rank Math, agregar a la lista de faltantes
            if (empty($rank_math_desc)) {
                $item_info = array(
                    'id' => $id,
                    'type' => $type,
                    'title' => $title,
                    'url' => $url,
                    'content_type' => $content_type
                );

                // Extraer el contenido para ayudar con la generación de descripción
                $content_text = '';
                if (isset($item['content']['rendered'])) {
                    $content_text = strip_tags($item['content']['rendered']);
                }
                
                $excerpt_text = '';
                if (isset($item['excerpt']['rendered'])) {
                    $excerpt_text = strip_tags($item['excerpt']['rendered']);
                }

                $item_info['content_preview'] = substr($content_text, 0, 200) . (strlen($content_text) > 200 ? '...' : '');
                $item_info['excerpt'] = $excerpt_text;
                $item_info['content_length'] = strlen($content_text);
                
                $missing_descriptions[$content_type][] = $item_info;
            } else {
                // Si tiene descripción, verificar calidad
                $desc_length = strlen($rank_math_desc);
                
                if ($desc_length < 50) {
                    $description_quality['too_short'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $url,
                        'description' => $rank_math_desc,
                        'length' => $desc_length,
                        'content_type' => $content_type
                    );
                } elseif ($desc_length > 160) {
                    $description_quality['too_long'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $url,
                        'description' => $rank_math_desc,
                        'length' => $desc_length,
                        'content_type' => $content_type
                    );
                } else {
                    // Verificar calidad mínima (no sea genérica o repetitiva)
                    $low_quality = false;
                    
                    // Palabras que indican baja calidad
                    $generic_words = ['post', 'page', 'entry', 'article', 'content', 'more'];
                    $lower_desc = strtolower($rank_math_desc);
                    
                    foreach ($generic_words as $word) {
                        if (strpos($lower_desc, $word) !== false && strlen($lower_desc) < 80) {
                            $low_quality = true;
                            break;
                        }
                    }
                    
                    if ($low_quality) {
                        $description_quality['low_quality'][] = array(
                            'id' => $id,
                            'type' => $type,
                            'title' => $title,
                            'url' => $url,
                            'description' => $rank_math_desc,
                            'content_type' => $content_type
                        );
                    }
                }
            }
        }

        // Mostrar resultados
        echo "\n📊 RESULTADOS DE METADESCRIPCIONES FALTANTES:\n";
        
        $total_missing = 0;
        foreach ($missing_descriptions as $type => $items) {
            $count = count($items);
            $total_missing += $count;
            echo "   • $type sin descripción: $count\n";
        }
        echo "   • TOTAL de elementos sin descripción: $total_missing\n";

        // Mostrar calidad de descripciones existentes
        echo "\n📈 CALIDAD DE DESCRIPCIONES EXISTENTES:\n";
        echo "   • Demasiado cortas (<50 chars): " . count($description_quality['too_short']) . "\n";
        echo "   • Demasiado largas (>160 chars): " . count($description_quality['too_long']) . "\n";
        echo "   • Baja calidad (genéricas): " . count($description_quality['low_quality']) . "\n";

        // Mostrar muestras de elementos sin descripción (priorizando posts y páginas)
        if ($total_missing > 0) {
            echo "\n🔴 EJEMPLOS DE ELEMENTOS SIN DESCRIPCIÓN (priorizados por importancia):\n";
            
            // Combinar y ordenar por prioridad
            $all_missing = array_merge(
                array_map(function($item) { $item['priority'] = 4; return $item; }, $missing_descriptions['posts']),
                array_map(function($item) { $item['priority'] = 3; return $item; }, $missing_descriptions['pages']),
                array_map(function($item) { $item['priority'] = 2; return $item; }, $missing_descriptions['categorias']),
                array_map(function($item) { $item['priority'] = 1; return $item; }, $missing_descriptions['etiquetas'])
            );
            
            // Ordenar por prioridad (posts > pages > cats > tags)
            usort($all_missing, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });

            // Mostrar los primeros 15
            $limit = min(15, count($all_missing));
            for ($i = 0; $i < $limit; $i++) {
                $item = $all_missing[$i];
                echo "  {$i}. [{$item['content_type']}] ID: {$item['id']}\n";
                echo "     Título: {$item['title']}\n";
                echo "     URL: {$item['url']}\n";
                if (!empty($item['excerpt'])) {
                    echo "     Extracto: '" . substr($item['excerpt'], 0, 60) . "...'\n";
                }
                if ($item['content_length'] > 0) {
                    echo "     Contenido: " . $item['content_length'] . " chars\n";
                }
                if (!empty($item['content_preview'])) {
                    echo "     Previsualización: '" . $item['content_preview'] . "'\n";
                }
                echo "\n";
            }
            
            if (count($all_missing) > $limit) {
                echo "  ... y " . (count($all_missing) - $limit) . " más\n";
            }
        }

        // Mostrar ejemplos de descripciones de baja calidad
        if (count($description_quality['too_short']) > 0) {
            echo "\n⚠️  EJEMPLOS DE DESCRIPCIONES DEMASIADO CORTAS:\n";
            $limit = min(5, count($description_quality['too_short']));
            for ($i = 0; $i < $limit; $i++) {
                $item = $description_quality['too_short'][$i];
                echo "   • {$item['title']} ({$item['length']} chars): '" . substr($item['description'], 0, 50) . "...'\n";
            }
        }

        if (count($description_quality['too_long']) > 0) {
            echo "\n⚠️  EJEMPLOS DE DESCRIPCIONES DEMASIADO LARGAS:\n";
            $limit = min(5, count($description_quality['too_long']));
            for ($i = 0; $i < $limit; $i++) {
                $item = $description_quality['too_long'][$i];
                echo "   • {$item['title']} ({$item['length']} chars): '" . substr($item['description'], 0, 50) . "...'\n";
            }
        }

        if (count($description_quality['low_quality']) > 0) {
            echo "\n⚠️  EJEMPLOS DE DESCRIPCIONES DE BAJA CALIDAD:\n";
            $limit = min(5, count($description_quality['low_quality']));
            for ($i = 0; $i < $limit; $i++) {
                $item = $description_quality['low_quality'][$i];
                echo "   • {$item['title']}: '" . $item['description'] . "'\n";
            }
        }

        return array(
            'missing_descriptions' => $missing_descriptions,
            'description_quality' => $description_quality,
            'total_missing' => $total_missing
        );
    }

    /**
     * Generar sugerencias para crear metadescripciones
     */
    public function generate_description_suggestions($missing_analysis) {
        echo "\n💡 SUGERENCIAS PARA CREAR METADESCRIPCIONES:\n";

        $suggestions = array();
        
        // Agrupar todos los elementos faltantes
        $all_missing = array();
        foreach ($missing_analysis['missing_descriptions'] as $type => $items) {
            foreach ($items as $item) {
                $all_missing[] = $item;
            }
        }

        // Priorizar por tipo de contenido y longitud del contenido
        usort($all_missing, function($a, $b) {
            // Prioridad: posts > pages > cats > tags
            $priority_map = array('post' => 4, 'page' => 3, 'category' => 2, 'tag' => 1);
            $a_prio = isset($priority_map[$a['content_type']]) ? $priority_map[$a['content_type']] : 0;
            $b_prio = isset($priority_map[$b['content_type']]) ? $priority_map[$b['content_type']] : 0;

            if ($a_prio != $b_prio) {
                return $b_prio - $a_prio;
            }
            
            // Si misma prioridad, ordenar por longitud de contenido (más contenido = más importante)
            return $b['content_length'] - $a['content_length'];
        });

        // Generar sugerencias para los primeros 10 elementos más importantes
        $count = 0;
        foreach ($all_missing as $item) {
            if ($count >= 10) break;

            $suggested_desc = '';
            
            // Generar sugerencia basada en el contenido disponible
            if (!empty($item['excerpt'])) {
                // Usar excerpt si está disponible
                $suggested_desc = $this->create_description_from_text($item['excerpt']);
            } elseif ($item['content_length'] > 0 && !empty($item['content_preview'])) {
                // Usar contenido si excerpt no está disponible
                $suggested_desc = $this->create_description_from_text($item['content_preview']);
            } else {
                // Si no hay contenido, crear una genérica basada en el título
                $suggested_desc = $this->create_generic_description($item['title']);
            }

            $suggestions[] = array(
                'id' => $item['id'],
                'title' => $item['title'],
                'url' => $item['url'],
                'content_type' => $item['content_type'],
                'suggested_description' => $suggested_desc
            );

            $count++;
        }

        // Mostrar sugerencias
        foreach ($suggestions as $idx => $suggestion) {
            echo "  {$idx}. ID: {$suggestion['id']} ({$suggestion['content_type']})\n";
            echo "     Título: {$suggestion['title']}\n";
            echo "     Sugerencia: \"{$suggestion['suggested_description']}\"\n";
            echo "     URL: {$suggestion['url']}\n\n";
        }

        // Estadísticas y recomendaciones
        echo "\n📈 ESTADÍSTICAS ADICIONALES:\n";
        echo "   • Posts sin descripción: " . count($missing_analysis['missing_descriptions']['posts']) . "\n";
        echo "   • Páginas sin descripción: " . count($missing_analysis['missing_descriptions']['pages']) . "\n";
        echo "   • Categorías sin descripción: " . count($missing_analysis['missing_descriptions']['categorias']) . "\n";
        echo "   • Etiquetas sin descripción: " . count($missing_analysis['missing_descriptions']['etiquetas']) . "\n";

        echo "\n📋 RECOMENDACIONES:\n";
        echo "   • Priorizar la creación de descripciones para posts y páginas principales\n";
        echo "   • Usar el contenido existente para generar descripciones relevantes\n";
        echo "   • Mantener las descripciones entre 120-155 caracteres\n";
        echo "   • Incluir palabras clave relevantes pero mantener el texto natural\n";
        echo "   • Revisar las descripciones existentes que sean demasiado cortas o largas\n";

        return $suggestions;
    }

    /**
     * Crear descripción desde texto
     */
    private function create_description_from_text($text) {
        // Eliminar etiquetas HTML
        $clean_text = strip_tags($text);
        
        // Limitar longitud y asegurar que no se corte en medio de una palabra
        if (strlen($clean_text) > 155) {
            $clean_text = substr($clean_text, 0, 152) . '...';
        }
        
        // Asegurar que no exceda 160 caracteres
        if (strlen($clean_text) > 160) {
            $clean_text = substr($clean_text, 0, 157) . '...';
        }
        
        return $clean_text;
    }

    /**
     * Crear descripción genérica desde título
     */
    private function create_generic_description($title) {
        // Crear una descripción genérica basada en el título
        $desc = "Lea más sobre {$title} en Mars Challenge. Descubra toda la información y novedades sobre este tema.";
        
        // Limitar longitud
        if (strlen($desc) > 155) {
            $desc = substr($desc, 0, 152) . '...';
        }
        
        return $desc;
    }

    /**
     * Generar reporte completo
     */
    public function run_identification() {
        echo "🚀 INICIANDO IDENTIFICACIÓN DE METADESCRIPCIONES FALTANTES\n";
        echo "=========================================================\n";

        $missing_analysis = $this->identify_missing_meta_descriptions();
        $suggestions = $this->generate_description_suggestions($missing_analysis);

        // Generar reporte detallado
        $report = array(
            'site' => $this->site_url,
            'identification_date' => date('Y-m-d H:i:s'),
            'missing_analysis' => $missing_analysis,
            'suggestions' => $suggestions,
            'summary' => array(
                'total_missing' => $missing_analysis['total_missing'],
                'posts_missing' => count($missing_analysis['missing_descriptions']['posts']),
                'pages_missing' => count($missing_analysis['missing_descriptions']['pages']),
                'categorias_missing' => count($missing_analysis['missing_descriptions']['categorias']),
                'etiquetas_missing' => count($missing_analysis['missing_descriptions']['etiquetas']),
                'too_short' => count($missing_analysis['description_quality']['too_short']),
                'too_long' => count($missing_analysis['description_quality']['too_long']),
                'low_quality' => count($missing_analysis['description_quality']['low_quality'])
            )
        );

        $report_file = __DIR__ . '/missing_meta_descriptions_report_' . date('Y-m-d') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        echo "\n📋 Reporte de metadescripciones faltantes guardado en: $report_file\n";

        return $report;
    }
}

// Ejecutar la identificación de metadescripciones faltantes
$identifier = new Missing_Meta_Descriptions_Identifier();
$report = $identifier->run_identification();

echo "\n✅ IDENTIFICACIÓN DE METADESCRIPCIONES FALTANTES FINALIZADA\n";