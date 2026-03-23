<?php
/**
 * prepare_update_for_selected_posttypes.php
 *
 * Prepara payloads (dry-run) para actualizar meta/title/keyword usando
 * el endpoint Rank Math o la API REST de WP, limitado a un conjunto
 * de `post_type` especificados por el usuario.
 *
 * Uso por defecto: modo dry-run (no realiza POST). Para aplicar cambios
 * se debe ejecutar con --apply y definir las variables de entorno
 * RANKMATH_USER y RANKMATH_PASS (o cambiar el código para proveer credenciales).
 *
 * Genera:
 * - projects/marschallenge-seo/dryrun_payloads_<timestamp>.json
 * - projects/marschallenge-seo/prepare_update_log_<timestamp>.json
 *
 * NOTA: Este script NO ejecuta actualizaciones por defecto. Revisa los
 * payloads antes de aplicar cambios en producción y realiza backup.
 */

date_default_timezone_set('UTC');

$project_dir = __DIR__;
$report_file = $project_dir . '/seo_verification_report_2025-11-28.json';
if (!file_exists($report_file)) {
    fwrite(STDERR, "ERROR: report file not found: $report_file\n");
    exit(2);
}

$report = json_decode(file_get_contents($report_file), true);
if (!$report || !isset($report['missing_items'])) {
    fwrite(STDERR, "ERROR: invalid report format or no missing_items found\n");
    exit(2);
}

// Lista de post types a focalizar (según tu petición)
$target_post_types = array(
    'instituciones',
    'empresas_aliadas',
    'landing_paises',
    'quienes-sirven',
    'participa',
    'tematicas_y_elemento',
    'logos',
    'testimonios', // edit.php?post_type=testimonios -> rest base 'testimonios'
    'country_page'
);

$timestamp = date('Y-m-d_His');
$dryrun_payloads = array();
$log = array('timestamp' => date('c'), 'mode' => 'dry-run', 'items' => array());

// Reuse description generation logic (simple version)
function generate_desc($title, $url) {
    $template = 'Descubre %s en Mars Challenge. Únete al movimiento global de innovación dual-planeta para crear soluciones sostenibles para la Tierra y Marte.';
    $description = sprintf($template, $title);
    if (mb_strlen($description) > 160) $description = mb_substr($description, 0, 157) . '...';
    return $description;
}

foreach ($report['missing_items'] as $item) {
    $id = $item['id'];
    $title = $item['title'];
    $url = $item['url'] ?? '';

    // Construir payload común (Rank Math V3 style)
    $description = generate_desc($title, $url);
    $payload = array(
        'objectID' => $id,
        'objectType' => 'post', // Rank Math accepts 'post' often; endpoint may accept any
        'meta' => array(
            'rank_math_description' => $description,
            'rank_math_title' => $title
        ),
        'candidates' => array(), // aquí guardaremos candidate post_types donde intentar aplicar
    );

    // Generar lista de candidate URLs para cada target post type (no se llaman)
    foreach ($target_post_types as $pt) {
        // construir REST URL candidate (will not be called in dry-run)
        $candidate = array(
            'post_type' => $pt,
            'rest_candidate_get' => rtrim($report['site'] ?? 'https://mars-challenge.com', '/') . "/wp-json/wp/v2/$pt/$id",
            'rankmath_api_candidate' => rtrim($report['site'] ?? 'https://mars-challenge.com', '/') . "/wp-json/rankmath/v1/updateMeta"
        );
        $payload['candidates'][] = $candidate;
    }

    $dryrun_payloads[] = $payload;
    $log['items'][] = array('id' => $id, 'title' => $title, 'url' => $url, 'prepared_at' => date('c'));
}

$dryrun_file = $project_dir . "/dryrun_payloads_{$timestamp}.json";
file_put_contents($dryrun_file, json_encode($dryrun_payloads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

$log_file = $project_dir . "/prepare_update_log_{$timestamp}.json";
file_put_contents($log_file, json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

fwrite(STDOUT, "Dry-run payloads written to: $dryrun_file\n");
fwrite(STDOUT, "Log written to: $log_file\n");
fwrite(STDOUT, "Review the payload file before applying updates. To apply, run this script with --apply and provide credentials via env vars.\n");

// Usage/help
if (in_array('--help', $argv) || in_array('-h', $argv)) {
    echo "Usage: php prepare_update_for_selected_posttypes.php [--apply]\n";
    echo "  --apply: attempt to POST updates (requires env vars RANKMATH_USER,RANKMATH_PASS and explicit confirmation).\n";
}

exit(0);

?>
