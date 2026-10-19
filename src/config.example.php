<?php

declare(strict_types=1);

/*
 * Local configuration template.
 *
 * Copy this file to `src/config.local.php` (git-ignored) and set your values:
 *
 *     cp src/config.example.php src/config.local.php
 *
 * Only list what you want to override; everything else keeps its default
 * (defaults are defined in `src/config.php`).
 */

return [
    // Full site URL, without trailing slash (e.g. https://shoes-shop.cm).
    // Leave empty to infer it from the request.
    'site_url' => '',

    // Shop WhatsApp number, international format without "+" or spaces.
    'whatsapp_number' => '237000000000',

    // Shop display name.
    'site_name' => 'SHOES - SHOP',

    // Symbol printed after amounts (the ISO code XAF stays in the data).
    'currency_label' => 'FCFA',
];
