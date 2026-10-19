<?php

declare(strict_types=1);

/*
 * Small helpers shared by the templates.
 */

/**
 * Escapes a value before printing it into HTML.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Formats an amount for display (e.g. "15 000 FCFA").
 */
function format_price(int|float $amount, array $config): string
{
    return number_format((float) $amount, 0, ',', ' ')
        . ' ' . $config['currency_label'];
}
