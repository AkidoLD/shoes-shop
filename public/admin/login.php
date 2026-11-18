<?php

declare(strict_types=1);

use ShoesShop\AdminAuth;

$root = dirname(__DIR__, 2);

$config = require $root.'/src/bootstrap.php';

$auth = new AdminAuth($config);

if ($auth->check()) {
    header('Location: index.php');

    exit;
}

$error = null;
$username = '';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $username = trim((string) ($_POST['username'] ?? ''));

    if (!$auth->verifyCsrf($_POST['csrf'] ?? null)) {
        http_response_code(400);
        $error = 'Session expirée. Recharge la page et réessaie.';
    } elseif ($auth->attempt($username, (string) ($_POST['password'] ?? ''))) {
        header('Location: index.php');

        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}

$pageTitle = 'Connexion';
$loggedOut = isset($_GET['logged_out']);

require $root.'/templates/admin-header.php';
?>

<section class="admin-login">

    <div class="admin-card">

        <h1>Connexion admin</h1>

        <p class="admin-muted">
            Connecte-toi pour gérer les produits du catalogue.
        </p>

        <?php if ($loggedOut) { ?>
            <div class="admin-flash admin-flash-success" role="status">
                Tu es déconnecté.
            </div>
        <?php } ?>

        <?php if (null !== $error) { ?>
            <div class="admin-flash admin-flash-error" role="alert">
                <?php echo e($error); ?>
            </div>
        <?php } ?>

        <form method="post" action="login.php" class="admin-form">

            <input type="hidden" name="csrf" value="<?php echo e($auth->csrfToken()); ?>">

            <label>
                <span>Utilisateur</span>
                <input
                    type="text"
                    name="username"
                    value="<?php echo e($username); ?>"
                    autocomplete="username"
                    required
                    autofocus>
            </label>

            <label>
                <span>Mot de passe</span>
                <input
                    type="password"
                    name="password"
                    autocomplete="current-password"
                    required>
            </label>

            <button type="submit" class="admin-btn admin-btn-primary">
                Se connecter
            </button>

        </form>

        <p class="admin-hint">
            Identifiants par défaut : <code>admin</code> / <code>admin</code>.
            À modifier dans <code>src/config.local.php</code>.
        </p>

    </div>

</section>

<?php require $root.'/templates/admin-footer.php'; ?>
