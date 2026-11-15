<?php

declare(strict_types=1);

$product_url = 'product.php?ref='.rawurlencode((string) $product['ref']);

if (!empty($product['is_new'])) {
    $status_badge = 'NOUVEAU';
} elseif (!empty($product['featured'])) {
    $status_badge = 'POPULAIRE';
} else {
    $status_badge = '';
}

$discount = '';
if (!empty($product['old_price']) && (int) $product['old_price'] > (int) $product['price']) {
    $discount = '−'.(int) round((1 - (int) $product['price'] / (int) $product['old_price']) * 100).'%';
}
?>
<article class="product-card<?php echo empty($product['available']) ? ' product-unavailable' : ''; ?>">

    <a href="<?php echo e($product_url); ?>"
       aria-label="Découvrir <?php echo e((string) $product['name']); ?>">

        <div class="product-image">

            <?php if ('' !== $status_badge) { ?>
                <span class="product-badge"><?php echo e($status_badge); ?></span>
            <?php } ?>

            <?php if ('' !== $discount) { ?>
                <span class="product-discount"><?php echo e($discount); ?></span>
            <?php } ?>

            <img
                src="<?php echo e((string) $product['image']); ?>"
                alt="<?php echo e((string) $product['name']); ?>"
                loading="lazy"
                width="500"
                height="450">

        </div>

    </a>

    <div class="product-info">

        <p class="product-category">
            <?php echo e((string) ($product['category_label'] ?? '')); ?>
        </p>

        <h3 class="product-name">
            <?php echo e((string) $product['name']); ?>
        </h3>

        <p class="product-reference">
            Réf. <?php echo e((string) $product['ref']); ?>
        </p>

        <p class="product-price">
            <?php echo e(format_price((int) $product['price'], $config)); ?>

            <?php if (!empty($product['old_price'])) { ?>
                <span class="product-old-price">
                    <?php echo e(format_price((int) $product['old_price'], $config)); ?>
                </span>
            <?php } ?>
        </p>

        <?php if (!empty($product['available'])) { ?>
            <a class="btn btn-dark" href="<?php echo e($product_url); ?>">
                Voir le produit →
            </a>
        <?php } else { ?>
            <button class="btn btn-outline" type="button" disabled>
                Indisponible
            </button>
        <?php } ?>

    </div>

</article>
