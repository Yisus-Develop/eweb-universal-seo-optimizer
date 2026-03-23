<?php
/**
 * Fix SEO Compliance Issues - Corrección de longitudes y aplicación de keywords
 * 
 * Soluciona:
 * 1. Títulos fuera de rango (30-60 chars)
 * 2. Descripciones fuera de rango (120-160 chars)
 * 3. Keywords faltantes usando rank_math_focus_keyword
 */

// Configuración
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';

// Cargar análisis previo
$compliance_file = 'google_seo_compliance_2025-11-28_163223.json';
if (!file_exists($compliance_file)) {
    die("Error: No se encontró el archivo de cumplimiento\n");
}

$compliance_data = json_decode(file_get_contents($compliance_file), true);
$items = $compliance_data['items'];

// Filtrar solo items con problemas
$items_to_fix = array_filter($items, fn($item) => !$item['compliant']);

echo "=== CORRECCIÓN SEO COMPLIANCE ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Items a corregir: " . count($items_to_fix) . "\n\n";

function make_request($url, $username, $password, $method = 'GET', $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode("$username:$password"),
        'Content-Type: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $http_code, 'body' => json_decode($response, true)];
}

function update_rankmath_meta($site_url, $post_id, $meta, $username, $password) {
    $url = "$site_url/wp-json/rankmath/v1/updateMeta";
    $payload = [
        'objectID' => $post_id,
        'objectType' => 'post',
        'meta' => $meta
    ];
    
    return make_request($url, $username, $password, 'POST', $payload);
}

function fix_title($title, $type, $original_title) {
    $len = mb_strlen($title);
    
    // Si está muy largo, acortar
    if ($len > 60) {
        // Casos específicos
        if (strpos($title, 'Federación Mexicana de la Industria Aeroespacial') !== false) {
            if ($type === 'instituciones') {
                return 'FEMIA | Instituciones Mars Challenge';
            } else {
                return 'FEMIA | Empresas Aliadas Mars Challenge';
            }
        }
        
        if (strpos($title, 'Ciudades y gobiernos locales') !== false) {
            return 'Ciudades y Gobiernos | Quiénes Sirven Mars';
        }
        
        // Acortar genéricamente si aún está muy largo
        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '...';
        }
    }
    
    // Si está muy corto (principalmente logos)
    if ($len < 30 && $type === 'logos') {
        // Expandir logos con contexto
        $company = str_replace(' | Mars Challenge', '', $title);
        return "$company - Aliado | Mars Challenge";
    }
    
    return $title;
}

function fix_description($desc, $type, $title) {
    $len = mb_strlen($desc);
    
    // Si está muy larga, recortar
    if ($len > 160) {
        return mb_substr($desc, 0, 157) . '...';
    }
    
    // Si está muy corta, expandir
    if ($len < 120) {
        $additions = [
            'logos' => ' Colaborador oficial del proyecto Mars Challenge para la innovación educativa global.',
            'participa' => ' Descubre cómo participar y ser parte de esta iniciativa transformadora.',
            'tematicas_y_elemento' => ' Explora los desafíos y oportunidades de esta temática específica.',
            'quienes-sirven' => ' Conoce más sobre este perfil de participante y su rol en el reto.',
            'testimonios' => ' Su experiencia inspira a miles de participantes en todo el mundo.',
            'empresas_aliadas' => ' Partner estratégico en nuestro compromiso con la innovación sostenible.',
            'instituciones' => ' Socio institucional clave en nuestra red de innovación dual-planeta.',
            'landing_paises' => ' Tu país puede marcar la diferencia en este desafío global.',
            'country_page' => ' Explora las actividades y logros de Mars Challenge en este país.'
        ];
        
        $addition = $additions[$type] ?? ' Más información sobre Mars Challenge y sus iniciativas.';
        $new_desc = $desc . $addition;
        
        // Si se pasó, recortar
        if (mb_strlen($new_desc) > 160) {
            $new_desc = mb_substr($new_desc, 0, 157) . '...';
        }
        
        return $new_desc;
    }
    
    return $desc;
}

