<?php
/**
 * Script de Auditoría SEO Completa para Mars Challenge
 * Identifica títulos duplicados y metadescripciones faltantes
 */

class Comprehensive_SEO_Audit {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔬 Iniciando auditoría SEO completa para: " . $this->site_url . "\n";
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
            CURLOPT_USERAGENT => 'Comprehensive-SEO-Audit/1.0',
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
     * Obtener todas las páginas y posts
     */
    private function get_all_content() {
        echo "🔄 Obteniendo páginas y posts...\n";

        $content = array('pages' => array(), 'posts' => array(), 'categorias' => array(), 'etiquetas' => array());

        // Obtener páginas
        $page_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages?per_page=50&page=$page_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_pages = $response['body'];
                $content['pages'] = array_merge($content['pages'], $new_pages);
                echo "   Obtenidas " . count($new_pages) . " páginas (página $page_num)\n";
                $page_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener posts
        $post_num = 1;
        do {
            $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts?per_page=50&page=$post_num");
            if ($response['status_code'] === 200 && !empty($response['body'])) {
                $new_posts = $response['body'];
                $content['posts'] = array_merge($content['posts'], $new_posts);
                echo "   Obtenidos " . count($new_posts) . " posts (página $post_num)\n";
                $post_num++;

                sleep(1);
            } else {
                break;
            }
        } while (count($response['body']) === 50);

        // Obtener categorías
        $cat_response = $this->make_request($this->site_url . "/wp-json/wp/v2/categories");
        if ($cat_response['status_code'] === 200 && !empty($cat_response['body'])) {
            $content['categorias'] = $cat_response['body'];
            echo "   Obtenidas " . count($content['categorias']) . " categorías\n";
        }

        // Obtener etiquetas
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
     * Analizar títulos duplicados (detallado)
     */
    public function analyze_duplicate_titles() {
        echo "\n🔍 Analizando títulos duplicados en detalle...\n";

        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

        // Analizar títulos normales
        $normal_titles = array();
        $yoast_titles = array();

        foreach ($all_items as $item) {
            // Verificar si es categoría o etiqueta
            if (isset($item['taxonomy'])) {
                $title = $item['name'] ?? '';
                $type = $item['taxonomy'];
                $id = $item['id'];
            } else {
                $title = $item['title']['rendered'] ?? '';
                $type = $item['type'] ?? 'page';
                $id = $item['id'];
            }

            // Título normal
            if (!empty($title)) {
                if (isset($normal_titles[$title])) {
                    $normal_titles[$title]['count']++;
                    $normal_titles[$title]['items'][] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $title,
                        'url' => $this->get_url_from_item($item)
                    );
                } else {
                    $normal_titles[$title] = array(
                        'count' => 1,
                        'items' => array(array(
                            'id' => $id,
                            'type' => $type,
                            'title' => $title,
                            'url' => $this->get_url_from_item($item)
                        ))
                    );
                }
            }

            // Título de Yoast (si es página o post)
            $yoast_title = '';
            if (isset($item['meta']['_yoast_wpseo_title'])) {
                $yoast_title = $item['meta']['_yoast_wpseo_title'];
                if (!empty($yoast_title) && $yoast_title !== $title) {
                    if (isset($yoast_titles[$yoast_title])) {
                        $yoast_titles[$yoast_title]['count']++;
                        $yoast_titles[$yoast_title]['items'][] = array(
                            'id' => $id,
                            'type' => $type,
                            'title' => $yoast_title,
                            'url' => $this->get_url_from_item($item)
                        );
                    } else {
                        $yoast_titles[$yoast_title] = array(
                            'count' => 1,
                            'items' => array(array(
                                'id' => $id,
                                'type' => $type,
                                'title' => $yoast_title,
                                'url' => $this->get_url_from_item($item)
                            ))
                        );
                    }
                }
            }
        }

        $duplicate_normal = array();
        $duplicate_yoast = array();

        // Encontrar títulos duplicados normales
        foreach ($normal_titles as $title => $info) {
            if ($info['count'] > 1) {
                $duplicate_normal[$title] = $info;
            }
        }

        // Encontrar títulos duplicados de Yoast
        foreach ($yoast_titles as $title => $info) {
            if ($info['count'] > 1) {
                $duplicate_yoast[$title] = $info;
            }
        }

        // Mostrar resultados
        echo "\n📊 RESULTADOS DE TÍTULOS DUPLICADOS:\n";

        if (count($duplicate_normal) > 0) {
            echo "\n🔴 Títulos normales duplicados encontrados:\n";
            foreach ($duplicate_normal as $title => $info) {
                echo "  • '$title' aparece {$info['count']} veces\n";
                foreach ($info['items'] as $item) {
                    echo "    - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
            }
        } else {
            echo "\n✅ No se encontraron títulos normales duplicados\n";
        }

        if (count($duplicate_yoast) > 0) {
            echo "\n🟡 Títulos de Yoast duplicados encontrados:\n";
            foreach ($duplicate_yoast as $title => $info) {
                echo "  • '$title' aparece {$info['count']} veces\n";
                foreach ($info['items'] as $item) {
                    echo "    - ID: {$item['id']} ({$item['type']}), URL: {$item['url']}\n";
                }
            }
        } else {
            echo "\n✅ No se encontraron títulos de Yoast duplicados\n";
        }

        return array(
            'normal_duplicates' => $duplicate_normal,
            'yoast_duplicates' => $duplicate_yoast
        );
    }

    /**
     * Analizar metadescripciones faltantes
     */
    public function analyze_missing_descriptions() {
        echo "\n🔍 Analizando metadescripciones faltantes...\n";

        $content = $this->get_all_content();
        $all_items = array_merge($content['pages'], $content['posts'], $content['categorias'], $content['etiquetas']);

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

            // Verificar metadescripción de Yoast
            if (isset($item['meta']['_yoast_wpseo_metadesc'])) {
                $yoast_desc = $item['meta']['_yoast_wpseo_metadesc'];
            } else {
                $yoast_desc = '';
            }

            // Verificar excerpt (como alternativa)
            if (isset($item['excerpt']['rendered'])) {
                $excerpt = trim(strip_tags($item['excerpt']['rendered']));
            } else {
                $excerpt = '';
            }

            if (empty($yoast_desc)) {
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
                    'yoast_desc' => $yoast_desc
                );
            } else {
                // Verificar si la descripción está vacía o es solo espacios
                if (trim($yoast_desc) === '') {
                    $empty_descriptions[] = array(
                        'id' => $id,
                        'type' => $type,
                        'title' => $name,
                        'url' => $url,
                        'yoast_desc' => $yoast_desc
                    );
                }
            }
        }

