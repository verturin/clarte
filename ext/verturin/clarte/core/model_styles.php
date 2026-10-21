<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\core;

/**
 * Modèles proposés comme styles phpBB : chaque modèle peut devenir un style enfant de Clarté
 * (dossier styles/clarte_m<id>/, quelques fichiers relais), que les membres choisissent dans leur panneau.
 * Les couleurs ne sont pas écrites dans le style : l'extension les lit dans le modèle à chaque page.
 * Table modèle => style en JSON dans config_text (clé clarte_model_styles).
 */
class model_styles
{
	const KEY = 'clarte_model_styles';

	/** Préfixe des dossiers de styles générés */
	const PREFIX = 'clarte_m';

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $styles_table;

	/** @var string */
	protected $users_table;

	/** @var array|null */
	protected $map;

	public function __construct(\phpbb\config\db_text $config_text, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db,
		\phpbb\cache\driver\driver_interface $cache, $root_path, $styles_table, $users_table)
	{
		$this->config_text = $config_text;
		$this->config = $config;
		$this->db = $db;
		$this->cache = $cache;
		$this->root_path = $root_path;
		$this->styles_table = $styles_table;
		$this->users_table = $users_table;
	}

	/** model_id => style_id */
	public function map()
	{
		if ($this->map === null)
		{
			$map = $this->cache->get('_clarte_model_styles');
			if ($map === false)
			{
				$raw = $this->config_text->get(self::KEY);
				$data = $raw ? json_decode($raw, true) : [];
				$map = [];
				foreach (is_array($data) ? $data : [] as $model_id => $style_id)
				{
					if ((int) $model_id > 0 && (int) $style_id > 0)
					{
						$map[(int) $model_id] = (int) $style_id;
					}
				}
				$this->cache->put('_clarte_model_styles', $map);
			}
			$this->map = $map;
		}

		return $this->map;
	}

	protected function save(array $map)
	{
		ksort($map);
		$this->map = $map;
		$this->config_text->set(self::KEY, json_encode((object) $map));
		$this->cache->destroy('_clarte_model_styles');
	}

	/** Dossier (style_path) du style d'un modèle */
	public static function path_for($model_id)
	{
		return self::PREFIX . (int) $model_id;
	}

	/** Identifiant du modèle d'un style généré, 0 sinon */
	public static function model_id_from_path($path)
	{
		return preg_match('/^' . self::PREFIX . '(\d+)$/', (string) $path, $m) ? (int) $m[1] : 0;
	}

	/** Le style Clarté (parent des styles générés), ou null */
	public function clarte_style()
	{
		$sql = 'SELECT style_id, style_name, bbcode_bitfield, style_parent_tree
			FROM ' . $this->styles_table . "
			WHERE style_path = 'clarte'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: null;
	}

