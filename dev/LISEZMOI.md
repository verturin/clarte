# Scripts de développement

Utilisés pendant la mise au point de Clarté sur un forum de test local. Ils ne font pas partie du style.

| Script | Rôle |
|---|---|
| `test_modeles.js` | test Playwright de la 1.3.0 (35 vérifications) : modèles (enregistrer, dupliquer, modifier, exporter, appliquer, supprimer, importer, nom piégé), sauvegarde et restauration, fichiers refusés, date sur l'accueil |
| `test_forums_styles.js` | test Playwright de la 1.4.0 (34 vérifications) : couleurs par forum (forum, sous-forum hérité, sujet, rédaction, catégorie, « couleurs générales »), style au choix (création, fichiers, choix dans le panneau, renommage, retrait, membre revenu à Clarté), sauvegarde/restauration des forums et styles, style supprimé ailleurs, suppression d'un modèle utilisé. Arborescence attendue : 1 catégorie { 2 forum { 3 sous-forum } } 4 catégorie { 5 forum } |
| `reset_forums_styles.sh` | remet le forum de test à zéro (modèles, forums, styles générés, taille du texte, images de fond et dossier `images/clarte/`) avant chaque test |
| `demo-captures.zip` | scripts des captures phpBB.com, regroupés pour garder le dépôt sous 100 fichiers (à décompresser dans `dev/`) : `demo_setup.py` (forum de démonstration neutre : `python3 demo_setup.py en\|fr`, part de `data/base.db`), `demo_shots.js` (contenu de démonstration puis captures : `node demo_shots.js en\|fr <dossier>`), `demo_compose.py` (assemble `06-mobile.png` et `00-cover.png`) |
| `test_onglets_acp.js` | 1.8.0 : onglets des pages de l'extension dans l'ACP (16 vérifications) — au-dessus des pages sans recouvrir le menu, seulement les pages de l'extension, page en cours, collés en haut après défilement, clic, rien sur les pages de phpBB, plusieurs lignes sans défilement, mobile, option et habillage désactivés. `node test_onglets_acp.js <sortie>` |
| `test_favicon.js` | 1.7.0 : icône du site (20 vérifications) — formats refusés par emplacement, envoi des trois icônes, balises sur Clarté, prosilver et l'ACP (avec ou sans habillage), listes filtrées, bibliothèque, sauvegarde, nom trafiqué ignoré. `node test_favicon.js <sortie> <dossier des images>` (favicon.ico, favicon-sombre.png, touch.png, paysage.jpg) |
| `test_ordre_cache.sh` | 1.4.3 : purge le cache puis affiche deux styles dans l'ordre donné (`?style=`) et rapporte code HTTP et traces de Clarté |
| `test_taille_texte.js` | 1.5.1 : curseur de taille (ACP, pas de 1), choix du membre dans le panneau (case « Taille du forum », aperçu), option refermée, valeur hors bornes, prosilver non touché (19 vérifications) |
| `test_images_fond.js` | 1.6.0 : images de fond (44 vérifications) — fichiers refusés (pas une image, trop grande), envoi des trois images, doublon évité, fond / voile / flou / blocs translucides / en-tête en clair et en sombre, sombre sans image dédiée, mobile, image qui défile et motif, prosilver non touché, bibliothèque (suppression, lien falsifié), choix dans la liste, membre qui masque, modèle de forum, sauvegarde et restauration avec les images. `node test_images_fond.js <sortie> <dossier des images>` (paysage.jpg, nuit.jpg, bandeau.jpg, motif.png, faux.jpg, immense.png) |

Les chemins et les comptes (`admin` / `adminpass123`) sont ceux du forum de test d'origine : à adapter. Les anciens scripts Python de la 1.0 (`client.py`, `clarte.py`, `shoot*.py`) ont été retirés en 1.6.0 : les tests Playwright les remplacent.
