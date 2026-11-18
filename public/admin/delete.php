<?php

declare(strict_types=1);

use ShoesShop\AdminAuth;
use ShoesShop\Database;
use ShoesShop\ProductRepository;

$root = dirname(__DIR__, 2);

$config = require $root.'/src/bootstrap.php';

$auth = new AdminAuth($config);
$auth->requireLogin();

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);

    exit('Méthode non autorisée.');
}

if (!$auth->verifyCsrf($_POST['csrf'] ?? null)) {
    http_response_code(400);

    exit('Jeton CSRF invalide.');
}

$ref = trim((string) ($_POST['ref'] ?? ''));

if ('' === $ref) {
    $auth->flash('Référence produit manquante.', 'error');
    header('Location: index.php');

    exit;
}

try {
    $repository = new ProductRepository(Database::connect($config));
    $deleted = $repository->delete($ref);
    $auth->flash(
        $deleted ? 'Produit supprimé : '.$ref : 'Produit introuvable : '.$ref,
        $deleted ? 'success' : 'error'
    );
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    $auth->flash('Impossible de supprimer le produit.', 'error');
}

header('Location: index.php');

exit;
