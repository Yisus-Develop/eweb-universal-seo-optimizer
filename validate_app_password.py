#!/usr/bin/env python3
"""
Script Python para ayudar a crear y verificar Application Passwords de WordPress
"""

import requests
import base64
import json
from typing import Optional, Tuple

def create_auth_header(username: str, password: str) -> str:
    """Crea la cabecera de autenticación para WordPress API"""
    credentials = f"{username}:{password}"
    encoded_credentials = base64.b64encode(credentials.encode()).decode()
    return f"Basic {encoded_credentials}"

def test_wordpress_api_connection(site_url: str, username: str, app_password: str) -> Tuple[bool, dict]:
    """Prueba la conexión a la API de WordPress"""
    # Eliminar espacios de la contraseña de aplicación
    clean_password = app_password.replace(' ', '')
    auth_header = create_auth_header(username, clean_password)
    
    headers = {
        'Authorization': auth_header,
        'Content-Type': 'application/json'
    }
    
    try:
        response = requests.get(
            f"{site_url}/wp-json/wp/v2/users/me",
            headers=headers,
            timeout=10
        )
        
        return response.status_code == 200, {
            'status_code': response.status_code,
            'response': response.json() if response.content else {},
            'raw_response': response.text
        }
    except requests.exceptions.RequestException as e:
        return False, {'error': str(e)}

def validate_app_password_format(app_password: str) -> Tuple[bool, str]:
    """Valida el formato de la Application Password"""
    clean_password = app_password.replace(' ', '')
    
    # Las Application Passwords de WordPress deben tener 24 caracteres alfanuméricos
    if len(clean_password) == 24 and clean_password.isalnum():
        return True, "Formato válido de Application Password"
    elif len(clean_password) == 20 and clean_password.isalnum():
        return False, f"Longitud incorrecta: se esperaban 24 caracteres, se obtuvieron {len(clean_password)}"
    else:
        return False, f"Formato no válido: {clean_password}"

def generate_app_password_instructions():
    """Genera instrucciones para crear una Application Password válida"""
    instructions = """
    PASOS PARA GENERAR UNA APPLICATION PASSWORD VÁLIDA:
    
    1. ACCEDER AL PANEL DE ADMINISTRACIÓN DE WORDPRESS:
       - Ir a: https://marschallenge.space/wp-admin
       - Iniciar sesión con tus credenciales
    
    2. NAVEGAR A LA SECCIÓN DE PERFIL:
       - Ir a: Usuarios > Tu Perfil
       - O directamente: https://marschallenge.space/wp-admin/profile.php
    
    3. ENCONTRAR LA SECCIÓN 'APPLICATION PASSWORDS':
       - Desplazarse hacia abajo hasta encontrar 'Application Passwords'
       - Si no la ves, asegúrate de tener WordPress 5.6+ o instala el plugin correspondiente
    
    4. CREAR UNA NUEVA CONTRASEÑA:
       - Ingresar un nombre (ej: 'SEO Automation Tool')
       - Hacer clic en 'Add New Application Password'
    
    5. COPIAR LA CONTRASEÑA GENERADA:
       - La contraseña se mostrará en el formato: 'abcd efgh ijkl mnop qrst uvwx'
       - Copiar exactamente como se muestra (con espacios)
    
    6. PROBAR LAS CREDENCIALES:
       - Usar el nombre de usuario y la contraseña generada en este script
    
    FORMATO ESPERADO:
    - Longitud: 24 caracteres alfanuméricos (en grupos de 4 separados por espacios)
    - Ejemplo: 'abcd efgh ijkl mnop qrst uvwx'
    """
    return instructions

def main():
    print("🔍 Verificador de Application Passwords de WordPress")
    print("="*60)
    
    # Solicitar información al usuario
    site_url = input("URL del sitio WordPress (ej: https://marschallenge.space): ").strip()
    if not site_url:
        site_url = "https://marschallenge.space"
    
    username = input("Nombre de usuario: ").strip()
    if not username:
        username = "wmaster_cs4or9qs"  # Valor por defecto basado en la conversación anterior
    
    app_password = input("Application Password: ").strip()
    
    print(f"\n📝 Validando credenciales para: {site_url}")
    print(f"Usuario: {username}")
    print(f"Contraseña (longitud): {len(app_password)} caracteres")
    
    # Validar formato de la contraseña
    is_valid_format, format_msg = validate_app_password_format(app_password)
    print(f"\n📋 Validación de formato: {format_msg}")
    
    if not is_valid_format:
        print("\n❌ ADVERTENCIA: La contraseña no tiene el formato correcto para una Application Password.")
        print(generate_app_password_instructions())
        
        continue_anyway = input("\n¿Deseas intentar conectar de todas formas? (s/n): ").strip().lower()
        if continue_anyway != 's':
            return
    
    # Intentar conexión
    print(f"\n🔗 Intentando conexión a la API...")
    is_connected, response_data = test_wordpress_api_connection(site_url, username, app_password)
    
    if is_connected:
        print("✅ ¡Conexión exitosa!")
        if 'response' in response_data and 'name' in response_data['response']:
            user_name = response_data['response']['name']
            user_id = response_data['response']['id']
            print(f"👤 Usuario autenticado: {user_name} (ID: {user_id})")
    else:
        print("❌ Conexión fallida")
        print(f"📡 Código de estado: {response_data.get('status_code', 'N/A')}")
        
        if 'error' in response_data:
            print(f"🚫 Error de red: {response_data['error']}")
        else:
            print(f"💬 Mensaje de error: {response_data.get('raw_response', 'N/A')[:200]}...")
        
        print("\n" + generate_app_password_instructions())

if __name__ == "__main__":
    main()