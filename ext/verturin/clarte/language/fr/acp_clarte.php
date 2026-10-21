<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_CLARTE_EXPLAIN'			=> 'Ces réglages s’appliquent aux membres qui utilisent le style Clarté. Les autres styles ne sont pas touchés.',
	'ACP_CLARTE_STYLE_MISSING'		=> 'Le style Clarté n’est pas encore installé. Copiez le dossier <strong>styles/clarte</strong> sur le forum, puis installez-le dans <a href="%s">Personnalisation › Styles</a>.',
	'ACP_CLARTE_STYLE_NOT_DEFAULT'	=> 'Le style Clarté est installé mais n’est pas le style par défaut du forum. Vous pouvez le choisir dans <a href="%s">Personnalisation › Styles</a>.',
	'ACP_CLARTE_STYLE_DEFAULT'		=> 'Le style Clarté est le style par défaut du forum.',

	'ACP_CLARTE_COLOURS'			=> 'Couleurs',
	'ACP_CLARTE_PRESETS'			=> 'Palettes prêtes',
	'ACP_CLARTE_PRESETS_EXPLAIN'	=> 'Un clic remplit les trois couleurs ci-dessous ; enregistrez ensuite.',
	'CLARTE_ACCENT'					=> 'Couleur principale',
	'CLARTE_ACCENT_EXPLAIN'			=> 'Liens, boutons, onglets actifs, pagination, citations. Les nuances (fonds clairs, survols) en sont tirées automatiquement, en mode clair comme en mode sombre.',
	'CLARTE_HEADER_FROM'			=> 'En-tête : première couleur',
	'CLARTE_HEADER_TO'				=> 'En-tête : seconde couleur',
	'CLARTE_HEADER_EXPLAIN'			=> 'L’en-tête est un dégradé de la première à la seconde couleur. Mettez deux fois la même pour une couleur unie. Préférez des teintes foncées : le titre est écrit en blanc.',
	'CLARTE_CAT_MODE'				=> 'Bandeau des catégories',
	'CLARTE_CAT_MODE_EXPLAIN'		=> 'Barre de titre des catégories et des listes de sujets (index, forums, sujets non lus, résultats de recherche…).',
	'CLARTE_CAT_MODE_NEUTRAL'		=> 'Neutre (gris clair)',
	'CLARTE_CAT_MODE_TINTED'		=> 'Teinté (couleur principale adoucie)',
	'CLARTE_CAT_MODE_HEADER'		=> 'Comme l’en-tête (dégradé)',
	'CLARTE_CAT_MODE_CUSTOM'		=> 'Couleur choisie',
	'CLARTE_CAT_COLOUR'				=> 'Couleur du bandeau',
	'CLARTE_CAT_COLOUR_EXPLAIN'		=> 'Utilisée en clair comme en sombre. Le texte passe en blanc ou en foncé selon la couleur, pour rester lisible.',
	'ACP_CLARTE_PREVIEW_CATEGORY'	=> 'Catégorie',
	'ACP_CLARTE_BAD_COLOUR'			=> '« %1$s » : la valeur « %2$s » n’est pas une couleur au format #rrggbb.',

	'ACP_CLARTE_APPEARANCE'			=> 'Apparence',
	'CLARTE_DEFAULT_MODE'			=> 'Mode par défaut',
	'CLARTE_DEFAULT_MODE_EXPLAIN'	=> 'Automatique suit le réglage de l’appareil du visiteur (clair le jour, sombre la nuit…).',
	'CLARTE_ALLOW_TOGGLE'			=> 'Laisser chacun choisir',
	'CLARTE_ALLOW_TOGGLE_EXPLAIN'	=> 'Affiche le bouton « Apparence » dans la barre de navigation. Le choix est retenu par le navigateur du visiteur.',
	'CLARTE_ACP'					=> 'Habiller l’administration',
	'CLARTE_ACP_EXPLAIN'			=> 'Toutes les pages de l’ACP, y compris celles des extensions, prennent la présentation de Clarté : mêmes couleurs, même en-tête et même mode clair, sombre ou automatique (bouton « Apparence » dans l’en-tête de l’administration).',
	'CLARTE_FONT'					=> 'Police',
	'CLARTE_FONT_INTER'				=> 'Inter (incluse dans le style)',
	'CLARTE_FONT_SYSTEM'			=> 'Police de l’appareil',
	'CLARTE_RADIUS'					=> 'Arrondi des blocs',
	'CLARTE_RADIUS_EXPLAIN'			=> 'En pixels, de 0 (angles droits) à 20. Par défaut : 12.',

	'ACP_CLARTE_PREVIEW'			=> 'Aperçu',
	'ACP_CLARTE_PREVIEW_TITLE'		=> 'Mon forum',
	'ACP_CLARTE_PREVIEW_LINK'		=> 'Un lien',
	'ACP_CLARTE_PREVIEW_BUTTON'		=> 'Bouton',

	'ACP_CLARTE_RESET'				=> 'Revenir aux valeurs d’origine',
	'ACP_CLARTE_RESET_CONFIRM'		=> 'Remettre toutes les couleurs et options d’origine ?',
	'ACP_CLARTE_SAVED'				=> 'Réglages enregistrés.',
	'ACP_CLARTE_RESET_DONE'			=> 'Valeurs d’origine rétablies.',

	'ACP_CLARTE_PRESET_BLUE'		=> 'Bleu',
	'ACP_CLARTE_PRESET_INDIGO'		=> 'Indigo',
	'ACP_CLARTE_PRESET_VIOLET'		=> 'Violet',
	'ACP_CLARTE_PRESET_ROSE'		=> 'Framboise',
	'ACP_CLARTE_PRESET_ORANGE'		=> 'Orange',
	'ACP_CLARTE_PRESET_GREEN'		=> 'Vert',
	'ACP_CLARTE_PRESET_TEAL'		=> 'Sarcelle',
	'ACP_CLARTE_PRESET_GRAPHITE'	=> 'Graphite',

	'CLARTE_INDEX_TIME'				=> 'Date sur l’accueil',
	'CLARTE_INDEX_TIME_EXPLAIN'		=> 'Lignes « Nous sommes le… » et « Dernière visite… » affichées sous la barre de navigation de l’accueil.',
	'CLARTE_INDEX_TIME_BOTH'		=> 'Tout afficher',
	'CLARTE_INDEX_TIME_NOW'			=> 'Masquer la dernière visite',
	'CLARTE_INDEX_TIME_LAST'		=> 'Masquer la date du jour',
	'CLARTE_INDEX_TIME_NONE'		=> 'Tout masquer',

	// Modèles
	'ACP_CLARTE_SAVED_MODELS'		=> 'Mes modèles',
	'ACP_CLARTE_SAVED_MODELS_EXPLAIN'	=> 'Un clic remplit tout le formulaire avec ce modèle ; enregistrez ensuite pour l’appliquer.',
	'ACP_CLARTE_SAVE_AS_MODEL'		=> 'Enregistrer comme modèle',
	'ACP_CLARTE_SAVE_AS_MODEL_EXPLAIN'	=> 'Garde les couleurs et l’apparence ci-dessus sous un nom, sans les appliquer au forum.',
	'ACP_CLARTE_SAVE_MODEL'			=> 'Enregistrer le modèle',
	'ACP_CLARTE_GO_MODELS'			=> 'Gérer les modèles enregistrés',
	'ACP_CLARTE_BACK_MODELS'		=> 'Retour aux modèles enregistrés',
	'ACP_CLARTE_MODEL'				=> 'Modèle',
	'ACP_CLARTE_MODEL_NAME'			=> 'Nom du modèle',
	'ACP_CLARTE_MODEL_NAME_PLACEHOLDER'	=> 'Ex. : Noël, Été vert, Sobre…',
	'ACP_CLARTE_MODEL_NAME_EMPTY'	=> 'Donnez un nom au modèle.',
	'ACP_CLARTE_MODEL_LIMIT'		=> 'Le nombre maximal de modèles (%d) est atteint. Supprimez-en un avant d’en ajouter.',
	'ACP_CLARTE_MODEL_SAVED'		=> 'Modèle « %s » enregistré.',
	'ACP_CLARTE_MODEL_UPDATED'		=> 'Modèle « %s » modifié.',
	'ACP_CLARTE_MODEL_APPLIED'		=> 'Modèle « %s » appliqué au forum.',
	'ACP_CLARTE_MODEL_DELETED'		=> 'Modèle « %s » supprimé.',
	'ACP_CLARTE_MODEL_NOT_FOUND'	=> 'Ce modèle n’existe pas (ou plus).',
	'ACP_CLARTE_MODEL_APPLY_CONFIRM'	=> 'Appliquer le modèle « %s » au forum ? Les couleurs et l’apparence en service seront remplacées (pensez à les enregistrer comme modèle si vous voulez les garder).',
	'ACP_CLARTE_MODEL_DELETE_CONFIRM'	=> 'Supprimer définitivement le modèle « %s » ?',
	'ACP_CLARTE_MODEL_COPY_OF'		=> '%s (copie)',
	'ACP_CLARTE_MODEL_EDIT'			=> 'Modifier un modèle',
	'ACP_CLARTE_MODEL_EDIT_EXPLAIN'	=> 'Les changements sont enregistrés dans le modèle seulement. Pour les voir sur le forum, appliquez ensuite le modèle depuis la liste.',
	'ACP_CLARTE_MODEL_SAVE'			=> 'Enregistrer le modèle',
	'ACP_CLARTE_MODELS_EXPLAIN'		=> 'Un modèle garde un ensemble de couleurs et d’apparence (couleur principale, en-tête, bandeau des catégories, mode par défaut, police, arrondi) sous un nom. Vous pouvez l’appliquer au forum en un clic, le modifier, le dupliquer, l’exporter pour un autre forum ou le supprimer.',
	'ACP_CLARTE_MODELS_EMPTY'		=> 'Aucun modèle enregistré pour l’instant. Enregistrez les réglages en service ci-dessous, ou depuis la page Couleurs et apparence.',
	'ACP_CLARTE_MODEL_SETTINGS'		=> 'Réglages',
	'ACP_CLARTE_MODEL_UPDATED_ON'	=> 'Modifié le',
	'ACP_CLARTE_MODEL_IN_USE'		=> 'En service',
	'ACP_CLARTE_MODEL_APPLY'		=> 'Appliquer',
	'ACP_CLARTE_MODEL_DUPLICATE'	=> 'Dupliquer',
	'ACP_CLARTE_MODEL_EXPORT'		=> 'Exporter',
	'ACP_CLARTE_MODEL_COUNT'		=> '%1$d modèle(s) sur %2$d possibles',
	'ACP_CLARTE_MODELS_EXPORT_ALL'	=> 'Exporter tous les modèles',
	'ACP_CLARTE_MODEL_NEW'			=> 'Nouveau modèle à partir des réglages en service',
	'ACP_CLARTE_MODEL_NEW_EXPLAIN'	=> 'Enregistre les couleurs et l’apparence actuellement appliquées au forum.',
	'ACP_CLARTE_MODELS_IMPORT'		=> 'Importer des modèles',
	'ACP_CLARTE_MODELS_IMPORT_EXPLAIN'	=> 'Fichier .json exporté par Clarté (un modèle, tous les modèles ou une sauvegarde complète). Les modèles sont ajoutés à la liste ; un nom déjà pris reçoit un numéro.',
	'ACP_CLARTE_IMPORT'				=> 'Importer',
	'ACP_CLARTE_MODELS_IMPORTED'	=> [
		0	=> 'Aucun modèle importé.',
		1	=> '%d modèle importé.',
		2	=> '%d modèles importés.',
	],
	'ACP_CLARTE_SUMMARY_CAT'		=> 'bandeau %s',
	'CLARTE_CAT_SHORT_NEUTRAL'		=> 'neutre',
	'CLARTE_CAT_SHORT_TINTED'		=> 'teinté',
	'CLARTE_CAT_SHORT_HEADER'		=> 'comme l’en-tête',
	'CLARTE_CAT_SHORT_CUSTOM'		=> 'couleur choisie',
	'ACP_CLARTE_SUMMARY_FONT_INTER'	=> 'Inter',
	'ACP_CLARTE_SUMMARY_FONT_SYSTEM'	=> 'police de l’appareil',
	'ACP_CLARTE_SUMMARY_RADIUS'		=> 'arrondi %d px',

	// Fichiers
	'ACP_CLARTE_FILE'				=> 'Fichier',
	'ACP_CLARTE_FILE_MISSING'		=> 'Choisissez un fichier à envoyer.',
	'ACP_CLARTE_FILE_TOO_BIG'		=> 'Le fichier est trop gros (%s au maximum).',
	'ACP_CLARTE_FILE_INVALID'		=> 'Ce fichier n’est pas un fichier Clarté valide (JSON attendu).',
	'ACP_CLARTE_FILE_NO_MODELS'		=> 'Ce fichier ne contient pas de modèles Clarté.',
	'ACP_CLARTE_FILE_NOT_BACKUP'	=> 'Ce fichier n’est pas une sauvegarde complète de Clarté. Pour un fichier de modèles, utilisez « Importer des modèles » dans la page Modèles enregistrés.',

	// Sauvegarde et restauration
	'ACP_CLARTE_BACKUP_EXPLAIN'		=> 'Une sauvegarde est un fichier .json qui contient tous les réglages de Clarté et tous les modèles enregistrés. Gardez-la avant une grosse modification, ou servez-vous-en pour copier votre présentation sur un autre forum.',
	'ACP_CLARTE_BACKUP_SAVE'		=> 'Sauvegarder',
	'ACP_CLARTE_BACKUP_CONTENT'		=> 'Contenu',
	'ACP_CLARTE_BACKUP_SAVE_EXPLAIN'	=> 'Le fichier est téléchargé par votre navigateur ; rien n’est gardé sur le serveur.',
	'ACP_CLARTE_BACKUP_SETTINGS'	=> 'Tous les réglages en service',
	'ACP_CLARTE_BACKUP_MODELS'		=> [
		0	=> 'Aucun modèle enregistré',
		1	=> '%d modèle enregistré',
		2	=> '%d modèles enregistrés',
	],
	'ACP_CLARTE_BACKUP_DOWNLOAD'	=> 'Télécharger la sauvegarde',
	'ACP_CLARTE_RESTORE'			=> 'Restaurer',
	'ACP_CLARTE_RESTORE_FILE_EXPLAIN'	=> 'Fichier de sauvegarde Clarté (.json, %s au maximum).',
	'ACP_CLARTE_RESTORE_WHAT'		=> 'À restaurer',
	'ACP_CLARTE_RESTORE_SETTINGS'	=> 'Les réglages (couleurs, apparence, options)',
	'ACP_CLARTE_RESTORE_MODELS'		=> 'Les modèles enregistrés',
	'ACP_CLARTE_RESTORE_MODELS_MODE'	=> 'Modèles',
	'ACP_CLARTE_RESTORE_MODELS_MODE_EXPLAIN'	=> 'Ajouter garde vos modèles actuels ; remplacer les supprime d’abord.',
	'ACP_CLARTE_RESTORE_MODE_ADD'	=> 'Ajouter à mes modèles',
	'ACP_CLARTE_RESTORE_MODE_REPLACE'	=> 'Remplacer mes modèles',
	'ACP_CLARTE_RESTORE_BUTTON'		=> 'Restaurer',
	'ACP_CLARTE_RESTORE_CONFIRM'	=> 'Restaurer cette sauvegarde ? Les éléments cochés seront remplacés.',
	'ACP_CLARTE_RESTORE_NOTHING'	=> 'Cochez au moins un élément à restaurer.',
	'ACP_CLARTE_RESTORE_DONE'		=> 'Sauvegarde restaurée.',
	'ACP_CLARTE_RESTORED_SETTINGS'	=> 'Réglages rétablis.',
	'ACP_CLARTE_RESTORED_MODELS'	=> [
		0	=> 'Aucun modèle ajouté.',
		1	=> '%d modèle ajouté.',
		2	=> '%d modèles ajoutés.',
	],

	// Couleurs par forum
	'ACP_CLARTE_FORUMS_EXPLAIN'		=> 'Choisissez un modèle pour un forum ou une catégorie : ses pages (liste des sujets, sujets, rédaction) prennent les couleurs du modèle — couleur principale, en-tête et bandeau des catégories — avec la même présentation que le reste du forum. Les sous-forums suivent leur parent, sauf si vous leur donnez un autre choix.',
	'ACP_CLARTE_FORUMS_NO_MODELS'	=> 'Aucun modèle enregistré : créez d’abord un modèle (par exemple une version rose de vos couleurs).',
	'ACP_CLARTE_FORUMS_MODEL'		=> 'Couleurs',
	'ACP_CLARTE_FORUMS_RESULT'		=> 'Résultat',
	'ACP_CLARTE_FORUMS_INHERIT'		=> 'Comme le forum parent (couleurs générales)',
	'ACP_CLARTE_FORUMS_INHERIT_FROM'	=> 'Comme le forum parent (%s)',
	'ACP_CLARTE_FORUMS_GENERAL'		=> 'Couleurs générales (ne pas hériter)',
	'ACP_CLARTE_FORUMS_GENERAL_SHORT'	=> 'Couleurs générales',
	'ACP_CLARTE_FORUMS_FROM'		=> 'hérité de « %s »',
	'ACP_CLARTE_FORUMS_STYLE_NOTE'	=> '<strong>Important :</strong> les couleurs ne s’appliquent que là où le style Clarté (ou un style « Clarté – … ») est utilisé. Dans <a href="%s">Forums › Gérer les forums</a>, le réglage <em>Style du forum</em> de chaque forum et sous-forum doit être sur <em>Style par défaut</em> (ou sur Clarté), et Clarté doit être le style par défaut du forum ou celui choisi par le membre.',
	'ACP_CLARTE_FORUMS_OTHER_STYLES'	=> [
		1	=> '%1$d forum impose un autre style que Clarté : ses couleurs ne changeront pas. Modifiez son <em>Style du forum</em> dans <a href="%2$s">Forums › Gérer les forums</a>.',
		2	=> '%1$d forums imposent un autre style que Clarté : leurs couleurs ne changeront pas. Modifiez leur <em>Style du forum</em> dans <a href="%2$s">Forums › Gérer les forums</a>.',
	],
	'ACP_CLARTE_FORUMS_FORCED_STYLE'	=> 'Style imposé : %s — couleurs non appliquées',
	'ACP_CLARTE_FORUMS_SAVED'		=> 'Couleurs des forums enregistrées.',

	// Modèles proposés comme styles
	'ACP_CLARTE_MODELS_STYLES_EXPLAIN'	=> '<strong>Proposer comme style</strong> crée un style « Clarté – nom du modèle » que chaque membre peut choisir dans son panneau (Préférences du forum). Il a la même présentation que Clarté avec les couleurs du modèle, et suit les modifications du modèle. <strong>Retirer le style</strong> le supprime des styles du forum : ses membres reviennent au style Clarté.',
	'ACP_CLARTE_STYLE_PREFIX'		=> 'Clarté – ',
	'ACP_CLARTE_STYLE_ADD'			=> 'Proposer comme style',
	'ACP_CLARTE_STYLE_REMOVE'		=> 'Retirer le style',
	'ACP_CLARTE_STYLE_CREATED'		=> 'Style « %s » créé : les membres peuvent le choisir dans leur panneau (Préférences du forum › Mon style), si le forum ne leur impose pas un style.',
	'ACP_CLARTE_STYLE_DELETED'		=> [
		0	=> 'Style retiré. Aucun membre ne l’utilisait.',
		1	=> 'Style retiré. %d membre est revenu au style Clarté.',
		2	=> 'Style retiré. %d membres sont revenus au style Clarté.',
	],
	'ACP_CLARTE_STYLE_DELETE_CONFIRM'	=> 'Retirer le style du modèle « %s » ? Il sera supprimé des styles du forum et ses membres reviendront au style Clarté. Le modèle, lui, est conservé.',
	'ACP_CLARTE_STYLE_NO_PARENT'	=> 'Le style Clarté n’est pas installé : installez-le d’abord dans Personnaliser › Styles.',
	'ACP_CLARTE_STYLE_NOT_WRITABLE'	=> 'Impossible d’écrire dans le dossier styles/ du forum. Vérifiez ses droits (le serveur doit pouvoir y créer un dossier).',
	'ACP_CLARTE_MODEL_STYLE_INFO'	=> [
		0	=> 'Style au choix : %2$s (aucun membre)',
		1	=> 'Style au choix : %2$s (%1$d membre)',
		2	=> 'Style au choix : %2$s (%1$d membres)',
	],
	'ACP_CLARTE_MODEL_FORUMS_INFO'	=> 'Forums : %s',
	'ACP_CLARTE_MODEL_USED_FORUMS'	=> 'Ce modèle colore les forums : %s. Ils reprendront les couleurs de leur parent.',
	'ACP_CLARTE_MODEL_USED_STYLE'	=> [
		0	=> 'Le style « %2$s » sera aussi retiré.',
		1	=> 'Le style « %2$s » sera aussi retiré ; son membre reviendra au style Clarté.',
		2	=> 'Le style « %2$s » sera aussi retiré ; ses %1$d membres reviendront au style Clarté.',
	],
	'ACP_CLARTE_RESTORED_FORUMS'	=> [
		1	=> '%d forum a retrouvé ses couleurs.',
		2	=> '%d forums ont retrouvé leurs couleurs.',
	],
	'ACP_CLARTE_RESTORED_STYLES'	=> [
		1	=> '%d style au choix recréé.',
		2	=> '%d styles au choix recréés.',
	],
]);
