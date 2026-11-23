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
	'CLARTE_TEXT_UCP'			=> 'Taille du texte',
	'CLARTE_TEXT_UCP_EXPLAIN'	=> 'Taille des textes du forum avec le style Clarté, de 80 à 150 %, sur tous vos appareils. L’aperçu est immédiat ; envoyez pour l’enregistrer.',
	'CLARTE_TEXT_BOARD'			=> 'Taille du forum (%s %%)',
	'CLARTE_BG_UCP'	=> 'Images de fond',
	'CLARTE_BG_UCP_EXPLAIN'	=> 'Images derrière la page et dans l’en-tête du forum, avec le style Clarté. Les masquer allège les pages.',
	'CLARTE_BG_SHOW'	=> 'Afficher',
	'CLARTE_BG_HIDE'	=> 'Masquer',
]);
