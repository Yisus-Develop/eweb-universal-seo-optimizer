# Automatización SEO (WordPress + Rank Math)

Este documento formaliza el proceso end-to-end para detectar, corregir y verificar metadatos SEO (título, descripción y focus keyword) en sitios WordPress usando Rank Math. Está listo para reutilizarse en otras webs y ejecutarse de forma periódica.

## Objetivos
- Detectar incumplimientos según estándares Google: Título 30-60, Descripción 120-160.
- Aplicar correcciones automáticas en custom post types y páginas.
- Verificar cambios en HTML real (post-render) y registrar KPIs.
- Mantener histórico (JSON/MD) y timeline global.

## Flujo End-to-End
1. Análisis inicial
   - `php analyze_seo_titles.php` (inventario vía REST WP)
   - `php analyze_google_seo_compliance.php` (verificación en HTML real)
2. Corrección automática
   - `php bulk_update_custom_posttypes_only.php` (primer pase CPT)
   - `php fix_seo_compliance.php` (ajusta longitudes + focus keyword)
   - `php final_expand_descriptions.php` (expande descripciones <120)
   - `php fix_short_titles.php` (correcciones puntuales de títulos <30)
3. Verificación final
   - `php verify_html_direct.php` (muestra ejemplos puntuales)
   - `php analyze_google_seo_compliance.php` (reporte integral JSON)
4. Documentación y timeline
   - `REPORTE_SEO_CUMPLIMIENTO.md` / `REPORTE_FINAL_SEO_COMPLIANCE.md`
   - Append a `global/timeline.jsonl`

## Estándares (Google)
- Título: 30-60 chars (ideal ~55)
- Descripción: 120-160 chars (ideal ~150)
- Keywords: Rank Math focus keyword (no se renderiza en HTML por diseño)

## Scripts (ubicación)
- `projects/marschallenge-seo/analyze_seo_titles.php`
- `projects/marschallenge-seo/analyze_google_seo_compliance.php`
- `projects/marschallenge-seo/bulk_update_custom_posttypes_only.php`
- `projects/marschallenge-seo/fix_seo_compliance.php`
- `projects/marschallenge-seo/final_expand_descriptions.php`
- `projects/marschallenge-seo/fix_short_titles.php`
- `projects/marschallenge-seo/verify_html_direct.php`

## Comandos (pwsh, Windows)
```powershell
# 1) Análisis
php analyze_seo_titles.php
php analyze_google_seo_compliance.php

# 2) Correcciones
php bulk_update_custom_posttypes_only.php
php fix_seo_compliance.php
php final_expand_descriptions.php
php fix_short_titles.php

# 3) Verificación
php verify_html_direct.php
php analyze_google_seo_compliance.php
```

## Variables para otras webs
Editar en cada script:
- `\$site_url = 'https://tu-sitio.com'`
- Credenciales `username` y `password` (App Password WordPress)
- `post_types` específicos del sitio (en `analyze_seo_titles.php` y derivados)

## Adaptación a post types
- Usar REST `GET /wp-json/wp/v2/{post_type}`
- Rank Math acepta `objectType: 'post'` para CPT.
- Ajustar funciones `generate_seo_title`, `generate_description` por tipo.

## KPIs y Reportes
- JSONs generados:
  - `seo_titles_analysis.json` (detección)
  - `google_seo_compliance_YYYY-MM-DD_HHMMSS.json` (verificación HTML)
  - `seo_fixes_YYYY-MM-DD_HHMMSS.json` (correcciones aplicadas)
- Métricas clave:
  - Total elementos
  - % con título OK, descripción OK
  - Pendientes por tipo
  - Éxitos / Errores de API

