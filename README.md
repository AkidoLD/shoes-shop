# shoes-shop

TP-4 E-Business : **étude de marché numérique et closing WhatsApp**.

Catalogue de chaussures en PHP, rendu côté serveur, avec un tunnel de conversion
conversationnel articulé sur WhatsApp Business.

## Fonctionnalités

- **Catalogue** : recherche, filtre par catégorie et tri, sans JavaScript.
- **SQLite + PDO** : produits, catégories, tailles et couleurs en base, avec une
  couche de persistance (`ProductRepository`) pour le CRUD.
- **Fiche produit** : `product.php?ref=SH001` (404 si la référence est inconnue).
- **Schema.org (JSON-LD)** : `Product` + `Offer` sur chaque fiche, `ItemList` du
  catalogue sur la page d'accueil.
- **Open Graph / Twitter Card** : aperçu riche (titre, description, image, prix)
  lors du partage d'un lien sur WhatsApp, Facebook, LinkedIn, Telegram…
- **Génération des images OG** : recadrage centré 1200 × 630 via GD
  (`composer og:generate`).
- **Closing WhatsApp** : liens `wa.me` pré-remplis avec le modèle, la référence,
  le prix et l'URL du produit.

## Démarrage

Prérequis : PHP 8.4 avec `pdo_sqlite` et `gd`, puis Composer.

```bash
composer install
cp src/config.example.php src/config.local.php
# Renseigner whatsapp_number dans src/config.local.php

composer db:init
# ou, pour repartir de zéro :
# composer db:init -- --force

# Facultatif : régénérer les images Open Graph
composer og:generate

php -S localhost:8000 -t public
```

Puis ouvrir <http://localhost:8000>.

La base est créée dans `data/shoes.sqlite` (ignorée par Git). Sur Ubuntu,
l'extension GD s'installe avec `sudo apt install php8.4-gd`.

## Configuration

`src/config.local.php` (ignoré par Git) surcharge `src/config.php` :

| Clé | Rôle |
| --- | --- |
| `site_url` | URL publique du site (vide = déduite de la requête) |
| `whatsapp_number` | Numéro WhatsApp au format international, sans `+` ni espaces |
| `site_name` | Nom affiché du magasin |
| `currency_label` | Symbole affiché après les montants (`FCFA`) |
| `og_image` | Image de partage par défaut (vide = première image produit) |
| `db_path` | Chemin de la base SQLite (vide = `data/shoes.sqlite`) |

## Base de données

Le schéma est dans `database/schema.sql` et les données initiales dans
`database/seed.sql`. `composer db:init` crée la base et importe les produits.
Le CRUD est exposé par `ShoesShop\ProductRepository` :

```php
$repository = new ProductRepository(Database::connect($config));

$product = $repository->create([...]);
$repository->update('SH001', ['price' => 16000]);
$repository->delete('SH002');
```

L'interface d'administration n'est pas incluse dans cette branche : elle fera
l'objet d'une branche dédiée.

## Tests

```bash
composer test
composer php-cs-fixer -- fix --dry-run
```

Tests manuels :

- Schema.org : [Rich Results Test](https://search.google.com/test/rich-results)
- Open Graph : partager l'URL dans WhatsApp (ou utiliser un débogueur de liens)
- Liens WhatsApp : cliquer sur « Commander sur WhatsApp » depuis une fiche

## Documentation

- [`docs/architecture.md`](docs/architecture.md) — structure et rôle des fichiers
- [`docs/data.md`](docs/data.md) — schéma SQLite, champs et import
- [`docs/tp4.md`](docs/tp4.md) — détail des trois fonctionnalités du TP 4
- [`docs/support-tp4.pptx`](docs/support-tp4.pptx) — support de soutenance (18 slides)
