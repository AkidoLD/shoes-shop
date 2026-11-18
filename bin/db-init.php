#!/usr/bin/env php
<?php

declare(strict_types=1);

use ShoesShop\Database;

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$config = require $root.'/src/config.php';
$databasePath = (string) ($config['db_path'] ?? '');

if ('' === $databasePath) {
    fwrite(STDERR, "Le chemin de la base (db_path) est vide.\n");

    exit(1);
}

$force = in_array('--force', $argv, true);

if (is_file($databasePath)) {
    if (!$force) {
        fwrite(STDOUT, "La base existe déjà : {$databasePath}\n");
        fwrite(STDOUT, "Utilise --force pour la recréer.\n");

        exit(0);
    }

    foreach ([$databasePath, $databasePath.'-wal', $databasePath.'-shm'] as $file) {
        if (is_file($file) && !unlink($file)) {
            fwrite(STDERR, "Impossible de supprimer : {$file}\n");

            exit(1);
        }
    }
}

try {
    $pdo = Database::connect($config, false);
    Database::migrate($pdo, $root.'/database/schema.sql');
    Database::seed($pdo, $root.'/database/seed.sql');
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage()."\n");

    exit(1);
}

fwrite(STDOUT, "Base initialisée : {$databasePath}\n");
