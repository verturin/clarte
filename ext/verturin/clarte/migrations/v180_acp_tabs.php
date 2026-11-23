<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.8.0 : onglets de la rubrique en cours en haut des pages de l'ACP (option clarte_acp_tabs, activée).
 */
class v180_acp_tabs extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_acp_tabs']);
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v170_favicon'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_acp_tabs', 1]],
			['config.update', ['clarte_version', '1.8.0']],
		];
	}
}
