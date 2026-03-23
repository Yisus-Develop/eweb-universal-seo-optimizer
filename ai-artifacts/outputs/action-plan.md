# Plan de Acción SEO para Mars Challenge
## Basado en el Informe de Semrush - Noviembre 2025

### Objetivo General
Mejorar la salud SEO técnica del sitio https://marschallenge.space/ corrigiendo los 116 errores, 581 advertencias y 158 notificaciones identificados por Semrush, priorizando las cuestiones críticas que afectan la visibilidad y ranking del sitio.

---

## FASE 1: CORRECCIÓN DE ERRORES CRÍTICOS (semanas 1-2)

### Tarea 1.1: Corrección de Links Internos Rotos (62 incidencias)
**Prioridad: CRÍTICA**
**Estimación:** 12-16 horas

**Acciones:**
- [ ] Generar lista completa de URLs con links rotos desde el informe de Semrush
- [ ] Revisar cada link roto manualmente para determinar la mejor solución
- [ ] Opción A: Eliminar el link si el contenido ya no es relevante
- [ ] Opción B: Reemplazar con un link interno funcional y relevante
- [ ] Opción C: Implementar redirección 301 si la página objetivo se ha movido
- [ ] Documentar todas las correcciones realizadas
- [ ] Probar manualmente un 10% de correcciones para asegurar funcionalidad

**Responsable:** Desarrollador/Editor de contenido
**Recursos necesarios:** Plugin "Broken Link Checker" o similar

### Tarea 1.2: Eliminación de Etiquetas de Título Duplicadas (38 incidencias)
**Prioridad: CRÍTICA**
**Estimación:** 8-10 horas

**Acciones:**
- [ ] Generar lista de páginas con títulos duplicados
- [ ] Crear títulos únicos para cada página
- [ ] Asegurar que cada título contenga palabras clave relevantes
- [ ] Mantener la estructura: Título Principal | Mars Challenge
- [ ] Verificar longitud óptima (50-60 caracteres)
- [ ] Actualizar títulos en CMS o plantillas según corresponda

**Responsable:** Especialista en SEO/Editor de contenido
**Recursos necesarios:** Yoast SEO, Rank Math o similar

### Tarea 1.3: Corrección de Páginas con Errores 4XX (8 páginas)
**Prioridad: CRÍTICA**
**Estimación:** 4-6 horas

**Acciones:**
- [ ] Identificar las 8 páginas que devuelven error 4XX
- [ ] Determinar si las páginas deben ser restauradas o redirigidas
- [ ] Implementar redirecciones 301 si las páginas se han movido permanentemente
- [ ] Restaurar contenido si fue eliminado accidentalmente
- [ ] Actualizar enlaces internos que apunten a las URLs de error

**Responsable:** Desarrollador web
**Recursos necesarios:** Acceso al servidor, editor de .htaccess o plugin de redirecciones

### Tarea 1.4: Resolución de Contenido Duplicado (8 páginas)
**Prioridad: ALTA**
**Estimación:** 6-8 horas

**Acciones:**
- [ ] Identificar las 8 páginas con contenido duplicado (85% o más similar)
- [ ] Determinar cuál página es la original/primaria
- [ ] Implementar etiquetas canonical rel="canonical" en páginas duplicadas
- [ ] Opcional: Implementar redirecciones 301 si se quiere consolidar contenido
- [ ] Añadir contenido único donde sea posible
- [ ] Verificar que las etiquetas canonical apunten a la URL correcta

**Responsable:** Especialista en SEO/Editor de contenido
**Recursos necesarios:** Plugin con soporte para canonical tags

---

## FASE 2: MEJORAS TÉCNICAS (semanas 2-3)

### Tarea 2.1: Minificación de Archivos JS y CSS (462 incidencias)
**Prioridad: ALTA**
**Estimación:** 6-8 horas

**Acciones:**
- [ ] Instalar plugin de optimización como "WP Rocket", "Autoptimize" o "WP Fastest Cache"
- [ ] Habilitar minificación de CSS
- [ ] Habilitar minificación de JavaScript
- [ ] Habilitar combinación de archivos CSS/JS si es seguro hacerlo
- [ ] Probar funcionalidad completa del sitio después de implementar minificación
- [ ] Verificar que no haya conflictos de scripts
- [ ] Configurar exclusión de archivos que puedan tener problemas

**Responsable:** Desarrollador web
**Recursos necesarios:** Plugin de optimización de WordPress

### Tarea 2.2: Creación de Meta Descripciones (51 páginas)
**Prioridad: ALTA**
**Estimación:** 10-12 horas

**Acciones:**
- [ ] Listar las 51 páginas sin meta descripciones
- [ ] Crear meta descripciones únicas para cada página
- [ ] Asegurar que cada descripción contenga palabras clave relevantes
- [ ] Mantener cada descripción entre 150-160 caracteres
- [ ] Hacer que cada descripción sea atractiva para mejorar CTR
- [ ] Implementar descripciones en CMS o plantillas según corresponda

**Responsable:** Especialista en SEO/Editor de contenido
**Recursos necesarios:** Yoast SEO, Rank Math o similar

