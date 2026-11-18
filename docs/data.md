# Données des produits

Source unique : **`data/shoes.sqlite`** (SQLite, accédée avec PDO).
Le schéma est versionné dans `database/schema.sql` et les données initiales dans
`database/seed.sql`. La page, les balises Open Graph, le JSON-LD et le lien
WhatsApp lisent tous le même enregistrement : un produit n'est défini qu'à un
seul endroit.

## Initialisation

```bash
composer db:init
# recréer la base depuis zéro
composer db:init -- --force
```

La base est ignorée par Git (`data/*.sqlite`). Le fichier `data/products.json`
a été supprimé après l'import initial.

## Tables

### `categories`

| Colonne | Type | Rôle |
| --- | --- | --- |
| `id` | entier | clé primaire |
| `code` | texte | identifiant du filtre (`sneakers`, `ville`…) |
| `label` | texte | libellé affiché (`Sneakers`, `Ville`…) |

### `products`

| Colonne | Type | Obligatoire | Description | Exemple |
| --- | --- | --- | --- | --- |
| `id` | entier | oui | clé primaire | `1` |
| `ref` | texte | oui | référence unique, sert d'identifiant public | `SH001` |
| `name` | texte | oui | nom du produit | `Sneakers Urban` |
| `description` | texte | oui | description courte | `Des sneakers modernes…` |
| `price` | entier | oui | prix en FCFA | `15000` |
| `old_price` | entier ou `NULL` | non | prix barré (promotion) | `18000` |
| `currency` | texte | oui | code ISO de la devise | `XAF` |
| `image` | texte | oui | chemin relatif à la racine web | `assets/img/sh001.jpg` |
| `og_image` | texte ou `NULL` | non | image 1200 × 630 pour Open Graph | `assets/img/og/sh001.jpg` |
| `category_id` | entier ou `NULL` | non | clé étrangère vers `categories.id` | `1` |
| `available` | booléen | oui | produit commandable (`0`/`1`) | `1` |
| `featured` | booléen | non | badge « POPULAIRE » | `1` |
| `is_new` | booléen | non | badge « NOUVEAU » | `1` |
| `created_at` | texte | oui | date de création (UTC) | `2026-01-01 12:00:00` |
| `updated_at` | texte | oui | date de dernière modification | `2026-01-01 12:00:00` |

### `product_sizes`

| Colonne | Type | Rôle |
| --- | --- | --- |
| `product_id` | entier | clé étrangère vers `products.id` |
| `size` | entier | taille proposée (`39`, `40`…) |
| `position` | entier | ordre d'affichage |

Clé primaire : `(product_id, size)`.

### `product_colors`

| Colonne | Type | Rôle |
| --- | --- | --- |
| `product_id` | entier | clé étrangère vers `products.id` |
| `color` | texte | couleur proposée (`Noir`, `Blanc`…) |
| `position` | entier | ordre d'affichage |

Clé primaire : `(product_id, color)`.

## Remarques

- Les **noms de colonnes sont en anglais** (convention du projet) ; les
  **valeurs** restent en français (`name`, `description`, `label`…).
- `currency` contient le **code** (`XAF`). Le **symbole affiché** (`FCFA`) est un
  réglage d'affichage : `currency_label` dans `src/config.php`.
- `image` est un chemin relatif à la racine web (`public/`). Les photos produit
  sont des JPEG 1200 × 900 ; `og_image` fournit la version 1200 × 630 utilisée
  par WhatsApp et les autres plateformes de partage.
- Les tailles et couleurs sont normalisées dans deux tables : le CRUD peut
  ajouter, modifier ou supprimer une valeur sans réécrire tout le produit.
- Si une référence inconnue est demandée, `public/product.php` répond `404`.

## CRUD

Le repository expose les opérations suivantes :

```php
$repository = new ProductRepository(Database::connect($config));

$repository->all();
$repository->findByRef('SH001');
$repository->search('sneakers', 'sneakers', 'price-asc');
$repository->create([...]);
$repository->update('SH001', ['price' => 16000]);
$repository->delete('SH002');
```

`create()` et `update()` valident la référence, le nom, le prix, les tailles et
les couleurs. `update()` fusionne les champs fournis avec l'existant : une mise
à jour partielle est donc possible.

## Images Open Graph

```bash
composer og:generate
composer og:generate -- --force
composer og:generate -- --ref=SH001
```

Le générateur recadre la photo produit au centre en 1200 × 630 et écrit un JPEG
via GD. Sur Ubuntu : `sudo apt install php8.4-gd`.

---

# Tests des fonctionnalités implémentées

- Outil de test du **Schema.org** : [Rich Results Test](https://search.google.com/test/rich-results)
- Outil de validation de **Open Graph** : **WhatsApp**
- Outil de validation des liens de conv dynamique : **WhatsApp**
