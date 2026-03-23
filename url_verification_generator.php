<?php
/**
 * Script para generar URLs de verificación visual de las actualizaciones de Rank Math
 * Proporciona las URLs exactas para que puedas verificar manualmente las actualizaciones
 */

class URL_Verification_Generator {

    private $site_url = 'https://mars-challenge.com';

    public function generate_verification_urls() {
        echo "🔍 URLs PARA VERIFICACIÓN VISUAL DE ACTUALIZACIONES RANK MATH\n";
        echo "=========================================================\n\n";

        echo "Páginas actualizadas con nuevos títulos y descripciones:\n\n";

        $updated_pages = array(
            array(
                'id' => 10,
                'name' => 'Página de Inicio',
                'url' => $this->site_url . '/',
                'expected_title' => 'Mars Challenge 2026 - Inicio',
                'expected_desc' => '¿Y si imaginar la vida en Marte nos ayudara a salvar el planeta Tierra? Conoce el Mars Challenge 2026, la llamada global para jóvenes innovadores que buscan soluciones para Marte y la Tierra.'
            ),
            array(
                'id' => 27,
                'name' => 'Sobre Mars Challenge',
                'url' => $this->site_url . '/sobre/mars-challenge/',
                'expected_title' => 'Sobre Mars Challenge',
                'expected_desc' => 'Conoce la historia del Mars Challenge, la iniciativa global que busca soluciones innovadoras para la vida en Marte y la Tierra. Participa en el cambio y transforma el futuro.'
            ),
            array(
                'id' => 37,
                'name' => 'Cómo participar',
                'url' => $this->site_url . '/participar/como-participar/',
                'expected_title' => 'Cómo participar en Mars Challenge',
                'expected_desc' => 'Descubre cómo participar en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante para jóvenes innovadores.'
            ),
            array(
                'id' => 1521,
                'name' => 'Registro',
                'url' => $this->site_url . '/registro/',
                'expected_title' => 'Registro Mars Challenge',
                'expected_desc' => 'Regístrate en el Mars Challenge 2026. Tu misión: prototipar la supervivencia humana en Marte y en la Tierra. Únete al reto global más importante para jóvenes innovadores.'
            ),
            array(
                'id' => 2883,
                'name' => 'Reto Marte 2025: Fuego',
                'url' => $this->site_url . '/fuego/',
                'expected_title' => 'Reto Marte 2025: Fuego',
                'expected_desc' => 'Reto Marte 2025: Fuego - Soluciones innovadoras para la gestión de energía y recursos en condiciones extremas. ¿Tienes lo que se necesita para este desafío planetario?'
            )
        );

        foreach ($updated_pages as $page) {
            echo "📌 {$page['name']} (ID: {$page['id']})\n";
            echo "   URL: {$page['url']}\n";
            echo "   Título esperado: {$page['expected_title']}\n";
            echo "   Descripción esperada: {$page['expected_desc']}\n";
            echo "\n";
        }

        echo "🔍 CÓMO VERIFICAR VISUALMENTE:\n";
        echo "1. Visita cada URL en tu navegador\n";
        echo "2. Haz clic derecho en la página y selecciona 'Ver código fuente' (o Ctrl+U)\n";
        echo "3. Busca las etiquetas meta en el encabezado:\n";
        echo "   - <title> para el título de página\n";
        echo "   - <meta name=\"description\" content=\"...\"> para la descripción\n";
        echo "   - <meta property=\"og:title\" content=\"...\"> para el título Open Graph\n";
        echo "   - <meta property=\"og:description\" content=\"...\"> para la descripción Open Graph\n";
        echo "   - <meta name=\"twitter:title\" content=\"...\"> para el título de Twitter Card\n";
        echo "   - <meta name=\"twitter:description\" content=\"...\"> para la descripción de Twitter Card\n\n";

        echo "   Alternativamente, puedes usar herramientas online como:\n";
        echo "   - https://www.seotesteronline.com/ para analizar meta tags\n";
        echo "   - https://opengraphcheck.com/ para verificar Open Graph\n";
        echo "   - Google Rich Results Test para estructura de datos\n\n";

        echo "💡 NOTA IMPORTANTE:\n";
        echo "   A veces los cambios pueden tardar unos minutos en reflejarse completamente.\n";
        echo "   Si no ves los cambios inmediatamente, espera unos minutos y vuelve a probar.\n";
        echo "   También puedes probar con una herramienta de terceros para forzar el rescaneo.\n\n";

        // También generar URLs para endpoint getHead para verificación técnica
        echo "🔧 URLS PARA VERIFICACIÓN TÉCNICA (getHead API):\n";
        echo "Estas URLs te permiten verificar técnicamente que los datos están disponibles vía API:\n\n";

        foreach ($updated_pages as $page) {
            $gethead_url = $this->site_url . "/wp-json/rankmath/v1/getHead?url=" . urlencode($page['url']);
            echo "   {$page['name']}: $gethead_url\n";
        }

        echo "\n";
    }
}

// Generar las URLs de verificación
$generator = new URL_Verification_Generator();
$generator->generate_verification_urls();

echo "✅ GENERACIÓN DE URLS DE VERIFICACIÓN COMPLETADA\n";
echo "Ahora puedes verificar visualmente las actualizaciones en tu sitio.\n";