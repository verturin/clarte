<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\migrations;

/**
 * 1.4.0 : couleurs par forum (un modèle par forum) et modèles proposés comme styles au choix des membres.
 * À la suppression des données de l'extension, les styles générés (styles/clarte_m<n>) sont retirés
 * et leurs membres reviennent au style Clarté.
 */
class v140_forum_styles extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['clarte_version']) && phpbb_version_compare($this->config['clarte_version'], '1.4.0', '>=');
	}

	public static function depends_on()
	{
		return ['\verturin\clarte\migrations\v130_models'];
	}

	public function update_data()
	{
		return [
			['config_text.add', ['clarte_forum_models', '{}']],
			['config_text.add', ['clarte_model_styles', '{}']],
			['module.add', ['acp', 'ACP_CLARTE_TITLE', [
				'module_basename'	=> '\verturin\clarte\acp\main_module',
				'modes'				=> ['forums'],
			]]],
			['config.update', ['clarte_version', '1.4.0']],
		];
	}

	public function revert_data()
	{
		return [
			['custom', [[$this, 'remove_generated_styles']]],
		];
	}

	/** Retire les styles générés : membres et style par défaut renvoyés vers Clarté (ou prosilver), lignes et dossiers effacés */
	public function remove_generated_styles()
	{
		$styles_table = $this->table_prefix . 'styles';
		$users_table = $this->table_prefix . 'users';

		$sql = 'SELECT style_id, style_path FROM ' . $styles_table;
		$result = $this->db->sql_query($sql);
		$fallback = 0;
		$prosilver = 0;
		$generated = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ($row['style_path'] === 'clarte')
			{
				$fallback = (int) $row['style_id'];
			}
			else if ($row['style_path'] === 'prosilver')
			{
				$prosilver = (int) $row['style_id'];
			}
			else if (preg_match('/^clarte_m\d+$/', $row['style_path']))
			{
				$generated[(int) $row['style_id']] = $row['style_path'];
			}
		}
		$this->db->sql_freeresult($result);
		$fallback = $fallback ?: ($prosilver ?: 1);

		if ($generated)
		{
			$ids = array_keys($generated);
			$this->db->sql_query('UPDATE ' . $users_table . ' SET user_style = ' . $fallback . ' WHERE ' . $this->db->sql_in_set('user_style', $ids));
			if (in_array((int) $this->config['default_style'], $ids, true))
			{
				$this->config->set('default_style', $fallback);
			}
			$this->db->sql_query('DELETE FROM ' . $styles_table . ' WHERE ' . $this->db->sql_in_set('style_id', $ids));
		}

		foreach (glob($this->phpbb_root_path . 'styles/clarte_m*', GLOB_ONLYDIR) ?: [] as $dir)
		{
			if (!preg_match('/\/clarte_m\d+$/', $dir))
			{
				continue;
			}
			$items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($items as $item)
			{
				$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
			}
			@rmdir($dir);
		}
	}
}
