<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.6.0 : images de fond (page et en-tête), rangées dans images/clarte/ ; colonne user_clarte_bg (le membre masque les images).
 */
class v160_backgrounds extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_bg_image']);
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v151_text_percent'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'users' => [
					'user_clarte_bg' => ['TINT:1', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'users' => ['user_clarte_bg'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_bg_image', '']],
			['config.add', ['clarte_bg_image_dark', '']],
			['config.add', ['clarte_bg_mode', 'cover']],
			['config.add', ['clarte_bg_position', 'center']],
			['config.add', ['clarte_bg_fixed', 1]],
			['config.add', ['clarte_bg_overlay', 30]],
			['config.add', ['clarte_bg_blur', 0]],
			['config.add', ['clarte_bg_glass', 0]],
			['config.add', ['clarte_bg_mobile', 1]],
			['config.add', ['clarte_header_image', '']],
			['config.add', ['clarte_header_image_dark', '']],
			['config.add', ['clarte_header_position', 'center']],
			['config.add', ['clarte_header_overlay', 40]],
			['config.add', ['clarte_bg_allow', 0]],
			['config.update', ['clarte_version', '1.6.0']],
		];
	}

	/** Désinstallation : les images envoyées sont effacées avec leur dossier */
	public function revert_data()
	{
		return [
			['custom', [[$this, 'remove_images']]],
		];
	}

	public function remove_images()
	{
		$dir = $this->phpbb_root_path . 'images/clarte/';
		if (!is_dir($dir))
		{
			return;
		}
		foreach ((array) scandir($dir) as $file)
		{
			if (preg_match('/^([0-9a-f]{16}\.(jpg|png|gif|webp|ico)|index\.htm)$/', $file))
			{
				@unlink($dir . $file);
			}
		}
		@rmdir($dir);
	}
}
