<?php

declare(strict_types=1);

$local = [];

if (is_file(__DIR__ . '/config.local.php')) {
    $local = require __DIR__ . '/config.local.php';

    if (!is_array($local)) {
        $local = [];
    }
}

$defaults = [
    'site_name'       => 'SHOES - SHOP',
    'site_url'        => '',
    'whatsapp_number' => '',
    'currency_label'  => 'FCFA',
    'og_image'        => '',
    'products_path'   => __DIR__ . '/../data/products.json',
];

$config = array_merge($defaults, $local);

$config['site_url'] = rtrim((string) $config['site_url'], '/');

if ($config['site_url'] === '') {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

    $config['site_url'] = ($is_https ? 'https://' : 'http://') . $host;
}

return $config;
