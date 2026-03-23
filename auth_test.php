<?php
/**
 * Script de verificación de autenticación con la API de WordPress
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV'; // Application Password actualizado

// Probar diferentes formatos de autenticación
echo "Probando autenticación con la API de: $site_url\n";
echo "Usuario: $username\n\n";

// Método 1: Con la contraseña tal cual (puede tener espacios)
echo "Método 1: Autenticación con la contraseña completa tal cual\n";
$auth_header1 = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password);

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/users/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array($auth_header1),
    CURLOPT_TIMEOUT => 10
));

$response1 = curl_exec($ch);
$http_code1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error1 = curl_error($ch);
curl_close($ch);

if ($error1) {
    echo "Error de cURL: $error1\n\n";
} else {
    echo "Código de respuesta: $http_code1\n";
    if ($http_code1 == 200) {
        echo "✓ Autenticación exitosa con el método 1\n";
        $user_data = json_decode($response1, true);
        echo "Usuario ID: " . ($user_data['id'] ?? 'N/A') . "\n";
        echo "Nombre: " . ($user_data['name'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Autenticación fallida con el método 1\n";
        echo "Respuesta: " . substr($response1, 0, 200) . "...\n\n";
    }
}

// Método 2: Eliminar espacios de la contraseña
echo "\nMétodo 2: Autenticación eliminando espacios de la contraseña\n";
$app_password_no_spaces = str_replace(' ', '', $app_password);
$auth_header2 = 'Authorization: Basic ' . base64_encode($username . ':' . $app_password_no_spaces);

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/users/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array($auth_header2),
    CURLOPT_TIMEOUT => 10
));

$response2 = curl_exec($ch);
$http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error2 = curl_error($ch);
curl_close($ch);

if ($error2) {
    echo "Error de cURL: $error2\n\n";
} else {
    echo "Código de respuesta: $http_code2\n";
    if ($http_code2 == 200) {
        echo "✓ Autenticación exitosa con el método 2\n";
        $user_data = json_decode($response2, true);
        echo "Usuario ID: " . ($user_data['id'] ?? 'N/A') . "\n";
        echo "Nombre: " . ($user_data['name'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Autenticación fallida con el método 2\n";
        echo "Respuesta: " . substr($response2, 0, 200) . "...\n\n";
    }
}

// Método 3: Probar acceso sin autenticación para ver si el endpoint está accesible
echo "\nMétodo 3: Acceso al endpoint sin autenticación (solo para ver si responde)\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/users/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
));

$response3 = curl_exec($ch);
$http_code3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error3 = curl_error($ch);
curl_close($ch);

if ($error3) {
    echo "Error de cURL: $error3\n\n";
} else {
    echo "Código de respuesta sin autenticación: $http_code3\n";
    if ($http_code3 != 401) {
        echo "El endpoint responde con un código distinto a 401\n";
        echo "Respuesta: " . substr($response3, 0, 200) . "...\n";
    } else {
        echo "Endpoint requiere autenticación (código 401)\n";
    }
}

echo "\nMétodo 4: Verificar si el WordPress REST API está disponible\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
));

$api_info = curl_exec($ch);
$http_code4 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error4 = curl_error($ch);
curl_close($ch);

if ($error4) {
    echo "Error de cURL verificando API: $error4\n";
} else {
    echo "Código de respuesta de la API raíz: $http_code4\n";
    if ($http_code4 == 200) {
        echo "✓ WordPress REST API está disponible\n";
    } else {
        echo "✗ WordPress REST API no responde correctamente\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "La contraseña de aplicación recibida es: '$app_password'\n";
echo "Longitud de la contraseña con espacios: " . strlen($app_password) . "\n";
echo "Longitud de la contraseña sin espacios: " . strlen($app_password_no_spaces) . "\n";
echo "\nSi la autenticación sigue fallando, verifica:\n";
echo "1. Que las Application Passwords estén habilitadas en WordPress\n";
echo "2. Que las credenciales sean correctas\n";
echo "3. Que el usuario tenga los permisos adecuados\n";
echo "4. Que no haya espacios extra en la contraseña\n";