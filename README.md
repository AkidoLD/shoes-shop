# shoes-shop

TP-4 E-Business : **étude de marché numérique et closing WhatsApp**.

Catalogue de chaussures en PHP, rendu côté serveur, avec un tunnel de conversion
conversationnel articulé sur WhatsApp Business.

## Fonctionnalités

- **Catalogue** : recherche, filtre par catégorie et tri, sans JavaScript.
- **Fiche produit** : `product.php?ref=SH001` (404 si la référence est inconnue).
- **Schema.org (JSON-LD)** : `Product` + `Offer` sur chaque fiche, `ItemList` du
  catalogue sur la page d'accueil.
- **Open Graph / Twitter Card** : aperçu riche (titre, description, image, prix)
  lors du partage d'un lien sur WhatsApp, Facebook, LinkedIn, Telegram…
- **Closing WhatsApp** : liens `wa.me` pré-remplis avec le modèle, la référence,
  le prix et l'URL du produit.

## Démarrage

```bash
cp src/config.example.php src/config.local.php
# Renseigner whatsapp_number dans src/config.local.php
php -S localhost:8000 -t public
```

Puis ouvrir <http://localhost:8000>.

## Configuration

`src/config.local.php` (ignoré par Git) surcharge `src/config.php` :

| Clé | Rôle |
| --- | --- |
| `site_url` | URL publique du site (vide = déduite de la requête) |
| `whatsapp_number` | Numéro WhatsApp au format international, sans `+` ni espaces |
| `site_name` | Nom affiché du magasin |
| `currency_label` | Symbole affiché après les montants (`FCFA`) |
| `og_image` | Image de partage par défaut (vide = première image produit) |

## Tests

- Schema.org : [Rich Results Test](https://search.google.com/test/rich-results)
- Open Graph : partager l'URL dans WhatsApp (ou utiliser un débogueur de liens)
- Liens WhatsApp : cliquer sur « Commander sur WhatsApp » depuis une fiche

## Documentation

- [`docs/architecture.md`](docs/architecture.md) — structure et rôle des fichiers
- [`docs/data.md`](docs/data.md) — champs de `data/products.json`
- [`docs/tp4.md`](docs/tp4.md) — détail des trois fonctionnalités du TP 4
- [`docs/support-tp4.pptx`](docs/support-tp4.pptx) — support de soutenance (18 slides)