function generate_keyword($title, $type) {
    // Extraer palabra principal del título
    $base = strtolower(trim(preg_replace('/\s*[|—-].*$/', '', $title)));
    $base = preg_replace('/[^\p{L}\p{N}\s]/u', '', $base);
    
    // Agregar contexto del tipo
    $type_keywords = [
        'logos' => 'aliado',
        'instituciones' => 'institución',
        'empresas_aliadas' => 'empresa',
        'landing_paises' => 'país',
        'quienes-sirven' => 'participante',
        'participa' => 'participar',
        'tematicas_y_elemento' => 'temática',
        'testimonios' => 'testimonio',
        'country_page' => 'país'
    ];
    
    $keyword = $base . ', mars challenge';
    if (isset($type_keywords[$type])) {
        $keyword .= ', ' . $type_keywords[$type];
    }
    
    return $keyword;
}

$results = [];
$stats = ['success' => 0, 'errors' => 0, 'skipped' => 0];

foreach ($items_to_fix as $item) {
    echo "[{$stats['success']}/{count($items_to_fix)}] ID: {$item['id']} ({$item['type']})\n";
    
    $needs_update = false;
    $meta = [];
    
    // Verificar si necesita corrección de título
    $fixed_title = $item['title'];
    if ($item['title_length'] < 30 || $item['title_length'] > 60) {
        $fixed_title = fix_title($item['title'], $item['type'], $item['title']);
        $meta['rank_math_title'] = $fixed_title;
        $needs_update = true;
        echo "  Título: {$item['title_length']} → " . mb_strlen($fixed_title) . " chars\n";
    }
    
    // Verificar si necesita corrección de descripción
    $fixed_desc = $item['description'];
    if ($item['desc_length'] < 120 || $item['desc_length'] > 160) {
        $fixed_desc = fix_description($item['description'], $item['type'], $fixed_title);
        $meta['rank_math_description'] = $fixed_desc;
        $needs_update = true;
        echo "  Desc: {$item['desc_length']} → " . mb_strlen($fixed_desc) . " chars\n";
    }
    
    // Agregar keyword (todos necesitan)
    $keyword = generate_keyword($fixed_title, $item['type']);
    $meta['rank_math_focus_keyword'] = $keyword;
    $needs_update = true;
    echo "  Keyword: $keyword\n";
    
    if ($needs_update) {
        $response = update_rankmath_meta($site_url, $item['id'], $meta, $username, $password);
        
        if ($response['code'] === 200) {
            echo "  ✓ Actualizado\n\n";
            $stats['success']++;
            
            $results[] = [
                'id' => $item['id'],
                'type' => $item['type'],
                'url' => $item['url'],
                'original_title' => $item['title'],
                'fixed_title' => $fixed_title,
                'title_changed' => ($fixed_title !== $item['title']),
                'original_desc' => $item['description'],
                'fixed_desc' => $fixed_desc,
                'desc_changed' => ($fixed_desc !== $item['description']),
                'keyword' => $keyword,
                'status' => 'success'
            ];
        } else {
            echo "  ✗ Error {$response['code']}\n\n";
            $stats['errors']++;
            
            $results[] = [
                'id' => $item['id'],
                'type' => $item['type'],
                'status' => 'error',
                'error_code' => $response['code']
            ];
        }
        
        usleep(500000); // 0.5s delay
    } else {
        echo "  - Sin cambios necesarios\n\n";
        $stats['skipped']++;
    }
}

echo "\n=== RESUMEN CORRECCIONES ===\n";
echo "✓ Exitosos: {$stats['success']}\n";
echo "✗ Errores: {$stats['errors']}\n";
echo "- Omitidos: {$stats['skipped']}\n";

// Guardar resultados
$report = [
    'timestamp' => date('Y-m-d H:i:s'),
    'stats' => $stats,
    'results' => $results
];

$filename = 'seo_fixes_' . date('Y-m-d_His') . '.json';
file_put_contents($filename, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "\n✓ Reporte guardado: $filename\n";
echo "\n=== CORRECCIONES COMPLETADAS ===\n";
