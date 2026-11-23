<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.5.0 : taille du texte (réglage de l'ACP) et choix de chaque membre dans son panneau, si l'ACP l'autorise.
 */
class v150_text_size extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_version']) && phpbb_version_compare($this->config['clarte_version'], '1.5.0', '>=');
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v140_forum_styles'];
	}

	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_clarte_text'	=> ['VCHAR:10', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'users'	=> [
					'user_clarte_text',
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['clarte_text_size', 'normal']],
			['config.add', ['clarte_text_allow', 0]],
			['config.update', ['clarte_version', '1.5.0']],
		];
	}
}
