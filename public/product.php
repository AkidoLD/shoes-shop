<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$config = require $root.'/src/config.php';

require $root.'/src/helpers.php';

require $root.'/src/products.php';

require $root.'/src/schema.org.php';

require $root.'/src/opengraph.php';

require $root.'/src/whatsapp.php';

$whatsapp_contact_url = whatsapp_contact_link($config);
$open_graph_tags = '';
$schema_org_tags = '';

$ref = trim((string) ($_GET['ref'] ?? ''));
$product = null;

try {
    if ('' !== $ref) {
        $product = find_product_by_ref(load_products($config), $ref);
    }
} catch (RuntimeException $error) {
    $product = null;
}

if (null === $product) {
    http_response_code(404);

    $title = 'Produit introuvable | '.$config['site_name'];
    $description = 'Ce produit n\'existe pas ou n\'est plus disponible.';
    $active_page = 'catalogue';
    $topbar = 'Produit introuvable';

    $open_graph_tags = render_open_graph($config, [
        'title' => $title,
        'description' => $description,
        'type' => 'website',
        'url' => 'index.php',
    ]);

    require $root.'/templates/header.php';
    ?>

    <section class="section">

        <div class="container">

            <div class="breadcrumb">
                <a href="index.php">Accueil</a>
                <span> / </span>
                <a href="index.php#catalogue">Catalogue</a>
                <span> / </span>
                <span>Produit introuvable</span>
            </div>

            <div class="empty-state">
                <h2>Produit introuvable</h2>
                <p>
                    Ce produit n'existe pas ou n'est plus disponible
                    dans notre catalogue.
                </p>
                <br>
                <a href="index.php#catalogue" class="btn btn-primary">
                    Retour au catalogue
                </a>
            </div>

        </div>

    </section>

    <?php
    require $root.'/templates/footer.php';

    exit;
}

$title = (string) $product['name'].' | '.$config['site_name'];
$description = (string) ($product['description'] ?? '');
$active_page = 'catalogue';
$topbar = 'Trouve la paire qui te correspond !';

$available = !empty($product['available']);

$product_url = absolute_url(
    'product.php?ref='.rawurlencode((string) $product['ref']),
    $config
);

$whatsapp_order_url = whatsapp_product_link($product, $config);

$open_graph_tags = render_open_graph($config, [
    'title' => (string) $product['name'],
    'description' => $description,
    'type' => 'product',
    'url' => $product_url,
    'image' => (string) ($product['og_image'] ?? $product['image'] ?? ''),
    'image_alt' => (string) $product['name'],
    'image_width' => 1200,
    'image_height' => 630,
    'image_type' => 'image/jpeg',
    'price' => $product['price'] ?? null,
    'currency' => (string) ($product['currency'] ?? 'XAF'),
    'availability' => $available ? 'in stock' : 'out of stock',
]);

$schema_org_tags = render_schema_org(
    schema_org_product($product, $config, $product_url)
);

require $root.'/templates/header.php';
?>

        <section class="section">

            <div class="container">

                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span> / </span>
                    <a href="index.php#catalogue">Catalogue</a>
                    <span> / </span>
                    <span><?php echo e((string) $product['name']); ?></span>
                </div>

                <div class="product-detail-layout">

                    <div class="product-detail-image">
                        <img
                            src="<?php echo e((string) $product['image']); ?>"
                            alt="<?php echo e((string) $product['name']); ?>">
                    </div>

                    <div class="product-detail-info">

                        <p class="product-detail-category">
                            <?php echo e((string) ($product['category_label'] ?? '')); ?>
                        </p>

                        <h1 class="product-detail-title">
                            <?php echo e((string) $product['name']); ?>
                        </h1>

                        <p class="product-detail-reference">
                            Référence : <?php echo e((string) $product['ref']); ?>
                        </p>

                        <p class="product-detail-price">
                            <?php echo e(format_price((int) $product['price'], $config)); ?>

                            <?php if (!empty($product['old_price'])) { ?>
                                <span class="product-detail-old-price">
                                    <?php echo e(format_price((int) $product['old_price'], $config)); ?>
                                </span>
                            <?php } ?>
                        </p>

                        <p class="product-detail-description">
                            <?php echo e((string) ($product['description'] ?? '')); ?>
                        </p>

                        <div class="product-detail-stock<?php echo $available ? '' : ' unavailable'; ?>">
                            <?php echo $available ? 'Disponible sur commande' : 'Indisponible'; ?>
                        </div>

                        <?php if (!empty($product['sizes'])) { ?>
                            <div class="product-option">
                                <label>Choisis ta taille</label>
                                <div class="product-size-list">
                                    <?php foreach ((array) $product['sizes'] as $size) { ?>
                                        <button type="button"
                                                class="option-button size-option"
                                                data-size="<?php echo e((string) $size); ?>"
                                                aria-pressed="false">
                                            <?php echo e((string) $size); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if (!empty($product['colors'])) { ?>
                            <div class="product-option">
                                <label>Choisis ta couleur</label>
                                <div class="product-color-list">
                                    <?php foreach ((array) $product['colors'] as $color) { ?>
                                        <button type="button"
                                                class="option-button color-option"
                                                data-color="<?php echo e((string) $color); ?>"
                                                aria-pressed="false">
                                            <?php echo e((string) $color); ?>
                                        </button>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="product-detail-actions">

                            <a href="<?php echo e($whatsapp_order_url); ?>"
                               class="btn btn-primary"
                               target="_blank"
                               rel="noopener">
                                Commander sur WhatsApp →
                            </a>

                            <a href="index.php#catalogue" class="btn btn-outline">
                                ← Retour au catalogue
                            </a>

                        </div>

                        <p class="product-note">
                            Confirme la disponibilité de la taille et de la couleur
                            avec notre équipe avant de finaliser ta commande.
                        </p>

                    </div>

                </div>

            </div>

        </section>

<?php require $root.'/templates/footer.php'; ?>
