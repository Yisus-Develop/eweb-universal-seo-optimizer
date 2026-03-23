<?php
/**
 * Verificación HTML directo - Consulta páginas reales para verificar metadatos en HTML
 */

$pages_to_test = [
    ['id' => 4961, 'type' => 'instituciones', 'url' => 'https://mars-challenge.com/instituciones/federacion-mexicana-de-la-industria-aeroespacial/'],
    ['id' => 5476, 'type' => 'landing_paises', 'url' => 'https://mars-challenge.com/peru/'],
    ['id' => 4255, 'type' => 'quienes-sirven', 'url' => 'https://mars-challenge.com/quienes-sirven/jovenes-zers/'],
    ['id' => 648, 'type' => 'testimonios', 'url' => 'https://mars-challenge.com/testimonios/carolina-londono-pelaez/'],
    ['id' => 2656, 'type' => 'participa', 'url' => 'https://mars-challenge.com/participa/paises/'],
];

echo "=== VERIFICACIÓN HTML DIRECTO ===\n";
echo "Consultando páginas actualizadas para verificar metadatos en HTML real\n\n";

foreach ($pages_to_test as $page) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Tipo: {$page['type']} | ID: {$page['id']}\n";
    echo "URL: {$page['url']}\n\n";
    
    $ch = curl_init($page['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0');
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        echo "✗ Error HTTP $http_code\n\n";
        continue;
    }
    
    // Extraer título
    $title = '';
    if (preg_match('/<title>([^<]+)<\/title>/i', $html, $matches)) {
        $title = html_entity_decode(trim($matches[1]), ENT_QUOTES, 'UTF-8');
    }
    
    // Extraer meta description
    $description = '';
    if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $html, $matches)) {
        $description = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    
    // Extraer meta keywords
    $keywords = '';
    if (preg_match('/<meta\s+name=["\']keywords["\']\s+content=["\'](.*?)["\']/i', $html, $matches)) {
        $keywords = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
    }
    
    // Verificar Rank Math
    $has_rankmath = strpos($html, 'rank-math') !== false || strpos($html, 'RankMath') !== false;
    
    $title_len = mb_strlen($title);
    $desc_len = mb_strlen($description);
    
    echo "TÍTULO: $title\n";
    echo "  Length: $title_len chars ";
    if ($title_len >= 30 && $title_len <= 60) {
        echo "✓ OK\n";
    } else if ($title_len < 30) {
        echo "✗ Muy corto\n";
    } else {
        echo "✗ Muy largo\n";
    }
    
    echo "\nDESCRIPCIÓN: " . substr($description, 0, 120) . "...\n";
    echo "  Length: $desc_len chars ";
    if ($desc_len >= 120 && $desc_len <= 160) {
        echo "✓ OK\n";
    } else if ($desc_len < 120) {
        echo "✗ Muy corta\n";
    } else {
        echo "✗ Muy larga\n";
    }
    
    if (!empty($keywords)) {
        echo "\nKEYWORDS: $keywords\n";
        echo "  ✓ Presente\n";
    } else {
        echo "\nKEYWORDS: (vacío)\n";
        echo "  ✗ No encontrado\n";
    }
    
    echo "\nRANK MATH: " . ($has_rankmath ? "✓ Detectado" : "✗ No detectado") . "\n";
    echo "\n";
    
    usleep(500000); // 0.5s delay
}

echo "=== VERIFICACIÓN HTML COMPLETADA ===\n";
