<?php
/**
 * Script de Auditoría SEO Completa con Enfoque en Rank Math para Mars Challenge
 * Identifica títulos duplicados y metadescripciones faltantes específicamente con Rank Math
 */

class Comprehensive_SEO_Audit_RankMath {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔬 Iniciando auditoría SEO completa con enfoque en Rank Math para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Comprehensive-SEO-Audit-RankMath/1.0',
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
     * Obtener todas las páginas y posts con datos de Rank Math
     */
    private function get_all_content_with_rankmath() {
        echo "🔄 Obteniendo páginas y posts con datos de Rank Math...\n";

        $content = array('pages' => array(), 'posts' => array(), 'categorias' => array(), 'etiquetas' => array());

        // Obtener páginas con datos personalizados
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num&_embed");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                
                // Agregar datos específicos de Rank Math si están disponibles
                foreach ($new_pages as &$page) {
                    // Asegurar que los campos de Rank Math estén disponibles
                    if (!isset($page['meta'])) {
                        $page['meta'] = array();
                    }
                }
                
                $content['pages'] = array_merge($content['pages'], $new_pages);
                echo "   Obtenidas " . count($new_pages) . " páginas (página $page_num)\n";
                $page_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener posts con datos personalizados
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num&_embed");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                
                // Agregar datos específicos de Rank Math si están disponibles
                foreach ($new_posts as &$post) {
                    if (!isset($post['meta'])) {
                        $post['meta'] = array();
                    }
                }
                
                $content['posts'] = array_merge($content['posts'], $new_posts);
                echo "   Obtenidos " . count($new_posts) . " posts (página $post_num)\n";
                $post_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener categorías con datos
        $cat_response = $this->make_request($this->site_url . "/wp-json/wp/v2/categories");
        if ($cat_response['status_code'] === 200 && !empty($cat_response['body'])) {
            $content['categorias'] = $cat_response['body'];
            echo "   Obtenidas " . count($content['categorias']) . " categorías\n";
        }

        // Obtener etiquetas con datos
        $tag_response = $this->make_request($this->site_url . "/wp-json/wp/v2/tags");
        if ($tag_response['status_code'] === 200 && !empty($tag_response['body'])) {
            $content['etiquetas'] = $tag_response['body'];
            echo "   Obtenidas " . count($content['etiquetas']) . " etiquetas\n";
        }

        echo "✅ Total - Páginas: " . count($content['pages']) . ", Posts: " . count($content['posts']) . 
             ", Categorías: " . count($content['categorias']) . 
             ", Etiquetas: " . count($content['etiquetas']) . "\n";
        return $content;
    }

    /**
     * Analizar títulos duplicados (enfoque Rank Math)
     */
    public function analyze_duplicate_titles() {
        echo "\n🔍 Analizando títulos duplicados (enfoque Rank Math)...\n";

        $content = $this->get_all_content_with_rankmath();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

        // Campos de Rank Math para títulos
        $rank_math_title_fields = array(
            'rank_math_title',           // Campo principal de Rank Math
            '_rank_math_title',          // Versión con guión bajo
        );

        // Analizar títulos normales y de Rank Math
        $normal_titles = array();
        $rankmath_titles = array();

        foreach ($all_items as $item) {
            // Verificar si es categoría o etiqueta
            if (isset($item['taxonomy'])) {
                $title = $item['name'] ?? '';
                $type = $item['taxonomy'];
                $id = $item['id'];
                $url = $item['link'] ?? '';
            } else {
                $title = $item['title']['rendered'] ?? '';
                $type = $item['type'] ?? 'page';
                $id = $item['id'];
                $url = $item['link'] ?? '';
            }

            // Título normal
            if (!empty($title)) {
                if (isset($normal_titles[$title])) {
                    $normal_titles[$title]['count']++;
                    $normal_titles[$title]['items'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $url
                    );
                } else {
                    $normal_titles[$title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $id,
                            'type' => $type,
                            'title' => $title,
                            'url' => $url
                        ))
                    );
                }
            }

