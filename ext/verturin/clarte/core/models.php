<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\core;

use verturin\clarte\event\listener;

/**
 * Modèles de réglages enregistrés (nom + couleurs et apparence).
 * Stockés en JSON dans config_text (clé clarte_models) : aucune table ajoutée.
 */
class models
{
	/** Clé config_text */
	const KEY = 'clarte_models';

	/** Nombre maximal de modèles */
	const MAX = 100;

	/** Longueur maximale d'un nom */
	const NAME_MAX = 60;

	/** Réglages qui forment un modèle (l'option ACP et le bouton Apparence restent propres au forum) */
	const KEYS = ['clarte_accent', 'clarte_header_from', 'clarte_header_to', 'clarte_cat_mode', 'clarte_cat_colour', 'clarte_default_mode', 'clarte_font', 'clarte_radius'];

	/** Tous les réglages de l'extension et leurs valeurs d'origine */
	const DEFAULTS = [
		'clarte_accent'			=> '#2563eb',
		'clarte_header_from'	=> '#0f172a',
		'clarte_header_to'		=> '#1e3a8a',
		'clarte_default_mode'	=> 'auto',
		'clarte_allow_toggle'	=> 1,
		'clarte_acp'			=> 1,
		'clarte_font'			=> 'inter',
		'clarte_radius'			=> 12,
		'clarte_cat_mode'		=> 'neutral',
		'clarte_cat_colour'		=> '#1e3a8a',
		'clarte_index_time'		=> 'both',
	];

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\cache\driver\driver_interface|null */
	protected $cache;

	/** @var array|null */
	protected $data;

	public function __construct(\phpbb\config\db_text $config_text, \phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->config_text = $config_text;
		$this->cache = $cache;
	}

	/**
	 * Vérifie et nettoie des réglages. Les clés inconnues sont ignorées ; une couleur invalide est signalée
	 * dans $errors (nom de la clé => valeur reçue) et remplacée par la valeur d'origine.
	 *
	 * @param array $in   valeurs reçues
	 * @param array $keys clés à garder
	 */
	public static function clean(array $in, array $keys, array &$errors = [])
	{
		$out = [];
		foreach ($keys as $key)
		{
			$default = self::DEFAULTS[$key];
			$value = array_key_exists($key, $in) && is_scalar($in[$key]) ? (string) $in[$key] : (string) $default;

			switch ($key)
			{
				case 'clarte_accent':
				case 'clarte_header_from':
				case 'clarte_header_to':
				case 'clarte_cat_colour':
					$colour = listener::colour($value);
					if ($colour === '')
					{
						$errors[$key] = $value;
						$colour = $default;
					}
					$out[$key] = $colour;
				break;

				case 'clarte_default_mode':
					$out[$key] = listener::mode($value);
				break;

				case 'clarte_cat_mode':
					$out[$key] = listener::cat_mode($value);
				break;

				case 'clarte_index_time':
					$out[$key] = listener::index_time($value);
				break;

				case 'clarte_font':
					$out[$key] = $value === 'system' ? 'system' : 'inter';
				break;

				case 'clarte_radius':
					$out[$key] = max(0, min(20, (int) $value));
				break;

				default:
					$out[$key] = (int) (bool) $value;
				break;
			}
		}

		return $out;
	}

	/**
	 * Nom nettoyé (espaces, longueur), stocké échappé comme le fait phpBB pour les textes saisis
	 * (request->variable). Accepte un nom brut (fichier importé) ou déjà échappé (formulaire).
	 * Chaîne vide si rien d'utilisable.
	 */
	public static function clean_name($name)
	{
		$name = htmlspecialchars_decode((string) $name, ENT_COMPAT);
		$name = trim(preg_replace('/[\x00-\x1F\x7F]+|\s+/u', ' ', $name));
		$name = utf8_substr($name, 0, self::NAME_MAX);

		return htmlspecialchars($name, ENT_COMPAT, 'UTF-8');
	}

	/** Nom en texte brut (exports) */
	public static function plain_name($name)
	{
		return htmlspecialchars_decode((string) $name, ENT_COMPAT);
	}

	/** Tous les modèles, triés par nom */
	public function all()
	{
		$items = $this->load()['items'];
		usort($items, function ($a, $b) {
			return strnatcasecmp($a['name'], $b['name']);
		});

		return $items;
	}

	public function count()
	{
		return count($this->load()['items']);
	}

	/** Un modèle, ou null */
	public function get($id)
	{
		foreach ($this->load()['items'] as $item)
		{
			if ($item['id'] === (int) $id)
			{
				return $item;
			}
		}

		return null;
	}

