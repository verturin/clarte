<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\core;

/**
 * Couleurs par forum : un modèle choisi pour un forum (ou une catégorie) s'applique à ses pages
 * (forum, sujets, rédaction) et à ses sous-forums, sauf choix contraire d'un sous-forum.
 * Stocké en JSON dans config_text (clé clarte_forum_models) : { "forum_id": model_id, … }.
 * model_id = -1 : « couleurs générales » (arrête l'héritage du parent).
 */
class forum_models
{
	const KEY = 'clarte_forum_models';

	/** Valeur qui arrête l'héritage : couleurs générales du forum */
	const GENERAL = -1;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var string */
	protected $forums_table;

	/** @var array|null */
	protected $map;

	public function __construct(\phpbb\config\db_text $config_text, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $forums_table)
	{
		$this->config_text = $config_text;
		$this->db = $db;
		$this->cache = $cache;
		$this->forums_table = $forums_table;
	}

	/** forum_id => model_id (ou -1) */
	public function map()
	{
		if ($this->map === null)
		{
			$map = $this->cache->get('_clarte_forum_models');
			if ($map === false)
			{
				$raw = $this->config_text->get(self::KEY);
				$data = $raw ? json_decode($raw, true) : [];
				$map = [];
				foreach (is_array($data) ? $data : [] as $forum_id => $model_id)
				{
					if ((int) $forum_id > 0 && ((int) $model_id > 0 || (int) $model_id === self::GENERAL))
					{
						$map[(int) $forum_id] = (int) $model_id;
					}
				}
				$this->cache->put('_clarte_forum_models', $map);
			}
			$this->map = $map;
		}

		return $this->map;
	}

	/** Remplace toute la table (valeurs 0 ignorées = « hériter ») */
	public function save(array $map)
	{
		$clean = [];
		foreach ($map as $forum_id => $model_id)
		{
			if ((int) $forum_id > 0 && ((int) $model_id > 0 || (int) $model_id === self::GENERAL))
			{
				$clean[(int) $forum_id] = (int) $model_id;
			}
		}
		ksort($clean);
		$this->map = $clean;
		$this->config_text->set(self::KEY, json_encode((object) $clean));
		$this->cache->destroy('_clarte_forum_models');
	}

	/** Un modèle supprimé : les forums qui l'utilisaient héritent de nouveau */
	public function remove_model($model_id)
	{
		$map = $this->map();
		$before = count($map);
		$map = array_filter($map, function ($id) use ($model_id) {
			return $id !== (int) $model_id;
		});
		if (count($map) !== $before)
		{
			$this->save($map);
		}

		return $before - count($map);
	}

	/** Forums qui utilisent directement un modèle */
	public function forums_of($model_id)
	{
		return array_keys(array_filter($this->map(), function ($id) use ($model_id) {
			return $id === (int) $model_id;
		}));
	}

	/**
	 * Modèle à appliquer à un forum : le sien, sinon celui du parent le plus proche.
	 * 0 si aucun (ou « couleurs générales »).
	 */
	public function resolve($forum_id)
	{
		$forum_id = (int) $forum_id;
		$map = $this->map();
		if ($forum_id <= 0 || !$map)
		{
			return 0;
		}
		if (isset($map[$forum_id]))
		{
			return max(0, $map[$forum_id]);
		}

		foreach ($this->ancestors($forum_id) as $parent_id)
		{
			if (isset($map[$parent_id]))
			{
				return max(0, $map[$parent_id]);
			}
		}

		return 0;
	}

	/** Parents d'un forum, du plus proche au plus éloigné */
	protected function ancestors($forum_id)
	{
		$sql = 'SELECT f2.forum_id
			FROM ' . $this->forums_table . ' f1, ' . $this->forums_table . ' f2
			WHERE f1.forum_id = ' . (int) $forum_id . '
				AND f2.left_id < f1.left_id
				AND f2.right_id > f1.right_id
			ORDER BY f2.left_id DESC';
		$result = $this->db->sql_query($sql, 600);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['forum_id'];
		}
		$this->db->sql_freeresult($result);

		return $ids;
	}

	/** Tous les forums dans l'ordre de l'arbre, avec leur profondeur */
	public function tree()
	{
		$sql = 'SELECT forum_id, parent_id, forum_name, forum_type, forum_style, left_id, right_id
			FROM ' . $this->forums_table . '
			ORDER BY left_id ASC';
		$result = $this->db->sql_query($sql);
		$forums = [];
		$stack = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			while ($stack && end($stack) < (int) $row['left_id'])
			{
				array_pop($stack);
			}
			$row['depth'] = count($stack);
			$stack[] = (int) $row['right_id'];
			$forums[] = $row;
		}
		$this->db->sql_freeresult($result);

		return $forums;
	}
}
