# Scripts de développement

Utilisés pendant la mise au point de Clarté sur un forum de test local. Ils ne font pas partie du style.

| Script | Rôle |
|---|---|
| `client.py` | petit client HTTP (connexion, formulaires, jeton) utilisé par les tests ; `PF_BASE` = adresse du forum (défaut `http://127.0.0.1:8080/`) ; `sql()` interroge la base de test |
| `clarte.py` | test fonctionnel de la page ACP de Clarté (20 vérifications) : enregistrement, couleur invalide refusée, rien d'injecté sous prosilver, remise à zéro, habillage de l'ACP activable, journal |
| `shoot.py` | captures Playwright du forum : `python3 shoot.py light dark d m index topic pf_user …` |
| `shoot_acp.py` | captures de l'ACP (clair / sombre, `m` pour mobile) |
| `shoot_perm.py` | captures de la grille des permissions avancées |
| `test_modeles.js` | test Playwright de la 1.3.0 (35 vérifications) : modèles (enregistrer, dupliquer, modifier, exporter, appliquer, supprimer, importer, nom piégé), sauvegarde et restauration, fichiers refusés, date sur l'accueil |
| `reset_modeles.sh` | remet le forum de test à zéro avant `test_modeles.js` |
| `test_forums_styles.js` | test Playwright de la 1.4.0 (34 vérifications) : couleurs par forum (forum, sous-forum hérité, sujet, rédaction, catégorie, « couleurs générales »), style au choix (création, fichiers, choix dans le panneau, renommage, retrait, membre revenu à Clarté), sauvegarde/restauration des forums et styles, style supprimé ailleurs, suppression d'un modèle utilisé. Arborescence attendue : 1 catégorie { 2 forum { 3 sous-forum } } 4 catégorie { 5 forum } |
| `reset_forums_styles.sh` | remet le forum de test à zéro (modèles, forums, styles générés) avant les tests |
| `demo_setup.py` | forum de démonstration neutre pour les captures phpBB.com : `python3 demo_setup.py en\|fr` (part de `data/base.db`, arborescence Communauté / Clubs, titre et langue du forum) |
| `demo_shots.js` | contenu de démonstration (2 membres, 6 sujets, 3 modèles, 2 forums colorés, un style au choix) puis captures : `node demo_shots.js en\|fr <dossier>` |
| `demo_compose.py` | assemble `06-mobile.png` (clair + sombre) et `00-cover.png` (moitié clair, moitié sombre) |
| `deploy.sh` | copie le style et l'extension dans le forum de test et vide le cache |

Les chemins (`S=…/scratchpad`) et les comptes (`admin`, `Camille`, `Julien`) sont ceux de l'environnement d'origine : à adapter.
