# TP 4 — Étude de marché numérique et closing WhatsApp

Trois briques indépendantes, chacune dans son fichier source :

| Fonctionnalité | Fichier | Point d'entrée |
| --- | --- | --- |
| Données structurées Schema.org | `src/schema.org.php` | `render_schema_org()` |
| Aperçus de partage Open Graph | `src/opengraph.php` | `render_open_graph()` |
| Closing WhatsApp | `src/whatsapp.php` | `whatsapp_product_link()` |

## 1. Schema.org (JSON-LD)

- `schema_org_product()` génère un `Product` avec un `Offer` imbriqué :
  `name`, `description`, `sku`, `image`, `url`, `brand`, `category`, puis
  `price`, `priceCurrency`, `availability`, `itemCondition` et `seller`.
- `schema_org_item_list()` expose tout le catalogue de la page d'accueil sous
  forme d'`ItemList` de `Product` / `Offer`.
- `render_schema_org()` encode le tout avec `JSON_HEX_TAG` (protection contre
  une valeur contenant `</script>`) et produit la balise
  `<script type="application/ld+json">`.

Les prix et disponibilités viennent directement de la base SQLite
(`data/shoes.sqlite`, lue via `ProductRepository`) : aucune donnée n'est
dupliquée dans le HTML.

## 2. Open Graph

`render_open_graph()` produit :

- les balises standards : `og:site_name`, `og:locale`, `og:type`, `og:title`,
  `og:description`, `og:url`, `og:image`, `og:image:alt` ;
- les balises produit : `product:price:amount`, `product:price:currency`,
  `product:availability` (uniquement sur une fiche) ;
- les équivalents Twitter Card (`twitter:card`, `twitter:title`,
  `twitter:description`, `twitter:image`).

Toutes les images sont converties en URL absolue avec `absolute_url()`, car les
robots de WhatsApp refusent les chemins relatifs. Sur l'accueil, la première
image produit sert d'aperçu par défaut ; `og_image` permet de fixer une image
de partage dédiée.

Les visuels sont des JPEG : `assets/img/shXXX.jpg` (1200 × 900) pour les pages
et `assets/img/og/shXXX.jpg` (1200 × 630) pour les aperçus de partage. Les
images OG sont générées avec GD par `composer og:generate`
(`src/OgImageGenerator.php`).

## 3. Closing WhatsApp

- `whatsapp_number()` nettoie le numéro (`+237…`, `00237…` → `237…`).
- `whatsapp_link()` construit l'URL `https://wa.me/<numéro>?text=…`.
- `whatsapp_product_message()` pré-remplit le message avec le modèle, la
  **référence**, le **prix** et le lien du produit.
- `whatsapp_contact_link()` fournit le message de premier contact (accueil et
  pied de page).

Exemple de message généré pour `SH001` :

```text
Bonjour SHOES - SHOP,

Je souhaite commander ce modèle :

• Modèle : Sneakers Urban
• Référence : SH001
• Prix : 15 000 FCFA
• Lien : https://exemple.com/product.php?ref=SH001

Merci de me confirmer la disponibilité, la taille et la couleur souhaitées.
```

Si `whatsapp_number` est vide, WhatsApp s'ouvre avec le message pré-rempli et
laisse le visiteur choisir le destinataire. Renseigner la clé dans
`src/config.local.php` pour un closing direct.

## Vérifications effectuées

- `php -l` sur tous les fichiers PHP : aucune erreur.
- `GET /index.php` : balises Open Graph + `ItemList` de 8 produits valide.
- `GET /product.php?ref=SH001` : `og:type=product`, prix, image absolue et
  JSON-LD `Product` + `Offer` valide.
- `GET /product.php?ref=INCONNU` : HTTP 404 avec Open Graph de secours.
- Lien WhatsApp : le message décodé contient bien `SH001` et `15 000 FCFA`.

## Dépannage des aperçus WhatsApp

WhatsApp ne lit pas le JavaScript et ne stocke pas les images : il faut que
l'URL complète soit joignable publiquement, en HTTPS, au moment du partage.

1. **Tester l'URL avec l'agent WhatsApp** :

   ```bash
   curl -I -A "WhatsApp/2.23.20.0 A" https://votre-domaine.com/product.php?ref=SH001
   ```

2. **HTTP 530 / erreur Cloudflare 1033** : le tunnel ou le serveur d'origine
   est hors ligne. C'est le cas d'une URL `trycloudflare.com` : elle change à
   chaque redémarrage et tombe dès que le tunnel local s'arrête. Utiliser un
   tunnel nommé Cloudflare ou un hébergement stable.

3. **Cloudflare bloque le robot** : si la réponse est une page de challenge
   (403/503), désactiver *Bot Fight Mode* pour le site ou créer une règle WAF
   qui autorise l'agent `WhatsApp` sur `product.php` et `assets/img/og/*`.

4. **Ancien aperçu conservé** : WhatsApp met les aperçus en cache. Tester avec
   une URL légèrement différente (`...?ref=SH001&v=2`) ou attendre l'expiration
   du cache.

5. **Image** : elle doit être publique, en JPEG/PNG, accessible sans cookie,
   idéalement 1200 × 630 px et sous 300 Ko.

