# Clarté — style phpBB

![Version](https://img.shields.io/badge/version-1.8.3-2563eb) ![phpBB](https://img.shields.io/badge/phpBB-3.3.15%2B-0f172a) ![PHP](https://img.shields.io/badge/PHP-7.2%2B-6366f1) ![Licence](https://img.shields.io/badge/licence-GPL--2.0-475569)

🇬🇧 **[English version](README.en.md)**

Style complet pour phpBB 3.3.15 et plus, au dessin moderne : en-tête en dégradé, blocs arrondis, police Inter, **mode clair, sombre ou automatique**, et **couleur principale réglable dans l'ACP**. **Toute l'administration (ACP) prend la même présentation**, pages des extensions comprises. Des **modèles** de couleurs s'enregistrent sous un nom, s'appliquent **forum par forum** ou deviennent des **styles au choix** des membres. Il est fait pour aller avec l'extension [Portfolio](https://github.com/verturin/portfolio), qui prend automatiquement ses couleurs et son mode, sur le forum comme dans l'ACP.

![Accueil, mode clair et mode sombre](docs/apercu.jpg)

![Sujet, mode clair et mode sombre](docs/sujet.jpg)

| Un forum coloré par un modèle | Mobile, barre en icônes seules |
|---|---|
| ![Forum coloré par un modèle](docs/forum-couleurs.jpg) | ![Mobile, clair et sombre](docs/mobile.jpg) |

## Fonctions

- **Mode clair, sombre ou automatique** sur tout le forum, avec un bouton *Apparence* dans la barre de navigation (choix retenu par le navigateur, invités compris), sans clignement au chargement.
- **Couleurs réglables dans l'ACP** : palettes prêtes ou couleurs libres, couleur principale, en-tête en dégradé, bandeau des catégories, police, arrondi, aperçu clair / sombre en direct.
- **Modèles enregistrés** : garder un ensemble de couleurs sous un nom, l'appliquer, le modifier, le dupliquer, l'exporter ou l'importer (.json).
- **Couleurs par forum** : un modèle pour un forum ou une catégorie, hérité par les sous-forums.
- **Styles au choix** : un modèle devient un style « Clarté – nom » que les membres choisissent dans leur panneau ; retiré en un clic, ses membres reviennent à Clarté.
- **Sauvegarde et restauration** de tous les réglages, modèles, couleurs par forum et styles au choix.
- **Toute l'ACP aux couleurs de Clarté**, pages des extensions comprises (désactivable), avec les **pages de l'extension en cours en onglets** au-dessus du titre, toujours visibles.
- **Taille du texte** réglable pour tout le forum, et au choix de chaque membre dans son panneau si l'ACP l'autorise.
- **Icône du site** (favicon) envoyée depuis l'ACP, avec une version pour le mode sombre et l'icône de l'écran d'accueil des téléphones.
- **Images de fond** : une image derrière la page et une dans l'en-tête, chacune avec sa version pour le mode sombre ; voile, flou, blocs translucides, image fixe ou qui défile, motif répété, réglage pour le mobile, et les membres peuvent les masquer.
- **Mobile** : barre de navigation compacte en icônes seules ; **date de l'accueil** masquable ; couleurs de groupes lisibles en mode sombre.
- Aucune table ajoutée, aucun fichier de phpBB modifié ; langues : français, anglais.

## Téléchargement

Chaque version publiée (onglet [Releases](https://github.com/verturin/clarte/releases)) propose trois paquets :

| Paquet | Contenu | Pour qui |
|---|---|---|
| `clarte - v X.Y.Z.zip` | `styles/clarte/` + `ext/verturin/clarte/` | **à utiliser en général** : tout Clarté, à déposer à la racine du forum |
| `clarte-style - v X.Y.Z.zip` | `clarte/` | le style seul (à déposer dans `styles/`) |
| `clarte-extension - v X.Y.Z.zip` | `verturin/clarte/` | l'extension seule (à déposer dans `ext/`) |

### Ensemble ou séparément

Le style et l'extension s'utilisent ensemble ou séparément :

| Installé | Ce que vous obtenez |
|---|---|
| **Style + extension** (conseillé) | tout ce qui est décrit ici |
| **Style seul** | le dessin complet, mode clair ou sombre selon l'appareil du visiteur, couleur bleue ; pas de réglages dans l'ACP ni de bouton « Apparence » |
| **Extension seule** (avec prosilver ou un autre style) | l'ACP habillée façon Clarté (mode clair / sombre, bouton « Apparence », couleur principale des réglages), les onglets des pages d'extension dans l'ACP et l'icône du site (favicon) sur tous les styles ; les réglages du forum (couleurs, modèles, couleurs par forum, images de fond, taille du texte…) ne s'appliquent que là où le style Clarté est utilisé, les autres styles ne sont jamais touchés |

## Contenu

| Dossier | Rôle |
|---|---|
| `styles/clarte/` | Le style lui-même, enfant de prosilver. Il fonctionne seul (mode automatique, couleur bleue). |
| `ext/verturin/clarte/` | L'extension « Clarté — style settings » (réglages du style) : pages de réglages, bouton « Apparence », modèles, couleurs par forum, styles au choix, sauvegarde, habillage de toute l'administration. |
| `docs/` | Captures du README. |
| `tools/` | Outils de génération des couleurs (après une mise à jour de phpBB ou de Portfolio). |
| `dev/` | Scripts de développement et de test (forum de test local) ; ne font pas partie du style. |

Comme Clarté est un style enfant, prosilver doit rester installé (il l'est toujours, c'est le style d'origine de phpBB). Les mises à jour de phpBB qui corrigent prosilver profitent aussi à Clarté.

## Installation

1. Décompressez `clarte - v 1.8.3.zip` et envoyez les dossiers `styles` et `ext` à la racine du forum (là où se trouvent `index.php` et `config.php`). Les dossiers se fusionnent avec ceux qui existent.
2. ACP › **Personnaliser** › **Gérer les extensions** : activez **Clarté — style settings** (nom affiché dans la liste des extensions depuis la 1.6.1 ; « Clarté — réglages du style » avant).
3. ACP › **Personnaliser** › **Styles** › *Installer des styles* : installez **Clarté**.
4. Pour en faire le style de tout le forum : ACP › **Général** › *Paramètres du forum* › **Style par défaut** = Clarté. Vérifiez aussi que le *Style du forum* de chaque forum (ACP › Forums › Gérer les forums) est sur *Style par défaut*.
5. ACP › **Extensions** › **Style Clarté** › *Couleurs et apparence* : choisissez vos couleurs.

## Mise à jour

Envoyez les dossiers `styles` et `ext` du nouveau zip par-dessus les anciens, puis, dans ACP › Personnaliser › Gérer les extensions, désactivez puis réactivez **Clarté — style settings** (les nouvelles options sont ajoutées, vos couleurs et modèles sont conservés). Purgez enfin le cache depuis l'accueil de l'ACP. Le journal des versions ci-dessous précise quand la désactivation n'est pas nécessaire.

## Réglages dans l'ACP

![Page Couleurs et apparence](docs/acp-reglages.jpg)

- **Palettes prêtes** : bleu, indigo, violet, framboise, orange, vert, sarcelle, graphite. Un clic remplit les trois couleurs.
- **Mes modèles** : vos modèles enregistrés, en un clic dans le formulaire.
- **Couleur principale** : liens, boutons, onglets actifs, pagination, citations. Toutes les nuances (fonds clairs, survols, couleurs de prosilver) en sont tirées, en mode clair comme en mode sombre.
- **En-tête** : dégradé de deux couleurs (mettez deux fois la même pour un aplat).
- **Bandeau des catégories** : barre de titre des catégories et des listes de sujets — neutre, teinté (couleur principale adoucie), comme l'en-tête ou couleur choisie (texte blanc ou foncé selon la couleur).
- **Mode par défaut** : automatique (suit l'appareil du visiteur), clair ou sombre.
- **Laisser chacun choisir** : affiche le bouton *Apparence* dans la barre de navigation (Automatique → Clair → Sombre). Le choix est retenu par le navigateur, y compris pour les invités.
- **Habiller l'administration** : applique Clarté à toutes les pages de l'ACP (voir plus bas).
- **Date sur l'accueil** : afficher ou masquer « Nous sommes le… » et « Dernière visite… » sur l'accueil.
- **Police** : Inter (incluse, aucun appel extérieur) ou celle de l'appareil.
- **Arrondi des blocs** : de 0 à 20 px.
- **Taille du texte** : curseur de 80 à 150 %, de 1 en 1, pour tout le forum ; **Laisser chaque membre choisir sa taille** ajoute ce choix dans le panneau de l'utilisateur (gardé dans le profil du membre).
- **Images de fond** (fond de la page et en-tête) : choix dans la bibliothèque ou envoi d'une image (JPG, PNG, WebP, GIF, 3 Mio et 8000 px au plus), version facultative pour le mode sombre, affichage (couvrir, motif répété, centrée), partie gardée, image fixe ou qui défile, voile de 0 à 80 %, flou de 0 à 20 px, blocs translucides de 0 à 60 %, image sur mobile ou non, voile de l'en-tête de 0 à 90 %. **Laisser chaque membre masquer les images** ajoute ce choix dans son panneau. La **bibliothèque d'images**, en bas de page, montre où chaque image sert et permet de supprimer celles qui ne servent plus.

![Images de fond, mode clair et mode sombre](docs/images-de-fond.jpg)

- **Icône du site** : icône (fichier .ico ou PNG carré), icône pour le mode sombre de l'appareil, icône de l'écran d'accueil (PNG 180 × 180 px). Elle s'applique à tout le forum, quel que soit le style, et à l'administration.
- Un **aperçu** clair et sombre se met à jour pendant la saisie. Le bouton *Revenir aux valeurs d'origine* remet tout à zéro. En bas de page, **Enregistrer comme modèle** garde les valeurs du formulaire sous un nom, sans les appliquer.

## Modèles enregistrés et styles au choix

![Modèles enregistrés](docs/acp-modeles.jpg)

- **Modèles enregistrés** (ACP › Extensions › Style Clarté) : appliquer, modifier, dupliquer, exporter, importer, supprimer. Un modèle contient la couleur principale, l'en-tête, le bandeau des catégories, le mode par défaut, la police et l'arrondi ; les options propres au forum (bouton *Apparence*, habillage de l'ACP, date sur l'accueil) n'en font pas partie et ne changent pas quand on applique un modèle.
- **Styles au choix** : *Proposer comme style* crée un style enfant de Clarté, `styles/clarte_m<n>/`, avec quelques fichiers relais ; les couleurs sont lues dans le modèle à chaque page. Les membres le choisissent dans *Préférences du forum › Mon style* (option *Remplacer le style des membres* désactivée). *Retirer le style* (ou supprimer le modèle) l'efface et renvoie ses membres vers Clarté. La liste indique le nombre de membres de chaque style et les forums colorés par chaque modèle.

![Le forum vu avec un style au choix](docs/style-au-choix.jpg)

## Couleurs par forum

![Couleurs par forum](docs/acp-couleurs-par-forum.jpg)

- Pour chaque forum ou catégorie (ACP › Extensions › Style Clarté › *Couleurs par forum*), choisissez un modèle, *Comme le forum parent* (par défaut) ou *Couleurs générales*. Seules les couleurs changent (couleur principale, en-tête, bandeau des catégories) : police, arrondi et mode restent ceux du forum. Les couleurs du forum passent avant celles du style choisi par le membre.
- **Important** : Clarté doit être le style utilisé partout où les couleurs doivent s'appliquer. Dans ACP › Forums › Gérer les forums, le réglage *Style du forum* de chaque forum et sous-forum doit être sur *Style par défaut* (ou Clarté), et Clarté doit être le style par défaut du forum (ou celui choisi par le membre). Un forum qui impose un autre style (prosilver ou autre) garde les couleurs de ce style ; la page *Couleurs par forum* le signale.

## Sauvegarde et restauration

![Sauvegarde et restauration](docs/acp-sauvegarde.jpg)

Un fichier `clarte-sauvegarde-AAAA-MM-JJ-hhmmss.json` contient tous les réglages, tous les modèles, les couleurs par forum et les styles au choix. À la restauration, vous choisissez quoi rétablir (réglages, modèles ajoutés ou remplacés). Il sert aussi à copier sa présentation sur un autre forum.

## L'administration aux couleurs de Clarté

| Paramètres du forum, mode sombre | Permissions avancées, mode sombre |
|---|---|
| ![Paramètres du forum, mode sombre](docs/acp-sombre.jpg) | ![Permissions avancées, mode sombre](docs/admin-permissions-sombre.jpg) |
| **Portfolio, mode sombre** | **Réglages de Portfolio, mode sombre** |
| ![Portfolio, mode sombre](docs/portfolio-sombre.jpg) | ![Réglages de Portfolio, mode sombre](docs/admin-portfolio-sombre.jpg) |

- **Toutes les pages de l'ACP** : accueil, paramètres, forums, membres et groupes, permissions (y compris la grille avancée), styles, extensions, journaux, maintenance… et les pages des extensions (Portfolio, phpBB Gallery, etc.).
- **Même charte que le forum** : en-tête en dégradé avec le logo phpBB en blanc, onglets, menu de gauche avec un trait sous chaque section, groupes de réglages en cartes arrondies, champs et boutons modernes, tableaux aérés.
- **Même couleur principale et même mode** que le forum. Un bouton *Apparence* dans l'en-tête de l'ACP passe d'Automatique à Clair puis à Sombre ; le choix est partagé avec le forum (même navigateur).
- L'option **Habiller l'administration** (Oui par défaut) permet de revenir à l'ACP d'origine de phpBB sans désactiver l'extension.

## Points techniques

- Toutes les couleurs de prosilver sont converties en variables CSS (`tools/tokenize_colours.py`), chacune avec sa valeur claire et sombre. Le mode sombre couvre donc l'ensemble du forum : index, sujets, rédaction, panneau de l'utilisateur, MCP, recherche, membres, messages privés.
- Le mode choisi est appliqué dans l'en-tête, avant l'affichage : pas de clignement blanc au chargement.
- Les couleurs saisies sont vérifiées (format `#rrggbb` uniquement) avant d'être écrites dans la page.
- Les réglages ne s'appliquent qu'aux membres qui utilisent Clarté (ou un style enfant de Clarté). Les autres styles ne sont pas touchés.
- Les couleurs de groupes, écrites en dur par phpBB, sont éclaircies en mode sombre pour rester lisibles.
- Extension Portfolio : ses pages suivent la couleur principale, la police et le mode sombre de Clarté.
- Administration : les couleurs de `adm/style/admin.css` de phpBB (et de la feuille de l'ACP de Portfolio) sont converties de la même façon par `tools/tokenize_admin.py`, qui produit `adm/style/clarte_admin_colours.css` ; `clarte_admin.css` apporte le dessin. Les deux feuilles sont chargées après celles de phpBB et des extensions, par l'événement `acp_overall_header_stylesheets_after` : aucun fichier de phpBB n'est modifié.
- Pour régénérer après une mise à jour de phpBB : `python3 tools/tokenize_admin.py ext/verturin/clarte/adm/style/clarte_admin_colours.css <forum>/adm/style/admin.css <forum>/ext/verturin/portfolio/adm/style/portfolio_acp.css`.
- Compatible phpBB 3.3.15 et plus ; aucune table ajoutée (32 clés `clarte_*` dans la configuration, deux colonnes `user_clarte_text` et `user_clarte_bg` dans la table des membres, images de fond dans le dossier `images/clarte/` ; modèles, couleurs par forum et styles au choix en JSON dans `config_text` : `clarte_models`, `clarte_forum_models`, `clarte_model_styles`).

## Désinstallation

Choisissez un autre style par défaut, désinstallez Clarté dans *Styles*, puis désactivez et supprimez les données de l'extension (les images de fond et le dossier `images/clarte/` sont effacés). Supprimez enfin `styles/clarte` et `ext/verturin/clarte`.

## Crédits

- Police **Inter** © The Inter Project Authors, licence SIL Open Font License 1.1 (`styles/clarte/theme/fonts/OFL.txt`).
- Mise en page de **prosilver** © phpBB Limited.
- Clarté © 2026 verturin — GPL-2.0.

## Historique des versions

### 1.8.3 — 12 novembre 2026
- Menu du panneau de l'utilisateur sur ordinateur : le menu de gauche et la liste des amis restent collés en haut de l'écran quand on fait défiler une page longue, plus besoin de remonter pour changer de page. S'ils sont plus hauts que l'écran, ils défilent dans leur colonne. Rien ne change sur tablette et mobile.
- Mise à jour depuis la 1.8.x : envoyez `styles` et `ext`, puis purgez le cache.

### 1.8.2 — 12 novembre 2026
- Menu du panneau de l'utilisateur sur tablette et mobile (900 px et moins) : au lieu d'un gros bloc de barres empilées, les entrées s'affichent en petits boutons alignés sur plusieurs lignes, dans un cadre, avec un trait vertical entre les groupes (rédiger, dossiers, réglages des messages privés) et la page en cours en surbrillance. Rien ne change sur ordinateur.
- Mise à jour depuis la 1.8.x : envoyez `styles` et `ext`, puis purgez le cache.

### 1.8.1 — 10 novembre 2026
- Onglets des pages de l'extension dans l'ACP, mis au point avec l'auteur :
  - placés au-dessus du titre, sur la largeur des pages de l'extension seulement (ils ne recouvrent plus le menu de gauche) ;
  - seulement pour les extensions (plus rien sur les pages de phpBB) ;
  - seulement les pages, sans le nom de l'extension devant ;
  - présentés en petites étiquettes encadrées, regroupées dans un cadre global, qui passent sur plusieurs lignes quand l'extension a beaucoup de pages (plus de défilement sur le côté) ;
  - toujours collés en haut de l'écran quand on fait défiler la page.
- Mise à jour depuis la 1.8.0 : envoyez `styles` et `ext`, puis purgez le cache.

### 1.8.0 — 10 novembre 2026
- **Onglets des pages de l'extension dans l'ACP** : quand on règle une extension qui a plusieurs pages, ses pages (celles de son bloc dans le menu de gauche) s'affichent aussi en onglets au-dessus du titre de la page, avec la page en cours en surbrillance. Seulement les pages, sans le nom de l'extension ; uniquement pour les extensions, pas pour les pages de phpBB. Les onglets passent sur plusieurs lignes s'il y en a beaucoup.
- Les onglets restent collés en haut de l'écran quand on fait défiler la page : plus besoin de remonter pour passer d'une page à l'autre.
- Nouvelle option **Onglets des pages de l'extension** (Oui par défaut), avec l'habillage de l'administration.
- Mise à jour : envoyez `styles` et `ext`, désactivez puis réactivez l'extension (nouvelle option), puis purgez le cache.

### 1.7.0 — 6 novembre 2026
- **Icône du site** (ACP › *Couleurs et apparence* › *Icône du site*) : l'icône affichée dans l'onglet du navigateur, à côté de l'adresse, et dans les favoris.
  - **Icône** : fichier .ico ou image PNG carrée (32 × 32 ou 48 × 48 px conseillé) ;
  - **Icône pour le mode sombre** (facultative) : utilisée quand l'appareil est en mode sombre (Chrome, Edge, Firefox) ;
  - **Icône de l'écran d'accueil** (facultative) : PNG 180 × 180 px, quand on ajoute le forum à l'écran d'accueil d'un iPhone, d'un iPad ou d'un téléphone Android.
- L'icône s'applique à **tout le forum, quel que soit le style** (prosilver compris), et à l'administration. Sans réglage, rien ne change : le navigateur garde le `favicon.ico` de la racine du forum s'il existe.
- Les icônes sont rangées dans la bibliothèque d'images (`images/clarte/`), incluses dans la sauvegarde ; chaque emplacement n'accepte que ses formats (ICO ou PNG pour l'icône, PNG pour l'écran d'accueil, plus d'ICO pour les images de fond).
- Mise à jour : envoyez `styles` et `ext`, désactivez puis réactivez l'extension (trois clés de configuration), puis purgez le cache.

### 1.6.1 — 6 novembre 2026
- ACP › *Images de fond* : les listes d'images et les champs d'envoi ne débordent plus du cadre (champ d'envoi sur sa propre ligne, nom de fichier raccourci dans la liste).
- Préparation de la publication sur phpBB.com : `composer.json` en anglais (nom affiché « Clarté — style settings », description), `license.txt` ajouté au style ; vérifié par l'Extension Pre-Validator sans aucune remarque.
- Mise à jour depuis la 1.6.0 : envoyez `styles` et `ext`, puis purgez le cache (pas besoin de désactiver l'extension).

### 1.6.0 — 5 novembre 2026
- **Images de fond** (ACP › *Couleurs et apparence* › *Images de fond*) :
  - **fond de la page** : image derrière les blocs, avec une version facultative pour le mode sombre (sans elle, la même image plus voilée) ; affichage *Couvrir tout l'écran*, *Motif répété* ou *Centrée, à sa taille* ; partie gardée (centre, haut, bas) ; image fixe ou qui défile ; **voile** à la couleur de la page (0 à 80 %) ; **flou** (0 à 20 px) ; **blocs translucides** avec léger flou (0 à 60 %, menus déroulants opaques) ; **image sur mobile** oui ou non (moins de 700 px) ;
  - **en-tête** : image à la place du dégradé, version sombre facultative, partie gardée, voile aux couleurs de l'en-tête (0 à 90 %) pour garder le titre lisible ;
  - avec une image de fond, « Qui est en ligne », les statistiques et la connexion rapide passent dans des blocs, et les textes restés sur l'image ont un halo, pour rester lisibles.
- Envoi depuis l'ACP (JPG, PNG, WebP ou GIF, 3 Mio et 8000 px au plus ; le contenu est vérifié), ou choix d'une image déjà envoyée. Les images sont rangées dans `images/clarte/` (hors de l'extension : une mise à jour ne les efface pas) ; une même image n'est stockée qu'une fois.
- **Bibliothèque d'images** en bas de la page : vignettes, taille, où chaque image sert ; suppression des images inutilisées.
- **Laisser chaque membre masquer les images** (Non par défaut) : « Images de fond : Afficher / Masquer » dans le panneau de l'utilisateur, gardé dans son profil.
- Les images font partie des **modèles** : couleurs par forum (un modèle avec image donne son image au forum ; sans image, le forum garde celle du forum), styles au choix. L'**export de modèles** et la **sauvegarde** contiennent les images, recréées à l'import ou à la restauration.
- Aperçu de l'ACP avec l'image, le voile, le flou et les blocs translucides, dès le choix du fichier.
- Mise à jour : envoyez `styles` et `ext`, désactivez puis réactivez l'extension (14 clés de configuration, colonne `user_clarte_bg`), puis purgez le cache. Le serveur doit pouvoir créer le dossier `images/clarte/`.

### 1.5.1 — 30 octobre 2026
- **Taille du texte** : les quatre tailles nommées sont remplacées par un **curseur de 80 à 150 %, qui avance de 1 en 1**. Le pourcentage s'affiche en direct à côté du curseur, l'aperçu de l'ACP suit, et un bouton « 100 % » remet la taille normale (à 100 %, le texte des messages fait environ 13,7 px).
- Panneau de l'utilisateur : même curseur, avec la case **« Taille du forum (N %) »** cochée par défaut. En la décochant, le membre choisit sa taille et voit la page changer tout de suite ; il enregistre avec *Envoyer*.
- Les réglages de la 1.5.0 sont convertis : Petite → 90 %, Normale → 100 %, Grande → 110 %, Très grande → 125 % (pour le forum et pour les membres).
- Mise à jour : envoyez `styles` et `ext`, désactivez puis réactivez l'extension (migration de conversion), puis purgez le cache.

### 1.5.0 — 30 octobre 2026
- **Taille du texte** (ACP › *Couleurs et apparence*) : Petite, Normale, Grande ou Très grande pour tout le forum. Toutes les tailles du texte suivent (messages, listes, titres) ; texte des messages en 12,6 / 13,7 / 15,3 / 17,1 px environ.
- **Laisser chaque membre choisir sa taille** (Non par défaut) : ajoute « Taille du texte » dans le panneau de l'utilisateur (*Préférences du forum › Modifier les paramètres généraux*). Le choix est gardé dans le profil du membre, donc sur tous ses appareils ; « Celle du forum » suit le réglage de l'ACP. Si l'option est refermée, le choix des membres est ignoré.
- Mise à jour : envoyez `styles` et `ext`, désactivez puis réactivez l'extension (nouvelle colonne `user_clarte_text` dans la table des membres, deux clés de configuration), puis purgez le cache.

### 1.4.3 — 24 octobre 2026
- Correction : sur un forum qui propose aussi un autre style (prosilver, style imposé à un forum, choix d'un membre), le premier style affiché après une purge du cache imposait sa version des pages à l'autre. Selon l'ordre, les pages de l'autre style affichaient une erreur, ou Clarté perdait ses couleurs, son mode clair / sombre et le bouton « Apparence ». Les événements de gabarit de l'extension sont désormais communs à tous les styles et ne s'affichent qu'avec Clarté.
- Mise à jour : envoyez les nouveaux dossiers, puis **supprimez l'ancien dossier `ext/verturin/clarte/styles/clarte/`** (les événements sont maintenant dans `styles/all/` ; sans cette suppression, ils seraient chargés deux fois avec Clarté). Purgez enfin le cache. Pas besoin de désactiver l'extension.

### 1.4.2 — 21 octobre 2026
- Extension conforme à l'**Extension Pre-Validator (EPV)** de phpBB, l'outil de vérification utilisé pour le catalogue de phpBB.com : aucune erreur ni remarque. Fichier `license.txt` ajouté dans le dossier de l'extension ; échappement des textes par `utf8_htmlspecialchars()` de phpBB ; fichiers .json envoyés par l'objet réponse de phpBB (Symfony) au lieu d'un `echo`.
- Aucun changement visible ; pas de migration (envoyez les fichiers et purgez le cache).

### 1.4.1 — 21 octobre 2026
- README refait avec de nouvelles captures (forum de démonstration), version anglaise `README.en.md`, section *Téléchargement* (paquet complet, style seul, extension seule).
- Page *Couleurs par forum* : rappel que les couleurs ne s'appliquent que là où Clarté (ou un style « Clarté – … ») est utilisé — le *Style du forum* de chaque forum et sous-forum doit être sur *Style par défaut* (ou Clarté). Les forums qui imposent un autre style sont signalés, avec un lien vers *Forums › Gérer les forums*.

### 1.4.0 — 20 octobre 2026
- **Couleurs par forum** (nouvelle page ACP › Extensions › Style Clarté › *Couleurs par forum*) : un modèle peut être choisi pour chaque forum ou catégorie. Ses pages (liste des sujets, sujets, rédaction) prennent les couleurs du modèle — couleur principale, en-tête, bandeau des catégories — avec la même présentation. Les sous-forums suivent leur parent, sauf s'ils ont leur propre choix ou *Couleurs générales*.
- **Modèles proposés comme styles** : sur la page *Modèles enregistrés*, *Proposer comme style* crée un style « Clarté – nom du modèle » que les membres choisissent dans leur panneau (Préférences du forum). Il suit les changements du modèle (couleurs et nom). *Retirer le style* le supprime des styles du forum : ses membres (et le style par défaut, s'il l'était) reviennent au style Clarté.
- La liste des modèles indique le style au choix (avec son nombre de membres) et les forums colorés par chaque modèle ; la suppression d'un modèle retire aussi son style et rend leurs couleurs aux forums concernés.
- Un style généré supprimé depuis ACP › Personnaliser › Styles est oublié par l'extension, qui efface son dossier.
- Sauvegarde et restauration : les forums de chaque modèle et ses styles au choix sont sauvegardés et rétablis.
- Désinstallation (suppression des données de l'extension) : les styles générés sont retirés, leurs membres reviennent au style Clarté.
- Mise à jour : désactivez puis réactivez l'extension (nouvelle page, clés `clarte_forum_models` et `clarte_model_styles`), puis purgez le cache. Le serveur doit pouvoir écrire dans le dossier `styles/` pour créer les styles au choix.

### 1.3.2 — 16 octobre 2026
- Mobile (700 px et moins) : la barre de navigation n'affiche plus que les icônes (liens de phpBB et des extensions, pseudo à côté de l'avatar), et tient sur une seule ligne. Le menu burger et le menu du profil gardent leurs textes ; les textes masqués restent lus par les lecteurs d'écran. Rien ne change sur ordinateur.

### 1.3.1 — 16 octobre 2026
- Correction : *Date sur l'accueil* ne masquait rien quand une extension place ces lignes dans un autre bloc de la page (bandeau, classement…). Les lignes sont maintenant visées où qu'elles soient sur l'accueil.

### 1.3.0 — 15 octobre 2026
- **Modèles enregistrés** : les couleurs et l'apparence (couleur principale, en-tête, bandeau des catégories, mode par défaut, police, arrondi) peuvent être gardées sous un nom, depuis la page de réglages (*Enregistrer comme modèle*) ou depuis la nouvelle page *Modèles enregistrés*. Les modèles apparaissent aussi en tête de la page de réglages (*Mes modèles*) : un clic remplit le formulaire.
- Nouvelle page **Modèles enregistrés** : appliquer (avec confirmation), modifier (nom et réglages, avec l'aperçu), dupliquer, exporter (un modèle ou tous, fichier .json), importer, supprimer (avec confirmation). Le modèle en service est signalé. Jusqu'à 100 modèles.
- Nouvelle page **Sauvegarde et restauration** : télécharge un fichier .json avec tous les réglages et tous les modèles ; restauration au choix des réglages et/ou des modèles (ajoutés aux modèles existants ou à leur place). Les fichiers envoyés sont vérifiés (format, taille, couleurs).
- Nouveau réglage **Date sur l'accueil** : afficher ou masquer les lignes « Nous sommes le… » et « Dernière visite… » de l'accueil (tout afficher, masquer l'une ou l'autre, tout masquer).
- Journal d'administration : enregistrement, application, suppression et import de modèles, sauvegarde téléchargée et restaurée.
- Mise à jour : désactivez puis réactivez l'extension (nouvelle clé `clarte_index_time`, modèles stockés dans `clarte_models`, deux nouvelles pages dans le module), puis purgez le cache.

### 1.2.0 — 12 octobre 2026
- Nouveau réglage *Bandeau des catégories* : couleur de la barre de titre des catégories et des listes de sujets (`.forabg` / `.forumbg` : index, forums, sujets non lus, recherche…). Quatre choix : *Neutre* (gris clair, comme avant), *Teinté* (couleur principale adoucie, suit le mode sombre), *Comme l'en-tête* (même dégradé) ou *Couleur choisie*. Avec une couleur choisie, le texte passe automatiquement en blanc ou en foncé pour rester lisible.
- L'aperçu de la page de réglages montre le bandeau.
- Mise à jour : désactivez puis réactivez l'extension (nouvelles clés `clarte_cat_mode` et `clarte_cat_colour`), puis purgez le cache.

### 1.1.2 — 12 octobre 2026
- ACP : un trait sépare à nouveau les sections du menu de gauche (sous chaque titre, à la couleur principale), et les titres de section prennent la couleur principale : les groupes de liens (Messages, Fichiers joints, Portfolio, pages des extensions…) se distinguent nettement, en clair comme en sombre.
- Correction : la page *Couleurs et apparence* refusait d'enregistrer (« Veuillez modifier la valeur pour correspondre au format demandé ») même avec des couleurs valides, et l'aperçu ne suivait pas la saisie du code couleur. Le moteur de gabarits de phpBB transformait le `{6}` du contrôle de format en simple `6`.
- Après la mise à jour, purgez le cache (onglet *Général*) pour que la nouvelle feuille de l'ACP soit chargée.

### 1.1.1 — 11 octobre 2026
- ACP : couleurs régénérées pour les nouveautés de Portfolio 1.9.8 (barre de modification en nombre des albums), lisibles en mode sombre.
- ACP en mode sombre : couleurs de groupes (pseudos des administrateurs, etc.) éclaircies, comme sur le forum.

### 1.1.0 — 11 octobre 2026
- Habillage de toute l'administration (ACP) : en-tête, onglets, menu, groupes de réglages, champs, boutons, tableaux, messages, pagination, grille des permissions ; pages des extensions comprises.
- Mode clair, sombre ou automatique dans l'ACP, avec un bouton *Apparence* dans l'en-tête (choix partagé avec le forum).
- Pages d'administration de Portfolio harmonisées (couleur principale de Clarté, mode sombre).
- Nouvelle option *Habiller l'administration* (Oui par défaut).
- Page de réglages de Clarté lisible en mode sombre (palettes, sélecteurs de couleur).

### 1.0.0 — 11 octobre 2026
- Première version : style enfant de prosilver, mode clair, sombre ou automatique, bouton *Apparence* pour les membres.
- Extension de réglages : couleur principale, couleurs de l'en-tête, palettes prêtes, mode par défaut, police, arrondi, aperçu en direct.
- Intégration de l'extension Portfolio (couleurs et mode sombre).
