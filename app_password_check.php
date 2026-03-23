<?php
/**
 * Script para verificar y probar la contraseña de aplicación de WordPress
 */

// Las Application Passwords de WordPress generalmente tienen 24 caracteres
// y están formateadas en grupos, como "xxxx xxxx xxxx xxxx xxxx"
// pero en la autenticación se usa como una sola cadena sin espacios

$app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
$app_password_clean = str_replace(' ', '', $app_password);

echo "Verificando contraseña de aplicación de WordPress\n";
echo "Contraseña original: '$app_password'\n";
echo "Contraseña sin espacios: '$app_password_clean'\n";
echo "Longitud con espacios: " . strlen($app_password) . "\n";
echo "Longitud sin espacios: " . strlen($app_password_clean) . "\n";

// Verificar formato típico de Application Password
if (strlen($app_password_clean) == 24 && ctype_alnum($app_password_clean)) {
    echo "✓ La contraseña tiene el formato típico de Application Password (24 caracteres alfanuméricos)\n";
} else {
    echo "✗ La contraseña NO tiene el formato típico de Application Password\n";
    echo "  Las Application Passwords de WordPress suelen tener 24 caracteres alfanuméricos\n";
}

// Las Application Passwords también pueden tener un formato específico
// con letras mayúsculas, minúsculas y números
if (preg_match('/^[a-zA-Z0-9]{24}$/', $app_password_clean)) {
    echo "✓ La contraseña cumple con el patrón alfanumérico estándar\n";
} else {
    echo "✗ La contraseña NO cumple con el patrón alfanumérico estándar\n";
}

echo "\nPara usar esta contraseña con la API de WordPress, debes:\n";
echo "1. Eliminar los espacios: " . $app_password_clean . "\n";
echo "2. Usarla en la autenticación básica como: usuario:contraseña_sin_espacios\n";
echo "3. Codificarla en base64 para la cabecera Authorization\n\n";

echo "Ejemplo de cabecera de autenticación:\n";
$auth_string = 'wmaster_cs4or9qs:' . $app_password_clean;
$auth_header = 'Authorization: Basic ' . base64_encode($auth_string);
echo $auth_header . "\n\n";

echo "Si la autenticación sigue fallando, posibles razones:\n";
echo "1. La Application Password ya no es válida (expiró o fue revocada)\n";
echo "2. El usuario no tiene los permisos adecuados\n";
echo "3. Las Application Passwords no están habilitadas en el sitio\n";
echo "4. El nombre de usuario es incorrecto\n";
echo "5. El sitio está detrás de Cloudflare o firewall que bloquea autenticación\n\n";

echo "Recomendación:\n";
echo "Acceder al sitio WordPress > Perfil de usuario > Application Passwords\n";
echo "y generar una nueva contraseña de aplicación para continuar con las pruebas.\n";