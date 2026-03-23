<?php
/**
 * Script de Identificación Específica de Títulos Duplicados Faltantes
 * para Mars Challenge - Busca títulos duplicados que puedan haber sido pasados por alto
 */

class Missing_Duplicate_Titles_Identifier {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔍 Iniciando identificación de títulos duplicados faltantes para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Missing-Duplicate-Titles-Identifier/1.0',
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
     * Obtener todas las páginas, posts, categorías y etiquetas
     */
    private function get_all_content_detailed() {
        echo "🔄 Obteniendo contenido detallado...\n";

        $content = array(
            'pages' => array(), 
            'posts' => array(), 
            'categorias' => array(), 
            'etiquetas' => array(),
            'autores' => array()
        );

        // Obtener páginas (con más detalles)
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num&context=edit");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                
                // Agregar información adicional a cada página
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

        // Obtener posts (con más detalles)
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num&context=edit&_embed");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                
                // Agregar información adicional a cada post
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

        // Obtener categorías con información detallada
        $cat_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/categories?per_page=50&page=$cat_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_cats = $response['body'];
                
                foreach ($new_cats as &$cat) {
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

        // Obtener etiquetas con información detallada
        $tag_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/tags?per_page=50&page=$tag_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_tags = $response['body'];
                
                foreach ($new_tags as &$tag) {
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

        // Obtener autores con información detallada
        $author_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/users?per_page=20&page=$author_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_authors = $response['body'];
                
                foreach ($new_authors as &$author) {
                    $author['content_type'] = 'author';
                    $author['title'] = array('rendered' => $author['name']);
                }
                
                $content['autores'] = array_merge($content['autores'], $new_authors);
                echo "   Obtenidos " . count($new_authors) . " autores (página $author_num)\n";
                $author_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        echo "✅ Total - Páginas: " . count($content['pages']) . 
             ", Posts: " . count($content['posts']) . 
             ", Categorías: " . count($content['categorias']) . 
             ", Etiquetas: " . count($content['etiquetas']) . 
             ", Autores: " . count($content['autores']) . "\n";
        return $content;
    }

    /**
     * Buscar títulos duplicados con enfoque específico
     */
    public function identify_missing_duplicate_titles() {
        echo "\n🔍 Buscando títulos duplicados que hayan sido pasados por alto...\n";

        $content = $this->get_all_content_detailed();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas'], $content['autores']);

        // Análisis de títulos - considerando variaciones
        $title_variations_map = array(); // Mapeo de variaciones de títulos
        $exact_titles = array(); // Títulos exactos
        $canonical_titles = array(); // Títulos normalizados

        foreach ($all_items as $item) {
            // Extraer título principal
            if (isset($item['title']['rendered'])) {
                $title = $item['title']['rendered'];
            } elseif (isset($item['name'])) {
                $title = $item['name'];
            } else {
                continue; // Saltar si no tiene título
            }

            $id = $item['id'];
            $type = $item['content_type'] ?? $item['type'] ?? 'unknown';
            $url = $item['link'] ?? '';

            // Normalizar título para comparación
            $normalized_title = $this->normalize_title($title);

            // Guardar título exacto
            if (!isset($exact_titles[$title])) {
                $exact_titles[$title] = array(
                    'count' => 0,
                    'items' => array()
                );
            }
            $exact_titles[$title]['count']++;
            $exact_titles[$title]['items'][] = array(
                'id' => $id,
                'type' => $type,
                'url' => $url,
                'raw_title' => $title
            );

            // Guardar título normalizado
            if (!isset($canonical_titles[$normalized_title])) {
                $canonical_titles[$normalized_title] = array(
                    'count' => 0,
                    'items' => array()
                );
            }
            $canonical_titles[$normalized_title]['count']++;
            $canonical_titles[$normalized_title]['items'][] = array(
                'id' => $id,
                'type' => $type,
                'url' => $url,
                'raw_title' => $title
            );

            // Buscar variaciones de título (posibles duplicados no obvios)
            $variations = $this->find_title_variations($title);
            foreach ($variations as $var) {
                if (!isset($title_variations_map[$var])) {
                    $title_variations_map[$var] = array(
                        'count' => 0,
                        'items' => array(),
                        'original_titles' => array()
                    );
                }
                $title_variations_map[$var]['count']++;
                $title_variations_map[$var]['items'][] = array(
                    'id' => $id,
                    'type' => $type,
                    'url' => $url,
                    'raw_title' => $title
                );
                $title_variations_map[$var]['original_titles'][] = $title;
            }
        }

        // Encontrar duplicados exactos
        $exact_duplicates = array();
        foreach ($exact_titles as $title => $info) {
            if ($info['count'] > 1) {
                $exact_duplicates[$title] = $info;
            }
        }

        // Encontrar duplicados canónicos (normalizados)
        $canonical_duplicates = array();
        foreach ($canonical_titles as $title => $info) {
            if ($info['count'] > 1) {
                $canonical_duplicates[$title] = $info;
            }
        }

        // Encontrar duplicados por variación
        $variation_duplicates = array();
        foreach ($title_variations_map as $var => $info) {
            if ($info['count'] > 1) {
                $variation_duplicates[$var] = $info;
            }
        }

        // Buscar títulos que podrían haber sido pasados por alto
        // específicamente buscando patrones comunes de duplicación
        $potential_omitted_duplicates = $this->find_potential_omissions($all_items);

        echo "\n📊 RESULTADOS DETALLADOS DE TÍTULOS DUPLICADOS:\n";

        // Mostrar duplicados exactos
        if (count($exact_duplicates) > 0) {
            echo "\n🔴 Títulos duplicados exactos:\n";
            $count = 0;
            foreach ($exact_duplicates as $title => $info) {
                echo "  {$count}. '$title' ({$info['count']} veces)\n";
                foreach ($info['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
                $count++;
                if ($count >= 10) {
                    echo "     ... y " . (count($exact_duplicates) - $count) . " más\n";
                    break;
                }
            }
        }

        // Mostrar duplicados canónicos
        if (count($canonical_duplicates) > 0) {
            echo "\n🟡 Títulos duplicados normalizados:\n";
            $count = 0;
            foreach ($canonical_duplicates as $title => $info) {
                echo "  {$count}. '$title' ({$info['count']} veces)\n";
                foreach ($info['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), URL: {$item['url']} - Original: '{$item['raw_title']}'\n";
                }
                $count++;
                if ($count >= 10) {
                    echo "     ... y " . (count($canonical_duplicates) - $count) . " más\n";
                    break;
                }
            }
        }

        // Mostrar duplicados por variación
        if (count($variation_duplicates) > 0) {
            echo "\n🟠 Títulos duplicados por variación:\n";
            $count = 0;
            foreach ($variation_duplicates as $var => $info) {
                echo "  {$count}. Variación '$var' ({$info['count']} veces)\n";
                echo "     Títulos originales: " . implode(', ', array_unique($info['original_titles'])) . "\n";
                foreach ($info['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
                $count++;
                if ($count >= 10) {
                    echo "     ... y " . (count($variation_duplicates) - $count) . " más\n";
                    break;
                }
            }
        }

        // Mostrar duplicados potenciales omitidos
        if (count($potential_omitted_duplicates) > 0) {
            echo "\n🟢 Títulos duplicados potencialmente omitidos en correcciones anteriores:\n";
            $count = 0;
            foreach ($potential_omitted_duplicates as $group) {
                echo "  {$count}. Posible duplicado omitido ({$group['count']} veces)\n";
                foreach ($group['items'] as $item) {
                    echo "     - ID: {$item['id']} ({$item['type']}), '{$item['title']}' - URL: {$item['url']}\n";
                }
                echo "     - Razón: {$group['reason']}\n\n";
                $count++;
                if ($count >= 10) {
                    echo "     ... y " . (count($potential_omitted_duplicates) - $count) . " más\n";
                    break;
                }
            }
        } else {
            echo "\n✅ No se identificaron títulos duplicados potencialmente omitidos\n";
        }

        return array(
            'exact_duplicates' => $exact_duplicates,
            'canonical_duplicates' => $canonical_duplicates,
            'variation_duplicates' => $variation_duplicates,
            'potential_omitted_duplicates' => $potential_omitted_duplicates
        );
    }

    /**
     * Normalizar título para comparación
     */
    private function normalize_title($title) {
        // Convertir a minúsculas
        $normalized = strtolower($title);
        
        // Eliminar espacios extra y caracteres especiales
        $normalized = preg_replace('/\s+/', ' ', $normalized); // Reemplazar múltiples espacios con uno solo
        $normalized = trim($normalized);
        
        // Eliminar caracteres especiales excepto letras, números y espacios
        $normalized = preg_replace('/[^a-z0-9\s]/', '', $normalized);
        
        // Eliminar palabras comunes que no aportan significado para duplicados
        $words_to_remove = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = explode(' ', $normalized);
        $filtered_words = array();
        foreach ($words as $word) {
            if (!in_array(strtolower($word), $words_to_remove) && strlen($word) > 0) {
                $filtered_words[] = $word;
            }
        }
        $normalized = implode(' ', $filtered_words);
        
        return trim($normalized);
    }

    /**
     * Encontrar variaciones de título
     */
    private function find_title_variations($title) {
        $variations = array();
        
        // Variación 1: Sin espacios
        $variations[] = preg_replace('/\s+/', '', strtolower($title));
        
        // Variación 2: Solo primera letra de cada palabra
        $words = explode(' ', $title);
        $acronym = '';
        foreach ($words as $word) {
            $acronym .= strtoupper(substr($word, 0, 1));
        }
        if (strlen($acronym) > 1) {
            $variations[] = $acronym;
        }
        
        // Variación 3: Sin artículos
        $articles_removed = preg_replace('/\b(the|a|an)\b/i', '', $title);
        $articles_removed = preg_replace('/\s+/', ' ', trim($articles_removed));
        if ($articles_removed !== $title) {
            $variations[] = strtolower($articles_removed);
        }
        
        // Variación 4: Sin preposiciones comunes
        $preps_removed = preg_replace('/\b(in|on|at|to|for|of|with|by)\b/i', '', $title);
        $preps_removed = preg_replace('/\s+/', ' ', trim($preps_removed));
        if ($preps_removed !== $title) {
            $variations[] = strtolower($preps_removed);
        }
        
        return array_unique($variations);
    }

    /**
     * Buscar duplicados potencialmente omitidos
     */
    private function find_potential_omissions($all_items) {
        $omissions = array();
        
        // Agrupar por tipo y patrones comunes de duplicación
        $patterns = array();
        
        foreach ($all_items as $item) {
            if (isset($item['title']['rendered'])) {
                $title = $item['title']['rendered'];
            } elseif (isset($item['name'])) {
                $title = $item['name'];
            } else {
                continue;
            }
            
            // Buscar patrones comunes de duplicados que podrían haber sido pasados por alto
            $id = $item['id'];
            $type = $item['content_type'] ?? $item['type'] ?? 'unknown';
            $url = $item['link'] ?? '';
            
            // Patrón 1: Títulos que empiezan igual pero terminan con números
            if (preg_match('/^(.+?)\s+(\d+)$/', $title, $matches)) {
                $base = $matches[1];
                $number = $matches[2];
                
                if (!isset($patterns["numbered_$base"])) {
                    $patterns["numbered_$base"] = array(
                        'items' => array(),
                        'reason' => 'Títulos numerados con la misma base',
                        'count' => 0
                    );
                }
                $patterns["numbered_$base"]['items'][] = array(
                    'id' => $id,
                    'type' => $type,
                    'title' => $title,
                    'url' => $url
                );
                $patterns["numbered_$base"]['count']++;
            }
            
            // Patrón 2: Títulos con sufijos comunes
            $suffix_patterns = array(
                '/^(.+?)\s+\-?\s+(part|section|pagina|page)\s+\d+$/i',
                '/^(.+?)\s+\-?\s+(version|v)\.?\s*\d+$/i',
                '/^(.+?)\s+\(?\d+\)?$/',  // Números entre paréntesis o al final
                '/^(.+?)\s+\-?\s+(copy|duplicate)$/i'
            );
            
            foreach ($suffix_patterns as $pattern) {
                if (preg_match($pattern, $title, $matches)) {
                    $base = $matches[1];
                    $pattern_key = "suffix_$base";
                    
                    if (!isset($patterns[$pattern_key])) {
                        $patterns[$pattern_key] = array(
                            'items' => array(),
                            'reason' => 'Títulos con sufijos comunes (Part, Version, etc.)',
                            'count' => 0
                        );
                    }
                    $patterns[$pattern_key]['items'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $url
                    );
                    $patterns[$pattern_key]['count']++;
                    break; // Solo contar una vez por título
                }
            }
            
            // Patrón 3: Títulos que contienen el nombre del sitio
            if (stripos($title, 'mars') !== false && stripos($title, 'challenge') !== false) {
                $cleaned_title = preg_replace('/\s*[-_]\s*mars\s+challenge(\.space)?\s*$/i', '', $title);
                if ($cleaned_title !== $title) {
                    $pattern_key = "site_tag_$cleaned_title";
                    
                    if (!isset($patterns[$pattern_key])) {
                        $patterns[$pattern_key] = array(
                            'items' => array(),
                            'reason' => 'Títulos con nombre del sitio añadido',
                            'count' => 0
                        );
                    }
                    $patterns[$pattern_key]['items'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $url
                    );
                    $patterns[$pattern_key]['count']++;
                }
            }
        }
        
        // Filtrar patrones con más de 2 elementos (posibles duplicados reales)
        foreach ($patterns as $pattern_key => $pattern_info) {
            if ($pattern_info['count'] > 1) {
                $omissions[] = $pattern_info;
            }
        }
        
        return $omissions;
    }

    /**
     * Generar reporte de recomendaciones específicas
     */
    public function generate_specific_recommendations($duplicate_analysis) {
        echo "\n📋 RECOMENDACIONES ESPECÍFICAS PARA TÍTULOS DUPLICADOS:\n";

        $total_exact = count($duplicate_analysis['exact_duplicates']);
        $total_canonical = count($duplicate_analysis['canonical_duplicates']);
        $total_variation = count($duplicate_analysis['variation_duplicates']);
        $total_potential = count($duplicate_analysis['potential_omitted_duplicates']);

        echo "   • Duplicados exactos encontrados: $total_exact\n";
        echo "   • Duplicados normalizados encontrados: $total_canonical\n";
        echo "   • Duplicados por variación encontrados: $total_variation\n";
        echo "   • Duplicados potencialmente omitidos: $total_potential\n";

        if ($total_potential > 0) {
            echo "\n🔴 PRIORIDADES PARA CORRECCIÓN:\n";
            echo "   1. Revisar los $total_potential grupos de títulos potencialmente omitidos\n";
            echo "   2. Aplicar estrategia de diferenciación coherente para cada grupo\n";
            echo "   3. Verificar que la actualización se haga correctamente en Rank Math\n";
            echo "   4. Comprobar que no se repitan patrones que causaron duplicados previamente\n";
        }

        if ($total_exact > 0 || $total_canonical > 0) {
            echo "\n🟡 OPORTUNIDADES DE MEJORA:\n";
            echo "   • Implementar control preventivo de duplicados en proceso editorial\n";
            echo "   • Establecer plantillas de título para contenidos recurrentes\n";
            echo "   • Formatear títulos de manera consistente\n";
        }

        // Generar sugerencias de corrección
        if ($total_potential > 0) {
            echo "\n💡 SUGERENCIAS DE CORRECCIÓN PARA ELEMENTOS OMITIDOS:\n";
            
            $sample_groups = array_slice($duplicate_analysis['potential_omitted_duplicates'], 0, 3);
            foreach ($sample_groups as $idx => $group) {
                echo "   Grupo " . ($idx + 1) . ": {$group['reason']}\n";
                echo "     • Aplicar diferenciación basada en contenido o contexto\n";
                echo "     • Ejemplo: \"Noticia A\" → \"Noticia A - Fecha/Resumen\"\n";
                echo "     • Verificar que el cambio se refleje en Rank Math\n";
            }
        }
    }

    /**
     * Ejecutar identificación completa
     */
    public function run_identification() {
        echo "🚀 INICIANDO IDENTIFICACIÓN DE TÍTULOS DUPLICADOS FALTANTES\n";
        echo "=========================================================\n";

        $duplicate_analysis = $this->identify_missing_duplicate_titles();
        $this->generate_specific_recommendations($duplicate_analysis);

        // Generar reporte detallado
        $report = array(
            'site' => $this->site_url,
            'identification_date' => date('Y-m-d H:i:s'),
            'duplicate_analysis' => $duplicate_analysis,
            'summary' => array(
                'exact_duplicates' => count($duplicate_analysis['exact_duplicates']),
                'canonical_duplicates' => count($duplicate_analysis['canonical_duplicates']),
                'variation_duplicates' => count($duplicate_analysis['variation_duplicates']),
                'potential_omitted_duplicates' => count($duplicate_analysis['potential_omitted_duplicates'])
            )
        );

        $report_file = __DIR__ . '/missing_duplicate_titles_report_' . date('Y-m-d') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        echo "\n📋 Reporte de títulos duplicados faltantes guardado en: $report_file\n";

        return $report;
    }
}

// Ejecutar la identificación de títulos duplicados faltantes
$identifier = new Missing_Duplicate_Titles_Identifier();
$report = $identifier->run_identification();

echo "\n✅ IDENTIFICACIÓN DE TÍTULOS DUPLICADOS FALTANTES FINALIZADA\n";