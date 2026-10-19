# AGENTS.md

## Contexte du projet

Projet de groupe pour le TP4 du cours GL 521 « E-Business et Économie de l'Information » (Licence Génie Logiciel, IUT de Douala).

Objectif final du TP : une page e-commerce qui se laisse **trouver** (Schema.org), **partager** (Open Graph) et **contacter** (lien WhatsApp généré dynamiquement). Le public visé est local (Cameroun) : prix en FCFA, commande conclue par discussion WhatsApp.

## Ta mission actuelle (et ses limites)

Ta tâche se limite à **construire la base du site en PHP**, à partir du code existant du groupe (HTML/CSS/JavaScript pur, en partie non fonctionnel).

À faire :
1. **Lire d'abord** tout le code existant et résumer en quelques lignes ce qu'il fait, ce qui marche et ce qui ne marche pas, avant de modifier quoi que ce soit.
2. **Conserver** le design, le HTML et le CSS existants autant que possible. Les adapter pour qu'ils soient générés par PHP, sans les refaire.
3. **Remplacer le JavaScript qui construisait la page** par du rendu côté serveur : le HTML envoyé au navigateur doit déjà contenir les produits. Garder du JavaScript uniquement pour du confort d'interface (menu, galerie...), jamais pour afficher le contenu essentiel.
4. Mettre en place une **source unique de données** pour les produits (voir plus bas).
5. Fournir une **liste des produits** et une **fiche par produit** (une URL par produit).
6. Laisser des **points d'accroche vides et clairement marqués** pour les trois fonctionnalités ci-dessous.

À NE PAS faire (ce travail est réservé aux membres du groupe) :
- Ne pas écrire les données **Schema.org (JSON-LD)**.
- Ne pas écrire les balises **Open Graph**.
- Ne pas écrire la génération du **lien WhatsApp**.
- Ne pas ajouter de panier, de comptes utilisateurs, de paiement, ni de framework ou de dépendance non demandés.

Pour ces trois points, crée seulement des fichiers ou fonctions **vides avec un commentaire `TODO`** décrivant ce qu'elles devront recevoir (un produit) et renvoyer (une chaîne de texte), et appelle-les aux bons endroits des gabarits.

## Pile technique

- PHP natif (version 8.x), **sans framework**, sans Composer sauf nécessité justifiée.
- Données : fichier JSON (`data/produits.json`). Pas de base de données pour l'instant.
- Front : HTML, CSS et un peu de JavaScript existants.
- Serveur de développement : `php -S localhost:8000 -t public`

## Structure attendue

```text
.
├── AGENTS.md
├── docs/                    # documentation du projet (voir section « Documentation »)
├── data/
│   └── produits.json        # source unique des produits
├── public/                  # racine web : seul dossier exposé
│   ├── index.php            # liste des produits
│   ├── produit.php          # fiche produit (ex. produit.php?ref=PW-014)
│   └── assets/              # css, js, images
├── src/
│   ├── config.php           # réglages : URL du site, numéro WhatsApp, devise par défaut
│   ├── produits.php         # lecture des données : charger tous les produits, en trouver un par référence
│   ├── seo.php              # TODO groupe : fonctions Schema.org et Open Graph (vides pour l'instant)
│   └── whatsapp.php         # TODO groupe : fonction du lien WhatsApp (vide pour l'instant)
└── templates/
    ├── header.php           # <head> avec un emplacement marqué pour les balises Open Graph et le JSON-LD
    ├── footer.php
    └── carte-produit.php    # affichage d'un produit dans la liste
```

Si le code existant impose une structure différente, adapte-la et explique brièvement pourquoi.

## Modèle de données

Chaque produit de `produits.json` contient au minimum :

| Champ | Description | Exemple |
| --- | --- | --- |
| `ref` | Référence unique, sert d'identifiant | `PW-014` |
| `nom` | Nom du produit | `Pagne wax 6 yards, motif fleurs` |
| `description` | Texte court | `Coton 100 %, 6 yards, coloris bleu et orange` |
| `prix` | Nombre entier, en FCFA | `12000` |
| `devise` | Code de la devise | `XAF` |
| `image` | Chemin ou URL de la photo | `assets/img/pw-014.webp` |
| `disponible` | Booléen | `true` |

Règle essentielle : **un produit est défini à un seul endroit**. La page, le JSON-LD, les balises Open Graph et le lien WhatsApp liront tous ce même enregistrement. Ne jamais recopier un nom ou un prix à la main dans un gabarit.

Si le code existant utilise d'autres produits ou d'autres noms de champs, conserve ses produits et mappe-les vers ce modèle.

## Conventions de code

- Séparer **logique** (dans `src/`) et **affichage** (dans `templates/`) : pas de lecture de fichier ni de calcul métier à l'intérieur des gabarits.
- Toujours échapper les valeurs affichées dans le HTML avec `htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8')` (un helper `e()` est bienvenu).
- Valider le paramètre `ref` d'une fiche produit. S'il n'existe pas, répondre avec le code HTTP 404 et une page claire.
- Fichiers en UTF-8, code et commentaires courts, en français.
- Des fonctions petites, avec un nom explicite. Pas de code mort ni d'abstraction « au cas où ».
- Les images doivent être légères (WebP de préférence), car la connexion mobile est souvent lente.

## Organisation du code et maintenabilité

