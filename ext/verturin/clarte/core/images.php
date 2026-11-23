<?php
/**
 * Clarté — réglages du style
 *
 * @copyright (c) 2026 verturin
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace verturin\clarte\core;

/**
 * Images de fond (1.6.0) : rangées dans images/clarte/ à la racine du forum, hors du dossier de l'extension
 * (une mise à jour de l'extension ne les efface pas). Nom = empreinte du contenu + extension : une même image
 * n'est jamais stockée deux fois, et le nom change quand l'image change (pas de cache périmé).
 */
class images
{
	/** Dossier, depuis la racine du forum */
	const DIR = 'images/clarte/';

	/** Taille maximale d'une image (octets) */
	const MAX_SIZE = 3145728;

	/** Largeur ou hauteur maximale (pixels) */
	const MAX_DIM = 8000;

	/** Types acceptés et extension utilisée */
	const TYPES = [
		IMAGETYPE_JPEG	=> 'jpg',
		IMAGETYPE_PNG	=> 'png',
		IMAGETYPE_GIF	=> 'gif',
		IMAGETYPE_WEBP	=> 'webp',
		IMAGETYPE_ICO	=> 'ico',
	];

	/** @var string */
	protected $root_path;

	public function __construct($root_path)
	{
		$this->root_path = $root_path;
	}

	/** Nom d'image valide (« 0123456789abcdef.jpg »), sinon chaîne vide */
	public static function valid_name($value)
	{
		$value = strtolower(trim((string) $value));

		return preg_match('/^[0-9a-f]{16}\.(jpg|png|gif|webp|ico)$/', $value) ? $value : '';
	}

	/** Chemin du dossier sur le disque */
	public function dir()
	{
		return $this->root_path . self::DIR;
	}

	public function exists($name)
	{
		$name = self::valid_name($name);

		return $name !== '' && is_file($this->dir() . $name);
	}

	/** Toutes les images du dossier : nom => [size, width, height, time], des plus récentes aux plus anciennes */
	public function all()
	{
		$list = [];
		foreach (is_dir($this->dir()) ? (array) scandir($this->dir()) : [] as $file)
		{
			if (self::valid_name($file) !== $file)
			{
				continue;
			}
			$path = $this->dir() . $file;
			$info = @getimagesize($path);
			$list[$file] = [
				'size'		=> (int) filesize($path),
				'width'		=> $info ? (int) $info[0] : 0,
				'height'	=> $info ? (int) $info[1] : 0,
				'time'		=> (int) filemtime($path),
			];
		}
		uasort($list, function ($a, $b) {
			return $b['time'] - $a['time'];
		});

		return $list;
	}

	/**
	 * Image envoyée par un formulaire (tableau de request->file()).
	 * $types : extensions acceptées pour cet emplacement (models::IMAGE_TYPES).
	 * Renvoie le nom de l'image, '' si aucun fichier, ou false avec $error (clé de langue).
	 */
	public function store_upload($file, &$error, array $types = [])
	{
		if (!is_array($file) || empty($file['name']) || (isset($file['error']) && (int) $file['error'] === UPLOAD_ERR_NO_FILE))
		{
			return '';
		}
		if (!empty($file['error']) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name']))
		{
			$error = in_array((int) $file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? 'ACP_CLARTE_IMAGE_TOO_BIG' : 'ACP_CLARTE_IMAGE_UPLOAD_FAILED';
			return false;
		}
		if ((int) $file['size'] > self::MAX_SIZE)
		{
			$error = 'ACP_CLARTE_IMAGE_TOO_BIG';
			return false;
		}

		return $this->store_data((string) file_get_contents($file['tmp_name']), $error, $types);
	}

	/** Enregistre une image à partir de son contenu ; renvoie son nom, ou false avec $error */
	public function store_data($data, &$error, array $types = [])
	{
		if ($data === '' || strlen($data) > self::MAX_SIZE)
		{
			$error = $data === '' ? 'ACP_CLARTE_IMAGE_INVALID' : 'ACP_CLARTE_IMAGE_TOO_BIG';
			return false;
		}
		$info = @getimagesizefromstring($data);
		if (!$info || !isset(self::TYPES[$info[2]]) || $info[0] < 1 || $info[1] < 1)
		{
			$error = 'ACP_CLARTE_IMAGE_INVALID';
			return false;
		}
		if ($types && !in_array(self::TYPES[$info[2]], $types, true))
		{
			$error = 'ACP_CLARTE_IMAGE_WRONG_TYPE';
			return false;
		}
		if ($info[0] > self::MAX_DIM || $info[1] > self::MAX_DIM)
		{
			$error = 'ACP_CLARTE_IMAGE_TOO_LARGE';
			return false;
		}

		$name = substr(hash('sha256', $data), 0, 16) . '.' . self::TYPES[$info[2]];
		$dir = $this->dir();
		if (!is_dir($dir))
		{
			@mkdir($dir, 0755, true);
			@file_put_contents($dir . 'index.htm', '');
		}
		if (!is_file($dir . $name) && @file_put_contents($dir . $name, $data) === false)
		{
			$error = 'ACP_CLARTE_IMAGE_NOT_WRITABLE';
			return false;
		}

		return $name;
	}

	public function delete($name)
	{
		$name = self::valid_name($name);

		return $name !== '' && is_file($this->dir() . $name) && @unlink($this->dir() . $name);
	}

	/** Contenu des images citées, en base64, pour un fichier de sauvegarde ou d'export : nom => données */
	public function encode(array $names)
	{
		$out = [];
		foreach (array_unique($names) as $name)
		{
			if ($this->exists($name))
			{
				$out[$name] = base64_encode((string) file_get_contents($this->dir() . $name));
			}
		}
		ksort($out);

		return $out;
	}

	/**
	 * Enregistre les images d'un fichier importé. Renvoie l'ancien nom => le nom enregistré
	 * (recalculé à partir du contenu) ; les images refusées sont absentes.
	 */
	public function decode($list, &$failed = 0)
	{
		$map = [];
		foreach (is_array($list) ? $list : [] as $name => $data)
		{
			$name = self::valid_name($name);
			$bytes = is_string($data) ? base64_decode($data, true) : false;
			$error = '';
			$stored = $name !== '' && $bytes !== false ? $this->store_data($bytes, $error) : false;
			if ($stored)
			{
				$map[$name] = $stored;
			}
			else
			{
				$failed++;
			}
		}

		return $map;
	}

	/** Remplace dans des réglages les noms d'images importées ; une image absente d'ici est retirée */
	public function relink(array $settings, array $map)
	{
		foreach (models::IMAGE_KEYS as $key)
		{
			if (!empty($settings[$key]) && is_string($settings[$key]))
			{
				$name = self::valid_name($settings[$key]);
				$settings[$key] = isset($map[$name]) ? $map[$name] : ($this->exists($name) ? $name : '');
			}
		}

		return $settings;
	}
}
