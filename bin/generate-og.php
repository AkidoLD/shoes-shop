#!/usr/bin/env php
<?php

declare(strict_types=1);

use ShoesShop\Database;
use ShoesShop\OgImageGenerator;
use ShoesShop\Product;
use ShoesShop\ProductRepository;

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$config = require $root.'/src/config.php';

$force = false;
$ref = null;

for ($index = 1; $index < $argc; ++$index) {
    $argument = $argv[$index];

    if ('--force' === $argument) {
        $force = true;

        continue;
    }

    if (str_starts_with($argument, '--ref=')) {
        $ref = substr($argument, 6);

        continue;
    }

    if ('--ref' === $argument && isset($argv[$index + 1])) {
        $ref = $argv[++$index];

        continue;
    }

    fwrite(STDERR, "Option inconnue : {$argument}\n");

    exit(2);
}

try {
    $repository = new ProductRepository(Database::connect($config));

    if (null === $ref) {
        $products = $repository->all();
    } else {
        $product = $repository->findByRef($ref);
        $products = null === $product ? [] : [$product];
    }
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");

    exit(1);
}

if ([] === $products) {
    fwrite(STDOUT, null === $ref ? "Aucun produit en base.\n" : "Produit introuvable : {$ref}\n");

    exit(null === $ref ? 0 : 1);
}

$generator = new OgImageGenerator();
$publicDirectory = $root.'/public';
$failures = 0;

foreach ($products as $product) {
    if (!$product instanceof Product) {
        continue;
    }

    $relativePath = $product->ogImage ?? 'assets/img/og/'.strtolower($product->ref).'.jpg';
    $relativePath = ltrim($relativePath, '/');
    $absolutePath = $publicDirectory.'/'.$relativePath;

    if (!$force && is_file($absolutePath)) {
        if ($product->ogImage !== $relativePath) {
            $repository->setOgImage($product->ref, $relativePath);
        }

        fwrite(STDOUT, "Ignoré (déjà présent) : {$relativePath}\n");

        continue;
    }

    try {
        $generated = $generator->generateForProduct($product, $publicDirectory);

        if ($product->ogImage !== $generated) {
            $repository->setOgImage($product->ref, $generated);
        }

        fwrite(STDOUT, "Généré : {$generated}\n");
    } catch (Throwable $error) {
        ++$failures;
        fwrite(STDERR, 'Échec pour '.$product->ref.' : '.$error->getMessage()."\n");
    }
}

exit($failures > 0 ? 1 : 0);
