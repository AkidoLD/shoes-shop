<?php

declare(strict_types=1);

use ShoesShop\AdminAuth;
use ShoesShop\Database;
use ShoesShop\ProductRepository;

$root = dirname(__DIR__, 2);

$config = require $root.'/src/bootstrap.php';

$auth = new AdminAuth($config);
$auth->requireLogin();

$ref = trim((string) ($_GET['ref'] ?? ''));

try {
    $repository = new ProductRepository(Database::connect($config));
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    http_response_code(500);

    $pageTitle = 'Base de données indisponible';

    require $root.'/templates/admin-header.php';
    ?>
    <div class="admin-empty">
        <h1>Base de données indisponible</h1>
        <p>Lance <code>composer db:init</code> puis recharge la page.</p>
        <a href="index.php" class="admin-btn admin-btn-primary">Retour à la liste</a>
    </div>
    <?php
    require $root.'/templates/admin-footer.php';

    exit;
}

$product = '' !== $ref ? $repository->findByRef($ref) : null;

if ('' !== $ref && null === $product) {
    http_response_code(404);

    $pageTitle = 'Produit introuvable';

    require $root.'/templates/admin-header.php';
    ?>
    <div class="admin-empty">
        <h1>Produit introuvable</h1>
        <p>La référence <code><?php echo e($ref); ?></code> n'existe pas.</p>
        <a href="index.php" class="admin-btn admin-btn-primary">Retour à la liste</a>
    </div>
    <?php
    require $root.'/templates/admin-footer.php';

    exit;
}

$form = $product?->toArray() ?? [
    'ref' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'old_price' => null,
    'currency' => 'XAF',
    'image' => 'assets/img/',
    'og_image' => null,
    'category' => '',
    'category_label' => '',
    'sizes' => [],
    'colors' => [],
    'available' => true,
    'featured' => false,
    'is_new' => false,
];