## Programación periódica (Task Scheduler)
1. Crear archivo `C:\AI\seo-run.ps1` con:
```powershell
Push-Location "C:\Users\jesus\AI-Vault\projects\marschallenge-seo"
php analyze_seo_titles.php
php analyze_google_seo_compliance.php
php fix_seo_compliance.php
php final_expand_descriptions.php
php fix_short_titles.php
php analyze_google_seo_compliance.php
Pop-Location
```
2. Programar tarea:
```powershell
$action = New-ScheduledTaskAction -Execute "pwsh.exe" -Argument "-File C:\AI\seo-run.ps1"
$trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Monday -At 09:00
Register-ScheduledTask -TaskName "SEO_Audit_MarsChallenge" -Action $action -Trigger $trigger -Description "Auditar y corregir SEO Rank Math"
```

## Timeline y Auditoría
- Anexar resumen al `global/timeline.jsonl`:
```powershell
cd C:\Users\jesus\AI-Vault
$entry = '{"timestamp":"2025-11-28T17:06:00Z","agent":"Copilot","source_format":"completed_v2","summary":"SEO Optimization Complete","details":"76 CPT optimizados; títulos y descripciones corregidos; keywords aplicadas (Rank Math)."}'
echo $entry >> global\timeline.jsonl
```

## Buenas prácticas
- Respetar caché: esperar 1-2s entre lotes si el sitio usa cache.
- No forzar meta `keywords` en HTML: Rank Math no lo renderiza; correcto.
- Hacer backup antes de grandes lotes.
- Mantener idempotencia: scripts pueden correrse múltiples veces sin duplicar.

## Checklist de mantenimiento
- [ ] Ejecutar análisis semanal/mensual
- [ ] Revisar KPIs y reportes JSON/MD
- [ ] Ajustar plantillas de título/descr por tipo si cambian
````markdown
# Automatización SEO (WordPress + Rank Math)

Este documento formaliza el proceso end-to-end para detectar, corregir y verificar metadatos SEO (título, descripción y focus keyword) en sitios WordPress usando Rank Math. Está listo para reutilizarse en otras webs y ejecutarse de forma periódica.

## Objetivos
- Detectar incumplimientos según estándares Google: Título 30-60, Descripción 120-160.
- Aplicar correcciones automáticas en custom post types y páginas.
- Verificar cambios en HTML real (post-render) y registrar KPIs.
- Mantener histórico (JSON/MD) y timeline global.

## Flujo End-to-End
1. Análisis inicial
    - `php analyze_seo_titles.php` (inventario vía REST WP)
    - `php analyze_google_seo_compliance.php` (verificación en HTML real)
2. Corrección automática
    - `php bulk_update_custom_posttypes_only.php` (primer pase CPT)
    - `php fix_seo_compliance.php` (ajusta longitudes + focus keyword)
    - `php final_expand_descriptions.php` (expande descripciones <120)
    - `php fix_short_titles.php` (correcciones puntuales de títulos <30)
3. Verificación final
    - `php verify_html_direct.php` (muestra ejemplos puntuales)
    - `php analyze_google_seo_compliance.php` (reporte integral JSON)
4. Documentación y timeline
    - `REPORTE_SEO_CUMPLIMIENTO.md` / `REPORTE_FINAL_SEO_COMPLIANCE.md`
    - Append a `global/timeline.jsonl`

## Estándares (Google)
- Título: 30-60 chars (ideal ~55)
- Descripción: 120-160 chars (ideal ~150)
- Keywords: Rank Math focus keyword (no se renderiza en HTML por diseño)

## Scripts (ubicación)
- `projects/marschallenge-seo/analyze_seo_titles.php`
- `projects/marschallenge-seo/analyze_google_seo_compliance.php`
- `projects/marschallenge-seo/bulk_update_custom_posttypes_only.php`
- `projects/marschallenge-seo/fix_seo_compliance.php`
- `projects/marschallenge-seo/final_expand_descriptions.php`
- `projects/marschallenge-seo/fix_short_titles.php`
- `projects/marschallenge-seo/verify_html_direct.php`
- `projects/marschallenge-seo/verify_rankmath_meta.php` (verificación meta plugin)

