<?php

declare(strict_types=1);

namespace ShoesShop;

final class Database
{
    public static function connect(array $config, bool $requireSchema = true): \PDO
    {
        $path = trim((string) ($config['db_path'] ?? ''));

        if ('' === $path) {
            throw new \RuntimeException('Le chemin de la base SQLite (db_path) est vide.');
        }

        if ($requireSchema && !is_file($path)) {
            throw new \RuntimeException('Base de données introuvable : '.$path.'. Lance `composer db:init`.');
        }

        $directory = dirname($path);

        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de créer le dossier de la base : '.$directory);
        }

        $pdo = new \PDO('sqlite:'.$path, null, null, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');

        if ($requireSchema) {
            $table = $pdo
                ->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'products'")
                ->fetchColumn()
            ;

            if (false === $table) {
                throw new \RuntimeException('Base de données non initialisée. Lance `composer db:init`.');
            }
        }

        return $pdo;
    }

    public static function migrate(\PDO $pdo, string $schemaPath): void
    {
        self::executeSqlFile($pdo, $schemaPath);
    }

    public static function seed(\PDO $pdo, string $seedPath): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();

        if ($count > 0) {
            return;
        }

        $pdo->beginTransaction();

        try {
            self::executeSqlFile($pdo, $seedPath);
            $pdo->commit();
        } catch (\Throwable $error) {
            $pdo->rollBack();

            throw $error;
        }
    }

    private static function executeSqlFile(\PDO $pdo, string $path): void
    {
        $sql = file_get_contents($path);

        if (false === $sql) {
            throw new \RuntimeException('Impossible de lire le fichier SQL : '.$path);
        }

        $pdo->exec($sql);
    }
}
