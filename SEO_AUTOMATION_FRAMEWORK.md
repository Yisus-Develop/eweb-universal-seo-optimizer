# Framework de Automatización SEO para WordPress + Rank Math

**Versión:** 2.0 (Unificada)  
**Fecha:** 28 de noviembre de 2025  
**Compatibilidad:** WordPress 5.0+, Rank Math 1.0+

---

## 📋 Índice

1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Flujo Completo de Trabajo](#flujo-completo-de-trabajo)
4. [Scripts y Herramientas](#scripts-y-herramientas)
5. [Adaptación a Nuevos Sitios](#adaptación-a-nuevos-sitios)
6. [Programación y Mantenimiento](#programación-y-mantenimiento)
7. [Casos de Uso](#casos-de-uso)

---

## 🎯 Resumen Ejecutivo

Este framework automatiza la optimización SEO de sitios WordPress usando Rank Math, cubriendo:

- ✅ **Detección automática** de problemas SEO (títulos, descripciones, keywords)
- ✅ **Corrección masiva** vía API de Rank Math
- ✅ **Verificación HTML** para confirmar cambios en producción
- ✅ **Soporte completo** para posts, pages y custom post types
- ✅ **Reportes detallados** en JSON y Markdown
- ✅ **Reutilizable** en múltiples sitios con mínima configuración

### Resultados Comprobados (marschallenge.space)
- **127 elementos optimizados** (51 pages/posts + 76 custom post types)
- **100% cumplimiento** estándares Google
- **0 errores** durante ejecución
- **Tiempo total:** ~2 horas (análisis + corrección + verificación)

---

## 🏗️ Arquitectura del Sistema

### Componentes Principales

```
┌─────────────────────────────────────────────────────┐
│                  ANÁLISIS INICIAL                   │
│  ┌──────────────┐  ┌────────────────────────────┐  │
│  │ WP REST API  │→ │ analyze_seo_titles.php     │  │
│  │ (Inventario) │  │ (Detección de problemas)   │  │
│  └──────────────┘  └────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│              CORRECCIÓN AUTOMÁTICA                  │
│  ┌──────────────────────────────────────────────┐  │
│  │ Rank Math API (/rankmath/v1/updateMeta)      │  │
│  ├──────────────────────────────────────────────┤  │
│  │ • bulk_update_titles_rm.php                  │  │
│  │ • bulk_update_v3_rm_api.php (descriptions)   │  │
│  │ • bulk_update_keywords_rm.php                │  │
│  │ • fix_seo_compliance.php (ajustes finos)     │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│            VERIFICACIÓN Y REPORTES                  │
│  ┌──────────────────────────────────────────────┐  │
│  │ • verify_html_direct.php (HTML real)         │  │
│  │ • analyze_google_seo_compliance.php          │  │
│  │ • scan_technical_seo.php (404s, noindex)     │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

### Estándares de Calidad

| Elemento | Rango Óptimo | Validación |
|----------|--------------|------------|
| **Título SEO** | 30-60 caracteres | Google trunca >60 |
| **Meta Description** | 120-160 caracteres | Google trunca >160 |
| **Focus Keyword** | Interno Rank Math | No se renderiza en HTML |
| **Branding** | Incluir nombre del sitio | Consistencia visual |

---

## 🔄 Flujo Completo de Trabajo

### Fase 1: Análisis Inicial (15-30 min)

```bash
# 1. Inventario completo del sitio
php analyze_seo_titles.php

# Output: seo_titles_analysis.json
# - Total de elementos
# - Problemas detectados por tipo
# - Longitudes actuales
```

**Qué detecta:**
- Títulos duplicados
- Títulos muy cortos (<30) o muy largos (>60)
- Descripciones faltantes o fuera de rango
- Falta de branding consistente
- Custom post types sin optimizar

### Fase 2: Corrección Automática (30-60 min)

#### Opción A: Corrección Básica (Pages + Posts)
```bash
# Metadescripciones
php bulk_update_v3_rm_api.php

# Títulos SEO
php bulk_update_titles_rm.php

# Keywords
php bulk_update_keywords_rm.php
```

#### Opción B: Corrección Avanzada (Incluye CPT)
```bash
# 1. Actualización inicial de CPT
php bulk_update_custom_posttypes_only.php

# 2. Ajustes finos de longitud
php fix_seo_compliance.php

# 3. Expansión de descripciones cortas
php final_expand_descriptions.php

# 4. Corrección de títulos específicos
php fix_short_titles.php
```

### Fase 3: Verificación (15-30 min)

```bash
# Verificación HTML directo (muestra)
php verify_html_direct.php

# Análisis completo de cumplimiento
php analyze_google_seo_compliance.php

# Escaneo técnico (404s, noindex)
php scan_technical_seo.php
```

### Fase 4: Documentación

```bash
# Generar reporte final
php analyze_google_seo_compliance.php > REPORTE_FINAL.json

# Agregar al timeline global
cd C:\Users\jesus\AI-Vault
$entry = @{
    timestamp = (Get-Date).ToUniversalTime().ToString("yyyy-MM-ddTHH:mm:ssZ")
    agent = "SEO-Framework"
    project = "tu-proyecto-seo"
    summary = "Optimización SEO completada"
    details = "X elementos optimizados, Y% cumplimiento Google"
} | ConvertTo-Json -Compress
echo $entry >> global\timeline.jsonl
```

---

## 🛠️ Scripts y Herramientas

### Scripts de Análisis

| Script | Propósito | Output |
|--------|-----------|--------|
| `analyze_seo_titles.php` | Inventario vía WP REST API | `seo_titles_analysis.json` |
| `analyze_google_seo_compliance.php` | Verificación HTML real | `google_seo_compliance_*.json` |
| `scan_technical_seo.php` | Detectar 404s y noindex | `technical_seo_report.json` |
| `scan_internal_links.php` | Enlaces rotos internos | `broken_links_report.json` |
| `analyze_image_alt.php` | Imágenes sin alt text | `missing_alt_text_report.json` |

### Scripts de Corrección

| Script | Función | Elementos Afectados |
|--------|---------|---------------------|
| `bulk_update_v3_rm_api.php` | Metadescripciones | Posts + Pages |
| `bulk_update_titles_rm.php` | Títulos SEO | Posts + Pages |
| `bulk_update_keywords_rm.php` | Focus Keywords | Posts + Pages |
| `bulk_update_custom_posttypes_only.php` | Optimización CPT | Custom Post Types |
| `fix_seo_compliance.php` | Ajustes de longitud | Todos |
| `final_expand_descriptions.php` | Expandir descripciones <120 | Todos |
| `fix_short_titles.php` | Corregir títulos <30 | Todos |

### Scripts de Verificación

| Script | Validación | Uso |
|--------|------------|-----|
| `verify_html_direct.php` | HTML renderizado | Muestra puntual |
| `verify_rankmath_meta.php` | Campos Rank Math | Verificación API |
| `verify_rm_gethead.php` | Endpoint getHead | Debug |

---

## 🔧 Adaptación a Nuevos Sitios

### Paso 1: Configuración Inicial

1. **Clonar carpeta del proyecto:**
```bash
cp -r projects/marschallenge-seo projects/tu-sitio-seo
cd projects/tu-sitio-seo
```

2. **Configurar variables de entorno:**
```powershell
# Crear archivo .env.ps1
$env:SITE_URL = 'https://tu-sitio.com'
$env:WP_USER = 'tu_usuario'
$env:WP_APP_PASS = 'tu_app_password'
```

3. **Generar App Password en WordPress:**
   - Ir a: `Usuarios > Perfil > Contraseñas de aplicación`
   - Crear nueva: "SEO Automation"
   - Copiar el password generado

### Paso 2: Detectar Post Types

```bash
# Ejecutar script de detección
php bulk_detect_posttypes.php
```

**Output esperado:**
```
Post types detectados:
- posts (3 items)
- pages (48 items)
- productos (25 items)
- servicios (12 items)
```

### Paso 3: Actualizar Scripts

Editar `analyze_seo_titles.php` línea ~30:

```php
$post_types = array(
    'posts',
    'pages',
    'productos',      // ← Agregar tus CPT
    'servicios',      // ← Agregar tus CPT
    // ... más tipos
);
```

### Paso 4: Personalizar Generadores

Editar funciones de generación en cada script:

```php
function generate_seo_title($item) {
    $title = $item['wp_title'];
    $url = $item['url'];
    
    // Reglas específicas de tu sitio
    if (strpos($url, '/productos/') !== false) {
        return "$title | Tu Marca - Productos Premium";
    }
    
    if (strpos($url, '/servicios/') !== false) {
        return "$title | Tu Marca - Servicios Profesionales";
    }
    
    // Default
    return "$title | Tu Marca";
}
```

### Paso 5: Ejecutar Flujo Completo

```bash
# 1. Análisis
php analyze_seo_titles.php

# 2. Corrección (revisar logs antes de continuar)
php bulk_update_titles_rm.php
php bulk_update_v3_rm_api.php
php bulk_update_keywords_rm.php

# 3. Verificación
php analyze_google_seo_compliance.php
```

---

## 📅 Programación y Mantenimiento

### Ejecución Automática Semanal

**Crear script de ejecución:** `C:\AI\seo-audit.ps1`

```powershell
# Cargar variables de entorno
. "C:\AI\.env.ps1"

# Navegar al proyecto
Push-Location "C:\Users\jesus\AI-Vault\projects\tu-sitio-seo"

# Crear log con timestamp
$logFile = "logs\audit_$(Get-Date -Format yyyy-MM-dd_HH-mm-ss).log"
New-Item -ItemType Directory -Force -Path (Split-Path $logFile) | Out-Null

# Ejecutar análisis
Write-Output "=== Iniciando auditoría SEO ===" | Tee-Object -FilePath $logFile
php analyze_seo_titles.php 2>&1 | Tee-Object -FilePath $logFile -Append
php analyze_google_seo_compliance.php 2>&1 | Tee-Object -FilePath $logFile -Append

# Correcciones automáticas (solo si hay problemas)
$analysis = Get-Content seo_titles_analysis.json | ConvertFrom-Json
if ($analysis.summary.missing_rm_title -gt 0) {
    Write-Output "Aplicando correcciones..." | Tee-Object -FilePath $logFile -Append
    php fix_seo_compliance.php 2>&1 | Tee-Object -FilePath $logFile -Append
}

# Verificación final
php analyze_google_seo_compliance.php 2>&1 | Tee-Object -FilePath $logFile -Append

Pop-Location

# Enviar notificación (opcional)
# Send-MailMessage -To "admin@tu-sitio.com" -Subject "SEO Audit Completado" -Body (Get-Content $logFile -Raw)
```

**Programar tarea:**

```powershell
$action = New-ScheduledTaskAction `
    -Execute "pwsh.exe" `
    -Argument "-NoProfile -WindowStyle Hidden -File C:\AI\seo-audit.ps1"

$trigger = New-ScheduledTaskTrigger `
    -Weekly `
    -DaysOfWeek Monday `
    -At 09:00

Register-ScheduledTask `
    -TaskName "SEO_Audit_TuSitio" `
    -Action $action `
    -Trigger $trigger `
    -Description "Auditoría SEO semanal automática" `
    -RunLevel Highest
```

### Checklist de Mantenimiento

**Semanal:**
- [ ] Revisar logs de ejecución automática
- [ ] Verificar tasa de éxito en reportes JSON
- [ ] Confirmar que no hay errores API

**Mensual:**
- [ ] Analizar KPIs de cumplimiento
- [ ] Actualizar plantillas de título/descripción si cambia branding
- [ ] Revisar nuevos custom post types
- [ ] Validar credenciales de API

**Trimestral:**
- [ ] Comparar con reportes de Search Console
- [ ] Ajustar estrategia de keywords
- [ ] Actualizar documentación

---

## 📊 Casos de Uso

### Caso 1: Sitio Nuevo (Sin Optimización)

**Situación:** 200 páginas sin metadescripciones ni títulos SEO.

**Solución:**
```bash
# 1. Análisis inicial
php analyze_seo_titles.php
# Resultado: 200 elementos sin optimizar

# 2. Corrección masiva
php bulk_update_titles_rm.php
php bulk_update_v3_rm_api.php
php bulk_update_keywords_rm.php

# 3. Verificación
php analyze_google_seo_compliance.php
# Resultado: 100% optimizado
```

**Tiempo estimado:** 1-2 horas  
**Resultado:** 200 elementos optimizados

### Caso 2: Sitio con Custom Post Types

**Situación:** E-commerce con productos, categorías, marcas.

**Solución:**
```bash
# 1. Detectar CPT
php bulk_detect_posttypes.php

# 2. Actualizar analyze_seo_titles.php con CPT detectados

# 3. Ejecutar flujo avanzado
php bulk_update_custom_posttypes_only.php
php fix_seo_compliance.php
```

**Tiempo estimado:** 2-3 horas  
**Resultado:** Posts + Pages + CPT optimizados

### Caso 3: Mantenimiento Periódico

**Situación:** Sitio ya optimizado, nuevas páginas cada semana.

**Solución:**
```bash
# Ejecutar script programado semanalmente
# Solo corrige elementos nuevos o modificados
php analyze_seo_titles.php
php fix_seo_compliance.php
```

**Tiempo estimado:** 15 minutos/semana  
**Resultado:** Optimización continua

### Caso 4: Migración de Plugin SEO

**Situación:** Cambio de Yoast a Rank Math.

**Solución:**
```bash
# 1. Exportar datos de Yoast (manual)
# 2. Importar a Rank Math (función nativa del plugin)
# 3. Verificar y corregir
php analyze_seo_titles.php
php fix_seo_compliance.php
```

**Tiempo estimado:** 3-4 horas  
**Resultado:** Migración completa sin pérdida de SEO

---

## 🎓 Mejores Prácticas

### Durante la Ejecución

1. **Backup primero:** Siempre hacer backup de la base de datos antes de correcciones masivas
2. **Ejecutar en staging:** Probar scripts en ambiente de prueba
3. **Revisar logs:** Verificar que no haya errores API antes de continuar
4. **Respetar rate limits:** Usar `usleep()` entre requests (300-500ms)
5. **Verificar caché:** Limpiar caché del sitio después de actualizaciones

### Generación de Contenido

1. **Títulos únicos:** Evitar duplicados, cada página debe ser identificable
2. **Descripciones atractivas:** Usar verbos de acción y beneficios claros
3. **Branding consistente:** Incluir nombre del sitio en todos los títulos
4. **Keywords relevantes:** Basadas en el contenido real de la página
5. **Longitud óptima:** 50-55 chars para títulos, 140-150 para descripciones

### Monitoreo

1. **KPIs clave:**
   - % de elementos con título optimizado
   - % de elementos con descripción optimizada
   - Tasa de éxito de API
   - Tiempo de ejecución

2. **Alertas:**
   - Más de 5% de errores API → Revisar credenciales
   - Títulos duplicados > 10 → Revisar lógica de generación
   - Tiempo de ejecución > 2x normal → Revisar rate limits

---

## 📚 Recursos Adicionales

### Documentación Oficial
- [WordPress REST API](https://developer.wordpress.org/rest-api/)
- [Rank Math API](https://rankmath.com/kb/rest-api/)
- [Google SEO Guidelines](https://developers.google.com/search/docs)

### Scripts de Referencia
- `README_SEO_AUTOMATION.md` - Documentación original
- `REPORTE_FINAL_SEO_COMPLIANCE.md` - Caso de estudio completo
- `TOOL_SUMMARY.md` - Resumen de herramientas

### Soporte
- Issues: Documentar en `ai-artifacts/issues/`
- Logs: Guardar en `logs/` con timestamp
- Timeline: Actualizar `global/timeline.jsonl`

---

## ✅ Checklist de Implementación

### Configuración Inicial
- [ ] Clonar proyecto base
- [ ] Configurar variables de entorno
- [ ] Generar App Password en WordPress
- [ ] Detectar post types del sitio
- [ ] Actualizar scripts con CPT específicos

### Primera Ejecución
- [ ] Ejecutar análisis inicial
- [ ] Revisar reporte JSON
- [ ] Personalizar funciones de generación
- [ ] Ejecutar correcciones en staging
- [ ] Verificar HTML renderizado
- [ ] Aplicar en producción

### Automatización
- [ ] Crear script de ejecución
- [ ] Programar tarea semanal
- [ ] Configurar logs
- [ ] Definir alertas
- [ ] Documentar en timeline

---

**Versión del Framework:** 2.0  
**Última actualización:** 28 de noviembre de 2025  
**Mantenido por:** AI-Vault Team  
**Licencia:** Uso interno / Reutilizable con atribución
