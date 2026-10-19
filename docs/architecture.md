# Architecture

## Structure des dossiers

```text
.
├── data/
│   └── products.json        # source unique des produits
├── docs/                    # cette documentation
├── public/                  # racine web : seul dossier exposé
│   ├── index.php            # liste des produits
│   ├── product.php          # fiche produit (product.php?ref=SH001)
│   └── assets/
│       ├── css/style.css    # feuille de style
│       ├── js/app.js        # JavaScript de confort uniquement
│       └── img/*.webp        # photos des produits (placeholders WebP)
├── src/                     # logique
│   ├── config.php           # réglages (URL, WhatsApp, devise…)
│   ├── env.php              # lecture du fichier .env (sans dépendance)
│   ├── helpers.php          # e(), format_price()
│   └── products.php         # lecture et interrogation des produits
└── templates/               # affichage
    ├── header.php           # <head> + en-tête
    ├── footer.php           # pied de page
    └── product-card.php     # une carte produit dans la liste
```

## Rôle de chaque fichier

- **`src/config.php`** — renvoie un tableau de configuration. Charge le fichier
  `.env` (via `src/env.php`) puis lit les valeurs ; les variables
  d'environnement du système restent prioritaires. Déduit l'URL du site de la
  requête si `SITE_URL` n'est pas défini (fonctionne en local et en hébergement
  distant).
- **`src/env.php`** — petit lecteur de `.env` « maison » (pas de dépendance) :
  lignes `CLÉ=VALEUR`, commentaires `#`, guillemets retirés, l'environnement
  réel gagne sur le fichier.
- **`src/helpers.php`** — fonctions d'affichage partagées : `e()` échappe une
  valeur pour le HTML, `format_price()` met en forme un montant.
- **`src/products.php`** — lit `data/products.json`, trouve un produit par
  référence (`find_product_by_ref`), liste les catégories (`list_categories`),
  applique recherche / filtre / tri (`filter_and_sort`).
- **`public/index.php`** — point d'entrée de la liste. Valide les paramètres
  d'URL, filtre, trie, puis rend le HTML.
- **`public/product.php`** — point d'entrée de la fiche. Répond `404` si la
  référence est absente ou inconnue.

## Flux d'une requête

Exemple pour la liste :

1. Le navigateur demande `GET /index.php?category=sneakers&sort=price-asc`.
2. `public/index.php` charge la configuration et la logique de `src/`.
3. `load_products()` lit `data/products.json`.
4. Les paramètres `q`, `category`, `sort` sont **validés** (valeurs autorisées).
5. `filter_and_sort()` renvoie la liste filtrée.
6. Les gabarits `templates/header.php`, `templates/product-card.php` (une fois
   par produit) et `templates/footer.php` produisent le HTML.
7. Le navigateur reçoit **un HTML complet** : les produits sont déjà dedans.

## Séparation logique / affichage

- Les gabarits ne lisent aucun fichier et ne font aucun calcul métier : ils
  affichent des variables préparées par les pages.
- Les paramètres d'URL ne sont jamais utilisés directement : ils passent par
  une validation (`valid_sort_options()`, liste des catégories connues).
- Toute valeur affichée est échappée avec `e()`.
