<?php
/**
 * Script para analizar directamente el HTML de páginas específicas
 * y verificar si los cambios de título y descripción se reflejan en las etiquetas meta
 */

class HTML_Meta_Analyzer {

    public function analyze_page_meta($url) {
        echo "🔍 ANALIZANDO HTML DE: $url\n";
        echo "========================================\n";

        // Obtener el contenido HTML de la página
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'HTML-Meta-Analyzer/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $html = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "✗ Error al obtener la página: $error\n";
            return false;
        }

        if ($http_code !== 200) {
            echo "✗ Error HTTP: $http_code\n";
            return false;
        }

        // Buscar título de página
        $title_pattern = '/<title>(.*?)<\/title>/is';
        $og_title_pattern = '/property=["\']og:title["\']\s+content=["\'](.*?)["\']/is';
        $og_desc_pattern = '/property=["\']og:description["\']\s+content=["\'](.*?)["\']/is';
        $meta_desc_pattern = '/name=["\']description["\']\s+content=["\'](.*?)["\']/is';
        $twitter_title_pattern = '/name=["\']twitter:title["\']\s+content=["\'](.*?)["\']/is';
        $twitter_desc_pattern = '/name=["\']twitter:description["\']\s+content=["\'](.*?)["\']/is';

        $title_found = false;
        $og_title_found = false;
        $og_desc_found = false;
        $meta_desc_found = false;
        $twitter_title_found = false;
        $twitter_desc_found = false;

        // Buscar título
        if (preg_match($title_pattern, $html, $matches)) {
            echo "✓ <title>: " . trim(html_entity_decode($matches[1])) . "\n";
            $title_found = true;
        } else {
            echo "✗ <title>: No encontrado\n";
        }

        // Buscar meta description
        if (preg_match($meta_desc_pattern, $html, $matches)) {
            echo "✓ <meta name='description'>: " . trim(html_entity_decode($matches[1])) . "\n";
            $meta_desc_found = true;
        } else {
            echo "✗ <meta name='description'>: No encontrado\n";
        }

        // Buscar Open Graph title
        if (preg_match($og_title_pattern, $html, $matches)) {
            echo "✓ <meta property='og:title'>: " . trim(html_entity_decode($matches[1])) . "\n";
            $og_title_found = true;
        } else {
            echo "✗ <meta property='og:title'>: No encontrado\n";
        }

        // Buscar Open Graph description
        if (preg_match($og_desc_pattern, $html, $matches)) {
            echo "✓ <meta property='og:description'>: " . trim(html_entity_decode($matches[1])) . "\n";
            $og_desc_found = true;
        } else {
            echo "✗ <meta property='og:description'>: No encontrado\n";
        }

        // Buscar Twitter Card title
        if (preg_match($twitter_title_pattern, $html, $matches)) {
            echo "✓ <meta name='twitter:title'>: " . trim(html_entity_decode($matches[1])) . "\n";
            $twitter_title_found = true;
        } else {
            echo "✗ <meta name='twitter:title'>: No encontrado\n";
        }

        // Buscar Twitter Card description
        if (preg_match($twitter_desc_pattern, $html, $matches)) {
            echo "✓ <meta name='twitter:description'>: " . trim(html_entity_decode($matches[1])) . "\n";
            $twitter_desc_found = true;
        } else {
            echo "✗ <meta name='twitter:description'>: No encontrado\n";
        }

        $found_any_meta = $title_found || $meta_desc_found || $og_title_found || $og_desc_found || $twitter_title_found || $twitter_desc_found;

        if (!$found_any_meta) {
            echo "\n⚠️  ADVERTENCIA: No se encontraron etiquetas meta. Esto podría deberse a:\n";
            echo "   - Uso de Elementor que maneja las meta tags dinámicamente\n";
            echo "   - Caché que no se ha actualizado\n";
            echo "   - Las meta tags se generan en tiempo de ejecución\n";
            echo "   - El plugin Rank Math tiene una configuración diferente\n";
        }

        return array(
            'title' => $title_found ? html_entity_decode($matches[1]) : null,
            'description' => $meta_desc_found ? html_entity_decode($matches[1]) : null,
            'og_title' => $og_title_found ? html_entity_decode($matches[1]) : null,
            'og_description' => $og_desc_found ? html_entity_decode($matches[1]) : null,
            'twitter_title' => $twitter_title_found ? html_entity_decode($matches[1]) : null,
            'twitter_description' => $twitter_desc_found ? html_entity_decode($matches[1]) : null,
            'html_snippet' => substr($html, 0, 2000) // Mostrar primeros 2000 caracteres para análisis
        );
    }

    /**
     * Analizar las páginas que actualizamos
     */
    public function analyze_updated_pages() {
        echo "🔍 ANALIZANDO PÁGINAS ACTUALIZADAS\n";
        echo "================================\n\n";

        $urls_to_check = array(
            'Inicio' => 'https://mars-challenge.com/',
            'Fuego' => 'https://mars-challenge.com/fuego/',
            'Registro' => 'https://mars-challenge.com/registro/',
            'Sobre Mars Challenge' => 'https://mars-challenge.com/sobre/mars-challenge/',
            'Cómo participar' => 'https://mars-challenge.com/participar/como-participar/'
        );

        $results = array();

        foreach ($urls_to_check as $name => $url) {
            echo "\n" . str_repeat("-", 60) . "\n";
            echo "Analizando: $name\n";
            echo "URL: $url\n";
            echo str_repeat("-", 60) . "\n";
            
            $result = $this->analyze_page_meta($url);
            $results[$url] = $result;
            
            if ($result) {
                echo "\nResumen para $name:\n";
                foreach ($result as $key => $value) {
                    if ($key !== 'html_snippet' && $value !== null) {
                        echo "  $key: " . (strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value) . "\n";
                    }
                }
            }
            echo "\n";
        }

        return $results;
    }
}

// Ejecutar el análisis
$analyzer = new HTML_Meta_Analyzer();
$results = $analyzer->analyze_updated_pages();

echo "✅ ANÁLISIS DE HTML COMPLETADO\n";
echo "Ahora puedes ver si los cambios se reflejan en las etiquetas meta de las páginas.\n";