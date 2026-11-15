<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_price(float|int $amount, array $config): string
{
    return number_format((float) $amount, 0, ',', ' ')
        .' '.$config['currency_label'];
}

function absolute_url(?string $path, array $config): string
{
    $path = trim((string) $path);

    if (1 === preg_match('#^(?:https?:)?//#i', $path)) {
        return $path;
    }

    $base = rtrim((string) ($config['site_url'] ?? ''), '/');

    if ('' === $path) {
        return $base.'/';
    }

    return $base.'/'.ltrim($path, '/');
}
