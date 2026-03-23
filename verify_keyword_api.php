<?php
/**
 * Verificación de Focus Keyword - Mars Challenge
 * Consulta la API para verificar si la palabra clave se guardó
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'j5rp BZIf 8dIm kio1 PXNy y0Ao';
$auth_header = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

$post_id = 2898; // Agua

echo "=== VERIFICANDO KEYWORD PARA ID $post_id (Agua) ===\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . "/wp-json/wp/v2/pages/$post_id?context=edit", // context=edit para ver campos protegidos
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array($auth_header),
    CURLOPT_TIMEOUT => 30
));

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['meta']['rank_math_focus_keyword'])) {
    echo "✓ Campo 'rank_math_focus_keyword' encontrado:\n";
    echo "  Valor: " . $data['meta']['rank_math_focus_keyword'] . "\n";
} else {
    echo "✗ Campo 'rank_math_focus_keyword' NO encontrado en la respuesta.\n";
    echo "Meta fields disponibles:\n";
    print_r(array_keys($data['meta']));
}
