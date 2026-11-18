<?php

declare(strict_types=1);

use ShoesShop\AdminAuth;

$root = dirname(__DIR__, 2);

$config = require $root.'/src/bootstrap.php';

$auth = new AdminAuth($config);

if ('POST' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);

    exit('Méthode non autorisée.');
}

if (!$auth->verifyCsrf($_POST['csrf'] ?? null)) {
    http_response_code(400);

    exit('Jeton CSRF invalide.');
}

$auth->logout();

header('Location: login.php?logged_out=1');

exit;
