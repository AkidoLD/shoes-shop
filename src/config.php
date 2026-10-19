<?php

declare(strict_types=1);

/*
 * Site settings.
 *
 * Every default lives here. To override anything locally, create
 * `src/config.local.php` (git-ignored) — see `src/config.example.php`.
 *
 * This file returns a configuration array.
 */

$local = [];

if (is_file(__DIR__ . '/config.local.php')) {
    $local = require __DIR__ . '/config.local.php';

    if (!is_array($local)) {
        $local = [];
    }
}

$defaults = [
    'site_name'       => 'SHOES - SHOP',
    'site_url'        => '',                      // empty = inferred from the request
    'whatsapp_number' => '',                      // international format, no "+" or spaces
    'currency_label'  => 'FCFA',                  // symbol printed after amounts
    'products_path'   => __DIR__ . '/../data/products.json',
];

$config = array_merge($defaults, $local);

/*
 * Site base URL. Inferred from the request when not set; a value in the local
 * config pins a canonical URL (public domain behind a proxy/CDN, or CLI).
 */
$config['site_url'] = rtrim((string) $config['site_url'], '/');

if ($config['site_url'] === '') {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

    $config['site_url'] = ($is_https ? 'https://' : 'http://') . $host;
}

return $config;
