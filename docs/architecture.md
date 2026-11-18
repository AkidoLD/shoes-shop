# Architecture

## Structure des dossiers

```text
.
├── bin/
│   ├── db-init.php          # crée la base et importe les données initiales
│   └── generate-og.php      # génère les images Open Graph 1200 × 630
├── data/
│   └── shoes.sqlite         # base SQLite générée (ignorée par Git)
├── database/
│   ├── schema.sql           # tables, contraintes et index
│   └── seed.sql             # données initiales (catégories, produits…)
├── docs/                    # cette documentation
├── public/                  # racine web : seul dossier exposé
│   ├── index.php            # liste des produits
│   ├── product.php          # fiche produit (product.php?ref=SH001)
│   ├── admin/               # administration protégée par session
│   │   ├── index.php        # liste des produits
│   │   ├── edit.php         # création / modification d'un produit
│   │   ├── delete.php       # suppression (POST + CSRF)
│   │   ├── login.php        # connexion admin
│   │   └── logout.php       # déconnexion (POST + CSRF)
│   └── assets/
│       ├── css/style.css    # feuille de style du catalogue
│       ├── css/admin.css    # feuille de style de l'administration
│       ├── js/app.js        # JavaScript de confort uniquement
│       └── img/             # photos produit (JPEG) + img/og/ (1200 × 630)
├── src/                     # logique
│   ├── bootstrap.php        # autoload + config + fonctions partagées
│   ├── config.php           # réglages (URL, WhatsApp, devise, base, admin…)
│   ├── config.example.php   # modèle de config locale à copier
│   ├── AdminAuth.php        # session, CSRF et identifiants admin
│   ├── Database.php         # connexion PDO SQLite et migrations
│   ├── Product.php          # entité produit
│   ├── ProductNotFoundException.php
│   ├── ProductRepository.php # CRUD et requêtes produits
│   ├── OgImageGenerator.php # recadrage GD des images de partage
│   ├── helpers.php          # e(), format_price(), absolute_url()
│   ├── schema.org.php       # JSON-LD Product / Offer / ItemList
│   ├── opengraph.php        # balises Open Graph et Twitter Card
│   └── whatsapp.php         # liens wa.me pré-remplis
├── templates/               # affichage
│   ├── header.php           # <head> + en-tête du catalogue
│   ├── footer.php           # pied de page du catalogue
│   ├── admin-header.php     # <head> + en-tête de l'administration
│   ├── admin-footer.php     # pied de page de l'administration
│   └── product-card.php     # une carte produit dans la liste
└── tests/                   # PHPUnit
```

## Rôle de chaque fichier

- **`database/schema.sql`** — crée les tables `categories`, `products`,
  `product_sizes` et `product_colors`, avec les contraintes d'intégrité et les
  index de recherche/tri.
- **`database/seed.sql`** — import initial des 8 produits. C'est la seule copie
  des données de départ : `data/products.json` a été supprimé.
- **`bin/db-init.php`** — crée `data/shoes.sqlite`, applique le schéma et insère
  les données. `--force` supprime la base existante avant de la recréer.
- **`bin/generate-og.php`** — parcourt les produits et génère leur image
  `assets/img/og/*.jpg` à partir de la photo principale. `--force` régénère les
  fichiers existants, `--ref=SH001` cible un seul produit.
- **`src/bootstrap.php`** — charge l'autoload Composer, la configuration et les
  fonctions partagées, puis renvoie `$config`.
- **`src/config.php`** — renvoie un tableau de configuration : valeurs par
  défaut, puis surcharge éventuelle par `src/config.local.php` (fusion). Déduit
  l'URL du site de la requête si `site_url` reste vide. Contient aussi les
  identifiants de l'administration (`admin_user`, `admin_password`).
- **`src/AdminAuth.php`** — gère la session de l'administration : vérification
  des identifiants, régénération de session à la connexion, jeton CSRF,
  messages flash et déconnexion.
