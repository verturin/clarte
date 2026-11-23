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
	'CLARTE_TEXT_UCP'			=> 'Text size',
	'CLARTE_TEXT_UCP_EXPLAIN'	=> 'Size of the board’s text with the Clarté style, from 80 to 150%, on all your devices. The preview is immediate; submit to save it.',
	'CLARTE_TEXT_BOARD'			=> 'Board size (%s%%)',
	'CLARTE_BG_UCP'	=> 'Background images',
	'CLARTE_BG_UCP_EXPLAIN'	=> 'Images behind the page and in the board header, with the Clarté style. Hiding them makes pages lighter.',
	'CLARTE_BG_SHOW'	=> 'Show',
	'CLARTE_BG_HIDE'	=> 'Hide',
]);
