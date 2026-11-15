<?php

declare(strict_types=1);

function render_open_graph(array $config, array $page = []): string
{
    $title = trim((string) ($page['title'] ?? $config['site_name']));
    $description = trim((string) ($page['description'] ?? ''));
    $type = trim((string) ($page['type'] ?? 'website'));
    $url = absolute_url((string) ($page['url'] ?? 'index.php'), $config);

    $image = trim((string) ($page['image'] ?? ($config['og_image'] ?? '')));
    $image_url = '' !== $image ? absolute_url($image, $config) : '';
    $image_alt = trim((string) ($page['image_alt'] ?? $title));

    $tags = [];

    $add = static function (string $attribute, string $name, string $content) use (&$tags): void {
        if ('' === $content) {
            return;
        }

        $tags[] = '<meta '.$attribute.'="'.e($name).'"'
            .' content="'.e($content).'">';
    };

    $add('property', 'og:site_name', (string) $config['site_name']);
    $add('property', 'og:locale', 'fr_FR');
    $add('property', 'og:type', $type);
    $add('property', 'og:title', $title);
    $add('property', 'og:description', $description);
    $add('property', 'og:url', $url);

    if ('' !== $image_url) {
        $add('property', 'og:image', $image_url);

        if (str_starts_with($image_url, 'https://')) {
            $add('property', 'og:image:secure_url', $image_url);
        }

        $add('property', 'og:image:type', (string) ($page['image_type'] ?? 'image/jpeg'));
        $add('property', 'og:image:width', (string) ($page['image_width'] ?? ''));
        $add('property', 'og:image:height', (string) ($page['image_height'] ?? ''));
        $add('property', 'og:image:alt', $image_alt);
    }

    if ('product' === $type) {
        $price = $page['price'] ?? null;

        if (is_numeric($price)) {
            $add('property', 'product:price:amount', (string) $price);
            $add('property', 'product:price:currency', (string) ($page['currency'] ?? 'XAF'));
        }

        $add('property', 'product:availability', (string) ($page['availability'] ?? ''));
    }

    $add('name', 'twitter:card', 'summary_large_image');
    $add('name', 'twitter:title', $title);
    $add('name', 'twitter:description', $description);

    if ('' !== $image_url) {
        $add('name', 'twitter:image', $image_url);
        $add('name', 'twitter:image:alt', $image_alt);
    }

    return implode("\n    ", $tags);
}
