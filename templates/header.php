<?php

declare(strict_types=1);

$active_page = $active_page ?? 'home';
$topbar = $topbar ?? 'Découvre les modèles disponibles !';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="<?php echo e($description); ?>">

    <?php echo $open_graph_tags ?? ''; ?>

    <?php echo $schema_org_tags ?? ''; ?>

    <meta name="theme-color" content="#0B0B0D">

    <title><?php echo e($title); ?></title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="topbar">
        <p>
            <strong><?php echo e($config['site_name']); ?></strong>
            — <?php echo e($topbar); ?>
        </p>
    </div>

    <header class="header">

        <div class="container header-inner">

            <a href="index.php"
                class="logo"
                aria-label="<?php echo e($config['site_name']); ?> - Accueil">

                SHOES <span>- SHOP</span>

            </a>

            <div class="header-actions">

                <button
                    class="icon-btn"
                    type="button"
                    data-focus-search
                    aria-label="Rechercher une chaussure">

                    ⌕

                </button>

            </div>

        </div>

        <nav class="nav container"
            aria-label="Navigation principale">

            <a href="index.php#accueil"
                <?php echo 'home' === $active_page ? 'class="active"' : ''; ?>>
                Accueil
            </a>

            <a href="index.php#catalogue"
                <?php echo 'catalogue' === $active_page ? 'class="active"' : ''; ?>>
                Catalogue
            </a>

            <a href="index.php#nouveautes">
                Nouveautés
            </a>

            <a href="index.php#contact">
                Contact
            </a>

        </nav>

    </header>

    <main>
