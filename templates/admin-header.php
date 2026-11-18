<?php

declare(strict_types=1);

$pageTitle ??= 'Administration';
$flash = $auth->pullFlash();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex, nofollow">

    <title><?php echo e($pageTitle); ?> | Admin <?php echo e($config['site_name']); ?></title>

    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body>

    <header class="admin-header">

        <div class="admin-header-inner">

            <a href="index.php" class="admin-brand">

                <span class="admin-brand-mark">S</span>

                <span>
                    <strong>Administration</strong>
                    <small><?php echo e($config['site_name']); ?></small>
                </span>

            </a>

            <nav class="admin-nav" aria-label="Navigation administration">

                <?php if ($auth->check()) { ?>

                    <a href="index.php">Produits</a>

                    <a href="edit.php" class="admin-nav-primary">Nouveau produit</a>

                    <form method="post" action="logout.php" class="admin-logout">
                        <input type="hidden" name="csrf" value="<?php echo e($auth->csrfToken()); ?>">
                        <button type="submit" class="admin-link-button">Déconnexion</button>
                    </form>

                <?php } else { ?>

                    <a href="login.php">Connexion</a>

                <?php } ?>

            </nav>

        </div>

    </header>

    <main class="admin-main">

        <?php if (null !== $flash) { ?>

            <div class="admin-flash admin-flash-<?php echo e($flash['type']); ?>" role="status">
                <?php echo e($flash['message']); ?>
            </div>

        <?php } ?>