### Tarea 2.3: Adición de Encabezados H1 (5 páginas)
**Prioridad: MEDIA**
**Estimación:** 2-3 horas

**Acciones:**
- [ ] Identificar las 5 páginas sin encabezado H1
- [ ] Añadir un encabezado H1 único y relevante a cada página
- [ ] Asegurar que el H1 contenga la palabra clave principal de la página
- [ ] Verificar que no haya más de un H1 por página
- [ ] Probar visualización correcta en diferentes dispositivos

**Responsable:** Editor de contenido/Desarrollador
**Recursos necesarios:** Editor de contenido CMS o editor de temas

---

## FASE 3: REFINAMIENTO Y MEJORAS ADICIONALES (semanas 3-4)

### Tarea 3.1: Corrección de Links Externos Rotos (3 incidencias)
**Prioridad: MEDIA**
**Estimación:** 1-2 horas

**Acciones:**
- [ ] Identificar los 3 links externos rotos reportados
- [ ] Probar manualmente cada link para confirmar que está roto
- [ ] Buscar alternativas válidas para los links rotos
- [ ] Reemplazar con links válidos o eliminar según sea apropiado
- [ ] Documentar cambios realizados

**Responsable:** Editor de contenido
**Recursos necesarios:** Acceso de edición de contenido

### Tarea 3.2: Mejora de Proporción Texto-HTML (53 páginas)
**Prioridad: MEDIA**
**Estimación:** 8-10 horas

**Acciones:**
- [ ] Identificar las 53 páginas con baja proporción texto-HTML
- [ ] Remover scripts y estilos incrustados en HTML
- [ ] Optimizar la estructura HTML de cada página
- [ ] Separar contenido de código en archivos diferentes
- [ ] Aumentar contenido de valor en páginas necesarias
- [ ] Verificar que la proporción mejore a más del 10%

**Responsable:** Desarrollador web/Editor de contenido
**Recursos necesarios:** Editor de código/CMS

### Tarea 3.3: Corrección de Otros Problemas Menores
**Prioridad: BAJA**
**Estimación:** 6-8 horas

**Acciones:**
- [ ] Corregir la página con H1 y título duplicados
- [ ] Añadir más enlaces internos a las 8 páginas con solo un enlace interno
- [ ] Eliminar atributos nofollow de los 3 links internos que los contienen
- [ ] Revisar y corregir según sea necesario los otros problemas menores
- [ ] Optimizar la página que requiere contenido optimizado

**Responsable:** Especialista en SEO/Editor de contenido
**Recursos necesarios:** Acceso al CMS y contenido

---

## FASE 4: VERIFICACIÓN Y MONITOREO (semana 4+)

### Tarea 4.1: Verificación Post-Implementación
**Prioridad: ALTA**
**Estimación:** 4-6 horas

**Acciones:**
- [ ] Ejecutar nueva auditoría con Semrush para verificar correcciones
- [ ] Comparar resultados con el informe original
- [ ] Documentar mejoras logradas
- [ ] Realizar pruebas funcionales completas del sitio
- [ ] Verificar Core Web Vitals mejorados
- [ ] Confirmar que no se introdujeron nuevos problemas

### Tarea 4.2: Implementación de Sistema de Monitoreo
**Prioridad: MEDIA**
**Estimación:** 2-3 horas

**Acciones:**
- [ ] Configurar monitoreo continuo de enlaces rotos
- [ ] Establecer alertas para nuevos problemas críticos
- [ ] Implementar seguimiento mensual de salud SEO
- [ ] Establecer proceso de revisión regular de nuevas páginas
- [ ] Crear informe de mantenimiento mensual

**Responsable:** Especialista en SEO
**Recursos necesarios:** Herramientas de monitoreo SEO

---

## KPIs de Éxito

1. **Reducción de errores de Semrush:** De 116 a menos de 10 errores críticos
2. **Mejora de Core Web Vitals:** Al menos 2 categorías en "Good" (verde)
3. **Aumento de velocidad de página:** Reducción de tiempo de carga en al menos 30%
4. **Mejora en indexación:** Mayor número de páginas indexadas correctamente
5. **Aumento de tráfico orgánico:** Incremento medible en tráfico proveniente de motores de búsqueda
6. **Mejora en posición de palabras clave:** Posiciones más altas para términos objetivo

## Recursos Necesarios
- Acceso administrativo al sitio WordPress
- Plugins de SEO (Yoast SEO, Rank Math)
- Plugin de optimización web (WP Rocket, Autoptimize)
- Plugin de gestión de redirecciones
- Acceso a Google Search Console
- Acceso al servidor para configuraciones técnicas
- Herramienta de edición de código si es necesario

## Riesgos Potenciales
- Problemas de funcionalidad al minificar JS/CSS
- Conflictos de redirecciones que afecten UX
- Cambios en ranking temporal durante la implementación
- Problemas de permisos que retrasen las correcciones

## Notas Adicionales
- Se recomienda realizar copia de seguridad antes de comenzar
- Realizar cambios en staging si es posible antes de producción
- Probar en diferentes dispositivos y navegadores
- Monitorear Google Search Console durante y después de la implementación