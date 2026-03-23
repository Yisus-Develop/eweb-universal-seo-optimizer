<?php
/**
 * Script para actualizar metadescripciones faltantes en Rank Math para Mars Challenge
 * Basado en el análisis de metadescripciones faltantes identificadas
 */

class Update_Missing_Meta_Descriptions {

    private $site_url = 'https://mars-challenge.com';
    private $username = 'wmaster_cs4or9qs';
    private $app_password = 'THuf KSXH coVd TyuX 9fLp 3SSv UxqV';
    private $auth_header;

    public function __construct() {
        $this->auth_header = 'Authorization: Basic ' . base64_encode($this->username . ':' . str_replace(' ', '', $this->app_password));
        echo "🔧 Iniciando actualización de metadescripciones faltantes para: " . $this->site_url . "\n";
    }

    /**
     * Hacer una petición HTTP usando cURL
     */
    private function make_request($url, $method = 'GET', $data = null) {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                $this->auth_header,
                'Content-Type: application/json'
            ),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Update-Missing-Meta-Descriptions/1.0',
            CURLOPT_FOLLOWLOCATION => true
        ));

        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array('error' => $error, 'status_code' => 0);
        }

        return array(
            'status_code' => $http_code,
            'body' => $response ? json_decode($response, true) : null,
            'raw_response' => $response
        );
    }

    /**
     * Obtener tipo de post correcto
     */
    private function get_post_type($post_id) {
        // Intentar con páginas
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/pages/$post_id");
        if ($response['status_code'] === 200) {
            return 'pages';
        }
        
        // Si no es página, intentar con posts
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/posts/$post_id");
        if ($response['status_code'] === 200) {
            return 'posts';
        }
        
        // Intentar con categorías
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/categories/$post_id");
        if ($response['status_code'] === 200) {
            return 'categories';
        }
        
        // Intentar con etiquetas
        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/tags/$post_id");
        if ($response['status_code'] === 200) {
            return 'tags';
        }
        
        return 'unknown';
    }

    /**
     * Actualizar metadescripción en Rank Math
     */
    private function update_rankmath_description($post_id, $new_description, $post_type) {
        if ($post_type === 'unknown') {
            return false;
        }

        $update_data = array(
            'meta' => array(
                'rank_math_description' => $new_description
            )
        );

        $response = $this->make_request($this->site_url . "/wp-json/wp/v2/$post_type/$post_id", 'POST', $update_data);
        return $response['status_code'] === 200;
    }

    /**
     * Generar descripciones para los elementos que carecen de ellas
     */
    public function update_missing_descriptions() {
        echo "\n🔧 Actualizando metadescripciones faltantes...\n";

        // IDs de los elementos que identificamos que carecen de descripción
        // Basado en el análisis anterior, estos son algunos de los principales candidatos
        $items_to_update = array(
            array('id' => 1200, 'type' => 'post', 'title' => 'Jóvenes puertorriqueños rumbo a Madrid', 'description' => 'Descubre cómo estudiantes puertorriqueños están representando a su país en el Mars Challenge rumbo a Madrid. Innovación y talento joven hacia Marte.'),
            array('id' => 1197, 'type' => 'post', 'title' => 'Reto Marte en Costa Rica', 'description' => 'Más de 150 universitarios costarricenses resuelven retos sobre el agua en Marte. Conoce cómo se desarrolló el Mars Challenge en Costa Rica.'),
            array('id' => 1193, 'type' => 'post', 'title' => 'Final Reto Marte 2024 — Madrid', 'description' => 'Estudiantes de 6 países presentan soluciones para la gestión del agua en Marte y en la Tierra. Resultados de la final del Mars Challenge 2024 en Madrid.'),
            array('id' => 10, 'type' => 'page', 'title' => 'Inicio', 'description' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores.'),
            array('id' => 1521, 'type' => 'page', 'title' => 'Registro', 'description' => 'Regístrate en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante.'),
            array('id' => 27, 'type' => 'page', 'title' => 'Mars Challenge', 'description' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio.'),
            array('id' => 37, 'type' => 'page', 'title' => 'Cómo participar', 'description' => 'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. ¡Únete al reto!'),
            array('id' => 2883, 'type' => 'page', 'title' => 'Fuego', 'description' => 'Reto Marte 2025: Fuego - Soluciones innovadoras para la gestión de energía y recursos en condiciones extremas. ¿Tienes lo que se necesita?'),
            array('id' => 57, 'type' => 'page', 'title' => 'Fases del reto', 'description' => 'Conoce las fases del Mars Challenge: del registro a la acción, del prototipo al impacto real. Sigue la estructura que guía a los participantes al éxito.'),
            array('id' => 39, 'type' => 'page', 'title' => 'Convocatoria actual — 2026 Tierra', 'description' => 'Mars Challenge 2026: Imagina la Tierra como un Marte en formación. Evita que llegue ese día. Participa en el reto que transformará el futuro del planeta.')
        );

        $descriptions_updated = 0;
        $errors = 0;

        foreach ($items_to_update as $item) {
            echo "\nActualizando descripción para: {$item['title']} (ID: {$item['id']})\n";
            
            $post_type = $item['type'] === 'page' ? 'pages' : 'posts';
            
            // Si el tipo es 'page' o 'post', verificar que el ID es correcto
            if ($post_type === 'pages' || $post_type === 'posts') {
                $success = $this->update_rankmath_description($item['id'], $item['description'], $post_type);
                
                if ($success) {
                    echo "  ✓ Descripción actualizada para ID {$item['id']}\n";
                    $descriptions_updated++;
                } else {
                    // Intentar con el tipo detectado automáticamente
                    $auto_type = $this->get_post_type($item['id']);
                    if ($auto_type !== 'unknown') {
                        $success = $this->update_rankmath_description($item['id'], $item['description'], $auto_type);
                        if ($success) {
                            echo "  ✓ Descripción actualizada para ID {$item['id']} (detectado como $auto_type)\n";
                            $descriptions_updated++;
                        } else {
                            echo "  ✗ Error al actualizar descripción para ID {$item['id']}\n";
                            $errors++;
                        }
                    } else {
                        echo "  ✗ No se pudo determinar el tipo para ID {$item['id']}\n";
                        $errors++;
                    }
                }
            } else {
                $auto_type = $this->get_post_type($item['id']);
                if ($auto_type !== 'unknown') {
                    $success = $this->update_rankmath_description($item['id'], $item['description'], $auto_type);
                    if ($success) {
                        echo "  ✓ Descripción actualizada para ID {$item['id']} (detectado como $auto_type)\n";
                        $descriptions_updated++;
                    } else {
                        echo "  ✗ Error al actualizar descripción para ID {$item['id']}\n";
                        $errors++;
                    }
                } else {
                    echo "  ✗ No se pudo determinar el tipo para ID {$item['id']}\n";
                    $errors++;
                }
            }
            
            sleep(1); // Evitar demasiadas solicitudes rápidas
        }

        // También actualizar algunos artículos de noticias que identificamos
        $news_items = array(
            array('id' => 1189, 'description' => 'Descubre cómo el Mars Challenge impulsa la innovación en Latinoamérica. Últimas noticias y avances del reto global hacia Marte.'),
            array('id' => 1185, 'description' => 'Conoce las últimas novedades del Mars Challenge. Innovación, jóvenes talentosos y soluciones para la vida en Marte y en la Tierra.'),
            array('id' => 1181, 'description' => 'Innovadoras soluciones del Mars Challenge para la supervivencia humana en Marte. Últimas noticias, avances y descubrimientos del reto global.')
        );

        foreach ($news_items as $item) {
            // Verificar si el ID existe (como post)
            $post_type = $this->get_post_type($item['id']);
            if ($post_type !== 'unknown') {
                echo "\nIntentando actualizar descripción para ID {$item['id']} (detectado como $post_type)\n";
                
                $success = $this->update_rankmath_description($item['id'], $item['description'], $post_type);
                if ($success) {
                    echo "  ✓ Descripción actualizada para ID {$item['id']}\n";
                    $descriptions_updated++;
                } else {
                    echo "  ✗ Error al actualizar descripción para ID {$item['id']}\n";
                    $errors++;
                }
                
                sleep(1);
            } else {
                echo "\n  - ID {$item['id']} no encontrado, omitiendo\n";
            }
        }

        echo "\n✅ Actualizadas $descriptions_updated metadescripciones\n";
        if ($errors > 0) {
            echo "⚠️  Hubo $errors errores al actualizar descripciones\n";
        }

        return array('updated' => $descriptions_updated, 'errors' => $errors);
    }

    /**
     * Ejecutar actualización
     */
    public function run_update() {
        echo "🚀 INICIANDO ACTUALIZACIÓN DE METADESCRIPCIONES FALTANTES\n";
        echo "=======================================================\n";

        $result = $this->update_missing_descriptions();

        echo "\n🎯 RESUMEN DE ACTUALIZACIÓN:\n";
        echo "   • Metadescripciones actualizadas: {$result['updated']}\n";
        if ($result['errors'] > 0) {
            echo "   • Errores encontrados: {$result['errors']}\n";
        }

        echo "\n⏭️  PRÓXIMOS PASOS:\n";
        echo "   - Verificar en Rank Math que los cambios se hayan aplicado correctamente\n";
        echo "   - Revisar manualmente las páginas que no se pudieron actualizar\n";
        echo "   - Monitorear el impacto en SEO de las descripciones actualizadas\n";

        return $result;
    }
}

// Ejecutar la actualización de metadescripciones faltantes
$updater = new Update_Missing_Meta_Descriptions();
$result = $updater->run_update();

echo "\n✅ ACTUALIZACIÓN DE METADESCRIPCIONES FALTANTES FINALIZADA\n";