<?php

declare(strict_types=1);

/*
 * Loading and querying products.
 *
 * Single source: the JSON file defined in the configuration.
 */

/**
 * Loads every product from the JSON file.
 *
 * @return array<int, array<string, mixed>>
 * @throws RuntimeException when the file is missing, unreadable or invalid.
 */
function load_products(array $config): array
{
    $path = $config['products_path'];

    if (!is_file($path)) {
        throw new RuntimeException('Products data file not found: ' . $path);
    }

    $content = file_get_contents($path);

    if ($content === false) {
        throw new RuntimeException('Unable to read products file: ' . $path);
    }

    $products = json_decode($content, true);

    if (!is_array($products)) {
        throw new RuntimeException('Products file does not contain valid JSON.');
    }

    return $products;
}

/**
 * Returns the product matching a reference, or null.
 *
 * @param array<int, array<string, mixed>> $products
 * @return array<string, mixed>|null
 */
function find_product_by_ref(array $products, string $ref): ?array
{
    foreach ($products as $product) {
        if (($product['ref'] ?? null) === $ref) {
            return $product;
        }
    }

    return null;
}

/**
 * Categories available, derived from the products.
 *
 * @param array<int, array<string, mixed>> $products
 * @return array<string, string> category code => label
 */
function list_categories(array $products): array
{
    $categories = [];

    foreach ($products as $product) {
        $code = (string) ($product['category'] ?? '');

        if ($code !== '') {
            $categories[$code] = (string) ($product['category_label'] ?? $code);
        }
    }

    return $categories;
}

/**
 * Applies search, category filter and sorting, server side.
 *
 * @param array<int, array<string, mixed>> $products
 * @return array<int, array<string, mixed>>
 */
function filter_and_sort(
    array $products,
    string $search = '',
    string $category = 'all',
    string $sort = 'default'
): array {
    $search = trim(mb_strtolower($search));

    if ($search !== '') {
        $products = array_values(array_filter(
            $products,
            static function (array $product) use ($search): bool {
                $text = mb_strtolower(implode(' ', [
                    (string) ($product['name'] ?? ''),
                    (string) ($product['ref'] ?? ''),
                    (string) ($product['category_label'] ?? ''),
                    (string) ($product['description'] ?? ''),
                ]));

                return str_contains($text, $search);
            }
        ));
    }

    if ($category !== 'all') {
        $products = array_values(array_filter(
            $products,
            static fn (array $product): bool => ($product['category'] ?? '') === $category
        ));
    }

    usort($products, static function (array $a, array $b) use ($sort): int {
        return match ($sort) {
            'price-asc'  => ($a['price'] ?? 0) <=> ($b['price'] ?? 0),
            'price-desc' => ($b['price'] ?? 0) <=> ($a['price'] ?? 0),
            'name-asc'   => strcoll(
                (string) ($a['name'] ?? ''),
                (string) ($b['name'] ?? '')
            ),
            default      => 0,
        };
    });

    return $products;
}

/**
 * Accepted sort values (guard for the `sort` URL parameter).
 *
 * @return array<int, string>
 */
function valid_sort_options(): array
{
    return ['default', 'price-asc', 'price-desc', 'name-asc'];
}
