# Clarté — dossier de passation

Ce fichier sert à reprendre le travail sur **Clarté** dans une nouvelle discussion avec Claude. Il suffit de joindre le pack `clarte-pack-discussion - v 1.4.1.zip` et de coller le message de démarrage proposé à la fin.

---

## 1. En bref

| | |
|---|---|
| **Projet** | Clarté : style phpBB complet (forum + administration), mode clair, sombre ou automatique, couleur réglable dans l'ACP |
| **Auteur** | verturin (GitHub : `verturin/clarte`), publication par l'interface web de GitHub uniquement |
| **Version actuelle** | **1.4.1** (style 1.4.1, extension 1.4.1, config `clarte_version` = 1.4.0 car aucune migration en 1.4.1) |
| **Compatibilité** | phpBB 3.3.15 minimum, PHP 7.2 et plus ; testé sur SQLite et MariaDB (mode strict) |
| **Licence** | GPL-2.0 ; police Inter sous licence OFL (incluse) |
| **Projet lié** | Extension **Portfolio** (`verturin/portfolio`, version 1.9.10) : galerie photos, dont les pages suivent la couleur et le mode de Clarté |
| **Forum réel** | cage-et-chastete.com (o2switch, PHP 8.2), forum en français |

**Règle de livraison (préférence de l'auteur)** : à chaque livraison, **incrémenter la version** (composer.json de l'extension, `style_version` dans style.cfg, `?v=` dans stylesheet.css, version.json, badges de `README.md` et `README.en.md`) et **compléter le README** avec le journal des versions (`README.en.md` : résumé *Recent changes*). Toujours livrer : le zip d'installation `clarte - v X.Y.Z.zip` (et, pour les Releases GitHub et phpBB.com, `clarte-style - v X.Y.Z.zip` et `clarte-extension - v X.Y.Z.zip`), le pack GitHub (`clarte-github - v X.Y.Z.zip`, moins de 100 fichiers pour l'envoi web) et un nouveau pack de passation (`clarte-pack-discussion - v X.Y.Z.zip`). **Nommage des zips** (préférence de l'auteur, depuis 1.4.0) : numéro de version à la fin, sous la forme « nom - v X.Y.Z.zip ». Répondre en français.

---

## 2. Deux parties à déposer à la racine du forum

```
styles/clarte/                 le style (enfant de prosilver) — fonctionne seul
ext/verturin/clarte/           l'extension « Clarté — réglages du style »
```

### Le style `styles/clarte/`

| Fichier | Rôle |
|---|---|
| `style.cfg` | `parent = prosilver`, version du style |
| `theme/stylesheet.css` | importe dans l'ordre : CSS de prosilver (normalize, base, utilities, common, links, content, buttons, cp, forms, icons), puis `tokens.css` et `colours.css`, puis `responsive.css` de prosilver, puis `clarte.css?v=…` |
| `theme/tokens.css` | **généré** : les 91 couleurs de prosilver en variables `--k-xxxxxx`, valeur claire dans `:root`, valeur sombre dans `html[data-theme="dark"]` et dans `@media (prefers-color-scheme: dark) html:not([data-theme="light"])` |
| `theme/colours.css` | **généré** : `colours.css` de prosilver où chaque couleur est remplacée par sa variable ; chemins d'images réécrits vers `../../prosilver/theme/images/` |
| `theme/clarte.css` | **écrit à la main** : le dessin (police Inter, variables de base, en-tête en dégradé, barre de navigation, blocs arrondis, listes, messages, citations, boutons, champs, menus, pagination, onglets, panneau de l'utilisateur, pied de page, mode sombre, intégration Portfolio, petits écrans) |
| `theme/bidi.css`, `plupload.css`, `tweaks.css`, `print.css` | fichiers relais qui importent ceux de prosilver (phpBB les appelle par `T_THEME_PATH`) |
| `theme/en/stylesheet.css`, `theme/fr/stylesheet.css` | relais vers `prosilver/theme/en/stylesheet.css` (phpBB les appelle par `T_STYLESHEET_LANG_LINK`) |
| `theme/fonts/` | Inter en woff2 (sous-ensemble latin : 400, 400 italique, 500, 600, 700) + `OFL.txt` |

Variables principales de `clarte.css` : `--accent` (#2563eb), `--header-from` (#0f172a), `--header-to` (#1e3a8a), `--font`, `--radius` (12px), `--radius-sm`, `--page`, `--surface`, `--surface-2`, `--surface-3`, `--line`, `--line-strong`, `--text`, `--muted`, `--accent-ink`, `--accent-soft`, `--accent-line`, `--on-accent`, `--shadow`, `--shadow-lg`, `--dk-surface`. Les nuances viennent de `--accent` par `color-mix()`.

### L'extension `ext/verturin/clarte/`

| Fichier | Rôle |
|---|---|
| `event/listener.php` | `core.page_header` : si le style du membre est Clarté (ou un style enfant de Clarté, via `style_parent_tree`), envoie `CLARTE_EXT`, `CLARTE_STYLE_VARS` (couleurs vérifiées `#rrggbb`, police, arrondi), `CLARTE_DEFAULT_MODE`, `CLARTE_ALLOW_TOGGLE`. `core.adm_page_header` : si `clarte_acp` = 1, envoie de quoi habiller l'ACP (`CLARTE_ACP`, chemin des CSS, version des fichiers) |
| `styles/clarte/template/event/*.html` | événements de gabarit **du forum** (phpBB ne lit les événements que dans les extensions, pas dans les styles) : variables + script de mode dans l'en-tête (avant l'affichage, sans clignement), bouton « Apparence » dans la barre (connecté et invité), script du bouton en pied de page |
| `styles/clarte/template/clarte_toggle.html` | le bouton Automatique → Clair → Sombre ; libellés FR/EN selon `S_USER_LANG` ; choix gardé dans `localStorage` (`clarte_mode`) |
| `adm/style/event/acp_overall_header_stylesheets_after.html` | ACP : charge `clarte_admin_colours.css` puis `clarte_admin.css` (après les CSS de phpBB et des extensions), variables de couleur, script de mode |
| `adm/style/event/acp_overall_footer_after.html` | ACP : ajoute le bouton « Apparence » à côté du logo (même clé `clarte_mode`, donc même choix que sur le forum) |
| `adm/style/clarte_admin.css` | **écrit à la main** : le dessin de toute l'ACP (en-tête avec logo phpBB blanc par `mask`, onglets, menu, cadres, groupes de réglages, champs, boutons, tableaux, messages, pagination, permissions, couleurs de groupes en sombre, accord avec l'habillage « acp-skin » de Portfolio, petits écrans) |
| `adm/style/clarte_admin_colours.css` | **généré** : couleurs de `adm/style/admin.css` de phpBB **et** de `portfolio_acp.css` de Portfolio, en variables claires / sombres |
| `adm/style/acp_clarte_settings.html` + `acp_clarte.css` | page de réglages (palettes, sélecteurs de couleur, mode, choix, habillage ACP, police, arrondi, aperçu clair/sombre en direct) |
| `controller/acp_controller.php` | lecture et vérification du formulaire, valeurs d'origine (`DEFAULTS`), palettes (`PRESETS`), état du style (installé / par défaut) |
| `acp/main_info.php`, `acp/main_module.php` | module ACP « Style Clarté › Couleurs et apparence » (onglet Extensions) |
| `migrations/v100_install.php` | clés de configuration + module |
| `migrations/v110_admin_skin.php` | clé `clarte_acp` (1) |
| `migrations/v120_category_colour.php` | clés `clarte_cat_mode` (neutral) et `clarte_cat_colour` (#1e3a8a) |
| `migrations/v130_models.php` | clé `clarte_index_time` (both), `config_text` `clarte_models`, modes `models` et `backup` du module |
| `migrations/v140_forum_styles.php` | `config_text` `clarte_forum_models` et `clarte_model_styles` (`{}`), mode `forums` du module (placé après « Modèles enregistrés » par `'after'` dans `main_info`) ; `revert_data` : retire les styles générés (membres et style par défaut vers Clarté, lignes et dossiers `styles/clarte_m<n>` effacés) |
| `core/forum_models.php` | service `verturin.clarte.forum_models` : `{forum_id: model_id}` (−1 = couleurs générales, arrête l'héritage), `resolve()` remonte les parents par `left_id`/`right_id` (requête en cache 600 s), `tree()` pour la page ACP, cache `_clarte_forum_models` |
| `core/model_styles.php` | service `verturin.clarte.model_styles` : `{model_id: style_id}`, `create()` écrit `styles/clarte_m<id>/` (style.cfg + relais stylesheet, langues, bidi, plupload, tweaks, print) et insère la ligne du style (`style_parent_tree` = `prosilver/clarte`, nom unique « Clarté – nom »), `delete()` renvoie membres et style par défaut vers Clarté puis efface ligne et dossier, `rename()`, `sync()` oublie les styles retirés ailleurs |
| `adm/style/acp_clarte_forums.html` | page *Couleurs par forum* (arbre, choix par forum, résultat avec l'héritage) |
| `core/models.php` | service `verturin.clarte.models` : modèles en JSON dans `config_text` (`{"next_id":n,"items":[{id,name,created,updated,settings}]}`), `DEFAULTS` (toutes les clés), `KEYS` (clés d'un modèle), `clean()` (vérification de toutes les valeurs, partagée par le formulaire, les modèles, l'import et la restauration), noms uniques « Nom (2) », 100 modèles au plus |
| `adm/style/acp_clarte_models.html` | page *Modèles enregistrés* (liste, nouveau à partir des réglages en service, import) |
| `adm/style/acp_clarte_backup.html` | page *Sauvegarde et restauration* |
| `language/fr|en/` | `info_acp_clarte.php` (titres, journal, libellés du bouton ACP), `acp_clarte.php` (page de réglages) |

Clés de configuration phpBB : `clarte_version`, `clarte_accent`, `clarte_header_from`, `clarte_header_to`, `clarte_default_mode` (auto/light/dark), `clarte_allow_toggle`, `clarte_acp`, `clarte_font` (inter/system), `clarte_radius` (0 à 20), `clarte_cat_mode` (neutral/tinted/header/custom), `clarte_cat_colour` (#rrggbb, utilisée en mode custom), `clarte_index_time` (both/now/last/none). `config_text` : `clarte_models`, `clarte_forum_models`, `clarte_model_styles`.

### Les outils `tools/`

- `palette.py` : calcul commun des valeurs claire et sombre d'une couleur selon sa famille : **neutre** (saturation < 0,18 : échelle ardoise, luminosité inversée en sombre, blanc → `--dk-surface`), **ardoise** (bleus peu saturés), **accent** (bleus 180–240° : `color-mix()` avec `--accent`, ce qui rend toute la palette réglable), **statut** (rouges, verts, oranges : gardés, adoucis en sombre).
- `tokenize_colours.py <prosilver/theme/colours.css> <styles/clarte/theme>` : produit `tokens.css` et `colours.css`.
- `tokenize_admin.py <sortie> <adm/style/admin.css> [<portfolio_acp.css> …]` : ne garde que les déclarations de couleur, sur les mêmes sélecteurs ; remplace les fonds en dégradé gif par des aplats.

À relancer après une mise à jour de phpBB qui change ces feuilles, ou une version de Portfolio qui change `portfolio_acp.css` (c'est ce qui a donné la 1.1.1).

---

## 3. Décisions à connaître

- **Style enfant de prosilver** (choix de l'auteur) : aucune copie des gabarits ; les corrections de prosilver profitent à Clarté.
- **Mode clair / sombre / automatique au choix du membre** (choix de l'auteur) : attribut `data-theme` posé sur `<html>` par un script en tête de page ; sans choix, `prefers-color-scheme`.
- **Couleur réglable dans l'ACP** (choix de l'auteur) : variables injectées par l'extension ; le style seul reste bleu et automatique.
- **Toute l'ACP habillée** (demande de l'auteur, v1.1.0), y compris les pages des extensions ; désactivable (`clarte_acp`).
- Aucune table ajoutée, aucun fichier de phpBB modifié.
- Couleurs de groupes (écrites en dur par phpBB) éclaircies en sombre par `filter: brightness(1.7)`.
- Menu de l'ACP : les titres de section gardent un trait de séparation (demande de l'auteur, 1.1.2) ; ne pas le retirer.
- **Piège phpBB** : dans un gabarit `.html`, le lexer convertit tout `{MOT}` (lettres, chiffres, `_`, `.`) en variable Twig, donc `{6}` devient `6`. Ne jamais écrire de quantificateur `{n}` dans un `pattern` ou un script en ligne : répéter la classe (`[0-9a-fA-F]` × 6) ou tester la longueur. Contrôle rapide : `grep -rnE "\{[a-zA-Z0-9_.]+\}" --include=*.html ext/ | grep -v '{{'` doit ne rien renvoyer.
- **Bandeau des catégories** (demande de l'auteur, 1.2.0) : `listener::category_vars()` pose `--cat-bg`, `--cat-ink`, `--cat-ink-2`, `--cat-line` dans `CLARTE_STYLE_VARS` ; `clarte.css` les lit sur `.forabg/.forumbg li.header` avec repli sur `--surface-2`, `--text`, `--muted`, `--line` (mode neutre = aucune variable). Le mode teinté s'appuie sur `--accent-soft/--accent-ink/--accent-line`, donc suit le mode sombre ; la couleur choisie est la même en clair et en sombre, le texte (blanc ou #1d2433) est choisi par contraste (`listener::ink()`, même calcul en JS pour l'aperçu). Les listes du panneau de l'utilisateur (`.panel ul.topiclist`) ne sont pas concernées.
- **Modèles** (demande de l'auteur, 1.3.0) : stockés en JSON dans `config_text` (pas de table, conforme à la décision « aucune table ») ; un modèle = `models::KEYS` (couleurs, bandeau, mode par défaut, police, arrondi). `clarte_allow_toggle`, `clarte_acp` et `clarte_index_time` sont des options du forum : hors modèles, gardées quand on applique un modèle, mais présentes dans la sauvegarde.
- **Noms de modèles** : stockés échappés (comme les textes saisis via `request->variable`) par `models::clean_name()`, qui accepte un nom brut (import) ou échappé (formulaire) ; `plain_name()` pour les exports. phpBB n'échappe pas automatiquement les variables de gabarit : tout ce qui part dans un attribut (JSON des puces « Mes modèles ») passe par `htmlspecialchars(…, ENT_QUOTES)`.
- **Fichiers** : formats `clarte-models` (export d'un ou de tous les modèles) et `clarte-backup` (réglages + modèles). Import de modèles : accepte les deux. Restauration : `clarte-backup` seulement. 512 Kio au plus, BOM toléré, toutes les valeurs repassent par `clean()`.
- **Date sur l'accueil** : CSS posée par `overall_header_head_append.html` selon `CLARTE_INDEX_TIME` ; dans `index_body.html` de prosilver, `p.time.rightside` = dernière visite (membre connecté), l'autre `p.time` = date du jour. Sélecteur `body.section-index p.responsive-center.time` **sans combinateur `>`** (1.3.1) : sur le forum réel, une extension (bandeau « Classement ») place ces lignes ailleurs que directement dans `.page-body`.
- **Barre de navigation sur mobile** (demande de l'auteur, 1.3.2) : dans `clarte.css`, `@media (max-width: 700px)`, le texte des liens directs de `#nav-main` (`> li > a > span`, hors `.badge`) et le pseudo quand il suit un avatar (`.header-avatar img ~ .username(-coloured)`) sont masqués visuellement (classe « lecteur d'écran », pas `display:none`). Les menus déroulants (burger `#quick-links .dropdown`, profil) ne sont pas visés. Sans avatar, le pseudo reste affiché. Le forum réel a trois liens d'extension en tête de barre (« Mon suivi », « Catalogue de cages », « Tchat », `data-skip-responsive`), reproduits pour les tests par une mini-extension `test/navlinks`.
- **Couleurs par forum** (demande de l'auteur, 1.4.0) : ordre d'application dans `listener::page_settings()` — réglages généraux, puis modèle du style choisi par le membre (style `clarte_m<n>` : tous les réglages du modèle), puis **couleurs seulement** du modèle du forum (`listener::FORUM_KEYS` : accent, en-tête, bandeau). Forum affiché : `item_id` de `core.page_header` (viewforum, viewtopic), sinon `f` dans l'adresse, sinon `$forum_id` global (posting.php appelle `page_header()` sans forum). Rien dans l'ACP.
- **Style du forum** (1.4.1) : phpBB permet d'imposer un style par forum (`forum_style`, ACP › Forums › Gérer les forums). Si ce style n'est pas Clarté ou un enfant de Clarté, l'extension ne fait rien sur ce forum (`style_is_clarte()` faux). La page *Couleurs par forum* l'explique et signale ces forums (`tree()` lit `forum_style`).
- **Styles au choix** (demande de l'auteur, 1.4.0) : de vrais styles phpBB enfants de Clarté, gérés uniquement depuis l'extension. Les couleurs ne sont pas écrites dans les fichiers : `model_id_from_path()` relie le dossier au modèle. Le style suit le nom du modèle. Les modèles, les forums et les styles sont mis en cache (`_clarte_models`, `_clarte_forum_models`, `_clarte_model_styles`) ; `cache->destroy('sql', STYLES_TABLE)` après chaque changement de style.
- **Sauvegarde 1.4.0** : chaque modèle de `clarte-backup` porte `forums` (identifiants) et `style` (booléen) ; à la restauration, `models->import()` renvoie les nouveaux identifiants et `restore_links()` réassocie les forums existants et recrée les styles. Remplacer les modèles retire d'abord tous les styles générés et vide la table des forums.
- Les feuilles de l'ACP sont versionnées par `assets_version` de phpBB : après une mise à jour, purger le cache.
- Portfolio : ses variables `--pf-*` sont reliées à celles de Clarté dans `clarte.css` (`html .pf { … }` pour passer devant la feuille de Portfolio) ; ses couleurs codées en dur ont des surcharges sombres dédiées.

---

## 4. Historique

- **1.0.0** (11/10/2026) : style + extension de réglages, mode clair/sombre/auto, bouton Apparence, palettes, aperçu, intégration Portfolio.
- **1.1.0** (11/10/2026) : habillage de toute l'ACP, bouton Apparence dans l'ACP, option « Habiller l'administration », page de réglages lisible en sombre.
- **1.1.1** (11/10/2026) : couleurs ACP régénérées pour Portfolio 1.9.8, couleurs de groupes lisibles en sombre dans l'ACP.
- **1.1.2** (12/10/2026) : menu de gauche de l'ACP — trait sous chaque titre de section (`border-bottom: 1px solid var(--accent-line)`) et titres à la couleur principale (`--accent-ink`), pour bien séparer les groupes ; marge haute annulée sur petit écran (blocs repliables). Correction : la page de réglages refusait tout enregistrement — le `{6}` de `pattern="#[0-9a-fA-F]{6}"` (et de la regex JS `valid()`) était changé en `6` par le lexer phpBB.
- **1.4.1** (21/10/2026) : page *Couleurs par forum* — rappel que Clarté doit être le style de chaque forum (*Style du forum* sur *Style par défaut*), forums à style imposé signalés avec lien vers la gestion des forums.
- **1.4.0** (20/10/2026) : couleurs par forum (page *Couleurs par forum*, héritage), modèles proposés comme styles au choix des membres, sauvegarde des forums et styles, nettoyage à la désinstallation ; migration `v140_forum_styles`. Testé sur phpBB 3.3.15 réel : mise à jour 1.3 → 1.4.0, 34 vérifications (`dev/test_forums_styles.js`) + non-régression 1.3.0 (`dev/test_modeles.js`), désinstallation avec un style généré en service.
- **1.3.2** (16/10/2026) : barre de navigation en icônes seules sur mobile (burger et profil gardent leurs textes).
- **1.3.1** (16/10/2026) : *Date sur l'accueil* — sélecteur sans `.page-body >`, `!important`, testé avec les lignes déplacées dans un autre bloc.
- **1.3.0** (15/10/2026) : modèles enregistrés (enregistrer comme modèle, puces « Mes modèles », page de gestion : appliquer, modifier, dupliquer, exporter, importer, supprimer), page Sauvegarde et restauration, réglage *Date sur l'accueil*, journal ; migration `v130_models`. Testé sur phpBB 3.3.15 réel (SQLite, PHP 8.3) : mise à jour 1.2.0 → 1.3.0, 35 vérifications de bout en bout (`dev/test_modeles.js`), désinstallation et réinstallation propres.
- **1.2.0** (12/10/2026) : réglage *Bandeau des catégories* (neutre, teinté, comme l'en-tête, couleur choisie) pour `.forabg` / `.forumbg`, migration `v120_category_colour`, bandeau dans l'aperçu ; « Dernier message » du bandeau suit la couleur du texte.

---

## 5. Vérifier une modification

1. Forum phpBB 3.3.15 de test, avec prosilver (et Portfolio si possible).
2. Copier `styles/clarte` et `ext/verturin/clarte`, activer l'extension, installer le style, le mettre par défaut.
3. Purger le cache après chaque changement de gabarit (`php bin/phpbbcli.php cache:purge`).
4. Contrôler en clair **et** en sombre, sur ordinateur **et** sur mobile (390 px) : accueil, forum, sujet, rédaction, panneau de l'utilisateur, membres, profil, recherche, connexion, pages Portfolio ; dans l'ACP : accueil, paramètres, forums, utilisateurs, permissions avancées, extensions, styles, journaux, pages Portfolio, page Clarté.
5. Pour forcer un mode dans un navigateur : `localStorage.setItem('clarte_mode', 'dark')` (ou `light`, `auto`).

**Captures pour phpBB.com (1.4.1)** : `dev/demo_setup.py` + `dev/demo_shots.js` + `dev/demo_compose.py`, en anglais et en français (pack de langue française de phpBB : `git clone https://github.com/qiaeru/phpbb-language-fr`, copier `language/fr` et `styles/prosilver/theme/fr`). 15 captures par langue (accueil clair / sombre, sujet, forum coloré, mobile, pages ACP, ACP sombre, choix du style dans le panneau, style au choix) et descriptions BBCode EN / FR. Pour phpBB.com, style et extension sont proposés séparément (catalogue des styles et catalogue des extensions) : paquets `clarte-style - v X.Y.Z.zip` (racine `clarte/`) et `clarte-extension - v X.Y.Z.zip` (racine `verturin/clarte/`). Sur GitHub, un seul dépôt `verturin/clarte` (dossiers `styles/` et `ext/`), avec ces paquets joints aux versions publiées (Releases).

**Forum de test réel (1.3.0)** : `git clone --depth 1 --branch release-3.3.15 https://github.com/phpbb/phpbb.git`, `composer install --no-dev` dans `phpBB/`, installation en ligne de commande (`php install/phpbbcli.php install install.yml`, SQLite), puis `php -S 127.0.0.1:8080`. Pièges rencontrés : vider `cookie_domain` (Chromium refuse un cookie posé sur une IP), l'ACP exige `?sid=` dans l'adresse, et phpBB refuse un formulaire envoyé moins d'une seconde après l'affichage (« formulaire invalide ») : les tests attendent 1,2 s avant chaque envoi.

Le dossier `dev/` contient les scripts utilisés pendant le développement (captures Playwright, test fonctionnel de la page ACP : enregistrement, refus d'une couleur invalide, absence d'injection sous prosilver, remise à zéro, habillage ACP activable). Ils visaient le forum de test local (`http://127.0.0.1:8080/`, comptes `admin` / `Camille`) : adapter l'adresse, les comptes et les chemins avant de les relancer.

---

## 6. Pistes pour la suite (rien n'est commencé)

- Logo personnalisé pour l'en-tête du forum et de l'ACP (envoi depuis la page Clarté).
- Choix du mode mémorisé dans le profil du membre (aujourd'hui : par navigateur).
- Contrôle de contraste automatique quand la couleur principale est trop claire.
- Variante « compacte » (marges et tailles réduites).
- Régénération des couleurs de l'ACP pour d'autres extensions installées (Chastity Tracker, etc.).
- Préparation phpBB 4 (vérifier les noms d'événements et `admin.css`).

---

## 7. Message de démarrage à coller dans la nouvelle discussion

> Je continue le développement de mon style phpBB **Clarté** (verturin/clarte, version 1.4.1). Le pack joint contient tout le code source et le fichier PASSATION.md qui explique l'architecture, les outils et les décisions : lis-le en premier. Je publie sur GitHub uniquement par l'interface web. À chaque livraison, incrémente la version et complète le README avec le journal des versions, puis donne-moi le zip d'installation, le pack GitHub et un nouveau pack de passation (zips nommés « nom - v X.Y.Z.zip »). Réponds-moi en français. Ma demande : …
