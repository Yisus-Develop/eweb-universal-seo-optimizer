<?php
/**
 * Script para actualizar metadescripciones de Rank Math directamente en WordPress
 * Este script debe ejecutarse en el contexto de WordPress (como un plugin temporal o snippet)
 */

// Asegurarse de que se está ejecutando en el contexto de WordPress
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Actualizar metadescripciones de Rank Math para múltiples posts/páginas
 */
function actualizar_descripciones_rankmath_masivo($descripciones_data) {
    $resultados = array(
        'exitosos' => 0,
        'fallidos' => 0,
        'detalles' => array()
    );
    
    foreach ($descripciones_data as $item) {
        $post_id = $item['id'];
        $descripcion = $item['descripcion'];
        
        // Actualizar el campo de descripción de Rank Math
        $update_result = update_post_meta($post_id, 'rank_math_description', $descripcion);
        
        // También actualizar el título si se proporciona
        if (isset($item['titulo'])) {
            $update_title = update_post_meta($post_id, 'rank_math_title', $item['titulo']);
        }
        
        if ($update_result) {
            $resultados['exitosos']++;
            $resultados['detalles'][] = array(
                'id' => $post_id,
                'titulo' => get_the_title($post_id),
                'estado' => 'exitoso',
                'descripcion' => $descripcion
            );
        } else {
            $resultados['fallidos']++;
            $resultados['detalles'][] = array(
                'id' => $post_id,
                'titulo' => get_the_title($post_id),
                'estado' => 'fallido',
                'error' => 'No se pudo actualizar'
            );
        }
        
        // Pequeña pausa para no sobrecargar el sistema
        usleep(100000); // 0.1 segundos
    }
    
    return $resultados;
}

// Datos de ejemplo a actualizar (reemplaza con tus datos reales)
$descripciones_para_actualizar = array(
    array(
        'id' => 10,
        'titulo' => 'Mars Challenge 2026',
        'descripcion' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores.'
    ),
    array(
        'id' => 27,
        'titulo' => 'Sobre Mars Challenge',
        'descripcion' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio.'
    ),
    array(
        'id' => 37,
        'titulo' => 'Cómo participar',
        'descripcion' => 'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. ¡Únete al reto!'
    ),
    // Agrega más entradas según sea necesario
);

// Ejecutar la actualización
$resultados = actualizar_descripciones_rankmath_masivo($descripciones_para_actualizar);

// Mostrar resultados
echo "<h2>Resultados de la actualización de Rank Math</h2>";
echo "<p>Exitosos: " . $resultados['exitosos'] . "</p>";
echo "<p>Fallidos: " . $resultados['fallidos'] . "</p>";

if (!empty($resultados['detalles'])) {
    echo "<ul>";
    foreach ($resultados['detalles'] as $detalle) {
        echo "<li>ID " . $detalle['id'] . " (" . $detalle['titulo'] . "): " . $detalle['estado'] . "</li>";
    }
    echo "</ul>";
}

// Opcional: Limpiar el script después de usarlo
// wp_delete_attachment(); // No borrar esta línea en producción
