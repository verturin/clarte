<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.3.0 : réglage « Date sur l'accueil », modèles de réglages enregistrés, pages « Modèles enregistrés » et « Sauvegarde et restauration ».
 */
class v130_models extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_version']) && phpbb_version_compare($this->config['clarte_version'], '1.3.0', '>=');
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v120_category_colour'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_index_time', 'both']],
			['config_text.add', ['clarte_models', '{"next_id":1,"items":[]}']],
			['module.add', ['acp', 'ACP_CLARTE_TITLE', [
				'module_basename'	=> '\verturin\clarte\acp\main_module',
				'modes'				=> ['models', 'backup'],
			]]],
			['config.update', ['clarte_version', '1.3.0']],
		];
	}
}