            // Títulos específicos de Rank Math - buscar en campos meta
            $rankmath_title = '';
            if (isset($item['meta'])) {
                foreach ($rank_math_title_fields as $field) {
                    if (isset($item['meta'][$field]) && !empty($item['meta'][$field])) {
                        $rankmath_title = $item['meta'][$field];
                        break;
                    }
                }
            }

            // Si no se encontró en meta, verificar posibles campos de contenido
            if (empty($rankmath_title) && isset($item['yoast_head']) && strpos($item['yoast_head'], 'rank-math') !== false) {
                // Si está usando Rank Math, podría estar en otros campos
                // Esto es solo un indicador general ya que no tenemos el campo específico
            }

            if (!empty($rankmath_title) && $rankmath_title !== $title) {
                if (isset($rankmath_titles[$rankmath_title])) {
                    $rankmath_titles[$rankmath_title]['count']++;
                    $rankmath_titles[$rankmath_title]['items'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $rankmath_title,
                        'url' => $url
                    );
                } else {
                    $rankmath_titles[$rankmath_title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $id,
                            'type' => $type,
                            'title' => $rankmath_title,
                            'url' => $url
                        ))
                    );
                }
            }
        }

        $duplicate_normal = array();
        $duplicate_rankmath = array();

        // Encontrar títulos duplicados normales
        foreach ($normal_titles as $title => $info) {
            if ($info['count'] > 1) {
                $duplicate_normal[$title] = $info;
            }
        }

        // Encontrar títulos duplicados de Rank Math
        foreach ($rankmath_titles as $title => $info) {
            if ($info['count'] > 1) {
                $duplicate_rankmath[$title] = $info;
            }
        }

        echo "\n📊 RESULTADOS DE TÍTULOS DUPLICADOS (Rank Math):\n";

        if (count($duplicate_normal) > 0) {
            echo "\n🔴 Títulos normales duplicados encontrados:\n";
            $count = 0;
            foreach ($duplicate_normal as $title => $info) {
                echo "  {$count}. '$title' aparece {$info['count']} veces\n";
                foreach ($info['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
                $count++;
                if ($count >= 10) { // Limitar a 10 para no sobrecargar
                    echo "     ... y " . (count($duplicate_normal) - $count) . " más\n";
                    break;
                }
            }
        } else {
            echo "\n✅ No se encontraron títulos normales duplicados\n";
        }

        if (count($duplicate_rankmath) > 0) {
            echo "\n🟡 Títulos de Rank Math duplicados encontrados:\n";
            $count = 0;
            foreach ($duplicate_rankmath as $title => $info) {
                echo "  {$count}. '$title' aparece {$info['count']} veces\n";
                foreach ($info['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
                $count++;
                if ($count >= 10) { // Limitar a 10 para no sobrecargar
                    echo "     ... y " . (count($duplicate_rankmath) - $count) . " más\n";
                    break;
                }
            }
        } else {
            echo "\n⚠️  No se encontraron títulos de Rank Math duplicados específicamente,\n";
            echo "    pero es posible que se necesite acceder directamente a través de la API específica de Rank Math\n";
        }

        return array(
            'normal_duplicates' => $duplicate_normal,
            'rankmath_duplicates' => $duplicate_rankmath
        );
    }

    /**
     * Analizar metadescripciones faltantes (enfoque Rank Math)
     */
    public function analyze_missing_descriptions() {
        echo "\n🔍 Analizando metadescripciones faltantes (enfoque Rank Math)...\n";

        $content = $this->get_all_content_with_rankmath();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

        // Campos de Rank Math para descripciones
        $rank_math_desc_fields = array(
            'rank_math_description',           // Campo principal de Rank Math
            '_rank_math_description',          // Versión con guión bajo
        );

        $missing_descriptions = array();
        $empty_descriptions = array();

        foreach ($all_items as $item) {
            // Determinar tipo de contenido
            if (isset($item['taxonomy'])) {
                $type = $item['taxonomy'];
                $id = $item['id'];
                $name = $item['name'] ?? '';
                $url = $item['link'] ?? '';
            } else {
                $type = $item['type'] ?? 'page';
                $id = $item['id'];
                $name = $item['title']['rendered'] ?? '';
                $url = $item['link'] ?? '';
            }

            // Verificar descripcion de Rank Math
            $rankmath_desc = '';
            if (isset($item['meta'])) {
                foreach ($rank_math_desc_fields as $field) {
                    if (isset($item['meta'][$field])) {
                        $rankmath_desc = $item['meta'][$field];
                        break;
                    }
                }
            }

            // Verificar excerpt (como alternativa)
            if (isset($item['excerpt']['rendered'])) {
                $excerpt = trim(strip_tags($item['excerpt']['rendered']));
            } else {
                $excerpt = '';
            }

            if (empty($rankmath_desc)) {
                // Verificar si hay contenido en el cuerpo
                $has_content = false;
                if (isset($item['content']['rendered'])) {
                    $content_text = trim(strip_tags($item['content']['rendered']));
                    $has_content = !empty($content_text);
                }

                $missing_descriptions[] = array(
                    'id' => $id,
                    'type' => $type,
                    'title' => $name,
                    'url' => $url,
                    'excerpt' => $excerpt,
                    'has_content' => $has_content,
                    'rankmath_desc' => $rankmath_desc
                );
            } else {
                // Verificar si la descripción está vacía o es solo espacios
                if (trim($rankmath_desc) === '') {
                    $empty_descriptions[] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $name,
                        'url' => $url,
                        'rankmath_desc' => $rankmath_desc
                    );
                }
            }
        }

        echo "\n📊 RESULTADOS DE METADESCIPCIONES FALTANTES (Rank Math):\n";
        echo "  • Páginas/posts sin descripción de Rank Math: " . count($missing_descriptions) . "\n";
        echo "  • Páginas/posts con descripción de Rank Math vacía: " . count($empty_descriptions) . "\n";

        if (count($missing_descriptions) > 0) {
            echo "\n🔴 Páginas/posts sin descripción de Rank Math (de mayor a menor importancia):\n";
            
            // Ordenar por importancia: posts > páginas > categorías > etiquetas
            usort($missing_descriptions, function($a, $b) {
                $type_order = array('post' => 4, 'page' => 3, 'category' => 2, 'post_tag' => 1);
                $a_priority = isset($type_order[$a['type']]) ? $type_order[$a['type']] : 0;
                $b_priority = isset($type_order[$b['type']]) ? $type_order[$b['type']] : 0;
                
                if ($a_priority == $b_priority) {
                    // Si tienen la misma prioridad, ordenar por si tienen contenido
                    return $b['has_content'] - $a['has_content'];
                }
                return $b_priority - $a_priority;
            });

            $count = 0;
            foreach ($missing_descriptions as $item) {
                echo "  {$count}. [{$item['type']}] ID: {$item['id']}\n";
                echo "     Título: {$item['title']}\n";
                echo "     URL: {$item['url']}\n";
                if (!empty($item['excerpt'])) {
                    echo "     Extracto disponible: Sí (" . strlen($item['excerpt']) . " chars)\n";
                } else {
                    echo "     Extracto disponible: No\n";
                }
                if ($item['has_content']) {
                    echo "     Contenido principal: Sí\n";
                } else {
                    echo "     Contenido principal: No\n";
                }
                echo "\n";
                $count++;
                
                // Limitar a 20 resultados para no sobrecargar
                if ($count >= 20) {
                    echo "  ... y " . (count($missing_descriptions) - $count) . " más\n";
                    break;
                }
            }

            // Mostrar estadísticas adicionales
            $posts_without_desc = 0;
            $pages_without_desc = 0;
            foreach ($missing_descriptions as $item) {
                if ($item['type'] === 'post') {
                    $posts_without_desc++;
                } elseif ($item['type'] === 'page') {
                    $pages_without_desc++;
                }
            }

            echo "\n📈 Estadísticas adicionales:\n";
            echo "   • Posts sin descripción: $posts_without_desc\n";
            echo "   • Páginas sin descripción: $pages_without_desc\n";
        } else {
            echo "\n✅ No se encontraron páginas/posts sin descripción de Rank Math\n";
        }

        // Buscar posibles problemas de calidad en descripciones existentes
        $quality_issues = $this->analyze_description_quality($all_items);
        
        return array(
            'missing_descriptions' => $missing_descriptions,
            'empty_descriptions' => $empty_descriptions,
            'quality_issues' => $quality_issues
        );
    }

    /**
     * Analizar calidad de descripciones existentes
     */
    private function analyze_description_quality($all_items) {
        $quality_issues = array(
            'too_short' => array(),
            'too_long' => array(),
            'keyword_repetition' => array(),
            'low_engagement' => array()
        );

        $rank_math_desc_fields = array(
            'rank_math_description',           
            '_rank_math_description',          
        );

        foreach ($all_items as $item) {
            if (isset($item['taxonomy'])) {
                $type = $item['taxonomy'];
                $id = $item['id'];
                $name = $item['name'] ?? '';
                $url = $item['link'] ?? '';
            } else {
                $type = $item['type'] ?? 'page';
                $id = $item['id'];
                $name = $item['title']['rendered'] ?? '';
                $url = $item['link'] ?? '';
            }

            // Obtener descripción de Rank Math
            $rankmath_desc = '';
            if (isset($item['meta'])) {
                foreach ($rank_math_desc_fields as $field) {
                    if (isset($item['meta'][$field])) {
                        $rankmath_desc = $item['meta'][$field];
                        break;
                    }
                }
            }

            if (!empty($rankmath_desc)) {
                $desc_length = strlen($rankmath_desc);
                
                // Demasiado corto
                if ($desc_length < 50) {
                    $quality_issues['too_short'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $name,
                        'url' => $url,
                        'description' => $rankmath_desc,
                        'length' => $desc_length
                    );
                }
                
                // Demasiado largo
                if ($desc_length > 160) {
                    $quality_issues['too_long'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $name,
                        'url' => $url,
                        'description' => $rankmath_desc,
                        'length' => $desc_length
                    );
                }
            }
        }

        echo "\n🔍 Análisis de calidad de descripciones existentes:\n";
        echo "   • Descripciones demasiado cortas (<50 chars): " . count($quality_issues['too_short']) . "\n";
        echo "   • Descripciones demasiado largas (>160 chars): " . count($quality_issues['too_long']) . "\n";

        // Mostrar algunos ejemplos de problemas de calidad
        if (count($quality_issues['too_short']) > 0) {
            echo "\n⚠️  Ejemplos de descripciones demasiado cortas:\n";
            $limit = min(5, count($quality_issues['too_short']));
            for ($i = 0; $i < $limit; $i++) {
                $item = $quality_issues['too_short'][$i];
                echo "   • ID {$item['id']}: {$item['title']} ({$item['length']} chars) - '" . substr($item['description'], 0, 30) . "...'\n";
            }
        }

        if (count($quality_issues['too_long']) > 0) {
            echo "\n⚠️  Ejemplos de descripciones demasiado largas:\n";
            $limit = min(5, count($quality_issues['too_long']));
            for ($i = 0; $i < $limit; $i++) {
                $item = $quality_issues['too_long'][$i];
                echo "   • ID {$item['id']}: {$item['title']} ({$item['length']} chars) - '" . substr($item['description'], 0, 30) . "...'\n";
            }
        }

        return $quality_issues;
    }

    /**
     * Analizar tendencias y patrones en los datos de SEO
     */
    public function analyze_seo_patterns() {
        echo "\n📊 Analizando patrones y tendencias de SEO...\n";

        $content = $this->get_all_content_with_rankmath();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

        $analysis = array(
            'title_length_distribution' => array(),
            'desc_length_distribution' => array(),
            'most_common_words_in_titles' => array(),
            'most_common_words_in_descriptions' => array()
        );

        $rank_math_desc_fields = array(
            'rank_math_description',           
            '_rank_math_description',          
        );

        $all_title_words = array();
        $all_desc_words = array();

        foreach ($all_items as $item) {
            // Análisis de títulos
            $title = $item['title']['rendered'] ?? '';
            if (!empty($title)) {
                $title_length = strlen($title);
                $bucket = floor($title_length / 10) * 10; // Agrupar por decenas
                
                if (isset($analysis['title_length_distribution'][$bucket])) {
                    $analysis['title_length_distribution'][$bucket]++;
                } else {
                    $analysis['title_length_distribution'][$bucket] = 1;
                }

                // Extraer palabras clave
                $words = explode(' ', strtolower($title));
                foreach ($words as $word) {
                    $clean_word = preg_replace('/[^\w]/', '', $word);
                    if (strlen($clean_word) > 3 && !in_array($clean_word, ['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'day', 'get', 'has', 'him', 'his', 'how', 'its', 'may', 'new', 'now', 'old', 'see', 'two', 'who', 'boy', 'did', 'man', 'men', 'run', 'too'])) {
                        if (isset($all_title_words[$clean_word])) {
                            $all_title_words[$clean_word]++;
                        } else {
                            $all_title_words[$clean_word] = 1;
                        }
                    }
                }
            }

            // Análisis de descripciones
            $rankmath_desc = '';
            if (isset($item['meta'])) {
                foreach ($rank_math_desc_fields as $field) {
                    if (isset($item['meta'][$field])) {
                        $rankmath_desc = $item['meta'][$field];
                        break;
                    }
                }
            }

            if (!empty($rankmath_desc)) {
                $desc_length = strlen($rankmath_desc);
                $bucket = floor($desc_length / 20) * 20; // Agrupar por veintenas
                
                if (isset($analysis['desc_length_distribution'][$bucket])) {
                    $analysis['desc_length_distribution'][$bucket]++;
                } else {
                    $analysis['desc_length_distribution'][$bucket] = 1;
                }

                // Extraer palabras clave de descripciones
                $desc_words = explode(' ', strtolower($rankmath_desc));
                foreach ($desc_words as $word) {
                    $clean_word = preg_replace('/[^\w]/', '', $word);
                    if (strlen($clean_word) > 3 && !in_array($clean_word, ['the', 'and', 'for', 'are', 'but', 'not', 'you', 'all', 'can', 'had', 'her', 'was', 'one', 'our', 'out', 'day', 'get', 'has', 'him', 'his', 'how', 'its', 'may', 'new', 'now', 'old', 'see', 'two', 'who', 'boy', 'did', 'man', 'men', 'run', 'too'])) {
                        if (isset($all_desc_words[$clean_word])) {
                            $all_desc_words[$clean_word]++;
                        } else {
                            $all_desc_words[$clean_word] = 1;
                        }
                    }
                }
            }
        }

        echo "\n📈 Distribución de longitud de títulos:\n";
        ksort($analysis['title_length_distribution']);
        foreach ($analysis['title_length_distribution'] as $length => $count) {
            echo "   • {$length}-" . ($length + 9) . " chars: $count elementos\n";
        }

        echo "\n📈 Distribución de longitud de descripciones:\n";
        ksort($analysis['desc_length_distribution']);
        foreach ($analysis['desc_length_distribution'] as $length => $count) {
            echo "   • {$length}-" . ($length + 19) . " chars: $count elementos\n";
        }

        // Palabras más comunes en títulos
        arsort($all_title_words);
        $top_title_words = array_slice($all_title_words, 0, 10);
        echo "\n🔤 Palabras más comunes en títulos:\n";
        foreach ($top_title_words as $word => $count) {
            echo "   • $word: $count veces\n";
        }

        // Palabras más comunes en descripciones
        arsort($all_desc_words);
        $top_desc_words = array_slice($all_desc_words, 0, 10);
        echo "\n🔤 Palabras más comunes en descripciones:\n";
        foreach ($top_desc_words as $word => $count) {
            echo "   • $word: $count veces\n";
        }

        return $analysis;
    }

    /**
     * Ejecutar auditoría completa
     */
    public function run_comprehensive_audit() {
        echo "🚀 INICIANDO AUDITORÍA SEO COMPLETA CON ENFOQUE EN RANK MATH\n";
        echo "==========================================================\n";

        $title_analysis = $this->analyze_duplicate_titles();
        $description_analysis = $this->analyze_missing_descriptions();
        $pattern_analysis = $this->analyze_seo_patterns();

        // Generar reporte final
        echo "\n🎯 RESUMEN COMPLETO DE LA AUDITORÍA (Rank Math):\n";
        echo "   • Títulos normales duplicados: " . count($title_analysis['normal_duplicates']) . "\n";
        echo "   • Títulos de Rank Math duplicados: " . count($title_analysis['rankmath_duplicates']) . "\n";
        echo "   • Páginas/posts sin descripción de Rank Math: " . count($description_analysis['missing_descriptions']) . "\n";
        echo "   • Páginas/posts con descripción vacía de Rank Math: " . count($description_analysis['empty_descriptions']) . "\n";
        echo "   • Descripciones demasiado cortas: " . count($description_analysis['quality_issues']['too_short']) . "\n";
        echo "   • Descripciones demasiado largas: " . count($description_analysis['quality_issues']['too_long']) . "\n";

        // Detectar posibles omisiones de correcciones anteriores
        echo "\n🔍 POSIBLES ELEMENTOS PASADOS POR ALTO EN CORRECCIONES ANTERIORES:\n";
        
        $total_missing = count($description_analysis['missing_descriptions']);
        $total_duplicates = count($title_analysis['normal_duplicates']) + count($title_analysis['rankmath_duplicates']);
        
        if ($total_missing > 50) {
            echo "   • Hay $total_missing páginas sin descripción de Rank Math - podría haber habido omisiones en la automatización\n";
        }
        
        if ($total_duplicates > 5) {
            echo "   • Hay $total_duplicates títulos duplicados - posiblemente no todos hayan sido corregidos\n";
        }

        // Recomendaciones específicas
        echo "\n💡 RECOMENDACIONES ESPECÍFICAS:\n";
        echo "   • Priorizar páginas/posts sin descripción de Rank Math (especialmente posts de contenido)\n";
        echo "   • Revisar títulos duplicados identificados para aplicar correcciones manuales si es necesario\n";
        echo "   • Evaluar la calidad de las descripciones existentes (longitud, palabras clave)\n";
        echo "   • Considerar la implementación de plantillas de título/descripción para categorías comunes\n";

        // Guardar resultados en un archivo
        $report = array(
            'site' => $this->site_url,
            'analysis_date' => date('Y-m-d H:i:s'),
            'title_analysis' => $title_analysis,
            'description_analysis' => $description_analysis,
            'pattern_analysis' => $pattern_analysis,
            'summary' => array(
                'duplicate_normal_titles' => count($title_analysis['normal_duplicates']),
                'duplicate_rankmath_titles' => count($title_analysis['rankmath_duplicates']),
                'missing_descriptions' => count($description_analysis['missing_descriptions']),
                'empty_descriptions' => count($description_analysis['empty_descriptions']),
                'short_descriptions' => count($description_analysis['quality_issues']['too_short']),
                'long_descriptions' => count($description_analysis['quality_issues']['too_long'])
            )
        );

        $report_file = __DIR__ . '/comprehensive_seo_audit_rankmath_report_' . date('Y-m-d') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        echo "\n📋 Reporte completo de Rank Math guardado en: $report_file\n";

        return $report;
    }
}

// Ejecutar la auditoría completa con enfoque en Rank Math
$audit = new Comprehensive_SEO_Audit_RankMath();
$report = $audit->run_comprehensive_audit();

echo "\n✅ AUDITORÍA SEO CON ENFOQUE EN RANK MATH FINALIZADA\n";