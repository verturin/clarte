<?php
/**
 * Clarté — style settings
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
	'ACP_CLARTE_TITLE'		=> 'Clarté style',
	'ACP_CLARTE_SETTINGS'	=> 'Colours and appearance',
	'ACP_CLARTE_MODELS'		=> 'Saved models',
	'ACP_CLARTE_BACKUP'		=> 'Backup and restore',
	'LOG_CLARTE_MODEL_SAVED'		=> '<strong>Clarté style: model saved</strong><br>» %s',
	'LOG_CLARTE_MODEL_APPLIED'		=> '<strong>Clarté style: model applied</strong><br>» %s',
	'LOG_CLARTE_MODEL_DELETED'		=> '<strong>Clarté style: model deleted</strong><br>» %s',
	'LOG_CLARTE_MODELS_IMPORTED'	=> '<strong>Clarté style: models imported</strong><br>» %s',
	'LOG_CLARTE_BACKUP_DOWNLOADED'	=> '<strong>Clarté style: backup downloaded</strong>',
	'LOG_CLARTE_BACKUP_RESTORED'	=> '<strong>Clarté style: backup restored</strong>',
	'ACP_CLARTE_FORUMS'		=> 'Colours per forum',
	'LOG_CLARTE_FORUMS'		=> '<strong>Clarté style: colours per forum changed</strong>',
	'LOG_CLARTE_STYLE_CREATED'	=> '<strong>Clarté style: model offered as a style</strong><br>» %s',
	'LOG_CLARTE_STYLE_DELETED'	=> '<strong>Clarté style: model style removed</strong><br>» %s',
	'LOG_CLARTE_SETTINGS'	=> '<strong>Clarté style: settings changed</strong>',
	'CLARTE_MODE_AUTO'		=> 'Automatic',
	'CLARTE_MODE_LIGHT'		=> 'Light',
	'CLARTE_MODE_DARK'		=> 'Dark',
	'CLARTE_MODE_TITLE'		=> 'Appearance: click to change',
]);
