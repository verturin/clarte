<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.1.0 : habillage de toute l'administration (ACP) aux couleurs de Clarté.
 */
class v110_admin_skin extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_acp']);
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v100_install'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_acp', 1]],
			['config.update', ['clarte_version', '1.1.0']],
		];
	}
}
