<?php

declare(strict_types=1);

/*
 * Site settings.
 *
 * Values come from the .env file (loaded below) or from real environment
 * variables, which always win. No secret is committed: .env is git-ignored.
 *
 * This file returns a configuration array.
 */

require_once __DIR__ . '/env.php';

load_env(__DIR__ . '/../.env');

/*
 * Reads a setting, falling back to a default when unset or empty.
 */
$setting = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);

    return ($value === false || $value === '') ? $default : $value;
};

/*
 * Site base URL.
 *
 * By default it is inferred from the request, so it is correct without any
 * configuration. SITE_URL stays available to force a canonical URL (public
 * domain behind a proxy/CDN, or when the Host header cannot be trusted).
 */
$site_url = (string) $setting('SITE_URL', '');

if ($site_url === '') {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

    $site_url = ($is_https ? 'https://' : 'http://') . $host;
}

return [
    'site_name'       => (string) $setting('SITE_NAME', 'SHOES - SHOP'),
    'site_url'        => rtrim($site_url, '/'),
    'whatsapp_number' => (string) $setting('WHATSAPP_NUMBER', ''),
    'currency_label'  => (string) $setting('CURRENCY_LABEL', 'FCFA'),
    'products_path'   => __DIR__ . '/../data/products.json',
];
