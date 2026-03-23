<?php
$site_url = 'https://mars-challenge.com';
$username = 'wmaster_cs4or9qs';
$app_password = 'KSXH coVd TyuX 9fLp 3SSv UxqV';

function wp_api_request($endpoint) {
    global $site_url, $username, $app_password;
    $url = $site_url . '/wp-json/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $app_password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => json_decode($response, true)];
}

$res = wp_api_request('wp/v2/proyectos_destacados/6317');
if ($res['code'] === 200) {
    $acf = $res['body']['acf'] ?? [];
    $keys = [];
    foreach ($acf as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $sk => $sv) {
                if ($sk === 'formatted_value') continue;
                $keys[] = $k . "_" . $sk;
                if (is_array($sv) && isset($sv[0])) {
                    foreach (array_keys($sv[0]) as $rsk) {
                        $keys[] = $k . "_" . $sk . "_[*]_" . $rsk;
                    }
                }
            }
        } else {
            $keys[] = $k;
        }
    }
    file_put_contents('projects/marschallenge-seo/all_acf_keys.json', json_encode($keys, JSON_PRETTY_PRINT));
    echo "Saved " . count($keys) . " potential keys to all_acf_keys.json\n";
}
