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
	'ACP_CLARTE_TITLE'		=> 'Style Clarté',
	'ACP_CLARTE_SETTINGS'	=> 'Couleurs et apparence',
	'ACP_CLARTE_MODELS'		=> 'Modèles enregistrés',
	'ACP_CLARTE_BACKUP'		=> 'Sauvegarde et restauration',
	'LOG_CLARTE_MODEL_SAVED'		=> '<strong>Style Clarté : modèle enregistré</strong><br>» %s',
	'LOG_CLARTE_MODEL_APPLIED'		=> '<strong>Style Clarté : modèle appliqué</strong><br>» %s',
	'LOG_CLARTE_MODEL_DELETED'		=> '<strong>Style Clarté : modèle supprimé</strong><br>» %s',
	'LOG_CLARTE_MODELS_IMPORTED'	=> '<strong>Style Clarté : modèles importés</strong><br>» %s',
	'LOG_CLARTE_BACKUP_DOWNLOADED'	=> '<strong>Style Clarté : sauvegarde téléchargée</strong>',
	'LOG_CLARTE_BACKUP_RESTORED'	=> '<strong>Style Clarté : sauvegarde restaurée</strong>',
	'ACP_CLARTE_FORUMS'		=> 'Couleurs par forum',
	'LOG_CLARTE_FORUMS'		=> '<strong>Style Clarté : couleurs par forum modifiées</strong>',
	'LOG_CLARTE_STYLE_CREATED'	=> '<strong>Style Clarté : modèle proposé comme style</strong><br>» %s',
	'LOG_CLARTE_STYLE_DELETED'	=> '<strong>Style Clarté : style d’un modèle retiré</strong><br>» %s',
	'LOG_CLARTE_SETTINGS'	=> '<strong>Style Clarté : réglages modifiés</strong>',
	'CLARTE_MODE_AUTO'		=> 'Automatique',
	'CLARTE_MODE_LIGHT'		=> 'Clair',
	'CLARTE_MODE_DARK'		=> 'Sombre',
	'CLARTE_MODE_TITLE'		=> 'Apparence : cliquer pour changer',
]);
