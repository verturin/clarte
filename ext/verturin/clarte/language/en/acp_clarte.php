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
	'ACP_CLARTE_EXPLAIN'			=> 'These settings apply to members using the Clarté style. Other styles are not affected.',
	'ACP_CLARTE_STYLE_MISSING'		=> 'The Clarté style is not installed yet. Copy the <strong>styles/clarte</strong> folder to the board, then install it in <a href="%s">Customise › Styles</a>.',
	'ACP_CLARTE_STYLE_NOT_DEFAULT'	=> 'The Clarté style is installed but is not the board’s default style. You can select it in <a href="%s">Customise › Styles</a>.',
	'ACP_CLARTE_STYLE_DEFAULT'		=> 'Clarté is the board’s default style.',

	'ACP_CLARTE_COLOURS'			=> 'Colours',
	'ACP_CLARTE_PRESETS'			=> 'Ready-made palettes',
	'ACP_CLARTE_PRESETS_EXPLAIN'	=> 'One click fills the three colours below; then save.',
	'CLARTE_ACCENT'					=> 'Main colour',
	'CLARTE_ACCENT_EXPLAIN'			=> 'Links, buttons, active tabs, pagination, quotes. Shades (light backgrounds, hovers) are derived from it automatically, in light and dark mode.',
	'CLARTE_HEADER_FROM'			=> 'Header: first colour',
	'CLARTE_HEADER_TO'				=> 'Header: second colour',
	'CLARTE_HEADER_EXPLAIN'			=> 'The header is a gradient from the first to the second colour. Use the same colour twice for a solid header. Prefer dark shades: the title is white.',
	'CLARTE_CAT_MODE'				=> 'Category bar',
	'CLARTE_CAT_MODE_EXPLAIN'		=> 'Title bar of categories and topic lists (index, forums, unread topics, search results…).',
	'CLARTE_CAT_MODE_NEUTRAL'		=> 'Neutral (light grey)',
	'CLARTE_CAT_MODE_TINTED'		=> 'Tinted (soft main colour)',
	'CLARTE_CAT_MODE_HEADER'		=> 'Same as the header (gradient)',
	'CLARTE_CAT_MODE_CUSTOM'		=> 'Chosen colour',
	'CLARTE_CAT_COLOUR'				=> 'Bar colour',
	'CLARTE_CAT_COLOUR_EXPLAIN'		=> 'Used in light and dark mode. The text turns white or dark depending on the colour, to stay readable.',
	'ACP_CLARTE_PREVIEW_CATEGORY'	=> 'Category',
	'ACP_CLARTE_BAD_COLOUR'			=> '“%1$s”: “%2$s” is not a colour in #rrggbb format.',

	'ACP_CLARTE_APPEARANCE'			=> 'Appearance',
	'CLARTE_DEFAULT_MODE'			=> 'Default mode',
	'CLARTE_DEFAULT_MODE_EXPLAIN'	=> 'Automatic follows the visitor’s device setting.',
	'CLARTE_ALLOW_TOGGLE'			=> 'Let everyone choose',
	'CLARTE_ALLOW_TOGGLE_EXPLAIN'	=> 'Shows the “Appearance” button in the navigation bar. The choice is remembered by the visitor’s browser.',
	'CLARTE_ACP'					=> 'Apply to the administration panel',
	'CLARTE_ACP_EXPLAIN'			=> 'Every ACP page, extension pages included, takes the Clarté look: same colours, same header and same light, dark or automatic mode (“Appearance” button in the ACP header).',
	'CLARTE_FONT'					=> 'Font',
	'CLARTE_FONT_INTER'				=> 'Inter (included with the style)',
	'CLARTE_FONT_SYSTEM'			=> 'Device font',
	'CLARTE_RADIUS'					=> 'Block rounding',
	'CLARTE_RADIUS_EXPLAIN'			=> 'In pixels, from 0 (square corners) to 20. Default: 12.',

	'ACP_CLARTE_PREVIEW'			=> 'Preview',
	'ACP_CLARTE_PREVIEW_TITLE'		=> 'My board',
	'ACP_CLARTE_PREVIEW_LINK'		=> 'A link',
	'ACP_CLARTE_PREVIEW_BUTTON'		=> 'Button',

	'ACP_CLARTE_RESET'				=> 'Restore the original values',
	'ACP_CLARTE_RESET_CONFIRM'		=> 'Restore all original colours and options?',
	'ACP_CLARTE_SAVED'				=> 'Settings saved.',
	'ACP_CLARTE_RESET_DONE'			=> 'Original values restored.',

	'ACP_CLARTE_PRESET_BLUE'		=> 'Blue',
	'ACP_CLARTE_PRESET_INDIGO'		=> 'Indigo',
	'ACP_CLARTE_PRESET_VIOLET'		=> 'Violet',
	'ACP_CLARTE_PRESET_ROSE'		=> 'Raspberry',
	'ACP_CLARTE_PRESET_ORANGE'		=> 'Orange',
	'ACP_CLARTE_PRESET_GREEN'		=> 'Green',
	'ACP_CLARTE_PRESET_TEAL'		=> 'Teal',
	'ACP_CLARTE_PRESET_GRAPHITE'	=> 'Graphite',

	'CLARTE_INDEX_TIME'				=> 'Date on the index',
	'CLARTE_INDEX_TIME_EXPLAIN'		=> '“It is currently…” and “Last visit…” lines shown under the navigation bar of the board index.',
	'CLARTE_INDEX_TIME_BOTH'		=> 'Show both',
	'CLARTE_INDEX_TIME_NOW'			=> 'Hide the last visit',
	'CLARTE_INDEX_TIME_LAST'		=> 'Hide the current date',
	'CLARTE_INDEX_TIME_NONE'		=> 'Hide both',

	// Models
	'ACP_CLARTE_SAVED_MODELS'		=> 'My models',
	'ACP_CLARTE_SAVED_MODELS_EXPLAIN'	=> 'One click fills the whole form with this model; then save to apply it.',
	'ACP_CLARTE_SAVE_AS_MODEL'		=> 'Save as a model',
	'ACP_CLARTE_SAVE_AS_MODEL_EXPLAIN'	=> 'Keeps the colours and appearance above under a name, without applying them to the board.',
	'ACP_CLARTE_SAVE_MODEL'			=> 'Save model',
	'ACP_CLARTE_GO_MODELS'			=> 'Manage saved models',
	'ACP_CLARTE_BACK_MODELS'		=> 'Back to saved models',
	'ACP_CLARTE_MODEL'				=> 'Model',
	'ACP_CLARTE_MODEL_NAME'			=> 'Model name',
	'ACP_CLARTE_MODEL_NAME_PLACEHOLDER'	=> 'E.g. Christmas, Summer green, Plain…',
	'ACP_CLARTE_MODEL_NAME_EMPTY'	=> 'Give the model a name.',
	'ACP_CLARTE_MODEL_LIMIT'		=> 'The maximum number of models (%d) has been reached. Delete one before adding another.',
	'ACP_CLARTE_MODEL_SAVED'		=> 'Model “%s” saved.',
	'ACP_CLARTE_MODEL_UPDATED'		=> 'Model “%s” updated.',
	'ACP_CLARTE_MODEL_APPLIED'		=> 'Model “%s” applied to the board.',
	'ACP_CLARTE_MODEL_DELETED'		=> 'Model “%s” deleted.',
	'ACP_CLARTE_MODEL_NOT_FOUND'	=> 'This model does not exist (any more).',
	'ACP_CLARTE_MODEL_APPLY_CONFIRM'	=> 'Apply the model “%s” to the board? The current colours and appearance will be replaced (save them as a model first if you want to keep them).',
	'ACP_CLARTE_MODEL_DELETE_CONFIRM'	=> 'Permanently delete the model “%s”?',
	'ACP_CLARTE_MODEL_COPY_OF'		=> '%s (copy)',
	'ACP_CLARTE_MODEL_EDIT'			=> 'Edit a model',
	'ACP_CLARTE_MODEL_EDIT_EXPLAIN'	=> 'Changes are saved in the model only. To see them on the board, apply the model from the list afterwards.',
	'ACP_CLARTE_MODEL_SAVE'			=> 'Save model',
	'ACP_CLARTE_MODELS_EXPLAIN'		=> 'A model keeps a set of colours and appearance (main colour, header, category bar, default mode, font, rounding) under a name. You can apply it to the board in one click, edit it, duplicate it, export it for another board or delete it.',
	'ACP_CLARTE_MODELS_EMPTY'		=> 'No saved model yet. Save the current settings below, or from the Colours and appearance page.',
	'ACP_CLARTE_MODEL_SETTINGS'		=> 'Settings',
	'ACP_CLARTE_MODEL_UPDATED_ON'	=> 'Updated',
	'ACP_CLARTE_MODEL_IN_USE'		=> 'In use',
	'ACP_CLARTE_MODEL_APPLY'		=> 'Apply',
	'ACP_CLARTE_MODEL_DUPLICATE'	=> 'Duplicate',
	'ACP_CLARTE_MODEL_EXPORT'		=> 'Export',
	'ACP_CLARTE_MODEL_COUNT'		=> '%1$d of %2$d possible model(s)',
	'ACP_CLARTE_MODELS_EXPORT_ALL'	=> 'Export all models',
	'ACP_CLARTE_MODEL_NEW'			=> 'New model from the current settings',
	'ACP_CLARTE_MODEL_NEW_EXPLAIN'	=> 'Saves the colours and appearance currently applied to the board.',
	'ACP_CLARTE_MODELS_IMPORT'		=> 'Import models',
	'ACP_CLARTE_MODELS_IMPORT_EXPLAIN'	=> '.json file exported by Clarté (one model, all models or a full backup). Models are added to the list; a name already in use gets a number.',
	'ACP_CLARTE_IMPORT'				=> 'Import',
	'ACP_CLARTE_MODELS_IMPORTED'	=> [
		0	=> 'No model imported.',
		1	=> '%d model imported.',
		2	=> '%d models imported.',
	],
	'ACP_CLARTE_SUMMARY_CAT'		=> '%s bar',
	'CLARTE_CAT_SHORT_NEUTRAL'		=> 'neutral',
	'CLARTE_CAT_SHORT_TINTED'		=> 'tinted',
	'CLARTE_CAT_SHORT_HEADER'		=> 'header-style',
	'CLARTE_CAT_SHORT_CUSTOM'		=> 'custom colour',
	'ACP_CLARTE_SUMMARY_FONT_INTER'	=> 'Inter',
	'ACP_CLARTE_SUMMARY_FONT_SYSTEM'	=> 'device font',
	'ACP_CLARTE_SUMMARY_RADIUS'		=> '%d px rounding',

	// Files
	'ACP_CLARTE_FILE'				=> 'File',
	'ACP_CLARTE_FILE_MISSING'		=> 'Choose a file to upload.',
	'ACP_CLARTE_FILE_TOO_BIG'		=> 'The file is too big (%s maximum).',
	'ACP_CLARTE_FILE_INVALID'		=> 'This is not a valid Clarté file (JSON expected).',
	'ACP_CLARTE_FILE_NO_MODELS'		=> 'This file contains no Clarté models.',
	'ACP_CLARTE_FILE_NOT_BACKUP'	=> 'This file is not a full Clarté backup. For a models file, use “Import models” on the Saved models page.',

	// Backup and restore
	'ACP_CLARTE_BACKUP_EXPLAIN'		=> 'A backup is a .json file holding every Clarté setting and every saved model. Keep one before a big change, or use it to copy your look to another board.',
	'ACP_CLARTE_BACKUP_SAVE'		=> 'Back up',
	'ACP_CLARTE_BACKUP_CONTENT'		=> 'Content',
	'ACP_CLARTE_BACKUP_SAVE_EXPLAIN'	=> 'The file is downloaded by your browser; nothing is kept on the server.',
	'ACP_CLARTE_BACKUP_SETTINGS'	=> 'All current settings',
	'ACP_CLARTE_BACKUP_MODELS'		=> [
		0	=> 'No saved model',
		1	=> '%d saved model',
		2	=> '%d saved models',
	],
	'ACP_CLARTE_BACKUP_DOWNLOAD'	=> 'Download the backup',
	'ACP_CLARTE_RESTORE'			=> 'Restore',
	'ACP_CLARTE_RESTORE_FILE_EXPLAIN'	=> 'Clarté backup file (.json, %s maximum).',
	'ACP_CLARTE_RESTORE_WHAT'		=> 'Restore',
	'ACP_CLARTE_RESTORE_SETTINGS'	=> 'Settings (colours, appearance, options)',
	'ACP_CLARTE_RESTORE_MODELS'		=> 'Saved models',
	'ACP_CLARTE_RESTORE_MODELS_MODE'	=> 'Models',
	'ACP_CLARTE_RESTORE_MODELS_MODE_EXPLAIN'	=> 'Add keeps your current models; replace deletes them first.',
	'ACP_CLARTE_RESTORE_MODE_ADD'	=> 'Add to my models',
	'ACP_CLARTE_RESTORE_MODE_REPLACE'	=> 'Replace my models',
	'ACP_CLARTE_RESTORE_BUTTON'		=> 'Restore',
	'ACP_CLARTE_RESTORE_CONFIRM'	=> 'Restore this backup? The ticked items will be replaced.',
	'ACP_CLARTE_RESTORE_NOTHING'	=> 'Tick at least one item to restore.',
	'ACP_CLARTE_RESTORE_DONE'		=> 'Backup restored.',
	'ACP_CLARTE_RESTORED_SETTINGS'	=> 'Settings restored.',
	'ACP_CLARTE_RESTORED_MODELS'	=> [
		0	=> 'No model added.',
		1	=> '%d model added.',
		2	=> '%d models added.',
	],

	// Colours per forum
	'ACP_CLARTE_FORUMS_EXPLAIN'		=> 'Choose a model for a forum or a category: its pages (topic list, topics, posting) take the model’s colours — main colour, header and category bar — with the same layout as the rest of the board. Subforums follow their parent unless you give them another choice.',
	'ACP_CLARTE_FORUMS_NO_MODELS'	=> 'No saved model: create a model first (for instance a pink version of your colours).',
	'ACP_CLARTE_FORUMS_MODEL'		=> 'Colours',
	'ACP_CLARTE_FORUMS_RESULT'		=> 'Result',
	'ACP_CLARTE_FORUMS_INHERIT'		=> 'Same as the parent forum (general colours)',
	'ACP_CLARTE_FORUMS_INHERIT_FROM'	=> 'Same as the parent forum (%s)',
	'ACP_CLARTE_FORUMS_GENERAL'		=> 'General colours (do not inherit)',
	'ACP_CLARTE_FORUMS_GENERAL_SHORT'	=> 'General colours',
	'ACP_CLARTE_FORUMS_FROM'		=> 'inherited from “%s”',
	'ACP_CLARTE_FORUMS_STYLE_NOTE'	=> '<strong>Important:</strong> colours only apply where the Clarté style (or a “Clarté – …” style) is used. In <a href="%s">Forums › Manage forums</a>, the <em>Forum style</em> setting of every forum and subforum must be <em>Default style</em> (or Clarté), and Clarté must be the board’s default style or the one chosen by the member.',
	'ACP_CLARTE_FORUMS_OTHER_STYLES'	=> [
		1	=> '%1$d forum forces a style other than Clarté: its colours will not change. Change its <em>Forum style</em> in <a href="%2$s">Forums › Manage forums</a>.',
		2	=> '%1$d forums force a style other than Clarté: their colours will not change. Change their <em>Forum style</em> in <a href="%2$s">Forums › Manage forums</a>.',
	],
	'ACP_CLARTE_FORUMS_FORCED_STYLE'	=> 'Forced style: %s — colours not applied',
	'ACP_CLARTE_FORUMS_SAVED'		=> 'Forum colours saved.',

	// Models offered as styles
	'ACP_CLARTE_MODELS_STYLES_EXPLAIN'	=> '<strong>Offer as a style</strong> creates a “Clarté – model name” style that every member can choose in their control panel (Board preferences). It has the Clarté layout with the model’s colours and follows changes to the model. <strong>Remove the style</strong> deletes it from the board’s styles: its members go back to the Clarté style.',
	'ACP_CLARTE_STYLE_PREFIX'		=> 'Clarté – ',
	'ACP_CLARTE_STYLE_ADD'			=> 'Offer as a style',
	'ACP_CLARTE_STYLE_REMOVE'		=> 'Remove the style',
	'ACP_CLARTE_STYLE_CREATED'		=> 'Style “%s” created: members can choose it in their control panel (Board preferences › My board style), unless the board overrides the user style.',
	'ACP_CLARTE_STYLE_DELETED'		=> [
		0	=> 'Style removed. No member was using it.',
		1	=> 'Style removed. %d member went back to the Clarté style.',
		2	=> 'Style removed. %d members went back to the Clarté style.',
	],
	'ACP_CLARTE_STYLE_DELETE_CONFIRM'	=> 'Remove the style of the model “%s”? It will be deleted from the board’s styles and its members will go back to the Clarté style. The model itself is kept.',
	'ACP_CLARTE_STYLE_NO_PARENT'	=> 'The Clarté style is not installed: install it first in Customise › Styles.',
	'ACP_CLARTE_STYLE_NOT_WRITABLE'	=> 'Cannot write to the board’s styles/ folder. Check its permissions (the server must be able to create a folder there).',
	'ACP_CLARTE_MODEL_STYLE_INFO'	=> [
		0	=> 'Optional style: %2$s (no member)',
		1	=> 'Optional style: %2$s (%1$d member)',
		2	=> 'Optional style: %2$s (%1$d members)',
	],
	'ACP_CLARTE_MODEL_FORUMS_INFO'	=> 'Forums: %s',
	'ACP_CLARTE_MODEL_USED_FORUMS'	=> 'This model colours the forums: %s. They will take their parent’s colours again.',
	'ACP_CLARTE_MODEL_USED_STYLE'	=> [
		0	=> 'The style “%2$s” will also be removed.',
		1	=> 'The style “%2$s” will also be removed; its member will go back to the Clarté style.',
		2	=> 'The style “%2$s” will also be removed; its %1$d members will go back to the Clarté style.',
	],
	'ACP_CLARTE_RESTORED_FORUMS'	=> [
		1	=> '%d forum got its colours back.',
		2	=> '%d forums got their colours back.',
	],
	'ACP_CLARTE_RESTORED_STYLES'	=> [
		1	=> '%d optional style recreated.',
		2	=> '%d optional styles recreated.',
	],
]);