- **`src/Database.php`** — ouvre une connexion PDO SQLite, active les clés
  étrangères et vérifie que le schéma est initialisé.
- **`src/Product.php`** — entité immuable (`readonly`) avec `fromRow()` et
  `toArray()` pour conserver des templates simples.
- **`src/ProductRepository.php`** — toute la persistance : `all()`,
  `findByRef()`, `categories()`, `search()`, `create()`, `update()`,
  `delete()`, `setOgImage()`. Les tailles et couleurs sont stockées dans des
  tables normalisées.
- **`src/OgImageGenerator.php`** — recadre l'image produit au centre en
  1200 × 630 et l'écrit en JPEG via GD.
- **`src/helpers.php`** — fonctions d'affichage partagées : `e()` échappe une
  valeur pour le HTML, `format_price()` met en forme un montant,
  `absolute_url()` transforme un chemin relatif en URL absolue.
- **`src/schema.org.php`** — construit les données structurées JSON-LD.
- **`src/opengraph.php`** — produit les balises Open Graph et Twitter Card.
- **`src/whatsapp.php`** — génère les liens `wa.me`.
- **`public/index.php`** — point d'entrée de la liste. Valide les paramètres
  d'URL, interroge le repository, puis rend le HTML.
- **`public/product.php`** — point d'entrée de la fiche. Répond `404` si la
  référence est absente ou inconnue.
- **`public/admin/login.php`** — affiche le formulaire de connexion et vérifie
  les identifiants configurés.
- **`public/admin/index.php`** — liste les produits et propose les actions de
  création, modification et suppression.
- **`public/admin/edit.php`** — formulaire de création/modification. Les
  tailles et couleurs sont saisies sous forme de listes séparées par des
  virgules, puis passent par `ProductRepository`.
- **`public/admin/delete.php`** — supprime un produit après vérification du
  jeton CSRF.
- **`public/admin/logout.php`** — détruit la session après vérification du
  jeton CSRF.

## Flux d'une requête

Exemple pour la liste :

1. Le navigateur demande `GET /index.php?category=sneakers&sort=price-asc`.
2. `public/index.php` charge `src/bootstrap.php`.
3. `Database::connect()` ouvre `data/shoes.sqlite`.
4. `ProductRepository::categories()` et `search()` exécutent les requêtes SQL
   (recherche, filtre, tri).
5. Les paramètres `q`, `category`, `sort` sont **validés** (valeurs autorisées).
6. Les objets `Product` sont convertis en tableaux pour les gabarits.
7. Les gabarits `templates/header.php`, `templates/product-card.php` (une fois
   par produit) et `templates/footer.php` produisent le HTML.
8. Le navigateur reçoit **un HTML complet** : les produits sont déjà dedans.

## Flux de l'administration

1. Le navigateur demande `GET /admin/`. `AdminAuth` vérifie la session : sans
   connexion, la page redirige vers `login.php`.
2. `login.php` vérifie le couple `admin_user` / `admin_password` et régénère
   l'identifiant de session. Un jeton CSRF protège les formulaires.
3. `index.php` liste les produits via `ProductRepository::all()`.
4. `edit.php` valide les champs, convertit les listes de tailles et couleurs,
   puis appelle `ProductRepository::create()` ou `update()`.
5. `delete.php` supprime le produit après vérification du jeton CSRF, puis
   redirige vers la liste avec un message flash.
6. `logout.php` détruit la session.

## Séparation logique / affichage

- Les gabarits ne lisent aucune base et ne font aucun calcul métier : ils
  affichent des variables préparées par les pages.
- Les paramètres d'URL ne sont jamais utilisés directement : ils passent par
  une validation (liste des catégories connues, options de tri).
- Toute valeur affichée est échappée avec `e()`.
- Le CRUD est centralisé dans `ProductRepository` ; les pages du catalogue et
  les écrans d'administration n'ont pas à écrire de SQL.
