<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$config = require $root . '/src/config.php';
require $root . '/src/helpers.php';
require $root . '/src/products.php';
require $root . '/src/schema.org.php';
require $root . '/src/opengraph.php';
require $root . '/src/whatsapp.php';

$title       = $config['site_name'] . ' | Des chaussures pour ton style';
$description = 'Découvre notre collection de sneakers, chaussures de ville, '
    . 'de sport et sandales. Commande facilement sur WhatsApp.';
$active_page = 'catalogue';
$topbar      = 'Découvre les modèles disponibles !';

$open_graph_tags      = render_open_graph($config, [
    'title'       => $title,
    'description' => $description,
    'type'        => 'website',
    'url'         => 'index.php',
]);
$schema_org_tags      = '';
$whatsapp_contact_url = whatsapp_contact_link($config);

try {
    $all_products = load_products($config);
} catch (RuntimeException $error) {
    http_response_code(500);
    require $root . '/templates/header.php';
    ?>
    <section class="section">
        <div class="container">
            <div class="empty-state">
                <h2>Catalogue momentanément indisponible</h2>
                <p>Les produits n'ont pas pu être chargés. Merci de réessayer plus tard.</p>
            </div>
        </div>
    </section>
    <?php
    require $root . '/templates/footer.php';
    exit;
}

$search   = trim((string) ($_GET['q'] ?? ''));
$category = (string) ($_GET['category'] ?? 'all');
$sort     = (string) ($_GET['sort'] ?? 'default');

$categories = list_categories($all_products);

if ($category !== 'all' && !array_key_exists($category, $categories)) {
    $category = 'all';
}

if (!in_array($sort, valid_sort_options(), true)) {
    $sort = 'default';
}

$products = filter_and_sort($all_products, $search, $category, $sort);

$og_image     = '';
$og_image_alt = $config['site_name'];

if ($products !== []) {
    $og_image     = (string) ($products[0]['og_image'] ?? $products[0]['image'] ?? '');
    $og_image_alt = (string) ($products[0]['name'] ?? $config['site_name']);
}

$open_graph_tags = render_open_graph($config, [
    'title'        => $title,
    'description'  => $description,
    'type'         => 'website',
    'url'          => 'index.php',
    'image'        => $og_image,
    'image_alt'    => $og_image_alt,
    'image_width'  => 1200,
    'image_height' => 630,
    'image_type'   => 'image/jpeg',
]);

$schema_org_tags = $products !== []
    ? render_schema_org(schema_org_item_list($products, $config))
    : '';

$sort_options = [
    'default'    => 'Tri par défaut',
    'price-asc'  => 'Prix croissant',
    'price-desc' => 'Prix décroissant',
    'name-asc'   => 'Nom A à Z',
];

require $root . '/templates/header.php';
?>

        <section class="hero" id="accueil">

            <div class="container hero-inner">

                <div class="hero-content">

                    <span class="hero-badge">TON STYLE. TES RÈGLES.</span>

                    <h1>
                        STEP INTO
                        <span>YOUR STYLE.</span>
                    </h1>

                    <p class="hero-description">
                        Des chaussures qui expriment ta personnalité.
                        Découvre notre collection et trouve la paire qui te correspond.
                    </p>

                    <div class="hero-actions">

                        <a href="#catalogue" class="btn btn-primary">
                            Découvrir la collection →
                        </a>

                        <a href="#nouveautes"
                           class="btn btn-outline"
                           style="color:white; border-color:#555;">
                            Nouveautés
                        </a>

                    </div>

                </div>

                <div class="hero-visual">
                    <div class="hero-circle">
                        <span aria-hidden="true">👟</span>
                    </div>
                </div>

            </div>

        </section>

        <section class="container trust-bar" aria-label="Nos avantages">

            <div class="trust-item">
                <span aria-hidden="true">✓</span>
                Plusieurs modèles
            </div>

            <div class="trust-item">
                <span aria-hidden="true">✓</span>
                Commande WhatsApp
            </div>

            <div class="trust-item">
                <span aria-hidden="true">✓</span>
                Assistance client
            </div>

        </section>

        <section class="section" id="catalogue">

            <div class="container">

                <div class="section-heading">

                    <div>

                        <span class="eyebrow">NOTRE SÉLECTION</span>

                        <h2 class="section-title">
                            Trouve ta prochaine paire.
                        </h2>

                        <p class="section-description">
                            Recherche, filtre et compare nos différents modèles.
                        </p>

                    </div>

                </div>

                <form class="catalogue-toolbar"
                      method="get"
                      action="index.php"
                      role="search">

                    <input
                        type="search"
                        id="search"
                        name="q"
                        class="catalogue-input"
                        placeholder="Rechercher une chaussure..."
                        aria-label="Rechercher une chaussure"
                        value="<?= e($search) ?>">

                    <select
                        id="categoryFilter"
                        name="category"
                        class="catalogue-select"
                        aria-label="Filtrer par catégorie">

                        <option value="all">Toutes les catégories</option>

                        <?php foreach ($categories as $code => $label): ?>
                            <option value="<?= e((string) $code) ?>"
                                <?= $category === $code ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <select
                        id="sortProducts"
                        name="sort"
                        class="catalogue-select"
                        aria-label="Trier les produits">

                        <?php foreach ($sort_options as $value => $label): ?>
                            <option value="<?= e($value) ?>"
                                <?= $sort === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button class="btn btn-dark catalogue-submit" type="submit">
                        Rechercher
                    </button>

                </form>

                <p class="catalogue-status" aria-live="polite">
                    <?= e((string) count($products)) ?>
                    produit<?= count($products) > 1 ? 's' : '' ?>
                    affiché<?= count($products) > 1 ? 's' : '' ?>.
                </p>

                <div class="product-grid" id="productGrid">

                    <?php if ($products === []): ?>

                        <div class="empty-state">
                            <h3>Aucun produit trouvé</h3>
                            <p>Essaie un autre nom ou modifie les filtres.</p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($products as $product): ?>
                            <?php require $root . '/templates/product-card.php'; ?>
                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <section class="section" id="nouveautes">

            <div class="container">

                <div class="promo-banner">

                    <div>

                        <h2>Ton prochain coup de cœur est ici.</h2>

                        <p>
                            Parcours notre sélection et contacte-nous
                            pour connaître les tailles disponibles.
                        </p>

                    </div>

                    <a href="<?= e($whatsapp_contact_url) ?>"
                       class="btn btn-dark"
                       target="_blank"
                       rel="noopener">
                        Nous contacter sur WhatsApp →
                    </a>

                </div>

            </div>

        </section>

<?php require $root . '/templates/footer.php'; ?>
