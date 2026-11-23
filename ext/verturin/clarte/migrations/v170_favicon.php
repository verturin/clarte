<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.7.0 : icône du site (favicon ICO ou PNG, version pour le mode sombre, icône de l'écran d'accueil).
 */
class v170_favicon extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_favicon']);
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v160_backgrounds'];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_favicon', '']],
			['config.add', ['clarte_favicon_dark', '']],
			['config.add', ['clarte_touch_icon', '']],
			['config.update', ['clarte_version', '1.7.0']],
		];
	}
}
