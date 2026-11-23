<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.5.1 : taille du texte en pourcentage (curseur de 80 à 150 %, de 1 en 1) au lieu de tailles nommées.
 * Conversion : small → 90, normal → 100, large → 110, xlarge → 125 (réglage du forum et choix des membres).
 */
class v151_text_percent extends \phpbb\db\migration\migration
{
	const MAP = ['small' => '90', 'normal' => '100', 'large' => '110', 'xlarge' => '125'];

	public function effectively_installed()
	{
		return isset($this->config['clarte_version']) && phpbb_version_compare($this->config['clarte_version'], '1.5.1', '>=');
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v150_text_size'];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'convert_sizes']]],
			['config.update', ['clarte_version', '1.5.1']],
		];
	}

	public function convert_sizes()
	{
		$current = isset($this->config['clarte_text_size']) ? (string) $this->config['clarte_text_size'] : 'normal';
		if (isset(self::MAP[$current]))
		{
			$this->config->set('clarte_text_size', self::MAP[$current]);
		}

		foreach (self::MAP as $old => $new)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "users
				SET user_clarte_text = '" . $this->db->sql_escape($new) . "'
				WHERE user_clarte_text = '" . $this->db->sql_escape($old) . "'";
			$this->db->sql_query($sql);
		}
	}
}
