<?php

declare(strict_types=1);

use ShoesShop\AdminAuth;
use ShoesShop\Database;
use ShoesShop\ProductRepository;

$root = dirname(__DIR__, 2);

$config = require $root.'/src/bootstrap.php';

$auth = new AdminAuth($config);
$auth->requireLogin();

$error = null;
$products = [];

try {
    $repository = new ProductRepository(Database::connect($config));
    $products = $repository->all();
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    $error = 'Impossible de charger les produits. Vérifie que la base est initialisée avec `composer db:init`.';
}

$pageTitle = 'Produits';
$productCount = count($products);

require $root.'/templates/admin-header.php';
?>

<section class="admin-heading">

    <div>
        <h1>Produits</h1>
        <p class="admin-muted">
            <?php echo $productCount; ?> produit<?php echo 1 === $productCount ? '' : 's'; ?> dans le catalogue.
        </p>
    </div>

    <a href="edit.php" class="admin-btn admin-btn-primary">+ Nouveau produit</a>

</section>

<?php if (null !== $error) { ?>

    <div class="admin-flash admin-flash-error" role="alert">
        <?php echo e($error); ?>
    </div>

<?php } elseif ([] === $products) { ?>

    <div class="admin-empty">
        <p>Aucun produit pour le moment.</p>
        <a href="edit.php" class="admin-btn admin-btn-primary">Créer le premier produit</a>
    </div>

<?php } else { ?>

    <div class="admin-table-wrapper">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>Réf.</th>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Mise en avant</th>
                    <th class="admin-actions-column">Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($products as $product) { ?>

                    <tr>
                        <td><code><?php echo e($product->ref); ?></code></td>

                        <td>
                            <strong><?php echo e($product->name); ?></strong>

                            <small class="admin-muted">
                                <?php echo e(implode(', ', $product->sizes)); ?>
                                —
                                <?php echo e(implode(', ', $product->colors)); ?>
                            </small>
                        </td>

                        <td>
                            <?php echo e('' !== $product->categoryLabel ? $product->categoryLabel : $product->category); ?>
                        </td>

                        <td>
                            <?php echo e(format_price($product->price, $config)); ?>

                            <?php if (null !== $product->oldPrice) { ?>
                                <small class="admin-muted admin-old-price">
                                    <?php echo e(format_price($product->oldPrice, $config)); ?>
                                </small>
                            <?php } ?>
                        </td>

                        <td>
                            <span class="admin-badge <?php echo $product->available ? 'admin-badge-success' : 'admin-badge-muted'; ?>">
                                <?php echo $product->available ? 'Disponible' : 'Indisponible'; ?>
                            </span>
                        </td>

                        <td>
                            <?php if ($product->featured) { ?>
                                <span class="admin-badge admin-badge-accent">Oui</span>
                            <?php } else { ?>
                                <span class="admin-muted">Non</span>
                            <?php } ?>

                            <?php if ($product->isNew) { ?>
                                <span class="admin-badge admin-badge-info">Nouveau</span>
                            <?php } ?>
                        </td>

                        <td class="admin-actions">
                            <a href="edit.php?ref=<?php echo e(rawurlencode($product->ref)); ?>" class="admin-btn admin-btn-small">
                                Modifier
                            </a>

                            <form method="post" action="delete.php" class="admin-inline-form" onsubmit="return confirm('Supprimer définitivement ce produit ?');">
                                <input type="hidden" name="csrf" value="<?php echo e($auth->csrfToken()); ?>">
                                <input type="hidden" name="ref" value="<?php echo e($product->ref); ?>">
                                <button type="submit" class="admin-btn admin-btn-small admin-btn-danger">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>

                <?php } ?>

            </tbody>

        </table>

    </div>

<?php } ?>

<?php require $root.'/templates/admin-footer.php'; ?>