	/** Ajoute un modèle ; renvoie son identifiant, ou 0 si la limite est atteinte */
	public function add($name, array $settings)
	{
		$data = $this->load();
		if (count($data['items']) >= self::MAX)
		{
			return 0;
		}

		$id = $data['next_id']++;
		$data['items'][] = [
			'id'		=> $id,
			'name'		=> $this->unique_name(self::clean_name($name), $data['items']),
			'created'	=> time(),
			'updated'	=> time(),
			'settings'	=> self::clean($settings, self::KEYS),
		];
		$this->save($data);

		return $id;
	}

	/** Modifie le nom et les réglages d'un modèle */
	public function update($id, $name, array $settings)
	{
		$data = $this->load();
		foreach ($data['items'] as $i => $item)
		{
			if ($item['id'] === (int) $id)
			{
				$others = $data['items'];
				unset($others[$i]);
				$data['items'][$i]['name'] = $this->unique_name(self::clean_name($name), $others);
				$data['items'][$i]['settings'] = self::clean($settings, self::KEYS);
				$data['items'][$i]['updated'] = time();
				$this->save($data);

				return true;
			}
		}

		return false;
	}

	public function delete($id)
	{
		$data = $this->load();
		$before = count($data['items']);
		$data['items'] = array_values(array_filter($data['items'], function ($item) use ($id) {
			return $item['id'] !== (int) $id;
		}));
		if (count($data['items']) === $before)
		{
			return false;
		}
		$this->save($data);

		return true;
	}

	/** Supprime tous les modèles */
	public function clear()
	{
		$this->save(['next_id' => $this->load()['next_id'], 'items' => []]);
	}

	/**
	 * Ajoute des modèles venant d'un fichier (export ou sauvegarde).
	 * Renvoie les identifiants créés, indexés comme la liste reçue.
	 */
	public function import(array $list)
	{
		$ids = [];
		foreach ($list as $index => $entry)
		{
			if (!is_array($entry) || !isset($entry['name'], $entry['settings']) || !is_array($entry['settings']))
			{
				continue;
			}
			$name = self::clean_name($entry['name']);
			if ($name === '')
			{
				continue;
			}
			$id = $this->add($name, $entry['settings']);
			if (!$id)
			{
				break;
			}
			$ids[$index] = $id;
		}

		return $ids;
	}

	/** Modèles prêts à exporter (sans identifiant interne) */
	public static function export_list(array $items)
	{
		return array_map(function ($item) {
			return [
				'name'		=> self::plain_name($item['name']),
				'created'	=> gmdate('c', $item['created']),
				'updated'	=> gmdate('c', $item['updated']),
				'settings'	=> $item['settings'],
			];
		}, array_values($items));
	}

	/** Évite deux modèles du même nom : « Nom (2) », « Nom (3) »… */
	protected function unique_name($name, array $items)
	{
		$names = array_map(function ($item) {
			return utf8_strtolower($item['name']);
		}, $items);

		$base = self::plain_name($name);
		for ($n = 2; in_array(utf8_strtolower($name), $names, true); $n++)
		{
			$suffix = ' (' . $n . ')';
			$name = self::clean_name(utf8_substr($base, 0, self::NAME_MAX - utf8_strlen($suffix)) . $suffix);
		}

		return $name;
	}

	protected function load()
	{
		if ($this->data === null && $this->cache && ($cached = $this->cache->get('_clarte_models')) !== false)
		{
			$this->data = $cached;
		}
		if ($this->data === null)
		{
			$raw = $this->config_text->get(self::KEY);
			$data = $raw ? json_decode($raw, true) : null;
			$this->data = ['next_id' => 1, 'items' => []];

			if (is_array($data) && isset($data['items']) && is_array($data['items']))
			{
				foreach ($data['items'] as $item)
				{
					if (!is_array($item) || !isset($item['id'], $item['name'], $item['settings']) || !is_array($item['settings']))
					{
						continue;
					}
					$this->data['items'][] = [
						'id'		=> (int) $item['id'],
						'name'		=> self::clean_name($item['name']),
						'created'	=> isset($item['created']) ? (int) $item['created'] : time(),
						'updated'	=> isset($item['updated']) ? (int) $item['updated'] : time(),
						'settings'	=> self::clean($item['settings'], self::KEYS),
					];
				}
				$max = $this->data['items'] ? max(array_column($this->data['items'], 'id')) : 0;
				$this->data['next_id'] = max($max + 1, isset($data['next_id']) ? (int) $data['next_id'] : 1);
			}
			if ($this->cache)
			{
				$this->cache->put('_clarte_models', $this->data);
			}
		}

		return $this->data;
	}

	protected function save(array $data)
	{
		$this->data = $data;
		$this->config_text->set(self::KEY, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		if ($this->cache)
		{
			$this->cache->destroy('_clarte_models');
		}
	}
}
