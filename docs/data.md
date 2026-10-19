# Données des produits

Source unique : **`data/products.json`** (un tableau d'objets, encodé en UTF-8).
La page, et demain les balises Open Graph, le JSON-LD et le lien WhatsApp,
lisent tous ce même enregistrement : un produit n'est défini qu'à un seul endroit.

## Champs

| Champ | Type | Obligatoire | Description | Exemple |
| --- | --- | --- | --- | --- |
| `ref` | texte | oui | Référence unique, sert d'identifiant | `SH001` |
| `name` | texte | oui | Nom du produit | `Sneakers Urban` |
| `description` | texte | oui | Description courte | `Des sneakers modernes pour un style urbain.` |
| `price` | entier | oui | Prix en FCFA | `15000` |
| `currency` | texte | oui | Code ISO de la devise | `XAF` |
| `image` | texte | oui | Chemin relatif à la racine web | `assets/img/sh001.webp` |
| `available` | booléen | oui | Produit commandable | `true` |
| `old_price` | entier ou `null` | non | Prix barré (promotion) | `18000` |
| `category` | texte | non | Code de la catégorie (filtre) | `sneakers` |
| `category_label` | texte | non | Libellé affiché | `Sneakers` |
| `sizes` | liste d'entiers | non | Tailles proposées | `[39, 40, 41]` |
| `colors` | liste de textes | non | Couleurs proposées | `["Noir", "Blanc"]` |
| `featured` | booléen | non | Mis en avant (badge « POPULAIRE ») | `true` |
| `is_new` | booléen | non | Nouveauté (badge « NOUVEAU ») | `true` |

## Remarques

- Les **noms de champs sont en anglais** (convention du projet) ; les **valeurs**
  restent en français (`name`, `description`, `category_label`…).
- `currency` contient le **code** (`XAF`). Le **symbole affiché** (`FCFA`) est un
  réglage d'affichage : `CURRENCY_LABEL` dans `src/config.php`.
- `image` est un chemin relatif à la racine web (`public/`). Les images sont des
  WebP légers, adaptés aux connexions mobiles ; ce sont des placeholders à
  remplacer par les vraies photos.
- Si une référence inconnue est demandée, `public/product.php` répond `404`.


# Tests des fonctionnalites implementes
- Outil de test du **Schema.org** : [Rich Results Test](https://search.google.com/test/rich-results)
- Outil de validation de **Open Graph** : **Whatsapp**
- Outil de validation des liens de conv dynamique : **Whatsapp**