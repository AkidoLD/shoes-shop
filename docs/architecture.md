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
│       └── img/              # photos produit (JPEG) + img/og/ (1200 × 630)
├── src/                     # logique
│   ├── config.php           # réglages (URL, WhatsApp, devise…)
│   ├── config.example.php   # modèle de config locale à copier
│   ├── helpers.php          # e(), format_price(), absolute_url()
│   ├── products.php         # lecture et interrogation des produits
│   ├── schema.org.php       # JSON-LD Product / Offer / ItemList
│   ├── opengraph.php        # balises Open Graph et Twitter Card
│   └── whatsapp.php         # liens wa.me pré-remplis
└── templates/               # affichage
    ├── header.php           # <head> + en-tête
    ├── footer.php           # pied de page
    └── product-card.php     # une carte produit dans la liste
```

## Rôle de chaque fichier

- **`src/config.php`** — renvoie un tableau de configuration : valeurs par
  défaut, puis surcharge éventuelle par `src/config.local.php` (fusion). Déduit
  l'URL du site de la requête si `site_url` reste vide (fonctionne en local et
  en hébergement distant).
- **`src/config.example.php`** — modèle de configuration locale à copier vers
  `src/config.local.php` (ignoré par Git, réservé aux secrets).
- **`src/helpers.php`** — fonctions d'affichage partagées : `e()` échappe une
  valeur pour le HTML, `format_price()` met en forme un montant,
  `absolute_url()` transforme un chemin relatif en URL absolue (obligatoire
  pour Open Graph et Schema.org).
- **`src/products.php`** — lit `data/products.json`, trouve un produit par
  référence (`find_product_by_ref`), liste les catégories (`list_categories`),
  applique recherche / filtre / tri (`filter_and_sort`).
- **`src/schema.org.php`** — construit les données structurées JSON-LD :
  `schema_org_product()` (Product + Offer), `schema_org_item_list()` (catalogue
  complet) et `render_schema_org()` (balise `<script type="application/ld+json">`).
- **`src/opengraph.php`** — `render_open_graph()` produit les balises Open Graph
  et Twitter Card (titre, description, URL, image absolue, prix et
  disponibilité pour une fiche produit).
- **`src/whatsapp.php`** — génère les liens `wa.me` : normalisation du numéro,
  message de contact général et message de commande pré-rempli avec le modèle,
  la référence, le prix et l'URL du produit.
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
