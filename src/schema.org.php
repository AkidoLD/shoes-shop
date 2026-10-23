<?php

declare(strict_types=1);

function schema_org_product(array $product, array $config, string $product_url): array
{
    $name  = (string) ($product['name'] ?? '');
    $ref   = (string) ($product['ref'] ?? '');
    $image = trim((string) ($product['image'] ?? ''));
    $og_image = trim((string) ($product['og_image'] ?? ''));

    $price = $product['price'] ?? 0;
    $price = is_numeric($price) ? $price + 0 : 0;

    $currency = trim((string) ($product['currency'] ?? ''));
    if ($currency === '') {
        $currency = 'XAF';
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'Product',
        '@id'      => $product_url . '#product',
        'name'     => $name,
        'description' => (string) ($product['description'] ?? ''),
        'sku'      => $ref,
        'url'      => $product_url,
        'brand'    => [
            '@type' => 'Brand',
            'name'  => (string) $config['site_name'],
        ],
        'offers'   => [
            '@type'         => 'Offer',
            '@id'           => $product_url . '#offer',
            'url'           => $product_url,
            'price'         => $price,
            'priceCurrency' => $currency,
            'availability'  => !empty($product['available'])
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller'        => [
                '@type' => 'Organization',
                'name'  => (string) $config['site_name'],
                'url'   => absolute_url('index.php', $config),
            ],
        ],
    ];

    $images = [];

    if ($image !== '') {
        $images[] = absolute_url($image, $config);
    }

    if ($og_image !== '') {
        $images[] = absolute_url($og_image, $config);
    }

    if ($images !== []) {
        $schema['image'] = array_values(array_unique($images));
    }

    $category = trim((string) ($product['category_label'] ?? ''));
    if ($category !== '') {
        $schema['category'] = $category;
    }

    return $schema;
}

function schema_org_item_list(array $products, array $config): array
{
    $items = [];

    foreach (array_values($products) as $index => $product) {
        $product_url = absolute_url(
            'product.php?ref=' . rawurlencode((string) ($product['ref'] ?? '')),
            $config
        );

        $item = schema_org_product($product, $config, $product_url);
        unset($item['@context']);

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $index + 1,
            'url'      => $product_url,
            'item'     => $item,
        ];
    }

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => (string) $config['site_name'] . ' — Catalogue',
        'numberOfItems'   => count($items),
        'itemListOrder'   => 'https://schema.org/ItemListOrderAscending',
        'itemListElement' => $items,
    ];
}

function schema_org_json(array $schema): string
{
    $json = json_encode(
        $schema,
        JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
    );

    return $json === false ? '{}' : $json;
}

function render_schema_org(array $schema): string
{
    return '<script type="application/ld+json">' . "\n"
        . schema_org_json($schema)
        . "\n" . '</script>';
}
