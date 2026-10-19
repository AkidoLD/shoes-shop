<?php

declare(strict_types=1);

/*
 * Product page: product.php?ref=SH001
 *
 * The product is read here (logic) then handed to the templates (display).
 * If the reference is missing or unknown, we answer with HTTP 404.
 */

$root = dirname(__DIR__);

$config = require $root . '/src/config.php';
require $root . '/src/helpers.php';
require $root . '/src/products.php';

$ref     = trim((string) ($_GET['ref'] ?? ''));
$product = null;

try {
    if ($ref !== '') {
        $product = find_product_by_ref(load_products($config), $ref);
    }
} catch (RuntimeException $error) {
    $product = null;
}


// Case 1: missing or unknown reference -> 404.

if ($product === null) {

    http_response_code(404);

    $title       = 'Produit introuvable | ' . $config['site_name'];
    $description = 'Ce produit n\'existe pas ou n\'est plus disponible.';
    $active_page = 'catalogue';
    $topbar      = 'Produit introuvable';

    require $root . '/templates/header.php';
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
    require $root . '/templates/footer.php';
    exit;
}


// Case 2: product found -> render the page.

$title       = (string) $product['name'] . ' | ' . $config['site_name'];
$description = (string) ($product['description'] ?? '');
$active_page = 'catalogue';
$topbar      = 'Trouve la paire qui te correspond !';

$available = !empty($product['available']);

require $root . '/templates/header.php';
?>

        <section class="section">

            <div class="container">

                <!-- FIL D'ARIANE -->
                <div class="breadcrumb">
                    <a href="index.php">Accueil</a>
                    <span> / </span>
                    <a href="index.php#catalogue">Catalogue</a>
                    <span> / </span>
                    <span><?= e((string) $product['name']) ?></span>
                </div>


                <div class="product-detail-layout">

                    <!-- IMAGE -->
                    <div class="product-detail-image">
                        <img
                            src="<?= e((string) $product['image']) ?>"
                            alt="<?= e((string) $product['name']) ?>">
                    </div>


                    <!-- INFORMATIONS -->
                    <div class="product-detail-info">

                        <p class="product-detail-category">
                            <?= e((string) ($product['category_label'] ?? '')) ?>
                        </p>

                        <h1 class="product-detail-title">
                            <?= e((string) $product['name']) ?>
                        </h1>

                        <p class="product-detail-reference">
                            Référence : <?= e((string) $product['ref']) ?>
                        </p>

                        <p class="product-detail-price">
                            <?= e(format_price((int) $product['price'], $config)) ?>

                            <?php if (!empty($product['old_price'])): ?>
                                <span class="product-detail-old-price">
                                    <?= e(format_price((int) $product['old_price'], $config)) ?>
                                </span>
                            <?php endif; ?>
                        </p>

                        <p class="product-detail-description">
                            <?= e((string) ($product['description'] ?? '')) ?>
                        </p>

                        <div class="product-detail-stock<?= $available ? '' : ' unavailable' ?>">
                            <?= $available ? 'Disponible sur commande' : 'Indisponible' ?>
                        </div>


                        <!-- CHOIX DE LA TAILLE -->
                        <?php if (!empty($product['sizes'])): ?>
                            <div class="product-option">
                                <label>Choisis ta taille</label>
                                <div class="product-size-list">
                                    <?php foreach ((array) $product['sizes'] as $size): ?>
                                        <button type="button"
                                                class="option-button size-option"
                                                data-size="<?= e((string) $size) ?>"
                                                aria-pressed="false">
                                            <?= e((string) $size) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>


                        <!-- CHOIX DE LA COULEUR -->
                        <?php if (!empty($product['colors'])): ?>
                            <div class="product-option">
                                <label>Choisis ta couleur</label>
                                <div class="product-color-list">
                                    <?php foreach ((array) $product['colors'] as $color): ?>
                                        <button type="button"
                                                class="option-button color-option"
                                                data-color="<?= e((string) $color) ?>"
                                                aria-pressed="false">
                                            <?= e((string) $color) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>


                        <!-- ACTIONS -->
                        <div class="product-detail-actions">

                            <!-- TODO(group): lien WhatsApp de commande du produit -->
                            <a href="#" class="btn btn-primary">
                                Commander directement →
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

<?php require $root . '/templates/footer.php'; ?>
