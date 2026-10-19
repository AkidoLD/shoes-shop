<?php

declare(strict_types=1);

/*
 * One product card in the catalogue list.
 *
 * Expected variables:
 * @var array<string, mixed> $product one product
 * @var array<string, mixed> $config  site settings
 */

$product_url = 'product.php?ref=' . rawurlencode((string) $product['ref']);

// Status badge: "NOUVEAU" wins over "POPULAIRE".
if (!empty($product['is_new'])) {
    $status_badge = 'NOUVEAU';
} elseif (!empty($product['featured'])) {
    $status_badge = 'POPULAIRE';
} else {
    $status_badge = '';
}

// Discount badge when an old price is set.
$discount = '';
if (!empty($product['old_price']) && (int) $product['old_price'] > (int) $product['price']) {
    $discount = '−' . (int) round((1 - (int) $product['price'] / (int) $product['old_price']) * 100) . '%';
}
?>
<article class="product-card<?= empty($product['available']) ? ' product-unavailable' : '' ?>">

    <a href="<?= e($product_url) ?>"
       aria-label="Découvrir <?= e((string) $product['name']) ?>">

        <div class="product-image">

            <?php if ($status_badge !== ''): ?>
                <span class="product-badge"><?= e($status_badge) ?></span>
            <?php endif; ?>

            <?php if ($discount !== ''): ?>
                <span class="product-discount"><?= e($discount) ?></span>
            <?php endif; ?>

            <img
                src="<?= e((string) $product['image']) ?>"
                alt="<?= e((string) $product['name']) ?>"
                loading="lazy"
                width="500"
                height="450">

        </div>

    </a>

    <div class="product-info">

        <p class="product-category">
            <?= e((string) ($product['category_label'] ?? '')) ?>
        </p>

        <h3 class="product-name">
            <?= e((string) $product['name']) ?>
        </h3>

        <p class="product-reference">
            Réf. <?= e((string) $product['ref']) ?>
        </p>

        <p class="product-price">
            <?= e(format_price((int) $product['price'], $config)) ?>

            <?php if (!empty($product['old_price'])): ?>
                <span class="product-old-price">
                    <?= e(format_price((int) $product['old_price'], $config)) ?>
                </span>
            <?php endif; ?>
        </p>

        <?php if (!empty($product['available'])): ?>
            <a class="btn btn-dark" href="<?= e($product_url) ?>">
                Voir le produit →
            </a>
        <?php else: ?>
            <button class="btn btn-outline" type="button" disabled>
                Indisponible
            </button>
        <?php endif; ?>

    </div>

</article>