	/** Lignes des styles générés existants (style_id => ligne), avec le nombre de membres */
	public function rows()
	{
		$ids = array_values($this->map());
		if (!$ids)
		{
			return [];
		}
		$sql = 'SELECT s.style_id, s.style_name, s.style_path, s.style_active, COUNT(u.user_id) AS members
			FROM ' . $this->styles_table . ' s
			LEFT JOIN ' . $this->users_table . ' u ON (u.user_style = s.style_id)
			WHERE ' . $this->db->sql_in_set('s.style_id', $ids) . '
			GROUP BY s.style_id, s.style_name, s.style_path, s.style_active';
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[(int) $row['style_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Oublie les styles retirés ailleurs (ACP › Styles) et efface leur dossier.
	 * Renvoie le nombre de styles oubliés.
	 */
	public function sync()
	{
		$map = $this->map();
		if (!$map)
		{
			return 0;
		}
		$rows = $this->rows();
		$gone = 0;
		foreach ($map as $model_id => $style_id)
		{
			if (!isset($rows[$style_id]) || $rows[$style_id]['style_path'] !== self::path_for($model_id))
			{
				unset($map[$model_id]);
				$this->remove_dir(self::path_for($model_id));
				$gone++;
			}
		}
		if ($gone)
		{
			$this->save($map);
		}

		return $gone;
	}

	/**
	 * Crée le style d'un modèle. Renvoie une clé de langue d'erreur, ou '' si tout va bien.
	 *
	 * @param array  $model     modèle (id, name échappé)
	 * @param string $prefix    début du nom du style (« Clarté – »)
	 */
	public function create(array $model, $prefix)
	{
		if (isset($this->map()[$model['id']]))
		{
			return '';
		}
		$parent = $this->clarte_style();
		if (!$parent)
		{
			return 'ACP_CLARTE_STYLE_NO_PARENT';
		}
		$styles_dir = $this->root_path . 'styles/';
		if (!is_writable($styles_dir))
		{
			return 'ACP_CLARTE_STYLE_NOT_WRITABLE';
		}

		$path = self::path_for($model['id']);
		$name = $this->unique_style_name($prefix . $model['name']);
		if (!$this->write_files($path, htmlspecialchars_decode($name, ENT_COMPAT), htmlspecialchars_decode($parent['style_name'], ENT_COMPAT)))
		{
			$this->remove_dir($path);
			return 'ACP_CLARTE_STYLE_NOT_WRITABLE';
		}

		// Un ancien style au même dossier (resté après une erreur) est remplacé
		$sql = 'DELETE FROM ' . $this->styles_table . " WHERE style_path = '" . $this->db->sql_escape($path) . "'";
		$this->db->sql_query($sql);

		$tree = trim($parent['style_parent_tree'] . '/clarte', '/');
		$sql = 'INSERT INTO ' . $this->styles_table . ' ' . $this->db->sql_build_array('INSERT', [
			'style_name'		=> $name,
			'style_copyright'	=> 'Clarté',
			'style_active'		=> 1,
			'style_path'		=> $path,
			'bbcode_bitfield'	=> $parent['bbcode_bitfield'],
			'style_parent_id'	=> (int) $parent['style_id'],
			'style_parent_tree'	=> $tree,
		]);
		$this->db->sql_query($sql);
		$style_id = (int) $this->db->sql_nextid();

		$map = $this->map();
		$map[(int) $model['id']] = $style_id;
		$this->save($map);
		$this->cache->destroy('sql', $this->styles_table);

		return '';
	}

	/**
	 * Retire le style d'un modèle : ses membres (et le style par défaut s'il l'était) reviennent à Clarté,
	 * la ligne du style et son dossier sont effacés. Renvoie le nombre de membres déplacés.
	 */
	public function delete($model_id)
	{
		$map = $this->map();
		if (!isset($map[$model_id]))
		{
			return 0;
		}
		$style_id = $map[$model_id];
		$moved = $this->release_style($style_id);

		unset($map[$model_id]);
		$this->save($map);
		$this->remove_dir(self::path_for($model_id));

		return $moved;
	}

	/** Retire tous les styles générés */
	public function delete_all()
	{
		foreach (array_keys($this->map()) as $model_id)
		{
			$this->delete($model_id);
		}
	}

	/** Nouveau nom de modèle : le nom du style suit */
	public function rename($model_id, $name, $prefix)
	{
		$map = $this->map();
		if (!isset($map[$model_id]))
		{
			return;
		}
		$style_id = $map[$model_id];
		$name = $this->unique_style_name($prefix . $name, $style_id);
		$sql = 'UPDATE ' . $this->styles_table . " SET style_name = '" . $this->db->sql_escape($name) . "' WHERE style_id = " . (int) $style_id;
		$this->db->sql_query($sql);
		$parent = $this->clarte_style();
		$this->write_files(self::path_for($model_id), htmlspecialchars_decode($name, ENT_COMPAT), $parent ? htmlspecialchars_decode($parent['style_name'], ENT_COMPAT) : 'Clarté');
		$this->cache->destroy('sql', $this->styles_table);
	}

	/** Membres et style par défaut renvoyés vers Clarté, puis suppression de la ligne du style */
	protected function release_style($style_id)
	{
		$parent = $this->clarte_style();
		$fallback = $parent ? (int) $parent['style_id'] : (int) $this->config['default_style'];
		if ($fallback === (int) $style_id)
		{
			$fallback = 1;
		}

		$sql = 'UPDATE ' . $this->users_table . ' SET user_style = ' . $fallback . ' WHERE user_style = ' . (int) $style_id;
		$this->db->sql_query($sql);
		$moved = (int) $this->db->sql_affectedrows();

		if ((int) $this->config['default_style'] === (int) $style_id)
		{
			$this->config->set('default_style', $fallback);
		}

		$sql = 'DELETE FROM ' . $this->styles_table . ' WHERE style_id = ' . (int) $style_id;
		$this->db->sql_query($sql);
		$this->cache->destroy('sql', $this->styles_table);
		$this->cache->destroy('sql', $this->users_table);

		return $moved;
	}

	/** Nom de style unique (« Nom », « Nom (2) »…) */
	protected function unique_style_name($name, $except_id = 0)
	{
		$base = $name;
		for ($n = 2; ; $n++)
		{
			$sql = 'SELECT style_id FROM ' . $this->styles_table . "
				WHERE style_name = '" . $this->db->sql_escape($name) . "'
					AND style_id <> " . (int) $except_id;
			$result = $this->db->sql_query($sql);
			$taken = (bool) $this->db->sql_fetchfield('style_id');
			$this->db->sql_freeresult($result);
			if (!$taken)
			{
				return $name;
			}
			$name = $base . ' (' . $n . ')';
		}
	}

	/**
	 * Fichiers du style généré : style.cfg et relais vers les feuilles de Clarté
	 * (phpBB appelle stylesheet.css, la feuille de langue, bidi, plupload, tweaks et print dans le dossier du style choisi).
	 */
	protected function write_files($path, $name, $parent_name)
	{
		$dir = $this->root_path . 'styles/' . $path . '/';
		$files = [
			'style.cfg'					=> "#\n# Style généré par l'extension Clarté à partir d'un modèle.\n# Ne pas modifier : il est géré dans ACP › Extensions › Style Clarté › Modèles enregistrés.\n#\n"
				. 'name = ' . str_replace(["\r", "\n"], ' ', $name) . "\n"
				. "copyright = Clarté\n"
				. 'style_version = ' . (isset($this->config['clarte_version']) ? $this->config['clarte_version'] : '1.4.0') . "\n"
				. "phpbb_version = 3.3.15\n"
				. 'parent = ' . str_replace(["\r", "\n"], ' ', $parent_name) . "\n",
			'theme/stylesheet.css'		=> "/* Style généré par Clarté : feuilles de Clarté (les couleurs viennent du modèle) */\n@import url(\"../../clarte/theme/stylesheet.css\");\n",
		];
		foreach (['bidi', 'plupload', 'tweaks', 'print'] as $sheet)
		{
			$files['theme/' . $sheet . '.css'] = "@import url(\"../../clarte/theme/" . $sheet . ".css\");\n";
		}
		foreach (glob($this->root_path . 'styles/clarte/theme/*/stylesheet.css') ?: [] as $lang_sheet)
		{
			$lang = basename(dirname($lang_sheet));
			if (preg_match('/^[a-z0-9_\-]+$/i', $lang) && $lang !== 'fonts' && $lang !== 'images')
			{
				$files['theme/' . $lang . '/stylesheet.css'] = "@import url(\"../../../clarte/theme/" . $lang . "/stylesheet.css\");\n";
			}
		}

		foreach ($files as $file => $content)
		{
			$target = $dir . $file;
			if (!is_dir(dirname($target)) && !@mkdir(dirname($target), 0755, true))
			{
				return false;
			}
			if (@file_put_contents($target, $content) === false)
			{
				return false;
			}
		}

		return true;
	}

	/** Efface le dossier d'un style généré (seulement styles/clarte_m<n>/) */
	protected function remove_dir($path)
	{
		if (!self::model_id_from_path($path))
		{
			return;
		}
		$dir = $this->root_path . 'styles/' . $path;
		if (!is_dir($dir))
		{
			return;
		}
		$items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($items as $item)
		{
			$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
		}
		@rmdir($dir);
	}
}
