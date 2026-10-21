<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

class v100_install extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_version']) && phpbb_version_compare($this->config['clarte_version'], '1.0.0', '>=');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_version', '1.0.0']],
			['config.add', ['clarte_accent', '#2563eb']],
			['config.add', ['clarte_header_from', '#0f172a']],
			['config.add', ['clarte_header_to', '#1e3a8a']],
			['config.add', ['clarte_default_mode', 'auto']],
			['config.add', ['clarte_allow_toggle', 1]],
			['config.add', ['clarte_font', 'inter']],
			['config.add', ['clarte_radius', 12]],
			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_CLARTE_TITLE']],
			['module.add', ['acp', 'ACP_CLARTE_TITLE', [
				'module_basename'	=> '\verturin\clarte\acp\main_module',
				'modes'				=> ['settings'],
			]]],
		];
	}
}