$error = null;

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    if (!$auth->verifyCsrf($_POST['csrf'] ?? null)) {
        http_response_code(400);
        $error = 'Session expirée. Recharge la page et réessaie.';
    } else {
        $list = static function (mixed $value): array {
            $value = is_scalar($value) ? (string) $value : '';
            $parts = preg_split('/[\s,;]+/', $value) ?: [];

            return array_values(array_filter(
                array_map(static fn (string $part): string => trim($part), $parts),
                static fn (string $part): bool => '' !== $part
            ));
        };

        $form = [
            'ref' => trim((string) ($_POST['ref'] ?? '')),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')),
            'old_price' => trim((string) ($_POST['old_price'] ?? '')),
            'currency' => trim((string) ($_POST['currency'] ?? 'XAF')),
            'image' => trim((string) ($_POST['image'] ?? '')),
            'og_image' => trim((string) ($_POST['og_image'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'category_label' => trim((string) ($_POST['category_label'] ?? '')),
            'sizes' => $list($_POST['sizes'] ?? ''),
            'colors' => $list($_POST['colors'] ?? ''),
            'available' => isset($_POST['available']),
            'featured' => isset($_POST['featured']),
            'is_new' => isset($_POST['is_new']),
        ];

        try {
            if (null === $product) {
                $saved = $repository->create($form);
                $auth->flash('Produit créé : '.$saved->ref);
            } else {
                $saved = $repository->update($product->ref, $form);
                $auth->flash('Produit mis à jour : '.$saved->ref);
            }

            header('Location: index.php');

            exit;
        } catch (InvalidArgumentException|RuntimeException $exception) {
            $error = $exception->getMessage();
        }
    }
}

$categories = $repository->categories();
$pageTitle = null === $product ? 'Nouveau produit' : 'Modifier '.$product->ref;

require $root.'/templates/admin-header.php';
?>

<section class="admin-heading">

    <div>
        <h1><?php echo $pageTitle; ?></h1>
        <p class="admin-muted">
            <?php echo null === $product
                ? 'Ajoute un nouveau modèle au catalogue.'
                : 'Modifie les informations du produit.'; ?>
        </p>
    </div>

    <a href="index.php" class="admin-btn">← Retour à la liste</a>

</section>

<?php if (null !== $error) { ?>
    <div class="admin-flash admin-flash-error" role="alert">
        <?php echo e($error); ?>
    </div>
<?php } ?>

<form method="post" action="edit.php<?php echo null === $product ? '' : '?ref='.rawurlencode($product->ref); ?>">

    <input type="hidden" name="csrf" value="<?php echo e($auth->csrfToken()); ?>">

    <div class="admin-form-section">

        <h2>Informations produit</h2>

        <div class="admin-form-grid">

            <label>
                <span>Référence *</span>
                <input
                    type="text"
                    name="ref"
                    value="<?php echo e((string) $form['ref']); ?>"
                    placeholder="SH009"
                    required>
            </label>

            <label>
                <span>Nom *</span>
                <input
                    type="text"
                    name="name"
                    value="<?php echo e((string) $form['name']); ?>"
                    placeholder="Sneakers Urban"
                    required>
            </label>

            <label class="admin-field-full">
                <span>Description</span>
                <textarea name="description" placeholder="Décris le produit en quelques phrases."><?php echo e((string) $form['description']); ?></textarea>
            </label>

            <label>
                <span>Prix *</span>
                <input
                    type="number"
                    name="price"
                    value="<?php echo e((string) $form['price']); ?>"
                    min="0"
                    step="1"
                    placeholder="15000"
                    required>
            </label>

            <label>
                <span>Ancien prix</span>
                <input
                    type="number"
                    name="old_price"
                    value="<?php echo e((string) ($form['old_price'] ?? '')); ?>"
                    min="0"
                    step="1"
                    placeholder="Laisser vide si pas de promotion">
            </label>

            <label>
                <span>Devise</span>
                <input
                    type="text"
                    name="currency"
                    value="<?php echo e((string) $form['currency']); ?>"
                    maxlength="3"
                    placeholder="XAF">
            </label>

            <label>
                <span>Catégorie (code)</span>
                <input
                    type="text"
                    name="category"
                    value="<?php echo e((string) $form['category']); ?>"
                    list="admin-category-codes"
                    placeholder="sneakers">
                <datalist id="admin-category-codes">
                    <?php foreach ($categories as $code => $label) { ?>
                        <option value="<?php echo e((string) $code); ?>"><?php echo e((string) $label); ?></option>
                    <?php } ?>
                </datalist>
            </label>

            <label>
                <span>Libellé catégorie</span>
                <input
                    type="text"
                    name="category_label"
                    value="<?php echo e((string) $form['category_label']); ?>"
                    placeholder="Sneakers">
            </label>

            <label class="admin-field-full">
                <span>Image principale *</span>
                <input
                    type="text"
                    name="image"
                    value="<?php echo e((string) $form['image']); ?>"
                    placeholder="assets/img/sh009.jpg"
                    required>
            </label>

            <label class="admin-field-full">
                <span>Image Open Graph</span>
                <input
                    type="text"
                    name="og_image"
                    value="<?php echo e((string) ($form['og_image'] ?? '')); ?>"
                    placeholder="assets/img/og/sh009.jpg">
            </label>

        </div>

        <p class="admin-hint">
            Les chemins d'image sont relatifs à <code>public/</code>.
            L'image Open Graph peut rester vide : elle sera générée par
            <code>composer og:generate</code>.
        </p>

    </div>

    <div class="admin-form-section">

        <h2>Tailles, couleurs et statut</h2>

        <div class="admin-form-grid">

            <label>
                <span>Tailles</span>
                <input
                    type="text"
                    name="sizes"
                    value="<?php echo e(implode(', ', (array) $form['sizes'])); ?>"
                    placeholder="39, 40, 41, 42">
            </label>

            <label>
                <span>Couleurs</span>
                <input
                    type="text"
                    name="colors"
                    value="<?php echo e(implode(', ', (array) $form['colors'])); ?>"
                    placeholder="Noir, Blanc, Rouge">
            </label>

            <div class="admin-field-full admin-checkboxes">

                <label class="admin-checkbox">
                    <input
                        type="checkbox"
                        name="available"
                        value="1"
                        <?php echo !empty($form['available']) ? 'checked' : ''; ?>>
                    <span>Disponible</span>
                </label>

                <label class="admin-checkbox">
                    <input
                        type="checkbox"
                        name="featured"
                        value="1"
                        <?php echo !empty($form['featured']) ? 'checked' : ''; ?>>
                    <span>Mise en avant</span>
                </label>

                <label class="admin-checkbox">
                    <input
                        type="checkbox"
                        name="is_new"
                        value="1"
                        <?php echo !empty($form['is_new']) ? 'checked' : ''; ?>>
                    <span>Nouveauté</span>
                </label>

            </div>

        </div>

    </div>

    <div class="admin-form-actions">

        <button type="submit" class="admin-btn admin-btn-primary">
            <?php echo null === $product ? 'Créer le produit' : 'Enregistrer les modifications'; ?>
        </button>

        <a href="index.php" class="admin-btn">Annuler</a>

    </div>

</form>

<?php require $root.'/templates/admin-footer.php'; ?>