        echo "\n📊 RESULTADOS DE METADESCIPCIONES FALTANTES:\n";
        echo "  • Páginas/posts sin descripción de Yoast: " . count($missing_descriptions) . "\n";
        echo "  • Páginas/posts con descripción vacía: " . count($empty_descriptions) . "\n";

        if (count($missing_descriptions) > 0) {
            echo "\n🔴 Páginas/posts sin metadescripción (de mayor a menor importancia):\n";
            
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
        } else {
            echo "\n✅ No se encontraron páginas/posts sin metadescripción\n";
        }

        return array(
            'missing_descriptions' => $missing_descriptions,
            'empty_descriptions' => $empty_descriptions
        );
    }

    /**
     * Obtener URL desde el item
     */
    private function get_url_from_item($item) {
        if (isset($item['link'])) {
            return $item['link'];
        } elseif (isset($item['type']) && isset($item['slug'])) {
            if ($item['type'] === 'page') {
                return $this->site_url . '/' . $item['slug'];
            } else {
                return $this->site_url . '/?p=' . $item['id'];
            }
        } else {
            return $this->site_url . '/?id=' . $item['id'];
        }
    }

    /**
     * Ejecutar auditoría completa
     */
    public function run_comprehensive_audit() {
        echo "🚀 INICIANDO AUDITORÍA SEO COMPLETA\n";
        echo "==================================\n";

        $title_analysis = $this->analyze_duplicate_titles();
        $description_analysis = $this->analyze_missing_descriptions();

        // Generar reporte final
        echo "\n🎯 RESUMEN COMPLETO DE LA AUDITORÍA:\n";
        echo "   • Títulos normales duplicados: " . count($title_analysis['normal_duplicates']) . "\n";
        echo "   • Títulos de Yoast duplicados: " . count($title_analysis['yoast_duplicates']) . "\n";
        echo "   • Páginas/posts sin metadescripción: " . count($description_analysis['missing_descriptions']) . "\n";
        echo "   • Páginas/posts con metadescripción vacía: " . count($description_analysis['empty_descriptions']) . "\n";

        // Guardar resultados en un archivo
        $report = array(
            'site' => $this->site_url,
            'analysis_date' => date('Y-m-d H:i:s'),
            'title_analysis' => $title_analysis,
            'description_analysis' => $description_analysis,
            'summary' => array(
                'duplicate_normal_titles' => count($title_analysis['normal_duplicates']),
                'duplicate_yoast_titles' => count($title_analysis['yoast_duplicates']),
                'missing_descriptions' => count($description_analysis['missing_descriptions']),
                'empty_descriptions' => count($description_analysis['empty_descriptions'])
            )
        );

        $report_file = __DIR__ . '/comprehensive_seo_audit_report_' . date('Y-m-d') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        echo "\n📋 Reporte completo guardado en: $report_file\n";

        return $report;
    }
}

// Ejecutar la auditoría completa
$audit = new Comprehensive_SEO_Audit();
$report = $audit->run_comprehensive_audit();

echo "\n✅ AUDITORÍA SEO COMPLETA FINALIZADA\n";