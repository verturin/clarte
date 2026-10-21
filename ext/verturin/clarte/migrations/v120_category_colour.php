<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.2.0 : couleur du bandeau des catégories et des listes de sujets (.forabg / .forumbg).
 */
class v120_category_colour extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_cat_mode']);
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v110_admin_skin'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_cat_mode', 'neutral']],
			['config.add', ['clarte_cat_colour', '#1e3a8a']],
			['config.update', ['clarte_version', '1.2.0']],
		];
	}
}