Le code doit rester facile à lire, à modifier et à reprendre par n'importe quel membre du groupe.

**Séparation des responsabilités**
- `src/` contient la logique, `templates/` l'affichage, `data/` les données, `public/` uniquement les points d'entrée et les ressources statiques. Aucun mélange.
- Un fichier, un rôle. Si un fichier fait deux choses, le découper.
- Les fonctions de `src/seo.php` et `src/whatsapp.php` doivent être **pures** : elles reçoivent un produit (et la config) et **renvoient** une chaîne de texte, sans `echo`, sans lire de fichier, sans effet de bord. Les gabarits décident où afficher le résultat.

**Écriture du code**
- Style PSR-12. `declare(strict_types=1);` en haut de chaque fichier PHP, avec des types sur les paramètres et les retours.
- Noms de fonctions et de variables en `snake_case`, noms de fichiers en minuscules avec tirets, en français ou en anglais, mais **de façon cohérente** dans tout le projet.
- Fonctions courtes (une idée chacune), nommées par ce qu'elles font (`trouver_produit_par_ref`, pas `traiter`).
- Aucune valeur « magique » dans le code : numéro WhatsApp, URL du site, devise vont dans `src/config.php`. L'URL du site sera nécessaire pour construire des adresses absolues (images, `og:url`).
- Ne pas dupliquer : si le même morceau apparaît deux fois, en faire une fonction ou un gabarit partiel.
- Pas de code commenté, pas de code mort. Les commentaires expliquent le **pourquoi**, pas le **quoi**.

**Robustesse et sécurité**
- Ne jamais faire confiance aux entrées (`$_GET`, `$_POST`) : les valider avant usage. Échapper toute sortie HTML.
- Gérer les cas d'erreur prévisibles : fichier JSON absent ou invalide, produit introuvable, image manquante. Un message clair vaut mieux qu'une page blanche.
- Aucun secret (clé, mot de passe, numéro personnel) dans le dépôt. Les réglages sensibles vont dans un fichier ignoré par Git (par exemple `src/config.local.php`), avec un `config.example.php` versionné.
- Un `.gitignore` est présent dès le départ (fichiers locaux, éditeur, dossiers temporaires).

## Git : commits et branches

- **Un commit = un changement logique.** Ne pas mélanger, dans un même commit, une nouvelle fonctionnalité, une correction et un reformatage.
- Format du message : `type(portée): description`, au présent, à l'impératif, 72 caractères maximum pour la première ligne, en français.
  - Types : `feat` (nouvelle fonctionnalité), `fix` (correction), `refactor` (restructuration sans changement de comportement), `style` (mise en forme), `docs` (documentation), `chore` (outillage, configuration).
  - Exemples : `feat(produits): ajouter la lecture de produits.json`, `fix(fiche): renvoyer un 404 si la référence est inconnue`, `docs(architecture): décrire la structure des dossiers`.
- Une ligne de corps optionnelle pour expliquer le **pourquoi** quand le changement n'est pas évident.
- Travailler sur des branches courtes (`feat/liste-produits`, `fix/image-manquante`), jamais directement sur `main`, qui doit rester fonctionnel.
- Ne pas committer sans que cela te soit demandé : propose plutôt le message adapté après chaque étape terminée. Ne jamais faire de `push` ni de réécriture d'historique de ta propre initiative.

## Documentation (dossier `docs/`)

Le dossier `docs/` rassemble la documentation du projet. Elle doit rester courte, en français, et expliquer le **pourquoi** autant que le **comment**.

Fichiers à créer dès cette étape :
- `docs/README.md` : sommaire de la documentation, description du projet, commande de lancement.
- `docs/architecture.md` : structure des dossiers, rôle de chaque fichier, et flux d'une requête (de l'URL au HTML renvoyé).
- `docs/donnees.md` : modèle de données des produits (champs, types, exemple).
- `docs/decisions.md` : journal des choix techniques, une entrée par décision (contexte, choix, raison). Premières entrées : PHP natif sans framework, JSON comme source de données, rendu côté serveur.

Fichiers que le groupe ajoutera plus tard : un document par fonctionnalité (`schema-org.md`, `open-graph.md`, `whatsapp.md`) et `tests.md` (comment vérifier chaque pièce).

Règle : tout changement qui touche la structure, les données ou les commandes de lancement met à jour `docs/` **dans le même commit**.

## Méthode de travail

- Avance **par petites étapes** et indique après chacune ce qui a changé et pourquoi, en quelques phrases. Les membres du groupe doivent pouvoir comprendre et défendre chaque choix à l'oral.
- Si une décision n'est pas couverte par ce fichier (technologie, structure, design), pose la question au lieu de deviner.
- Ne supprime pas de fichiers existants du groupe : renomme-les ou déplace-les dans un dossier `ancien/` si besoin.

## Critère de réussite pour cette étape

- `php -S localhost:8000 -t public` démarre sans erreur.
- La liste des produits et chaque fiche s'affichent, avec les produits **présents dans le HTML source** (visibles via « Afficher le code source » du navigateur, sans exécuter de JavaScript).
- Les trois points d'accroche (JSON-LD, Open Graph, lien WhatsApp) existent, sont vides et clairement marqués `TODO`.
- Le dossier `docs/` contient les quatre fichiers listés plus haut, et reflète l'état réel du code.
- Un `.gitignore` existe, et aucun secret n'est présent dans le dépôt.