## Comandos (pwsh, Windows)
Los siguientes pasos asumen estar en la carpeta del proyecto. Opcional: exportar variables de entorno para no incrustar credenciales.
```powershell
# Definir variables (ejemplo)
$env:SITE_URL = 'https://marschallenge.space'
$env:WP_USER = 'wmaster_cs4or9qs'
$env:WP_APP_PASS = 'tu_app_password_aqui'

# 1) Análisis
php analyze_seo_titles.php
php analyze_google_seo_compliance.php

# 2) Correcciones (pases seguros, revisar logs)
php bulk_update_custom_posttypes_only.php
php fix_seo_compliance.php
php final_expand_descriptions.php
php fix_short_titles.php

# 3) Verificación final
php verify_html_direct.php
php verify_rankmath_meta.php
php analyze_google_seo_compliance.php
```

## Variables para otras webs
Editar en cada script o exportar como variables de entorno:
- `$site_url` o `env:SITE_URL` = 'https://tu-sitio.com'
- Credenciales `username` y `password` (App Password WordPress) en `env:WP_USER` / `env:WP_APP_PASS`
- `post_types` específicos del sitio (en `analyze_seo_titles.php` y derivados)

## Adaptación a post types
- Usar REST `GET /wp-json/wp/v2/{post_type}` para listar elementos
- Rank Math acepta `objectType: 'post'` para CPT en el endpoint `rankmath/v1/updateMeta`.
- Ajustar funciones `generate_seo_title`, `generate_description` por tipo.

## KPIs y Reportes
- JSONs generados:
   - `seo_titles_analysis.json` (detección)
   - `google_seo_compliance_YYYY-MM-DD_HHMMSS.json` (verificación HTML)
   - `seo_fixes_YYYY-MM-DD_HHMMSS.json` (correcciones aplicadas)
- Métricas clave (fórmulas):
   - `total` = cantidad de items analizados
   - `titles_ok_pct` = titles_ok / total * 100
   - `descriptions_ok_pct` = descriptions_ok / total * 100
   - `pending_by_type` = agrupado por `post_type` en los JSONs
   - `api_success_rate` = api_success / api_total * 100

Comandos rápidos con PowerShell para extraer métricas desde el JSON (ejemplo):
```powershell
$r = Get-Content google_seo_compliance_*.json | ConvertFrom-Json
[PSCustomObject]@{
   total = $r.total
   titles_ok_pct = [math]::Round(($r.titles_ok / $r.total) * 100,2)
   descriptions_ok_pct = [math]::Round(($r.descriptions_ok / $r.total) * 100,2)
}
```

## Exportar `rank_math_focus_keyword` (ejemplo)
Si necesita un listado audit-ready con los `focus keywords` guardados en Rank Math, use el script de verificación existente `verify_rankmath_meta.php`. Si desea crear un export simple, ejemplo de script PHP CLI (crear `export_rankmath_focus_keywords.php`):

```php
<?php
// export_rankmath_focus_keywords.php
// Requiere: php-cli y acceso al endpoint WP REST con credenciales (App Password)
$site = rtrim(getenv('SITE_URL') ?: 'https://marschallenge.space', '/');
$user = getenv('WP_USER');
$pass = getenv('WP_APP_PASS');

function req($url, $user, $pass) {
   $opts = [
      'http' => [
         'method' => 'GET',
         'header' => "Authorization: Basic " . base64_encode("$user:$pass") . "\r\n",
         'timeout' => 15
      ]
   ];
   $ctx = stream_context_create($opts);
   return @file_get_contents($url, false, $ctx);
}

$post_types = ['logos','quienes-sirven','participa','testimonios','tematicas_y_elemento'];
$out = [];
foreach ($post_types as $pt) {
   $page = 1;
   while (true) {
      $url = "$site/wp-json/wp/v2/$pt?per_page=100&page=$page";
      $raw = req($url, $user, $pass);
      if (!$raw) break;
      $items = json_decode($raw, true);
      if (!is_array($items) || count($items)===0) break;
      foreach ($items as $it) {
         $id = $it['id'];
         // Intento de leer meta expuesto; dependiendo del WP esta ruta puede variar
         $metaUrl = "$site/wp-json/rankmath/v1/getHead?url=".urlencode($site.'/?p='.$id);
         $headRaw = req($metaUrl, $user, $pass);
         $focus = null;
         if ($headRaw) {
            $h = json_decode($headRaw, true);
            if (isset($h['meta']) && is_array($h['meta'])) {
               foreach ($h['meta'] as $m) {
                  if (isset($m['name']) && $m['name']==='rank_math_focus_keyword') {
                     $focus = $m['content'];
                     break;
                  }
               }
            }
         }
         $out[] = ['post_type'=>$pt,'id'=>$id,'title'=>$it['title']['rendered'] ?? '', 'focus_keyword'=>$focus];
      }
      $page++;
   }
}
echo json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

// Uso: set env vars y ejecutar: php export_rankmath_focus_keywords.php > focus_keywords.json
```

