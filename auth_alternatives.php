<?php
/**
 * Script para probar conexión con diferentes métodos de autenticación
 * cuando Application Passwords no está disponible o no funciona
 */

$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
// NOTA: Esta contraseña no parece válida como Application Password de WordPress
$app_password = 'tLn6MQXGbCsSusyNyBca'; // La versión sin espacios

echo "Probando métodos alternativos de autenticación con: $site_url\n\n";

// Método 1: Probar si el sitio tiene el plugin de Basic Authentication
echo "Método 1: Probar con la contraseña como credencial directa (si se usa Basic Authentication plugin)\n";

$auth_string = $username . ':' . $app_password;
$auth_header = 'Authorization: Basic ' . base64_encode($auth_string);

echo "Cabecera de autenticación: $auth_header\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/users/me',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => array($auth_header),
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false, // Deshabilitar verificación SSL temporalmente para pruebas
    CURLOPT_USERAGENT => 'SEO-Fixer-Tool/1.0'
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "Error de cURL: $error\n";
} else {
    echo "Código de respuesta: $http_code\n";
    if ($http_code == 200) {
        echo "✓ Autenticación exitosa con credenciales directas\n";
        $user_data = json_decode($response, true);
        echo "Usuario ID: " . ($user_data['id'] ?? 'N/A') . "\n";
        echo "Nombre: " . ($user_data['name'] ?? 'N/A') . "\n";
    } else {
        echo "✗ Autenticación fallida con credenciales directas\n";
        $response_data = json_decode($response, true);
        $error_msg = $response_data['message'] ?? substr($response, 0, 100);
        echo "Mensaje de error: $error_msg\n\n";
        
        echo "POSIBLES SOLUCIONES:\n";
        echo "1. Verifica que el usuario y contraseña sean credenciales de WordPress válidas\n";
        echo "2. Asegúrate de que las Application Passwords estén habilitadas en WordPress\n";
        echo "3. Genera una nueva Application Password correcta en la página de Perfil\n";
        echo "4. O instala el plugin 'Basic Authentication' en WordPress para permitir autenticación simple\n";
        echo "\n";
    }
}

echo "\nMétodo 2: Verificar si se puede acceder a información pública del sitio\n";

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/pages?per_page=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✓ Se puede acceder a la API públicamente (páginas)\n";
} else {
    echo "✗ No se puede acceder a la API públicamente (páginas)\n";
}

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => $site_url . '/wp-json/wp/v2/posts?per_page=1',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo "✓ Se puede acceder a la API públicamente (posts)\n";
} else {
    echo "✗ No se puede acceder a la API públicamente (posts)\n";
}

echo "\n=== CONCLUSIÓN ===\n";
echo "Para continuar con la automatización de correcciones SEO:\n";
echo "1. Necesitas credenciales de API válidas (Application Passwords)\n";
echo "2. La contraseña actual no cumple el formato estándar de Application Password\n";
echo "3. Deberías generar una nueva Application Password en tu perfil de WordPress\n";
echo "\n";
echo "Pasos para generar una Application Password válida:\n";
echo "1. Accede a tu sitio WordPress como administrador\n";
echo "2. Ve a Usuarios > Tu Perfil\n";
echo "3. Baja hasta la sección 'Application Passwords'\n";
echo "4. Ingresa un nombre para la aplicación (por ejemplo 'SEO Automation')\n";
echo "5. Haz clic en 'Add New Application Password'\n";
echo "6. Copia la contraseña generada (24 caracteres alfanuméricos)\n";
echo "\n";
echo "La contraseña de Application Password correcta se verá como: 'abcd efgh ijkl mnop qrst uvwx'";