Nota: la disponibilidad de meta via REST depende de la configuración del sitio y plugins. Use `verify_rankmath_meta.php` incluido si la ruta anterior no devuelve datos.

## Programación periódica (Task Scheduler) - ejemplo robusto
1. Crear archivo de ejecución `C:\AI\seo-run.ps1` (con rotación básica de logs):
```powershell
Push-Location "C:\Users\jesus\AI-Vault\projects\marschallenge-seo"
$log = "logs\seo-run_$(Get-Date -Format yyyy-MM-dd_HH-mm-ss).log"
New-Item -ItemType Directory -Force -Path (Split-Path $log)
php analyze_seo_titles.php 2>&1 | Tee-Object -FilePath $log
php analyze_google_seo_compliance.php 2>&1 | Tee-Object -FilePath $log -Append
php fix_seo_compliance.php 2>&1 | Tee-Object -FilePath $log -Append
php final_expand_descriptions.php 2>&1 | Tee-Object -FilePath $log -Append
php fix_short_titles.php 2>&1 | Tee-Object -FilePath $log -Append
php analyze_google_seo_compliance.php 2>&1 | Tee-Object -FilePath $log -Append
Pop-Location
```
2. Registrar tarea programada (ejemplo: semanal lunes 09:00):
```powershell
$action = New-ScheduledTaskAction -Execute "pwsh.exe" -Argument "-NoProfile -WindowStyle Hidden -File C:\AI\seo-run.ps1"
$trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Monday -At 09:00
Register-ScheduledTask -TaskName "SEO_Audit_MarsChallenge" -Action $action -Trigger $trigger -Description "Auditar y corregir SEO Rank Math" -RunLevel Highest
```

## Timeline y Auditoría
- Anexar resumen al `global/timeline.jsonl`:
```powershell
cd C:\Users\jesus\AI-Vault
$entry = '{"timestamp":"2025-11-28T17:06:00Z","agent":"Copilot","source_format":"completed_v2","summary":"SEO Optimization Complete - 100% Google Compliance Achieved","details":"76 CPT optimizados; títulos y descripciones corregidos; keywords aplicadas (Rank Math)."}'
echo $entry >> global\timeline.jsonl
```

## Buenas prácticas
- Respetar caché: esperar 1-2s entre lotes si el sitio usa cache.
- No forzar meta `keywords` en HTML: Rank Math no lo renderiza; correcto.
- Hacer backup antes de grandes lotes.
- Mantener idempotencia: scripts pueden correrse múltiples veces sin duplicar.

## Checklist de mantenimiento
- [x] Ejecutar análisis inicial y crear baseline
- [ ] Ejecutar análisis semanal/mensual
- [ ] Revisar KPIs y reportes JSON/MD
- [ ] Ajustar plantillas de título/descr por tipo si cambian
- [ ] Validar credenciales y endpoint Rank Math
- [ ] Actualizar timeline

## Notas finales
Este stack ha sido validado en `marschallenge.space` con 76 items corregidos, manteniendo compatibilidad con Rank Math y estándares de Google. Para nuevos sitios, clonar carpeta de proyecto, ajustar `site_url`, credenciales y post types, y ejecutar el mismo flujo.

````
